<?php
/**
 * Migration: Vérifier et ajouter les colonnes manquantes pour video_calls
 */
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = Database::getInstance()->getConnection();
    
    $migrations = [];
    
    // 1. Ajouter `created_at` si absent
    $stmt = $pdo->prepare("SHOW COLUMNS FROM video_calls LIKE 'created_at'");
    $stmt->execute();
    if ($stmt->rowCount() === 0) {
        $pdo->exec("
            ALTER TABLE video_calls 
            ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER id_video_call
        ");
        $migrations[] = "✓ Colonne 'created_at' ajoutée";
    } else {
        $migrations[] = "✓ Colonne 'created_at' existe déjà";
    }
    
    // 2. Ajouter `date_fin` si absent
    $stmt = $pdo->prepare("SHOW COLUMNS FROM video_calls LIKE 'date_fin'");
    $stmt->execute();
    if ($stmt->rowCount() === 0) {
        $pdo->exec("
            ALTER TABLE video_calls 
            ADD COLUMN date_fin TIMESTAMP NULL AFTER date_debut
        ");
        $migrations[] = "✓ Colonne 'date_fin' ajoutée";
    } else {
        $migrations[] = "✓ Colonne 'date_fin' existe déjà";
    }
    
    // 3. Ajouter `duree_secondes` si absent
    $stmt = $pdo->prepare("SHOW COLUMNS FROM video_calls LIKE 'duree_secondes'");
    $stmt->execute();
    if ($stmt->rowCount() === 0) {
        $pdo->exec("
            ALTER TABLE video_calls 
            ADD COLUMN duree_secondes INT UNSIGNED DEFAULT 0 AFTER date_fin
        ");
        $migrations[] = "✓ Colonne 'duree_secondes' ajoutée";
    } else {
        $migrations[] = "✓ Colonne 'duree_secondes' existe déjà";
    }
    
    // 4. Vérifier la colonne `statut`
    $stmt = $pdo->prepare("SHOW COLUMNS FROM video_calls LIKE 'statut'");
    $stmt->execute();
    if ($stmt->rowCount() === 0) {
        $pdo->exec("
            ALTER TABLE video_calls 
            ADD COLUMN statut ENUM('en_attente', 'en_cours', 'termine', 'rejete') DEFAULT 'en_attente'
        ");
        $migrations[] = "✓ Colonne 'statut' ajoutée";
    } else {
        $migrations[] = "✓ Colonne 'statut' existe déjà";
    }
    
    // 5. État actuel de la table
    $stmt = $pdo->prepare("SHOW COLUMNS FROM video_calls");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    
    // 6. Compter les appels par statut
    $stmt = $pdo->prepare("
        SELECT 
            statut,
            COUNT(*) as count
        FROM video_calls
        GROUP BY statut
    ");
    $stmt->execute();
    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Migration complète',
        'migrations' => $migrations,
        'columns' => $columns,
        'stats' => $stats,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
