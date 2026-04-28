<?php
require_once __DIR__ . '/database.php';

$pdo = Database::getInstance()->getConnection();

echo "=== VÉRIFICATION DES PASSWORDS ===\n\n";

// Récupérer les passwords actuels
$stmt = $pdo->query("SELECT id_u, prenom, nom, password FROM utilisateurs LIMIT 5");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    $pwd = $user['password'];
    $isBcrypt = (strlen($pwd) === 60 && strpos($pwd, '$2') === 0);
    
    echo "ID: {$user['id_u']} | {$user['prenom']} {$user['nom']}\n";
    echo "  Password: " . substr($pwd, 0, 30) . "...\n";
    echo "  Hachage: " . ($isBcrypt ? "✅ BCRYPT (sûr)" : "❌ TEXTE CLAIR (besoin de hasher)") . "\n";
    echo "  Longueur: " . strlen($pwd) . " caractères\n\n";
}
?>
