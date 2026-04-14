<?php
require_once __DIR__ . '/../model/Conversation.php';
require_once __DIR__ . '/../model/Message.php';

class ConversationController {
    private Conversation $conversationModel;
    private Message $messageModel;

    public function __construct() {
        $this->conversationModel = new Conversation();
        $this->messageModel      = new Message();
    }

    public function indexBack(): void {
        $conversations = $this->conversationModel->getAll();
        require __DIR__ . '/../view/Back/conversations.php';
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