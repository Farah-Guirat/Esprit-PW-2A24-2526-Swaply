<?php
/**
 * Script d'installation des tables vidéo
 * Exécute la migration SQL pour créer/vérifier les tables video_calls et video_call_participants
 */

require_once __DIR__ . '/config/database.php';

try {
    $pdo = Database::getInstance()->getConnection();
    echo "<h2>Installation des tables vidéo Swaply</h2>";
    echo "<p>Connexion à la base de données établie.</p>";

    // Lire le fichier de migration
    $migrationPath = __DIR__ . '/migrations/003_create_video_calls.sql';
    if (!file_exists($migrationPath)) {
        die("<p style='color:red;'>❌ Erreur: Fichier de migration non trouvé: $migrationPath</p>");
    }

    $sql = file_get_contents($migrationPath);
    
    // Exécuter les requêtes SQL
    // On split par ; pour exécuter chaque requête séparément
    $queries = array_filter(array_map('trim', explode(';', $sql)), function($q) {
        return !empty($q) && !preg_match('/^--/', $q);
    });

    $successCount = 0;
    $errorCount = 0;

    foreach ($queries as $query) {
        if (empty($query)) continue;
        
        try {
            $pdo->exec($query);
            echo "<p style='color:green;'>✓ Requête exécutée</p>";
            $successCount++;
        } catch (PDOException $e) {
            // Si la table existe déjà, c'est ok
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "<p style='color:blue;'>ℹ Table existe déjà</p>";
                $successCount++;
            } else {
                echo "<p style='color:red;'>❌ Erreur SQL: " . htmlspecialchars($e->getMessage()) . "</p>";
                $errorCount++;
            }
        }
    }

    echo "<hr>";
    echo "<p><strong>Résumé:</strong></p>";
    echo "<p style='color:green;'>✓ Requêtes réussies: $successCount</p>";
    if ($errorCount > 0) {
        echo "<p style='color:red;'>❌ Erreurs: $errorCount</p>";
    } else {
        echo "<p style='color:green;'><strong>✓ Installation réussie! Les tables vidéo sont prêtes.</strong></p>";
    }

    // Vérifier les colonnes de la table video_calls
    echo "<hr>";
    echo "<h3>Vérification de la structure de la table video_calls:</h3>";
    $stmt = $pdo->prepare("DESCRIBE video_calls");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($columns)) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Colonne</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:red;'>❌ La table video_calls n'existe pas!</p>";
    }

} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Erreur: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
