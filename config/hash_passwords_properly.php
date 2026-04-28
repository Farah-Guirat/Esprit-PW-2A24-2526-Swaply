<?php
require_once __DIR__ . '/database.php';

$pdo = Database::getInstance()->getConnection();

echo "=== HACHAGE DES PASSWORDS EN BCRYPT ===\n\n";

try {
    // Récupérer tous les utilisateurs
    $stmt = $pdo->query("SELECT id_u, prenom, nom, email, password FROM utilisateurs");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $count = 0;
    $alreadyHashed = 0;
    
    foreach ($users as $user) {
        $pwd = $user['password'];
        $id = $user['id_u'];
        $name = $user['prenom'] . ' ' . $user['nom'];
        
        // Vérifier si déjà hachés en bcrypt (60 caractères et commence par $2)
        if (strlen($pwd) === 60 && strpos($pwd, '$2') === 0) {
            echo "✓ ID {$id} ({$name}): Déjà hachés\n";
            $alreadyHashed++;
        } else {
            // Password en texte clair, le hasher
            $hashedPwd = password_hash($pwd, PASSWORD_BCRYPT, ['cost' => 11]);
            
            $updateStmt = $pdo->prepare("UPDATE utilisateurs SET password = ? WHERE id_u = ?");
            $updateStmt->execute([$hashedPwd, $id]);
            
            echo "✅ ID {$id} ({$name}): '{$pwd}' → Hachés avec succès\n";
            $count++;
        }
    }

    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✅ OPÉRATION COMPLÉTÉE!\n";
    echo "   - {$count} passwords hachés\n";
    echo "   - {$alreadyHashed} passwords déjà hachés\n";
    echo "   - Total: " . ($count + $alreadyHashed) . " utilisateurs\n\n";
    
    echo "💡 PROCHAINE ÉTAPE:\n";
    echo "   1. Allez à: http://localhost/swaply/view/Front/login.php\n";
    echo "   2. Entrez votre email\n";
    echo "   3. Entrez le PASSWORD EN TEXTE CLAIR d'avant le hachage\n";
    echo "   4. Cliquez 'Se Connecter'\n\n";
    
    echo "⚠️ IMPORTANT:\n";
    echo "   Le password que vous avez en base de données AVANT le hachage\n";
    echo "   doit être utilisé pour se connecter (le système va l'hasher\n";
    echo "   et le comparer avec le hash en base de données)\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage();
    exit(1);
}
?>
