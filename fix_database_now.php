<?php
/**
 * FIX IMMÉDIAT: Ajouter les colonnes manquantes
 */
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = Database::getInstance()->getConnection();
    
    echo "🔧 Réparation de la base de données...\n\n";
    
    // 1. Ajouter colonne duree_secondes
    try {
        $pdo->exec("ALTER TABLE video_calls ADD COLUMN duree_secondes INT UNSIGNED DEFAULT 0");
        echo "✅ Colonne 'duree_secondes' ajoutée\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "✅ Colonne 'duree_secondes' existe déjà\n";
        } else {
            echo "❌ Erreur duree_secondes: " . $e->getMessage() . "\n";
        }
    }
    
    // 2. Ajouter colonne date_fin
    try {
        $pdo->exec("ALTER TABLE video_calls ADD COLUMN date_fin TIMESTAMP NULL");
        echo "✅ Colonne 'date_fin' ajoutée\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "✅ Colonne 'date_fin' existe déjà\n";
        } else {
            echo "❌ Erreur date_fin: " . $e->getMessage() . "\n";
        }
    }
    
    // 3. Ajouter colonne created_at
    try {
        $pdo->exec("ALTER TABLE video_calls ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        echo "✅ Colonne 'created_at' ajoutée\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "✅ Colonne 'created_at' existe déjà\n";
        } else {
            echo "❌ Erreur created_at: " . $e->getMessage() . "\n";
        }
    }
    
    // 4. Ajouter colonne statut
    try {
        $pdo->exec("ALTER TABLE video_calls ADD COLUMN statut ENUM('en_attente', 'en_cours', 'termine', 'rejete') DEFAULT 'en_attente'");
        echo "✅ Colonne 'statut' ajoutée\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "✅ Colonne 'statut' existe déjà\n";
        } else {
            echo "❌ Erreur statut: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    // 5. Nettoyer les appels bloqués
    echo "🧹 Nettoyage des appels bloqués...\n";
    $stmt = $pdo->prepare("
        UPDATE video_calls 
        SET statut = 'termine', date_fin = NOW()
        WHERE statut IN ('en_attente', 'en_cours')
    ");
    $result = $stmt->execute();
    $cleaned = $stmt->rowCount();
    echo "✅ $cleaned appel(s) nettoyé(s)\n";
    
    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    // 6. Afficher la structure finale
    echo "\n📊 Structure table video_calls:\n";
    $stmt = $pdo->prepare("SHOW COLUMNS FROM video_calls");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        echo "  • {$col['Field']}: {$col['Type']}\n";
    }
    
    echo "\n✅ BASE DE DONNÉES RÉPARÉE!\n";
    echo "✅ TOUS LES APPELS NETTOYÉS!\n";
    echo "\n→ L'appel vidéo est PRÊT! 🎉\n";
    
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    http_response_code(500);
}
