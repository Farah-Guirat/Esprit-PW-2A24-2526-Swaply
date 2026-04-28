<?php
require_once __DIR__ . '/database.php';

$pdo = Database::getInstance()->getConnection();

echo "=== DIAGNOSTIC DES PASSWORDS ===\n\n";

// Récupérer les passwords actuels
$stmt = $pdo->query("SELECT id_u, prenom, nom, email, password FROM utilisateurs LIMIT 5");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    $pwd = $user['password'];
    $isBcrypt = (strlen($pwd) === 60 && strpos($pwd, '$2') === 0);
    
    echo "=== {$user['prenom']} {$user['nom']} ===\n";
    echo "Email: {$user['email']}\n";
    echo "Password: {$pwd}\n";
    echo "Longueur: " . strlen($pwd) . " caractères\n";
    echo "Format: ";
    
    if ($isBcrypt) {
        echo "✅ BCRYPT HACHÉS\n";
    } else {
        echo "⚠️ TEXTE CLAIR (probable)\n";
        
        // Essayer de vérifier si c'est un hash MD5
        if (strlen($pwd) === 32 && ctype_xdigit($pwd)) {
            echo "       → Looks like MD5 hash\n";
        }
    }
    
    echo "\n";
}

echo "=== RECOMMANDATION ===\n";
echo "Si tous les passwords sont en texte clair, il faut:\n";
echo "1. Aller à: http://localhost/swaply/config/hash_passwords_properly.php\n";
echo "2. Cela va les hasher en bcrypt\n";
?>
