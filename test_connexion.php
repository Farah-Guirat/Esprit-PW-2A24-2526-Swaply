<?php
echo "═══════════════════════════════════════════════════════════════\n";
echo "🔗 TEST CONNEXION MYSQL\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "Tentative de connexion à MySQL...\n";

try {
    $conn = new PDO(
        "mysql:host=localhost",
        "root",
        ""
    );
    echo "✅ CONNECTION RÉUSSIE ! MySQL est opérationnel.\n\n";
    
    // Tester la base de données swaply
    try {
        $conn->exec("USE swaply");
        echo "✅ Base de données 'swaply' existe et est accessible.\n";
    } catch (Exception $e) {
        echo "⚠️ Base de données 'swaply' n'existe pas ou n'est pas accessible.\n";
        echo "   Erreur : " . $e->getMessage() . "\n";
    }
    
} catch (PDOException $e) {
    echo "❌ ERREUR DE CONNEXION :\n";
    echo $e->getMessage() . "\n\n";
    echo "💡 Solutions :\n";
    echo "   1. Vérifier que le processus MySQL est en cours d'exécution\n";
    echo "   2. Attendre quelques secondes après le démarrage\n";
    echo "   3. Redémarrer le service MySQL\n";
}

echo "\n═══════════════════════════════════════════════════════════════\n";
?>
