<?php
require_once 'config/Database.php';

$database = new Database();
$db = $database->getConnection();

try {
    $query = "ALTER TABLE publications ADD COLUMN likes INT DEFAULT 0";
    $stmt = $db->prepare($query);
    $stmt->execute();
    echo "Colonne 'likes' ajoutée avec succès.";
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>