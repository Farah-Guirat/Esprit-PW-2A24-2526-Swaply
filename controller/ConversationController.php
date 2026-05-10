<?php
require_once __DIR__ . '/../model/Conversation.php';
require_once __DIR__ . '/../model/Message.php';
require_once __DIR__ . '/../config/database.php';

class ConversationController {
    private Conversation $conversationModel;
    private Message $messageModel;

    public function __construct() {
        $this->conversationModel = new Conversation();
        $this->messageModel      = new Message();
    }

    public function indexBack(): void {
        $conversations = $this->conversationModel->getAll();
        $stats = $this->getConversationStats();
        require __DIR__ . '/../view/Back/conversations.php';
    }

    // ─── Statistiques conversations ───────────────────────────────────────────
    private function getConversationStats(): array {
       $pdo = Database::getInstance();

        // Total conversations
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM conversations");
        $total = (int)$stmt->fetchColumn();

        // Conversations actives
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM conversations WHERE statut = 'active'");
        $active = (int)$stmt->fetchColumn();

        // Conversations fermées
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM conversations WHERE statut = 'fermee'");
        $closed = (int)$stmt->fetchColumn();

        // Conversations ce mois
        $stmt = $pdo->query(
            "SELECT COUNT(*) as total FROM conversations 
             WHERE MONTH(date_creation) = MONTH(NOW()) 
             AND YEAR(date_creation) = YEAR(NOW())"
        );
        $thisMonth = (int)$stmt->fetchColumn();

        // Total messages dans toutes les conversations
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM messages");
        $totalMessages = (int)$stmt->fetchColumn();

        // Messages lus
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM messages WHERE lu = 1");
        $readMessages = (int)$stmt->fetchColumn();

        // Taux de messages lus
        $readRate = $totalMessages > 0 ? round(($readMessages / $totalMessages) * 100, 0) : 0;

        return [
            'total'           => $total,
            'actives'         => $active,
            'fermees'         => $closed,
            'ce_mois'         => $thisMonth,
            'total_messages'  => $totalMessages,
            'lus'             => $readMessages,
            'taux_lus'        => $readRate,
        ];
    }

    public function viewBack(): void {
        $id           = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $conversation = $this->conversationModel->getById($id);
        if (!$conversation) { header('Location: ../view/Back/conversations.php'); exit; }
        $messages = $this->messageModel->getByConversation($id);
        require __DIR__ . '/../view/Back/view_conversation.php';
    }

    public function closeBack(): void {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id > 0) $this->conversationModel->close($id);
        header('Location: ../view/Back/conversations.php?closed=1'); exit;
    }

    public function deleteBack(): void {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id > 0) $this->conversationModel->delete($id);
        header('Location: ../view/Back/conversations.php?deleted=1'); exit;
    }
}

// ── ROUTER ───────────────────────────────────────────────────────────────────
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    if (!class_exists('Database')) require_once __DIR__ . '/../config/database.php';
    $ctrl   = new ConversationController();
    $action = $_GET['action'] ?? '';
    switch ($action) {
        case 'viewBack':   $ctrl->viewBack();   break;
        case 'closeBack':  $ctrl->closeBack();  break;
        case 'deleteBack': $ctrl->deleteBack(); break;
        default: header('Location: ../view/Back/conversations.php'); exit;
    }
}
?>