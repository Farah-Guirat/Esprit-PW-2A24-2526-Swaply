<?php
require_once '../../controller/ReclamationController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id          = (int)$_POST['id'];
    $description = trim($_POST['description'] ?? '');
    $type        = $_POST['type'] ?? 'person';
    $rating      = (int)($_POST['rating'] ?? 1);

    if ($description !== '') {
        $controller = new ReclamationController();
        $controller->modifier($id, $description, $rating, $type);
    }

    header("Location: detail_reclamation.php?id=" . $id);
    exit;
}

header("Location: reclamations.php");
exit;
?>