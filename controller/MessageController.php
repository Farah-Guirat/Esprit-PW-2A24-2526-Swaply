<?php
require_once __DIR__ . '/../model/Message.php';
require_once __DIR__ . '/../model/Conversation.php';
require_once __DIR__ . '/../config/database.php';

class MessageController {

    private Message $messageModel;
    private Conversation $conversationModel;

    public function __construct() {
        $this->messageModel      = new Message();
        $this->conversationModel = new Conversation();
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    private function getUserId(): int {
        $this->startSession();
        return (int)($_SESSION['id_user'] ?? 0);
    }

    // Redirection relative depuis controller/ vers view/Front/messagerie.php
    private function redirectFront(string $query = ''): void {
        header('Location: ../view/Front/messagerie.php' . $query);
        exit;
    }

    // ─── FRONT : liste des conversations ─────────────────────────────────────
    public function indexFront(): void {
        $this->startSession();
        $id_user        = $this->getUserId();
        $conversations  = $this->conversationModel->getByUser($id_user);
        $id_active_conv = 0;
        $conversation   = null;
        $messages       = [];
        $errors         = [];
        require __DIR__ . '/../view/Front/Messages.php';
    }

    // ─── FRONT : ouvrir une conversation ─────────────────────────────────────
    // PROBLÈME 1 CORRIGÉ : on charge TOUTES les conversations pour la sidebar
    // ET les messages de la conversation active — les deux en même temps
    public function showConversation(): void {
        $this->startSession();
        $id_user        = $this->getUserId();
        $id_active_conv = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $errors         = [];

        // Vérifier que la conversation appartient bien à l'utilisateur
        $conversation = $this->conversationModel->getById($id_active_conv);
        if (!$conversation ||
            ($conversation['id_user1'] != $id_user && $conversation['id_user2'] != $id_user)) {
            $this->redirectFront();
        }

        // Marquer les messages reçus comme lus dès l'ouverture de la conversation
        $this->messageModel->markAsRead($id_active_conv, $id_user);

        // Charger les messages de cette conversation (BDD)
        $messages = $this->messageModel->getByConversation($id_active_conv);

        // Charger TOUTES les conversations de l'utilisateur pour la sidebar
        $conversations = $this->conversationModel->getByUser($id_user);

        require __DIR__ . '/../view/Front/Messages.php';
    }

    // ─── FRONT : envoyer un message → enregistrement en BDD ──────────────────
    // PROBLÈME 2 CORRIGÉ : create() insère réellement en BDD via PDO
    // Supporte aussi l'upload de fichiers
    public function sendMessage(): void {
        $this->startSession();
        $errors          = [];
        $id_user         = $this->getUserId();
        $id_conversation = isset($_POST['id_conversation']) ? (int)$_POST['id_conversation'] : 0;
        $contenu         = isset($_POST['contenu']) ? trim($_POST['contenu']) : '';

        // Validation serveur (sans HTML5)
        if ($contenu === '' && empty($_FILES['fichier']))  
            $errors[] = "Le message ne peut pas être vide.";
        elseif (strlen($contenu) > 2000)  
            $errors[] = "Le message ne peut pas dépasser 2000 caractères.";
        if ($id_conversation <= 0)        
            $errors[] = "Conversation invalide.";

        if (empty($errors)) {
            $conv = $this->conversationModel->getById($id_conversation);
            if (!$conv || ($conv['id_user1'] != $id_user && $conv['id_user2'] != $id_user))
                $errors[] = "Accès refusé à cette conversation.";
        }

        // Traiter l'upload du fichier
        $fichier_path = null;
        $fichier_nom_original = null;
        $fichier_type = null;
        $fichier_taille = null;

        if (!empty($_FILES['fichier']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
            $upload = $this->handleFileUpload($_FILES['fichier']);
            if (is_array($upload) && isset($upload['error'])) {
                $errors[] = $upload['error'];
            } else {
                $fichier_path = $upload['path'];
                $fichier_nom_original = $upload['name'];
                $fichier_type = $upload['type'];
                $fichier_taille = $upload['size'];
            }
        }

        if (empty($errors)) {
            // INSERT en base de données via PDO (model Message::create)
            $this->messageModel->create(
                $contenu, 
                $id_user, 
                $id_conversation,
                $fichier_path,
                $fichier_nom_original,
                $fichier_type,
                $fichier_taille
            );
            $this->redirectFront("?id=$id_conversation");
        }

        // Retour à la vue avec erreurs + toutes les données
        $id_active_conv = $id_conversation;
        $conversation   = $this->conversationModel->getById($id_conversation);
        $messages       = $this->messageModel->getByConversation($id_conversation);
        $conversations  = $this->conversationModel->getByUser($id_user);
        require __DIR__ . '/../view/Front/Messages.php';
    }

    // ─── Gestion de l'upload de fichier ──────────────────────────────────────
    private function handleFileUpload(array $file): array {
        // Limite de taille : 10 MB
        $maxSize = 10 * 1024 * 1024;
        // Extensions autorisées
        $allowedExts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'jpg', 'jpeg', 'png', 'gif'];
        // Types MIME autorisés
        $allowedMimes = ['application/pdf', 'application/msword', 
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-powerpoint',
                        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                        'text/plain', 'application/zip',
                        'image/jpeg', 'image/png', 'image/gif'];

        // Vérification de la taille
        if ($file['size'] > $maxSize) {
            return ['error' => 'Le fichier est trop volumineux (max 10 MB).'];
        }

        // Vérification du type MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimes)) {
            return ['error' => 'Type de fichier non autorisé.'];
        }

        // Vérification de l'extension
        $filename = basename($file['name']);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts)) {
            return ['error' => 'Extension de fichier non autorisée.'];
        }

        // Créer le dossier d'upload s'il n'existe pas
        $uploadDir = __DIR__ . '/../uploads/messages/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Générer un nom unique pour le fichier
        $uniqueName = uniqid() . '_' . time() . '.' . $ext;
        $uploadPath = $uploadDir . $uniqueName;

        // Déplacer le fichier uploadé
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return [
                'path' => 'uploads/messages/' . $uniqueName,
                'name' => $filename,
                'type' => $mimeType,
                'size' => $file['size']
            ];
        } else {
            return ['error' => 'Erreur lors de l\'upload du fichier.'];
        }
    }

    // ─── FRONT : créer une conversation ──────────────────────────────────────
    // PROBLÈME 3 CORRIGÉ :
    //   - Après suppression : l'utilisateur réapparaît dans la liste
    //   - Si conv déjà active avec lui : il n'apparaît PAS dans la liste
    public function createConversation(): void {
        $this->startSession();
        $errors  = [];
        $id_user = $this->getUserId();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_destinataire = isset($_POST['id_destinataire']) ? (int)$_POST['id_destinataire'] : 0;
            $contenu_init    = isset($_POST['contenu_init']) ? trim($_POST['contenu_init']) : '';

            
            if ($id_destinataire <= 0)             $errors[] = "Veuillez sélectionner un destinataire.";
            elseif ($id_destinataire === $id_user)  $errors[] = "Vous ne pouvez pas vous écrire à vous-même.";
            if ($contenu_init === '')               $errors[] = "Le premier message ne peut pas être vide.";
            elseif (strlen($contenu_init) > 2000)   $errors[] = "Le message ne peut pas dépasser 2000 caractères.";

            if (empty($errors)) {
                // Créer la conv + insérer le premier message en BDD
                $id_conv = $this->conversationModel->create($id_user, $id_destinataire);
                $this->messageModel->create($contenu_init, $id_user, $id_conv);
                $this->redirectFront("?id=$id_conv");
            }
        }

        // Construire la liste des destinataires disponibles :
        // - exclure soi-même
        // - exclure les users avec qui il existe DÉJÀ une conv active
        // - inclure les users dont la conv a été SUPPRIMÉE (statut fermee ou inexistante)
        $all   = $this->conversationModel->getAllUsers();
        $users = [];
        foreach ($all as $u) {
            $uid = (int)$u['id_u'];
            if ($uid === $id_user) continue; // exclure soi-même
            $existing = $this->conversationModel->existsBetween($id_user, $uid);
            if ($existing) continue; // conv active existe → ne pas afficher
            $users[] = $u;           // conv supprimée ou inexistante → afficher
        }

        require __DIR__ . '/../view/Front/ajouter_message.php';
    }

    // ─── FRONT : éditer un message (UPDATE en BDD) ───────────────────────────
    public function editMessage(): void {
        $this->startSession();
        $errors  = [];
        $id_user = $this->getUserId();
        $id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $message = $this->messageModel->getById($id);

        if (!$message || $message['id_expediteur'] != $id_user) {
            $this->redirectFront();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $contenu = isset($_POST['contenu']) ? trim($_POST['contenu']) : '';
            // Validation serveur
            if ($contenu === '')             $errors[] = "Le contenu ne peut pas être vide.";
            elseif (strlen($contenu) > 2000) $errors[] = "Le contenu ne peut pas dépasser 2000 caractères.";
            if (empty($errors)) {
                // UPDATE en base de données via PDO
                $this->messageModel->update($id, $contenu);
                $this->redirectFront("?id=" . $message['id_conversation']);
            }
        }

        require __DIR__ . '/../view/Front/edit_message.php';
    }

    // ─── FRONT : supprimer un message (DELETE en BDD) ────────────────────────
    public function deleteMessage(): void {
        $this->startSession();
        $id_user = $this->getUserId();
        $id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $message = $this->messageModel->getById($id);
        $id_conv = 0;

        if ($message) {
            $id_conv = $message['id_conversation'];
            if ($message['id_expediteur'] == $id_user) {
                // DELETE en base de données via PDO
                $this->messageModel->delete($id);
            }
        }

        // Retour à la même conversation après suppression
        $this->redirectFront($id_conv ? "?id=$id_conv" : '');
    }

    // ─── FRONT : supprimer une conversation (DELETE cascade en BDD) ──────────
    // PROBLÈME 3 : après suppression, la conv est retirée de la BDD
    // donc existsBetween() retourne null → l'utilisateur réapparaît dans "Nouveau"
    public function deleteConversation(): void {
        $this->startSession();
        $id_user = $this->getUserId();
        $id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $conv    = $this->conversationModel->getById($id);

        if ($conv && ($conv['id_user1'] == $id_user || $conv['id_user2'] == $id_user)) {
            // DELETE conversation + tous ses messages en BDD (cascade dans le model)
            $this->conversationModel->delete($id);
        }

        // Retour à la liste des conversations (vide si plus aucune)
        $this->redirectFront();
    }

    // ─── BACK : liste des messages ────────────────────────────────────────────
    public function indexBack(): void {
        $messages = $this->messageModel->getAll();
        $stats = $this->getMessageStats();
        require __DIR__ . '/../view/Back/messages.php';
    }

    // ─── Statistiques messages ────────────────────────────────────────────────
    private function getMessageStats(): array {
        $pdo = Database::getInstance()->getConnection();

        // Total messages
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM messages");
        $total = (int)$stmt->fetchColumn();

        // Messages ce mois
        $stmt = $pdo->query(
            "SELECT COUNT(*) as total FROM messages 
             WHERE MONTH(date_envoi) = MONTH(NOW()) 
             AND YEAR(date_envoi) = YEAR(NOW())"
        );
        $thisMonth = (int)$stmt->fetchColumn();

        // Messages aujourd'hui
        $stmt = $pdo->query(
            "SELECT COUNT(*) as total FROM messages 
             WHERE DATE(date_envoi) = DATE(NOW())"
        );
        $today = (int)$stmt->fetchColumn();

        // Messages lus
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM messages WHERE lu = 1");
        $read = (int)$stmt->fetchColumn();

        // Messages non lus
        $unread = $total - $read;

        // Messages avec fichiers
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM messages WHERE fichier_path IS NOT NULL");
        $withFiles = (int)$stmt->fetchColumn();

        // Longueur moyenne
        $stmt = $pdo->query("SELECT AVG(LENGTH(contenu)) as avg FROM messages");
        $avgLength = round((float)$stmt->fetchColumn(), 0);

        // Top 5 utilisateurs
        $stmt = $pdo->query(
            "SELECT u.prenom, u.nom, COUNT(m.id_message) as count
             FROM messages m
             JOIN utilisateurs u ON u.id_u = m.id_expediteur
             GROUP BY m.id_expediteur
             ORDER BY count DESC
             LIMIT 5"
        );
        $topUsers = $stmt->fetchAll();

        return [
            'total'         => $total,
            'ce_mois'       => $thisMonth,
            'aujourd_hui'   => $today,
            'lus'           => $read,
            'non_lus'       => $unread,
            'avec_fichiers' => $withFiles,
            'longueur_avg'  => $avgLength,
            'taux_lus'      => $total > 0 ? round(($read / $total) * 100, 0) : 0,
            'top_users'     => $topUsers,
        ];
    }

    // ─── BACK : éditer un message ─────────────────────────────────────────────
    public function editBack(): void {
        $errors    = [];
        $id        = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $from_conv = isset($_GET['from_conv']) ? (int)$_GET['from_conv'] : 0;
        $message   = $this->messageModel->getById($id);
        if (!$message) { header('Location: ../view/Back/messages.php'); exit; }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $contenu   = isset($_POST['contenu']) ? trim($_POST['contenu']) : '';
            $from_conv = isset($_GET['from_conv']) ? (int)$_GET['from_conv'] : 0;
            // Validation serveur (sans HTML5)
            if ($contenu === '')             $errors[] = "Le contenu ne peut pas être vide.";
            elseif (strlen($contenu) > 2000) $errors[] = "Le contenu ne peut pas dépasser 2000 caractères.";
            if (empty($errors)) {
                $this->messageModel->update($id, $contenu);
                // Retour vers la conv si on vient de là, sinon vers la liste messages
                if ($from_conv > 0) {
                    header("Location: ../view/Back/view_conversation.php?id=$from_conv&success=1"); exit;
                }
                header('Location: ../view/Back/messages.php?success=1'); exit;
            }
        }
        require __DIR__ . '/../view/Back/edit_message.php';
    }

    // ─── BACK : supprimer un message ──────────────────────────────────────────
    public function deleteBack(): void {
        $id        = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $from_conv = isset($_GET['from_conv']) ? (int)$_GET['from_conv'] : 0;
        if ($id > 0) $this->messageModel->delete($id);
        // Retour vers la conv si on vient de là, sinon vers la liste messages
        if ($from_conv > 0) {
            header("Location: ../view/Back/view_conversation.php?id=$from_conv&deleted=1"); exit;
        }
        header('Location: ../view/Back/messages.php?deleted=1'); exit;
    }
}

// ─── ROUTER ───────────────────────────────────────────────────────────────────
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    if (!class_exists('Database')) require_once __DIR__ . '/../config/database.php';
    $ctrl   = new MessageController();
    $action = $_GET['action'] ?? '';
    switch ($action) {
        case 'send':               $ctrl->sendMessage();        break;
        case 'createConversation': $ctrl->createConversation(); break;
        case 'editMessage':        $ctrl->editMessage();        break;
        case 'deleteMessage':      $ctrl->deleteMessage();      break;
        case 'deleteConversation': $ctrl->deleteConversation(); break;
        case 'editBack':           $ctrl->editBack();           break;
        case 'deleteBack':         $ctrl->deleteBack();         break;
        default: header('Location: ../view/Front/messagerie.php'); exit;
    }
}
?>