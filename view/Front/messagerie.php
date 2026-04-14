<?php
// ── Point d'entrée principal – Messages ──────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) session_start();

// SESSION FORCÉE (pas de login) — changer 1=Farah ou 2=Aziz pour tester
$_SESSION['id_user'] = 1;
$_SESSION['prenom']  = 'Farah';
$_SESSION['nom']     = 'Ksouri';

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../model/Message.php';
require_once __DIR__ . '/../../model/Conversation.php';
require_once __DIR__ . '/../../controller/MessageController.php';

$ctrl = new MessageController();
$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $ctrl->showConversation();
} else {
    $ctrl->indexFront();
}