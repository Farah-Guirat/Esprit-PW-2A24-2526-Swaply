<?php
/**
 * Test Script - Vérifier que la migration voice messages a réussi
 * Accédez à: http://localhost/swaply/test_voice_messages.php
 */

require_once __DIR__ . '/config/database.php';

try {
    $pdo = Database::getInstance()->getConnection();
    
    echo "<h2>🔍 Test: Système de Messages Vocaux</h2>";
    echo "<hr>";
    
    // Vérifier que la colonne type_message existe
    $stmt = $pdo->query("SHOW COLUMNS FROM messages LIKE 'type_message'");
    $hasTypeMessage = $stmt->rowCount() > 0;
    echo $hasTypeMessage ? "✅ Colonne 'type_message' présente<br>" : "❌ Colonne 'type_message' MANQUANTE<br>";
    
    // Vérifier que la colonne voix_duree existe
    $stmt = $pdo->query("SHOW COLUMNS FROM messages LIKE 'voix_duree'");
    $hasVoixDuree = $stmt->rowCount() > 0;
    echo $hasVoixDuree ? "✅ Colonne 'voix_duree' présente<br>" : "❌ Colonne 'voix_duree' MANQUANTE<br>";
    
    // Vérifier le dossier uploads/voice
    $voiceDir = __DIR__ . '/uploads/voice';
    $hasDirVoice = is_dir($voiceDir);
    echo $hasDirVoice ? "✅ Dossier 'uploads/voice' existe<br>" : "❌ Dossier 'uploads/voice' N'EXISTE PAS<br>";
    
    // Vérifier les permissions du dossier
    if ($hasDirVoice) {
        $perms = substr(sprintf('%o', fileperms($voiceDir)), -4);
        $writable = is_writable($voiceDir);
        echo $writable ? "✅ Dossier 'uploads/voice' est ACCESSIBLE en ÉCRITURE (perms: $perms)<br>" : "❌ Dossier 'uploads/voice' N'EST PAS accessible en écriture<br>";
    }
    
    // Afficher un message vocal de test s'il y en a
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM messages WHERE type_message = 'voix'");
    $voiceCount = (int)$stmt->fetchColumn();
    echo "<br>📊 Messages vocaux en base: <strong>$voiceCount</strong><br>";
    
    if ($voiceCount > 0) {
        echo "<br>📋 Dernier message vocal:<br>";
        $stmt = $pdo->query("SELECT id_message, id_expediteur, voix_duree, fichier_taille FROM messages WHERE type_message = 'voix' ORDER BY date_envoi DESC LIMIT 1");
        $msg = $stmt->fetch();
        if ($msg) {
            echo "  • ID: " . $msg['id_message'] . "<br>";
            echo "  • Durée: " . $msg['voix_duree'] . " secondes<br>";
            echo "  • Taille: " . ($msg['fichier_taille'] / 1024) . " KB<br>";
        }
    }
    
    echo "<hr>";
    echo $hasTypeMessage && $hasVoixDuree && $hasDirVoice ? 
        "✨ <strong style='color:green;'>Le système de messages vocaux est PRÊT !</strong>" :
        "⚠️ <strong style='color:red;'>Certaines colonnes/dossiers sont MANQUANTS. Exécutez la migration.</strong>";
    
    echo "<br><br><a href='config/migrate_voice_messages.php'>▶️ Exécuter la migration</a>";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage();
}
?>
