<?php
header('Content-Type: application/json');
require_once '../../controller/ReclamationController.php';

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    echo json_encode(['ok' => false, 'msg' => 'ID manquant']);
    exit;
}

try {
    $controller = new ReclamationController();
    $ok = $controller->supprimer($id);
    echo json_encode(['ok' => (bool)$ok]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
