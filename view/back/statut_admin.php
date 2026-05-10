<?php
header('Content-Type: application/json');
// Thabbet houni el path mta' el controller lezem ykoun s-hih
require_once '../../controller/ReclamationController.php'; 

$id     = (int)($_POST['id_reclamation'] ?? 0);
$statut = trim($_POST['statut'] ?? '');

if (!$id || $statut === '') {
    echo json_encode(['ok' => false, 'msg' => 'Données manquantes']);
    exit;
}

try {
    $controller = new ReclamationController();
    $ok = $controller->changerStatut($id, $statut); // Houni el controller bech y-3ayet lel model
    echo json_encode(['ok' => (bool)$ok]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}