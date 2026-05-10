<?php
echo "═══════════════════════════════════════════════════════════════\n";
echo "🔍 DIAGNOSTIC CONNEXION MYSQL\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Vérifier les extensions PHP
echo "📋 Extensions PHP :\n";
echo "- Extension PDO disponible : " . (extension_loaded('PDO') ? "✅ OUI" : "❌ NON") . "\n";
echo "- Extension PDO MySQL disponible : " . (extension_loaded('pdo_mysql') ? "✅ OUI" : "❌ NON") . "\n\n";

// Tester la connexion
echo "🔗 Test de connexion :\n";
try {
    $conn = new PDO(
        "mysql:host=localhost",
        "root",
        ""
    );
    echo "✅ Connexion à MySQL établie avec succès !\n";
    
    // Vérifier les bases de données existantes
    $databases = $conn->query("SHOW DATABASES")->fetchAll();
    echo "\n📂 Bases de données disponibles :\n";
    foreach ($databases as $db) {
        $dbName = $db[0];
        echo "   - $dbName\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion : " . $e->getMessage() . "\n";
    echo "\n💡 Solutions possibles :\n";
    echo "   1. Démarrer MySQL via le XAMPP Control Panel\n";
    echo "   2. Vérifier que le port 3306 est disponible\n";
    echo "   3. Vérifier les logs MySQL dans : C:\\xampp\\mysql\\data\\mysql_error.log\n";
}

echo "\n═══════════════════════════════════════════════════════════════\n";
?>
