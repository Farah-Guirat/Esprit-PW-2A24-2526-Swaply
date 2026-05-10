<?php
/**
 * Contrôleur pour les fonctionnalités temps réel (typing, online status)
 */
require_once __DIR__ . '/../config/database.php';

class RealtimeController {
    private PDO $pdo;
    private const TYPING_TIMEOUT = 3; // secondes
    private const ONLINE_TIMEOUT = 30; // secondes

    public function __construct() {
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
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);
        echo json_encode($data);
        exit;
    }

    // ─── Marquer l'utilisateur comme en train de taper ─────────────────────
    public function updateTypingStatus(): void {
        $this->startSession();
        $id_user = $this->getUserId();
        $id_conversation = isset($_POST['id_conversation']) ? (int)$_POST['id_conversation'] : 0;
        $isTyping = isset($_POST['is_typing']) ? (bool)$_POST['is_typing'] : false;

        if ($id_user <= 0 || $id_conversation <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Données invalides'], 400);
        }

        // Fichier temporaire pour le statut "typing"
        $typingFile = __DIR__ . '/../tmp/typing_' . $id_conversation . '_' . $id_user . '.tmp';
        $typingDir = dirname($typingFile);

        // Créer le dossier tmp s'il n'existe pas
        if (!is_dir($typingDir)) {
            mkdir($typingDir, 0755, true);
        }

        if ($isTyping) {
            // Créer/mettre à jour le fichier avec le timestamp actuel
            file_put_contents($typingFile, time());
        } else {
            // Supprimer le fichier si l'utilisateur a arrêté de taper
            if (file_exists($typingFile)) {
                unlink($typingFile);
            }
        }

        $this->jsonResponse(['success' => true, 'message' => 'Statut typing mis à jour']);
    }

    // ─── Récupérer qui est en train de taper ────────────────────────────────
    public function getTypingUsers(): void {
        $this->startSession();
        $id_user = $this->getUserId();
        $id_conversation = isset($_GET['id_conversation']) ? (int)$_GET['id_conversation'] : 0;

        if ($id_user <= 0 || $id_conversation <= 0) {
            $this->jsonResponse(['success' => false, 'typing_users' => []], 400);
        }

        $typingDir = __DIR__ . '/../tmp';
        $currentTime = time();
        $typingUsers = [];

        // Parcourir les fichiers temporaires de typing pour cette conversation
        if (is_dir($typingDir)) {
            $pattern = 'typing_' . $id_conversation . '_*.tmp';
            foreach (glob($typingDir . '/' . $pattern) as $file) {
                $fileTime = (int)file_get_contents($file);
                
                // Vérifier si le fichier n'est pas expiré
                if (($currentTime - $fileTime) <= self::TYPING_TIMEOUT) {
                    // Extraire l'ID utilisateur du nom de fichier
                    preg_match('/typing_' . $id_conversation . '_(\d+)\.tmp/', basename($file), $matches);
                    if (isset($matches[1])) {
                        $userId = (int)$matches[1];
                        if ($userId != $id_user) {
                            $typingUsers[] = $userId;
                        }
                    }
                } else {
                    // Supprimer les fichiers expirés
                    unlink($file);
                }
            }
        }

        // Récupérer les infos des utilisateurs qui tapent
        $typingUsersInfo = [];
        if (!empty($typingUsers)) {
            $placeholders = implode(',', array_fill(0, count($typingUsers), '?'));
            $stmt = $this->pdo->prepare(
                "SELECT id_u, CONCAT(prenom, ' ', nom) as nom_complet
                 FROM utilisateurs
                 WHERE id_u IN ($placeholders)"
            );
            $stmt->execute($typingUsers);
            $typingUsersInfo = $stmt->fetchAll();
        }

        $this->jsonResponse(['success' => true, 'typing_users' => $typingUsersInfo]);
    }

    // ─── Mettre à jour le statut "en ligne" ──────────────────────────────────
    public function updateOnlineStatus(): void {
        $this->startSession();
        $id_user = $this->getUserId();

        if ($id_user <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Utilisateur non identifié'], 401);
        }

        // Fichier temporaire pour le statut "online"
        $onlineFile = __DIR__ . '/../tmp/online_' . $id_user . '.tmp';
        $onlineDir = dirname($onlineFile);

        // Créer le dossier tmp s'il n'existe pas
        if (!is_dir($onlineDir)) {
            mkdir($onlineDir, 0755, true);
        }

        // Mettre à jour le timestamp
        file_put_contents($onlineFile, time());

        $this->jsonResponse(['success' => true, 'message' => 'Statut en ligne mis à jour']);
    }

    // ─── Récupérer le statut en ligne d'un utilisateur ──────────────────────
    public function getOnlineStatus(): void {
        $id_user = isset($_GET['id_user']) ? (int)$_GET['id_user'] : 0;

        if ($id_user <= 0) {
            $this->jsonResponse(['success' => false, 'online' => false], 400);
        }

        $onlineFile = __DIR__ . '/../tmp/online_' . $id_user . '.tmp';
        $currentTime = time();
        $isOnline = false;

        if (file_exists($onlineFile)) {
            $lastActivity = (int)file_get_contents($onlineFile);
            // Vérifier si l'utilisateur est encore actif
            if (($currentTime - $lastActivity) <= self::ONLINE_TIMEOUT) {
                $isOnline = true;
            } else {
                // Nettoyer le fichier expiré
                unlink($onlineFile);
            }
        }

        // Calculer le temps depuis la dernière activité
        $lastSeenAgo = null;
        if (file_exists($onlineFile)) {
            $lastActivity = (int)file_get_contents($onlineFile);
            $secondsAgo = $currentTime - $lastActivity;
            if ($secondsAgo < 60) {
                $lastSeenAgo = 'à l\'instant';
            } elseif ($secondsAgo < 3600) {
                $minutes = floor($secondsAgo / 60);
                $lastSeenAgo = "il y a {$minutes}m";
            } elseif ($secondsAgo < 86400) {
                $hours = floor($secondsAgo / 3600);
                $lastSeenAgo = "il y a {$hours}h";
            }
        }

        $this->jsonResponse([
            'success' => true,
            'online' => $isOnline,
            'last_seen_ago' => $lastSeenAgo
        ]);
    }

    // ─── Récupérer les statuts en ligne de plusieurs utilisateurs ──────────
    public function getMultipleOnlineStatus(): void {
        $userIds = isset($_GET['user_ids']) ? $_GET['user_ids'] : [];
        $userIds = is_array($userIds) ? $userIds : [$userIds];

        $statuses = [];
        $currentTime = time();

        foreach ($userIds as $id_user) {
            $id_user = (int)$id_user;
            $onlineFile = __DIR__ . '/../tmp/online_' . $id_user . '.tmp';
            $isOnline = false;

            if (file_exists($onlineFile)) {
                $lastActivity = (int)file_get_contents($onlineFile);
                if (($currentTime - $lastActivity) <= self::ONLINE_TIMEOUT) {
                    $isOnline = true;
                } else {
                    @unlink($onlineFile);
                }
            }

            $statuses[$id_user] = ['online' => $isOnline];
        }

        $this->jsonResponse(['success' => true, 'statuses' => $statuses]);
    }
}

// ─── Router ──────────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) session_start();

$action = $_GET['action'] ?? 'index';
$controller = new RealtimeController();

switch ($action) {
    case 'updateTyping':
        $controller->updateTypingStatus();
        break;
    case 'getTyping':
        $controller->getTypingUsers();
        break;
    case 'updateOnline':
        $controller->updateOnlineStatus();
        break;
    case 'getOnline':
        $controller->getOnlineStatus();
        break;
    case 'getMultipleOnline':
        $controller->getMultipleOnlineStatus();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Action inconnue']);
        break;
}
?>
