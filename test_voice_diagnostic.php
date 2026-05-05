<?php
/**
 * Script de diagnostic - Messages Vocaux
 * Accédez à: http://localhost/swaply/test_voice_diagnostic.php
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/model/Message.php';

echo "<h2>🔍 Diagnostic - Messages Vocaux</h2>";
echo "<hr>";

try {
    $pdo = Database::getInstance()->getConnection();
    $messageModel = new Message();
    
    // 1. Vérifier les colonnes
    echo "<h3>1️⃣ Vérification de la Base de Données</h3>";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM messages LIKE 'type_message'");
    $hasTypeMessage = $stmt->rowCount() > 0;
    echo $hasTypeMessage ? "✅ Colonne 'type_message' présente<br>" : "❌ Colonne 'type_message' MANQUANTE<br>";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM messages LIKE 'voix_duree'");
    $hasVoixDuree = $stmt->rowCount() > 0;
    echo $hasVoixDuree ? "✅ Colonne 'voix_duree' présente<br>" : "❌ Colonne 'voix_duree' MANQUANTE<br>";
    
    // 2. Vérifier le dossier uploads/voice
    echo "<h3>2️⃣ Vérification des Fichiers</h3>";
    
    $voiceDir = __DIR__ . '/uploads/voice';
    echo "Chemin: $voiceDir<br>";
    echo is_dir($voiceDir) ? "✅ Dossier existe<br>" : "❌ Dossier N'EXISTE PAS<br>";
    
    if (is_dir($voiceDir)) {
        echo "✅ Accessible en écriture: " . (is_writable($voiceDir) ? "OUI" : "NON") . "<br>";
        
        $files = scandir($voiceDir);
        $audioFiles = array_filter($files, fn($f) => pathinfo($f, PATHINFO_EXTENSION) === 'webm');
        echo "📁 Fichiers audio: " . count($audioFiles) . "<br>";
        
        if (count($audioFiles) > 0) {
            echo "<ul>";
            foreach (array_slice($audioFiles, -5) as $file) {
                $path = $voiceDir . '/' . $file;
                $size = filesize($path);
                echo "<li>$file (" . round($size / 1024, 2) . " KB)</li>";
            }
            echo "</ul>";
        }
    }
    
    // 3. Vérifier les messages vocaux en base
    echo "<h3>3️⃣ Messages Vocaux en Base</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM messages WHERE type_message = 'voix'");
    $voiceCount = (int)$stmt->fetchColumn();
    echo "Total: <strong>$voiceCount</strong> messages vocaux<br>";
    
    if ($voiceCount > 0) {
        echo "<h4>Derniers messages vocaux:</h4>";
        $stmt = $pdo->query("
            SELECT m.id_message, m.id_expediteur, m.voix_duree, m.fichier_taille, m.date_envoi, u.prenom, u.nom
            FROM messages m
            LEFT JOIN utilisateurs u ON u.id_u = m.id_expediteur
            WHERE m.type_message = 'voix'
            ORDER BY m.date_envoi DESC
            LIMIT 5
        ");
        
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Utilisateur</th><th>Durée</th><th>Taille</th><th>Date</th></tr>";
        foreach ($stmt->fetchAll() as $msg) {
            $user = ($msg['prenom'] && $msg['nom']) ? $msg['prenom'] . ' ' . $msg['nom'] : 'ID ' . $msg['id_expediteur'];
            echo "<tr>";
            echo "<td>" . $msg['id_message'] . "</td>";
            echo "<td>" . htmlspecialchars($user) . "</td>";
            echo "<td>" . $msg['voix_duree'] . "s</td>";
            echo "<td>" . round($msg['fichier_taille'] / 1024, 2) . " KB</td>";
            echo "<td>" . $msg['date_envoi'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 4. Vérifier les autres types de messages
    echo "<h3>4️⃣ Répartition des Messages</h3>";
    
    $stmt = $pdo->query("SELECT type_message, COUNT(*) as cnt FROM messages GROUP BY type_message");
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Type</th><th>Quantité</th></tr>";
    foreach ($stmt->fetchAll() as $row) {
        echo "<tr><td>" . htmlspecialchars($row['type_message']) . "</td><td>" . $row['cnt'] . "</td></tr>";
    }
    echo "</table>";
    
    // 5. Vérifier les erreurs de fichiers
    echo "<h3>5️⃣ Vérification des Fichiers Orphelins</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM messages WHERE fichier_path IS NOT NULL AND fichier_path != ''");
    $totalFiles = (int)$stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM messages WHERE type_message = 'voix' AND fichier_path IS NOT NULL");
    $voiceFilesInDb = (int)$stmt->fetchColumn();
    
    echo "📊 Total fichiers en base: $totalFiles<br>";
    echo "🎤 Fichiers vocaux en base: $voiceFilesInDb<br>";
    
    if ($voiceFilesInDb > 0) {
        $stmt = $pdo->query("SELECT fichier_path FROM messages WHERE type_message = 'voix' AND fichier_path IS NOT NULL LIMIT 1");
        $firstFile = $stmt->fetchColumn();
        if ($firstFile) {
            $fullPath = __DIR__ . '/' . $firstFile;
            $exists = file_exists($fullPath);
            echo "Exemple: $firstFile<br>";
            echo $exists ? "✅ Fichier existe<br>" : "❌ Fichier N'EXISTE PAS<br>";
        }
    }
    
    echo "<hr>";
    echo "<h3>✨ Résumé</h3>";
    
    if ($hasTypeMessage && $hasVoixDuree && is_dir($voiceDir) && is_writable($voiceDir)) {
        echo "✅ <strong style='color:green;'>Tout semble OK!</strong><br>";
        if ($voiceCount > 0) {
            echo "✅ <strong>$voiceCount messages vocaux trouvés en base</strong>";
        }
    } else {
        echo "❌ <strong style='color:red;'>Certains problèmes détectés:</strong>";
        if (!$hasTypeMessage) echo "<br>❌ Colonne type_message manquante";
        if (!$hasVoixDuree) echo "<br>❌ Colonne voix_duree manquante";
        if (!is_dir($voiceDir)) echo "<br>❌ Dossier uploads/voice manquant";
        if (is_dir($voiceDir) && !is_writable($voiceDir)) echo "<br>❌ Dossier uploads/voice non accessible";
    }
    
    echo "<br><br><a href='config/migrate_voice_messages.php'>▶️ Exécuter la migration</a>";
    
} catch (Exception $e) {
    echo "<h3>❌ Erreur</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
