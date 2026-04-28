<?php
require_once __DIR__ . '/database.php';

$pdo = Database::getInstance()->getConnection();

echo "=== HACHAGE DES PASSWORDS ===\n\n";

try {
    // Récupérer tous les utilisateurs
    $stmt = $pdo->query("SELECT id_u, password FROM utilisateurs");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $count = 0;
    foreach ($users as $user) {
        $pwd = $user['password'];
        
        // Vérifier si déjà hachés (bcrypt = 60 caractères et commence par $2)
        if (strlen($pwd) !== 60 || strpos($pwd, '$2') !== 0) {
            // Password en texte clair, le hasher
            $hashedPwd = password_hash($pwd, PASSWORD_BCRYPT);
            
            $updateStmt = $pdo->prepare("UPDATE utilisateurs SET password = ? WHERE id_u = ?");
            $updateStmt->execute([$hashedPwd, $user['id_u']]);
            
            echo "✅ ID {$user['id_u']}: '{$pwd}' → hachage en cours...\n";
            $count++;
        } else {
            echo "✓ ID {$user['id_u']}: Déjà hachés (bcrypt)\n";
        }
    }

    echo "\n✅ Opération complétée!\n";
    echo "   {$count} passwords ont été hachés\n";
    echo "\n💡 Vous pouvez maintenant vous connecter avec:\n";
    echo "   - Email et le password d'AVANT le hachage\n";
    echo "   - Exemple: farah@gmail.com / (votre ancien password)\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage();
    exit(1);
}
?>
