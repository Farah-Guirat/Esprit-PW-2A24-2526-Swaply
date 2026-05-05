<?php
/**
 * Migration pour ajouter le support des réactions aux messages
 * Exécuter une fois pour mettre à jour la base de données
 */
require_once __DIR__ . '/../config/database.php';

$pdo = Database::getInstance()->getConnection();

try {
    // Vérifier si la table 'message_reactions' existe déjà
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'message_reactions'");
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        // Créer la table message_reactions
        $pdo->exec("
            CREATE TABLE message_reactions (
                id_reaction INT AUTO_INCREMENT PRIMARY KEY,
                id_message INT NOT NULL,
                id_user INT NOT NULL,
                emoji VARCHAR(10) NOT NULL,
                date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_user_emoji (id_message, id_user, emoji),
                FOREIGN KEY (id_message) REFERENCES messages(id_message) ON DELETE CASCADE,
                FOREIGN KEY (id_user) REFERENCES utilisateurs(id_u) ON DELETE CASCADE
            )
        ");
        echo "✓ Table 'message_reactions' créée avec succès\n";
    } else {
        echo "✓ Table 'message_reactions' existe déjà\n";
    }

    echo "\n✓ Migration des réactions terminée avec succès!\n";
} catch (Exception $e) {
    die("Erreur lors de la migration: " . $e->getMessage());
}
?>
