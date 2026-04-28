<?php
require_once __DIR__ . '/database.php';

$pdo = Database::getInstance()->getConnection();

echo "=== STRUCTURE DE LA TABLE MESSAGES ===\n\n";

// Afficher les colonnes
$stmt = $pdo->query("DESCRIBE messages");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($columns as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")" . ($col['Null'] === 'NO' ? ' - NOT NULL' : '') . "\n";
}

echo "\n=== COLONNES ATTENDUES POUR LES FICHIERS ===\n";
echo "- fichier_path\n";
echo "- fichier_nom_original\n";
echo "- fichier_type\n";
echo "- fichier_taille\n";
?>
