<?php
require_once __DIR__ . '/../model/VideoCall.php';
require_once __DIR__ . '/../model/Conversation.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/FormValidator.php';

class VideoCallController {
    private VideoCall $videoCallModel;
    private Conversation $conversationModel;
    private PDO $pdo;

    public function __construct() {
        $this->videoCallModel = new VideoCall();
        $this->conversationModel = new Conversation();
        $this->pdo = Database::getInstance()->getConnection();
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────
    private function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    private function getUserId(): int {
        $this->startSession();
        return (int)($_SESSION['id_user'] ?? 0);
    }

    private function jsonResponse(array $data, int $code = 200): void {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);
        echo json_encode($data);
        exit;
    }

    // ─── Créer un appel vidéo ───────────────────────────────────────────────
    public function initiate(): void {
        $this->startSession();
        $id_user = $this->getUserId();
        $id_conversation = isset($_POST['id_conversation']) ? (int)$_POST['id_conversation'] : 0;
        $type_appel = isset($_POST['type_appel']) ? $_POST['type_appel'] : '1to1';
        $force_new = isset($_POST['force_new']) ? (bool)$_POST['force_new'] : false;

        // VALIDATION: Vérifier les données obligatoires
        $errors = [];
        if ($id_user <= 0) {
            $errors[] = 'Utilisateur non identifié';
        }
        if ($id_conversation <= 0) {
            $errors[] = 'Conversation invalide';
        }
        if (!in_array($type_appel, ['1to1', 'groupe'])) {
            $errors[] = 'Type d\'appel invalide';
        }
        
        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'message' => implode(', ', $errors), 'errors' => $errors], 400);
        }

        // Vérifier que l'utilisateur appartient à la conversation
        $conversation = $this->conversationModel->getById($id_conversation);
        if (!$conversation || 
            ($conversation['id_user1'] != $id_user && $conversation['id_user2'] != $id_user)) {
            $this->jsonResponse(['success' => false, 'message' => 'Accès refusé à cette conversation'], 403);
        }

        // AMÉLIORATION: Détecter et nettoyer les appels "zombies" (stagnants depuis >1 min)
        // 1. Nettoyer TOUS les appels de plus de 1 minute (même s'ils ont le bon statut)
        $this->cleanupZombieCalls();
        
        // 2. Vérifier s'il existe un appel actif RÉCENT (< 1 min)
        $activeCall = $this->videoCallModel->getActiveByConversation($id_conversation);
        
        if ($activeCall) {
            // Calculer l'âge de l'appel (utiliser date_debut ou date_creation)
            $dateDebut = $activeCall['date_debut'] ?? $activeCall['created_at'] ?? null;
            
            if (!$dateDebut) {
                // Pas de date début, l'appel est vraiment stagnant
                error_log("[VideoCall] ⚠️  Appel sans date_debut détecté - ID: {$activeCall['id_video_call']}");
                $this->videoCallModel->endCall($activeCall['id_video_call']);
            } else {
                $ageAppel = time() - strtotime($dateDebut);
                
                // Si appel existe depuis moins d'1 minute ET pas force_new
                if ($ageAppel < 60 && !$force_new) {
                    // C'est un appel VRAIMENT actif - refuser
                    error_log("[VideoCall] ❌ Appel actif refusé (age: {$ageAppel}s) - ID: {$activeCall['id_video_call']}");
                    $this->jsonResponse([
                        'success' => false, 
                        'message' => '📞 Un appel est déjà en cours. Attendez la fin ou réessayez (Force).',
                        'id_video_call' => $activeCall['id_video_call'],
                        'age_seconds' => $ageAppel,
                        'can_force_retry' => true
                    ], 409);
                }
                
                // Si force_new, terminer l'appel existant
                if ($force_new && $ageAppel > 0) {
                    error_log("[VideoCall] 🔄 Forçage d'un nouvel appel (age: {$ageAppel}s) - Ancien ID: {$activeCall['id_video_call']}");
                    $this->videoCallModel->endCall($activeCall['id_video_call']);
                }
            }
        }

        // Créer le nouvel appel
        $id_video_call = $this->videoCallModel->create($id_conversation, $id_user, $type_appel);

        // Ajouter les participants
        if ($type_appel === '1to1') {
            $other_user_id = ($conversation['id_user1'] == $id_user) ? $conversation['id_user2'] : $conversation['id_user1'];
            $this->videoCallModel->addParticipant($id_video_call, $other_user_id, 'en_attente');
        }
        
        // Ajouter l'initiateur comme participant
        $this->videoCallModel->addParticipant($id_video_call, $id_user, 'accepte');

        $this->jsonResponse([
            'success' => true,
            'message' => 'Appel vidéo initialisé',
            'id_video_call' => $id_video_call
        ]);
    }

    // ─── Accepter un appel vidéo ────────────────────────────────────────────
    public function accept(): void {
        $this->startSession();
        $id_user = $this->getUserId();
        
        // VALIDATION: Utiliser FormValidator
        $validator = new FormValidator($_POST);
        $validator
            ->addRule('id_video_call', 'required', 'L\'ID de l\'appel est obligatoire')
            ->addRule('id_video_call', 'positive', 'L\'ID de l\'appel doit être un nombre positif');

        if (!$validator->validate()) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->getErrors()
            ], 400);
        }

        if ($id_user <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Utilisateur non identifié'], 403);
        }

        $id_video_call = (int)$validator->get('id_video_call');

        // Vérifier que l'utilisateur est participant
        $call = $this->videoCallModel->getById($id_video_call);
        if (!$call) {
            $this->jsonResponse(['success' => false, 'message' => 'Appel non trouvé'], 404);
        }

        $participants = $this->videoCallModel->getParticipants($id_video_call);
        $isParticipant = array_filter($participants, fn($p) => $p['id_user'] == $id_user);
        
        if (empty($isParticipant)) {
            $this->jsonResponse(['success' => false, 'message' => 'Vous n\'êtes pas participant'], 403);
        }

        if ($this->videoCallModel->acceptCall($id_video_call, $id_user)) {
            $this->jsonResponse([
                'success' => true,
                'message' => 'Appel accepté'
            ]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de l\'acceptation'], 500);
        }
    }

    // ─── Rejeter un appel vidéo ─────────────────────────────────────────────
    public function reject(): void {
        $this->startSession();
        $id_user = $this->getUserId();
        
        // VALIDATION
        $validator = new FormValidator($_POST);
        $validator
            ->addRule('id_video_call', 'required', 'L\'ID de l\'appel est obligatoire')
            ->addRule('id_video_call', 'positive', 'L\'ID de l\'appel doit être un nombre positif');

        if (!$validator->validate()) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->getErrors()
            ], 400);
        }

        if ($id_user <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Utilisateur non identifié'], 403);
        }

        $id_video_call = (int)$validator->get('id_video_call');

        if ($this->videoCallModel->rejectCall($id_video_call, $id_user)) {
            $this->jsonResponse(['success' => true, 'message' => 'Appel rejeté']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Erreur lors du rejet'], 500);
        }
    }

    // ─── Terminer un appel vidéo ────────────────────────────────────────────
    public function end(): void {
        $this->startSession();
        $id_user = $this->getUserId();
        
        // VALIDATION
        $validator = new FormValidator($_POST);
        $validator
            ->addRule('id_video_call', 'required', 'L\'ID de l\'appel est obligatoire')
            ->addRule('id_video_call', 'positive', 'L\'ID de l\'appel doit être un nombre positif');

        if (!$validator->validate()) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->getErrors()
            ], 400);
        }

        if ($id_user <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Utilisateur non identifié'], 403);
        }

        $id_video_call = (int)$validator->get('id_video_call');

        // Marquer l'utilisateur comme déconnecté
        $this->videoCallModel->markParticipantDisconnected($id_video_call, $id_user);

        $this->jsonResponse(['success' => true, 'message' => 'Appel terminé']);
    }

    // ─── Obtenir les participants d'un appel ────────────────────────────────
    public function getParticipants(): void {
        $this->startSession();
        $id_user = $this->getUserId();
        $id_video_call = isset($_GET['id_video_call']) ? (int)$_GET['id_video_call'] : 0;

        if ($id_user <= 0 || $id_video_call <= 0) {
            $this->jsonResponse(['success' => false, 'participants' => []], 400);
        }

        $participants = $this->videoCallModel->getParticipants($id_video_call);
        $this->jsonResponse([
            'success' => true,
            'participants' => $participants
        ]);
    }

    // ─── Obtenir les détails d'un appel ──────────────────────────────────────
    public function getCall(): void {
        $this->startSession();
        $id_user = $this->getUserId();
        $id_video_call = isset($_GET['id_video_call']) ? (int)$_GET['id_video_call'] : 0;

        if ($id_user <= 0 || $id_video_call <= 0) {
            $this->jsonResponse(['success' => false, 'call' => null], 400);
        }

        $call = $this->videoCallModel->getById($id_video_call);
        if (!$call) {
            $this->jsonResponse(['success' => false, 'message' => 'Appel non trouvé'], 404);
        }

        $this->jsonResponse([
            'success' => true,
            'call' => $call
        ]);
    }

    // ─── Obtenir l'appel actif d'une conversation ────────────────────────────
    public function getActive(): void {
        $this->startSession();
        $id_user = $this->getUserId();
        $id_conversation = isset($_GET['id_conversation']) ? (int)$_GET['id_conversation'] : 0;

        if ($id_user <= 0 || $id_conversation <= 0) {
            $this->jsonResponse(['success' => false, 'call' => null], 400);
        }

        // Vérifier l'accès
        $conversation = $this->conversationModel->getById($id_conversation);
        if (!$conversation || 
            ($conversation['id_user1'] != $id_user && $conversation['id_user2'] != $id_user)) {
            $this->jsonResponse(['success' => false, 'message' => 'Accès refusé'], 403);
        }

        $call = $this->videoCallModel->getActiveByConversation($id_conversation);
        $this->jsonResponse([
            'success' => true,
            'call' => $call
        ]);
    }

    // ─── Obtenir l'historique des appels ────────────────────────────────────
    public function getHistory(): void {
        $this->startSession();
        $id_user = $this->getUserId();
        $id_conversation = isset($_GET['id_conversation']) ? (int)$_GET['id_conversation'] : 0;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

        if ($id_user <= 0 || $id_conversation <= 0) {
            $this->jsonResponse(['success' => false, 'history' => []], 400);
        }

        $conversation = $this->conversationModel->getById($id_conversation);
        if (!$conversation || 
            ($conversation['id_user1'] != $id_user && $conversation['id_user2'] != $id_user)) {
            $this->jsonResponse(['success' => false, 'message' => 'Accès refusé'], 403);
        }

        $history = $this->videoCallModel->getHistoryByConversation($id_conversation, $limit, $offset);
        $this->jsonResponse([
            'success' => true,
            'history' => $history
        ]);
    }

    // ─── Obtenir les appels manqués ──────────────────────────────────────────
    public function getMissedCalls(): void {
        $this->startSession();
        $id_user = $this->getUserId();

        if ($id_user <= 0) {
            $this->jsonResponse(['success' => false, 'missed_calls' => []], 400);
        }

        $missedCalls = $this->videoCallModel->getMissedCalls($id_user);
        $this->jsonResponse([
            'success' => true,
            'missed_calls' => $missedCalls
        ]);
    }

    // ─── Obtenir les statistiques utilisateur ────────────────────────────────
    public function getUserStats(): void {
        $this->startSession();
        $id_user = $this->getUserId();

        if ($id_user <= 0) {
            $this->jsonResponse(['success' => false, 'stats' => []], 400);
        }

        $stats = $this->videoCallModel->getUserStats($id_user);
        $this->jsonResponse([
            'success' => true,
            'stats' => $stats
        ]);
    }

    // ─── Enregistrer un événement d'appel vidéo ──────────────────────────────
    /**
     * Enregistrer un événement d'appel vidéo (accepté, rejeté, manqué) dans la conversation
     * @param int $id_video_call ID de l'appel vidéo
     * @param string $event Type d'événement: 'accepted', 'rejected', 'missed'
     * @param int $id_user ID de l'utilisateur effectuant l'action
     */
    public function logCallEvent(): void {
        $this->startSession();
        $id_user = $this->getUserId();
        
        // VALIDATION
        $validator = new FormValidator($_POST);
        $validator
            ->addRule('id_video_call', 'required', 'L\'ID de l\'appel est obligatoire')
            ->addRule('id_video_call', 'positive', 'L\'ID de l\'appel doit être un nombre positif')
            ->addRule('event', 'required', 'Le type d\'événement est obligatoire')
            ->addRule('initiateur_nom', 'required', 'Le nom de l\'initiateur est obligatoire')
            ->addRule('initiateur_prenom', 'required', 'Le prénom de l\'initiateur est obligatoire');

        if (!$validator->validate()) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->getErrors()
            ], 400);
        }

        $id_video_call = (int)$validator->get('id_video_call');
        $event = $validator->get('event');
        $initiateur_nom = $validator->get('initiateur_nom');
        $initiateur_prenom = $validator->get('initiateur_prenom');

        // Vérifier l'événement valide
        if (!in_array($event, ['accepted', 'rejected', 'missed', 'ended'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Événement invalide'], 400);
        }

        // Récupérer l'appel
        $call = $this->videoCallModel->getById($id_video_call);
        if (!$call) {
            $this->jsonResponse(['success' => false, 'message' => 'Appel non trouvé'], 404);
        }

        $id_conversation = (int)$call['id_conversation'];
        $conversation = $this->conversationModel->getById($id_conversation);
        if (!$conversation) {
            $this->jsonResponse(['success' => false, 'message' => 'Conversation non trouvée'], 404);
        }

        // Vérifier que l'utilisateur appartient à la conversation
        if ($conversation['id_user1'] != $id_user && $conversation['id_user2'] != $id_user) {
            $this->jsonResponse(['success' => false, 'message' => 'Accès refusé'], 403);
        }

        // Créer le message d'événement
        $callerName = trim($initiateur_prenom . ' ' . $initiateur_nom);
        
        // Message personnalisé selon l'événement
        $messages = [
            'accepted' => "📞 Appel accepté de $callerName",
            'rejected' => "☎️ Appel rejeté de $callerName",
            'missed'   => "📵 Appel manqué de $callerName",
            'ended'    => "✓ Appel terminé avec $callerName"
        ];

        $contenu = $messages[$event] ?? "Événement appel: $event";

        // Utiliser le Model Message pour enregistrer
        require_once __DIR__ . '/../model/Message.php';
        $messageModel = new Message();
        
        if ($messageModel->create(
            contenu: $contenu,
            id_expediteur: $id_user,
            id_conversation: $id_conversation,
            type_message: 'video_call_' . $event
        )) {
            $this->jsonResponse(['success' => true, 'message' => 'Événement enregistré']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de l\'enregistrement'], 500);
        }
    }
    
    // ─── Nettoyer les appels zombies ─────────────────────────────────────────
    /**
     * Marquer comme terminé tous les appels de plus de 1 minute
     * Appelée automatiquement avant toute création d'appel
     */
    private function cleanupZombieCalls(): void {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE video_calls 
                SET statut = 'termine', date_fin = NOW()
                WHERE statut IN ('en_attente', 'en_cours')
                AND (
                    TIMESTAMPDIFF(MINUTE, COALESCE(date_debut, created_at), NOW()) >= 1
                    OR date_debut IS NULL
                )
            ");
            
            if ($stmt->execute()) {
                $count = $stmt->rowCount();
                if ($count > 0) {
                    error_log("[VideoCall] 🧹 Nettoyage: {$count} appel(s) zombie(s) terminé(s)");
                }
            }
        } catch (PDOException $e) {
            error_log("[VideoCall] ❌ Erreur nettoyage: " . $e->getMessage());
        }
    }
    
}
$controller = new VideoCallController();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'initiate':
        $controller->initiate();
        break;
    case 'accept':
        $controller->accept();
        break;
    case 'reject':
        $controller->reject();
        break;
    case 'end':
        $controller->end();
        break;
    case 'logCallEvent':
        $controller->logCallEvent();
        break;
    case 'getParticipants':
        $controller->getParticipants();
        break;
    case 'getCall':
        $controller->getCall();
        break;
    case 'getActive':
        $controller->getActive();
        break;
    case 'getHistory':
        $controller->getHistory();
        break;
    case 'getMissedCalls':
        $controller->getMissedCalls();
        break;
    case 'getUserStats':
        $controller->getUserStats();
        break;
    default:
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Action invalide']);
        exit;
}
