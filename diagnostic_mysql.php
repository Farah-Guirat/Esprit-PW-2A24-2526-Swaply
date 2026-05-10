<?php
echo "═══════════════════════════════════════════════════════════════\n";
echo "🔍 DIAGNOSTIC AVANCÉ MYSQL - XAMPP\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// 1. Vérifier les fichiers de configuration
echo "📁 Fichiers de configuration :\n";
$xampp_path = "C:\\xampp";
$mysql_config = $xampp_path . "\\mysql\\bin\\my.ini";
$mysql_data = $xampp_path . "\\mysql\\data";

echo "   - Chemin XAMPP : " . (is_dir($xampp_path) ? "✅ " . $xampp_path : "❌ Non trouvé") . "\n";
echo "   - Config MySQL : " . (file_exists($mysql_config) ? "✅ " . $mysql_config : "❌ Non trouvé") . "\n";
echo "   - Répertoire data : " . (is_dir($mysql_data) ? "✅ " . $mysql_data : "❌ Non trouvé") . "\n\n";

// 2. Lire le fichier de configuration
if (file_exists($mysql_config)) {
    echo "📋 Port configuré dans my.ini :\n";
    $config = file_get_contents($mysql_config);
    if (preg_match('/port\s*=\s*(\d+)/i', $config, $matches)) {
        echo "   Port : " . $matches[1] . "\n\n";
    }
}

// 3. Tester différents ports
echo "🔗 Test de connexion sur différents ports :\n";
$ports = [3306, 3307, 3308, 3309, 33060];
$connected = false;

foreach ($ports as $port) {
    try {
        $conn = new PDO(
            "mysql:host=127.0.0.1;port=$port",
            "root",
            ""
        );
        echo "   ✅ Connecté sur le port $port !\n";
        $connected = true;
        // Tester la base de données swaply
        $databases = $conn->query("SHOW DATABASES")->fetchAll();
        $has_swaply = false;
        foreach ($databases as $db) {
            if ($db[0] === 'swaply') {
                $has_swaply = true;
                break;
            }
        }
        echo "   ✅ Base de données 'swaply' : " . ($has_swaply ? "EXISTE" : "N'EXISTE PAS") . "\n";
    } catch (PDOException $e) {
        echo "   ❌ Port $port : Pas de réponse\n";
    }
}

if (!$connected) {
    echo "\n❌ AUCUN PORT ACCESSIBLE !\n\n";
    echo "💡 Actions à effectuer :\n";
    echo "   1. Ouvrir XAMPP Control Panel (C:\\xampp\\xampp-control.exe)\n";
    echo "   2. Cliquer sur 'Start' pour MySQL\n";
    echo "   3. Attendre que le statut devienne vert\n";
    echo "   4. Rafraîchir cette page (F5)\n";
    
    echo "\n📊 Vérifier les logs MySQL :\n";
    $error_log = $xampp_path . "\\mysql\\data\\mysql_error.log";
    if (file_exists($error_log)) {
        $logs = file_get_contents($error_log);
        $lines = array_slice(explode("\n", $logs), -20); // Dernières 20 lignes
        echo implode("\n", $lines);
    } else {
        echo "   ❌ Fichier de log non trouvé : $error_log\n";
    }
}

echo "\n═══════════════════════════════════════════════════════════════\n";
?>
