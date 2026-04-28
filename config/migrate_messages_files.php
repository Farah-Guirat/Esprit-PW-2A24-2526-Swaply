<?php
require_once __DIR__ . '/database.php';

$pdo = Database::getInstance()->getConnection();

echo "=== MIGRATION: AJOUTER LES COLONNES DE FICHIERS ===\n\n";

try {
    // Vérifier si les colonnes existent déjà
    $stmt = $pdo->query("SHOW COLUMNS FROM messages LIKE 'fichier_path'");
    $hasFilePath = $stmt->rowCount() > 0;

    $stmt = $pdo->query("SHOW COLUMNS FROM messages LIKE 'fichier_nom_original'");
    $hasFileNomOriginal = $stmt->rowCount() > 0;

    $stmt = $pdo->query("SHOW COLUMNS FROM messages LIKE 'fichier_type'");
    $hasFileType = $stmt->rowCount() > 0;

    $stmt = $pdo->query("SHOW COLUMNS FROM messages LIKE 'fichier_taille'");
    $hasFileTaille = $stmt->rowCount() > 0;

    // Ajouter les colonnes si elles n'existent pas
    if (!$hasFilePath) {
        $pdo->exec("ALTER TABLE messages ADD COLUMN fichier_path VARCHAR(255) NULL");
        echo "✅ Colonne 'fichier_path' ajoutée\n";
    } else {
        echo "✓ Colonne 'fichier_path' existe déjà\n";
    }

    if (!$hasFileNomOriginal) {
        $pdo->exec("ALTER TABLE messages ADD COLUMN fichier_nom_original VARCHAR(255) NULL");
        echo "✅ Colonne 'fichier_nom_original' ajoutée\n";
    } else {
        echo "✓ Colonne 'fichier_nom_original' existe déjà\n";
    }

    if (!$hasFileType) {
        $pdo->exec("ALTER TABLE messages ADD COLUMN fichier_type VARCHAR(50) NULL");
        echo "✅ Colonne 'fichier_type' ajoutée\n";
    } else {
        echo "✓ Colonne 'fichier_type' existe déjà\n";
    }

    if (!$hasFileTaille) {
        $pdo->exec("ALTER TABLE messages ADD COLUMN fichier_taille INT NULL");
        echo "✅ Colonne 'fichier_taille' ajoutée\n";
    } else {
        echo "✓ Colonne 'fichier_taille' existe déjà\n";
    }

    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✅ MIGRATION COMPLÉTÉE!\n";
    echo "   Vous pouvez maintenant envoyer des messages.\n\n";

    echo "💡 PROCHAINES ÉTAPES:\n";
    echo "   1. Allez à: http://localhost/swaply/view/Front/login.php\n";
    echo "   2. Connectez-vous\n";
    echo "   3. Essayez d'envoyer un message\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage();
    exit(1);
}
?>
