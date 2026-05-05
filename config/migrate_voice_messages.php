<?php
/**
 * Migration: Ajouter le support des messages vocaux
 * Exécuter avec: php config/migrate_voice_messages.php
 */

require_once __DIR__ . '/database.php';

try {
    $pdo = Database::getInstance()->getConnection();

    // Ajouter colonne type_message
    try {
        $pdo->exec("ALTER TABLE messages ADD COLUMN type_message VARCHAR(20) DEFAULT 'texte' AFTER fichier_taille");
        echo "✅ Colonne 'type_message' ajoutée avec succès\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "ℹ️  Colonne 'type_message' existe déjà\n";
        } else {
            throw $e;
        }
    }

    // Ajouter colonne voix_duree (durée du message vocal en secondes)
    try {
        $pdo->exec("ALTER TABLE messages ADD COLUMN voix_duree INT DEFAULT 0 AFTER type_message");
        echo "✅ Colonne 'voix_duree' ajoutée avec succès\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "ℹ️  Colonne 'voix_duree' existe déjà\n";
        } else {
            throw $e;
        }
    }

    // Créer un index sur type_message pour optimiser les requêtes
    try {
        $pdo->exec("CREATE INDEX idx_type_message ON messages(type_message)");
        echo "✅ Index 'idx_type_message' créé avec succès\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "ℹ️  Index 'idx_type_message' existe déjà\n";
        } else {
            throw $e;
        }
    }

    // Créer le dossier pour les messages vocaux
    $uploadDir = __DIR__ . '/../uploads/voice';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
        echo "✅ Dossier 'uploads/voice' créé avec succès\n";
    } else {
        echo "ℹ️  Dossier 'uploads/voice' existe déjà\n";
    }

    echo "\n✨ Migration réussie ! Le système de messages vocaux est maintenant prêt.\n";

} catch (Exception $e) {
    echo "❌ Erreur lors de la migration : " . $e->getMessage() . "\n";
    exit(1);
}
?>
