<?php
/**
 * Nettoyage des appels vidéo - Pour résoudre les erreurs 409 Conflict
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/model/VideoCall.php';

try {
    $pdo = Database::getInstance()->getConnection();
    $videoCallModel = new VideoCall();
    
    echo "<h2>🧹 Nettoyage des appels vidéo</h2>";
    
    // 1. Nettoyer les appels "en attente" depuis plus d'1 minute
    $stmt = $pdo->prepare(
        "SELECT id_video_call, statut, created_at 
         FROM video_calls 
         WHERE statut IN ('en_attente', 'en_cours') 
         AND created_at < DATE_SUB(NOW(), INTERVAL 1 MINUTE)"
    );
    $stmt->execute();
    $oldCalls = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Appels anciens trouvés:</strong> " . count($oldCalls) . "</p>";
    
    foreach ($oldCalls as $call) {
        $videoCallModel->endCall($call['id_video_call']);
        echo "<p style='color: green;'>✓ Appel #{$call['id_video_call']} ({$call['statut']}) terminé - créé: {$call['created_at']}</p>";
    }
    
    // 2. Afficher l'état des appels par conversation
    $stmt = $pdo->query(
        "SELECT 
            c.id_conversation,
            COUNT(DISTINCT vc.id_video_call) as total_calls,
            SUM(CASE WHEN vc.statut IN ('en_attente', 'en_cours') THEN 1 ELSE 0 END) as active_calls
         FROM conversations c
         LEFT JOIN video_calls vc ON vc.id_conversation = c.id_conversation
         GROUP BY c.id_conversation
         HAVING active_calls > 0"
    );
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Conversations avec appels actifs:</strong> " . count($results) . "</p>";
    
    if (empty($results)) {
        echo "<p style='color: green;'>✓ Aucun appel actif - système propre!</p>";
    } else {
        foreach ($results as $row) {
            echo "<p style='color: orange;'>⚠ Conversation #{$row['id_conversation']}: {$row['active_calls']} appel(s) actif(s)</p>";
        }
    }
    
    echo "<hr>";
    echo "<p><a href='view/Front/Messages.php'>Retour à la messagerie</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
