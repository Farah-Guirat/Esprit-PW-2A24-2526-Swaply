<?php
require_once __DIR__ . '/database.php';

$pdo = Database::getInstance()->getConnection();

echo "=== STRUCTURE DE LA TABLE UTILISATEURS ===\n\n";

// Afficher les colonnes
$stmt = $pdo->query("DESCRIBE utilisateurs");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($columns as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")" . ($col['Null'] === 'NO' ? ' - NOT NULL' : '') . "\n";
}

echo "\n=== UTILISATEURS EXISTANTS ===\n\n";

// Afficher les utilisateurs
$stmt = $pdo->query("SELECT * FROM utilisateurs LIMIT 10");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    echo "ID: " . $user['id_u'] . " | ";
    echo "Prénom: " . $user['prenom'] . " | ";
    echo "Nom: " . $user['nom'];
    
    if (isset($user['email'])) {
        echo " | Email: " . ($user['email'] ?? 'NULL');
    }
    if (isset($user['password'])) {
        echo " | Password: " . ($user['password'] ? 'OUI' : 'NULL');
    }
    echo "\n";
}

echo "\n=== TOTAL D'UTILISATEURS ===\n";
$stmt = $pdo->query("SELECT COUNT(*) as count FROM utilisateurs");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Total: " . $result['count'] . " utilisateurs\n";
?>
