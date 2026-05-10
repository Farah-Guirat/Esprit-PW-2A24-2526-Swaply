<?php
header('Content-Type: application/json');
require_once '../../controller/ReponseController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'msg' => 'Méthode non autorisée']);
    exit;
}

$id_reclamation = (int)($_POST['id_reclamation'] ?? 0);
$contenu        = trim($_POST['contenu'] ?? '');
$status         = trim($_POST['status'] ?? 'en cours');

if (!$id_reclamation || $contenu === '') {
    echo json_encode(['ok' => false, 'msg' => 'Données manquantes']);
    exit;
}

try {
    $controller = new ReponseController();
    $ok = $controller->ajouter($id_reclamation, $contenu, $status);
    echo json_encode(['ok' => (bool)$ok]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
