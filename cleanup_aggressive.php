<?php
/**
 * Nettoyage AGRESSIF des appels vidéo stagnants
 * ATTENTION: Supprime tous les appels de plus de 1 minute
 */
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = Database::getInstance()->getConnection();
    
    // 1. MARQUER comme "termine" tous les appels qui ne sont pas "termine"
    //    et qui ont plus de 1 minute
    $stmt = $pdo->prepare("
        UPDATE video_calls 
        SET statut = 'termine', date_fin = NOW()
        WHERE statut IN ('en_attente', 'en_cours')
        AND (
            TIMESTAMPDIFF(MINUTE, COALESCE(date_debut, created_at), NOW()) >= 1
            OR date_debut IS NULL
        )
    ");
    $result1 = $stmt->execute();
    $updated_count = $stmt->rowCount();
    
    // 2. Détail des appels nettoyés
    $stmt = $pdo->prepare("
        SELECT 
            id_video_call, 
            id_conversation, 
            statut,
            COALESCE(date_debut, created_at) as date_debut,
            TIMESTAMPDIFF(SECOND, COALESCE(date_debut, created_at), NOW()) as age_secondes
        FROM video_calls 
        WHERE statut = 'termine'
        ORDER BY date_fin DESC
        LIMIT 10
    ");
    $stmt->execute();
    $cleaned_calls = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 3. Compter les appels actifs restants (< 1 minute)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count_active FROM video_calls 
        WHERE statut IN ('en_attente', 'en_cours')
    ");
    $stmt->execute();
    $active_count = $stmt->fetch()['count_active'];
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Nettoyage effectué avec succès',
        'cleaned' => [
            'count' => $updated_count,
            'calls' => $cleaned_calls
        ],
        'active_remaining' => $active_count,
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
