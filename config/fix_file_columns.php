<?php
require_once __DIR__ . '/database.php';

$pdo = Database::getInstance()->getConnection();

echo "=== MODIFICATION DES COLONNES DE FICHIERS ===\n\n";

try {
    // Modifier les colonnes pour accepter NULL
    $alterations = [
        "ALTER TABLE messages MODIFY COLUMN fichier_path VARCHAR(255) NULL",
        "ALTER TABLE messages MODIFY COLUMN fichier_nom_original VARCHAR(255) NULL",
        "ALTER TABLE messages MODIFY COLUMN fichier_type VARCHAR(50) NULL",
        "ALTER TABLE messages MODIFY COLUMN fichier_taille INT NULL"
    ];
    
    foreach ($alterations as $sql) {
        $pdo->exec($sql);
        echo "✅ Exécuté: " . substr($sql, 0, 50) . "...\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✅ MODIFICATION COMPLÉTÉE!\n";
    echo "\n💡 Vous pouvez maintenant:\n";
    echo "   1. Allez à: http://localhost/swaply/view/Front/login.php\n";
    echo "   2. Connectez-vous\n";
    echo "   3. Envoyez un message SIMPLE (sans fichier)\n";
    echo "   4. Ça devrait marcher! ✓\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage();
    exit(1);
}
?>
