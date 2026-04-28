<?php
// ── Point d'entrée principal – Messages ──────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit;
}

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