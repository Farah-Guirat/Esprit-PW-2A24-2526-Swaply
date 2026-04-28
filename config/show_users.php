<?php
require_once __DIR__ . '/database.php';

$pdo = Database::getInstance()->getConnection();

echo "=== UTILISATEURS ET PASSWORDS ===\n\n";

// Récupérer tous les utilisateurs
$stmt = $pdo->query("SELECT id_u, prenom, nom, email, password FROM utilisateurs");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    $pwd = $user['password'];
    $isBcrypt = (strlen($pwd) === 60 && strpos($pwd, '$2') === 0);
    
    echo "👤 {$user['prenom']} {$user['nom']}\n";
    echo "   Email: {$user['email']}\n";
    
    if ($isBcrypt) {
        echo "   Password: ❓ HACHÉS EN BCRYPT (impossible à voir)\n";
        echo "   ⚠️ Vous ne pouvez pas voir le mot de passe\n";
        echo "   💡 Si vous l'avez oublié, demandez une réinitialisation\n";
    } else {
        echo "   Password: {$pwd}\n";
        echo "   ✅ En texte clair - vous pouvez vous connecter avec\n";
    }
    
    echo "\n";
}
?>
