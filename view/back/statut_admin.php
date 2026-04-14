<?php
header('Content-Type: application/json');
require_once '../../controller/ReclamationController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'msg' => 'Méthode non autorisée']);
    exit;
}

$id     = (int)($_POST['id_reclamation'] ?? 0);
$statut = trim($_POST['statut'] ?? '');

if (!$id || $statut === '') {
    echo json_encode(['ok' => false, 'msg' => 'Données manquantes']);
    exit;
}

try {
    $controller = new ReclamationController();
    $ok = $controller->changerStatut($id, $statut);
    echo json_encode(['ok' => (bool)$ok]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
