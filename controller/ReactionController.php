<?php
require_once __DIR__ . '/../model/Reaction.php';
require_once __DIR__ . '/../model/Message.php';

class ReactionController {
    private Reaction $reaction;
    private Message $message;

    public function __construct() {
        $this->reaction = new Reaction();
        $this->message = new Message();
    }

    /**
     * Ajouter une réaction à un message
     */
    public function addReaction() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['id_message']) || !isset($input['emoji']) || !isset($input['id_user'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Paramètres manquants']);
                return;
            }

            $id_message = (int)$input['id_message'];
            $emoji = $input['emoji'];
            $id_user = (int)$input['id_user'];

            // Vérifier que le message existe
            $msg = $this->message->getById($id_message);
            if (!$msg) {
                http_response_code(404);
                echo json_encode(['error' => 'Message non trouvé']);
                return;
            }

            // Ajouter la réaction
            $this->reaction->add($id_message, $id_user, $emoji);
            
            // Récupérer les réactions mises à jour
            $reactions = $this->reaction->getByMessage($id_message);

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'reactions' => $reactions
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Supprimer une réaction
     */
    public function removeReaction() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['id_message']) || !isset($input['emoji']) || !isset($input['id_user'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Paramètres manquants']);
                return;
            }

            $id_message = (int)$input['id_message'];
            $emoji = $input['emoji'];
            $id_user = (int)$input['id_user'];

            // Supprimer la réaction
            $this->reaction->remove($id_message, $id_user, $emoji);

            // Récupérer les réactions mises à jour
            $reactions = $this->reaction->getByMessage($id_message);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'reactions' => $reactions
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Récupérer toutes les réactions d'un message
     */
    public function getReactions() {
        try {
            if (!isset($_GET['id_message'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID du message manquant']);
                return;
            }

            $id_message = (int)$_GET['id_message'];
            $reactions = $this->reaction->getByMessage($id_message);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'reactions' => $reactions
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Obtenir les réactions d'un utilisateur sur un message
     */
    public function getUserReactions() {
        try {
            if (!isset($_GET['id_message']) || !isset($_GET['id_user'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID du message ou utilisateur manquant']);
                return;
            }

            $id_message = (int)$_GET['id_message'];
            $id_user = (int)$_GET['id_user'];

            $userReactions = $this->reaction->getUserReactions($id_message, $id_user);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'reactions' => $userReactions
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Obtenir la liste des emojis autorisés
     */
    public function getAllowedEmojis() {
        try {
            $emojis = $this->reaction->getAllowedEmojis();

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'emojis' => $emojis
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}

// Dispatcher les requêtes
$action = null;

// Pour GET
if (!empty($_GET['action'])) {
    $action = $_GET['action'];
}
// Pour POST (JSON ou form-data)
elseif (!empty($_POST['action'])) {
    $action = $_POST['action'];
}
// Pour POST avec JSON body
else {
    $input = json_decode(file_get_contents('php://input'), true);
    if (is_array($input) && !empty($input['action'])) {
        $action = $input['action'];
    }
}

if ($action) {
    $controller = new ReactionController();

    switch ($action) {
        case 'add':
            $controller->addReaction();
            break;
        case 'remove':
            $controller->removeReaction();
            break;
        case 'get':
            $controller->getReactions();
            break;
        case 'getUserReactions':
            $controller->getUserReactions();
            break;
        case 'getEmojis':
            $controller->getAllowedEmojis();
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Action non valide']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Aucune action spécifiée']);
}
?>
