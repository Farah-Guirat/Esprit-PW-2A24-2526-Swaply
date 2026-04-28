<?php
/**
 * Migration pour ajouter le support des fichiers aux messages
 * Exécuter une fois pour mettre à jour la base de données
 */
require_once __DIR__ . '/../config/database.php';

$pdo = Database::getInstance()->getConnection();

try {
    // Vérifier si la colonne 'fichier_path' existe déjà
    $stmt = $pdo->prepare("SHOW COLUMNS FROM messages LIKE 'fichier_path'");
    $stmt->execute();
    if ($stmt->rowCount() === 0) {
        // Ajouter la colonne fichier_path
        $pdo->exec("ALTER TABLE messages ADD COLUMN fichier_path VARCHAR(255) NULL AFTER contenu");
        echo "✓ Colonne 'fichier_path' ajoutée aux messages\n";
    }

    // Vérifier si la colonne 'fichier_nom_original' existe déjà
    $stmt = $pdo->prepare("SHOW COLUMNS FROM messages LIKE 'fichier_nom_original'");
    $stmt->execute();
    if ($stmt->rowCount() === 0) {
        // Ajouter la colonne fichier_nom_original
        $pdo->exec("ALTER TABLE messages ADD COLUMN fichier_nom_original VARCHAR(255) NULL AFTER fichier_path");
        echo "✓ Colonne 'fichier_nom_original' ajoutée aux messages\n";
    }

    // Vérifier si la colonne 'fichier_type' existe déjà
    $stmt = $pdo->prepare("SHOW COLUMNS FROM messages LIKE 'fichier_type'");
    $stmt->execute();
    if ($stmt->rowCount() === 0) {
        // Ajouter la colonne fichier_type
        $pdo->exec("ALTER TABLE messages ADD COLUMN fichier_type VARCHAR(50) NULL AFTER fichier_nom_original");
        echo "✓ Colonne 'fichier_type' ajoutée aux messages\n";
    }

    // Vérifier si la colonne 'fichier_taille' existe déjà
    $stmt = $pdo->prepare("SHOW COLUMNS FROM messages LIKE 'fichier_taille'");
    $stmt->execute();
    if ($stmt->rowCount() === 0) {
        // Ajouter la colonne fichier_taille
        $pdo->exec("ALTER TABLE messages ADD COLUMN fichier_taille INT NULL AFTER fichier_type");
        echo "✓ Colonne 'fichier_taille' ajoutée aux messages\n";
    }

    echo "\n✓ Migration terminée avec succès!\n";
} catch (Exception $e) {
    die("Erreur lors de la migration: " . $e->getMessage());
}
?>
