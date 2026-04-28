<?php
// ── Migration pour ajouter les colonnes email et password ──────────────────
require_once __DIR__ . '/database.php';

$pdo = Database::getInstance()->getConnection();

try {
    // Vérifier si les colonnes existent déjà
    $stmt = $pdo->query("SHOW COLUMNS FROM utilisateurs LIKE 'email'");
    $hasEmail = $stmt->rowCount() > 0;

    $stmt = $pdo->query("SHOW COLUMNS FROM utilisateurs LIKE 'password'");
    $hasPassword = $stmt->rowCount() > 0;

    // Ajouter la colonne email si elle n'existe pas
    if (!$hasEmail) {
        $pdo->exec("ALTER TABLE utilisateurs ADD COLUMN email VARCHAR(255) UNIQUE NOT NULL");
        echo "✅ Colonne 'email' ajoutée avec succès\n";
    } else {
        echo "✓ Colonne 'email' existe déjà\n";
    }

    // Ajouter la colonne password si elle n'existe pas
    if (!$hasPassword) {
        $pdo->exec("ALTER TABLE utilisateurs ADD COLUMN password VARCHAR(255) NOT NULL");
        echo "✅ Colonne 'password' ajoutée avec succès\n";
    } else {
        echo "✓ Colonne 'password' existe déjà\n";
    }

    // Insérer les données de test (utilisateurs par défaut)
    // Vérifier si les utilisateurs existent déjà
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM utilisateurs WHERE email = ?");
    
    $stmt->execute(['farah@example.com']);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] == 0) {
        // Insérer les utilisateurs de test
        $pdo->exec("
            INSERT INTO utilisateurs (prenom, nom, email, password) VALUES 
            ('Farah', 'Ksouri', 'farah@example.com', '" . password_hash('password123', PASSWORD_BCRYPT) . "'),
            ('Aziz', 'Ben', 'aziz@example.com', '" . password_hash('password123', PASSWORD_BCRYPT) . "')
        ");
        echo "✅ Utilisateurs de test insérés\n";
    } else {
        echo "✓ Utilisateurs de test existent déjà\n";
    }

    echo "\n✅ Migration complétée avec succès!\n";
    echo "   Identifiants de test:\n";
    echo "   - Farah: farah@example.com / password123\n";
    echo "   - Aziz: aziz@example.com / password123\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage();
    exit(1);
}
?>
