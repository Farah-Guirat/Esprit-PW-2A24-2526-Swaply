<?php
/**
 * Script de migration pour créer les tables nécessaires
 */

require_once "Database.php";

$db = new Database();
$conn = $db->connect();

try {
    // Créer la table email_verification_tokens
    $sql = "CREATE TABLE IF NOT EXISTS email_verification_tokens (
        id_token INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        token VARCHAR(255) NOT NULL UNIQUE,
        user_data LONGTEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME NOT NULL,
        verified INT DEFAULT 0
    )";
    
    $conn->exec($sql);
    echo "✓ Table email_verification_tokens créée avec succès ou existe déjà\n";
    
    // Ajouter une colonne verified à la table utilisateurs si elle n'existe pas
    $checkColumn = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_NAME = 'utilisateurs' AND COLUMN_NAME = 'email_verified'";
    $stmt = $conn->query($checkColumn);
    
    if ($stmt->rowCount() == 0) {
        $conn->exec("ALTER TABLE utilisateurs ADD COLUMN email_verified INT DEFAULT 0");
        echo "✓ Colonne email_verified ajoutée à la table utilisateurs\n";
    } else {
        echo "✓ Colonne email_verified existe déjà\n";
    }
    
} catch (Exception $e) {
    echo "✗ Erreur : " . $e->getMessage() . "\n";
}

?>
