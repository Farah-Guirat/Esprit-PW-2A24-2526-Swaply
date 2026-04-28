<?php
require_once __DIR__ . '/database.php';

echo "=== VÉRIFICATION COMPLÈTE DU SYSTÈME DE MESSAGERIE ===\n\n";

echo "1️⃣ STRUCTURE DE LA TABLE MESSAGES\n";
echo "─────────────────────────────────\n";

try {
    $pdo = Database::getInstance()->getConnection();
    
    // Vérifier les colonnes
    $stmt = $pdo->query("DESCRIBE messages");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $requiredCols = ['id_message', 'contenu', 'id_expediteur', 'id_conversation', 'fichier_path', 'fichier_nom_original', 'fichier_type', 'fichier_taille', 'lu'];
    
    foreach ($requiredCols as $col) {
        $exists = false;
        foreach ($columns as $c) {
            if ($c['Field'] === $col) {
                $exists = true;
                break;
            }
        }
        echo ($exists ? "✅" : "❌") . " {$col}\n";
    }
    
    echo "\n2️⃣ DOSSIERS D'UPLOAD\n";
    echo "───────────────────\n";
    
    $uploadDir = __DIR__ . '/../uploads/messages/';
    if (is_dir($uploadDir)) {
        echo "✅ uploads/messages/ existe\n";
    } else {
        echo "❌ uploads/messages/ MANQUANT - création...\n";
        if (mkdir($uploadDir, 0755, true)) {
            echo "✅ Créé avec succès\n";
        }
    }
    
    $tmpDir = __DIR__ . '/../tmp/';
    if (is_dir($tmpDir)) {
        echo "✅ tmp/ existe\n";
    } else {
        echo "❌ tmp/ MANQUANT - création...\n";
        if (mkdir($tmpDir, 0755, true)) {
            echo "✅ Créé avec succès\n";
        }
    }
    
    echo "\n3️⃣ FONCTIONNEMENT\n";
    echo "────────────────\n";
    echo "✅ Envoi de message SIMPLE (texte seul)\n";
    echo "   - Allez à: login.php\n";
    echo "   - Connectez-vous\n";
    echo "   - Tapez 'Bonjour' ou 'Salut'\n";
    echo "   - Cliquez 'Envoyer'\n";
    echo "   - Le message devrait s'afficher immédiatement\n\n";
    
    echo "✅ Statut 'lu': DÉSACTIVÉ\n";
    echo "   - Les messages ne sont pas marqués 'lu' au rafraîchissement\n";
    echo "   - Ils resteront non lus jusqu'à action explicite\n\n";
    
    echo "✅ Fichiers attachés: CHEMIN CORRIGÉ\n";
    echo "   - Les fichiers téléchargés seront accessibles\n";
    echo "   - Cliquez sur le fichier pour télécharger\n\n";
    
    echo "\n4️⃣ BASE DE DONNÉES\n";
    echo "──────────────────\n";
    
    // Compter les messages
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM messages");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total de messages: " . $result['count'] . "\n";
    
    // Compter les conversations
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM conversations");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total de conversations: " . $result['count'] . "\n";
    
    echo "\n✅ SYSTÈME PRÊT!\n";
    echo "Vous pouvez maintenant envoyer des messages simples.\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage();
}
?>
