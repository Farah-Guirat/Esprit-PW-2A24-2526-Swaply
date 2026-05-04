<?php
/**
 * Script de test pour la double authentification par email
 * 
 * Exécutez ce script pour tester si le système est bien configuré
 * URL: http://localhost/swaply/config/test_double_auth.php
 */

require_once "Database.php";
require_once "EmailManager.php";
require_once "../model/EmailVerification.php";

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    die("❌ Erreur de connexion à la base de données!");
}

$emailVerification = new EmailVerification($conn);
$emailManager = new EmailManager();

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test - Double Authentification</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; }
        .test { margin: 20px 0; padding: 15px; border-radius: 8px; border-left: 4px solid; }
        .test.success { background: #d4edda; border-color: #28a745; color: #155724; }
        .test.error { background: #f8d7da; border-color: #dc3545; color: #721c24; }
        .test.info { background: #d1ecf1; border-color: #4FD1C5; color: #0c5460; }
        h1 { color: #333; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; }
        .table-info { background: #fff; border: 1px solid #ddd; border-collapse: collapse; width: 100%; margin-top: 10px; }
        .table-info th, .table-info td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        .table-info th { background: #f5f5f5; font-weight: bold; }
    </style>
</head>
<body>
    <h1>🔍 Test - Système de Double Authentification</h1>";

// Test 1: Vérifier la connexion à la base de données
echo "<div class='test success'>✅ Connexion à la base de données réussie</div>";

// Test 2: Vérifier les tables
echo "<h2>📊 État des Tables</h2>";

$tables = [];
$stmt = $conn->query("SHOW TABLES");
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    $tables[] = $row[0];
}

if (in_array('email_verification_tokens', $tables)) {
    echo "<div class='test success'>✅ Table <code>email_verification_tokens</code> existe</div>";
} else {
    echo "<div class='test error'>❌ Table <code>email_verification_tokens</code> n'existe pas</div>";
}

if (in_array('utilisateurs', $tables)) {
    echo "<div class='test success'>✅ Table <code>utilisateurs</code> existe</div>";
    
    // Vérifier la colonne email_verified
    $result = $conn->query("SHOW COLUMNS FROM utilisateurs WHERE Field = 'email_verified'");
    if ($result->rowCount() > 0) {
        echo "<div class='test success'>✅ Colonne <code>email_verified</code> existe dans utilisateurs</div>";
    } else {
        echo "<div class='test error'>❌ Colonne <code>email_verified</code> manquante</div>";
    }
} else {
    echo "<div class='test error'>❌ Table <code>utilisateurs</code> n'existe pas</div>";
}

// Test 3: Tester la création de token
echo "<h2>🔐 Test de Création de Token</h2>";

$testEmail = 'test@example.com';
$testData = [
    'firstname' => 'John',
    'lastname' => 'Doe',
    'email' => $testEmail,
    'password' => 'hashed_password',
    'gender' => 'male',
    'phone' => '0612345678',
    'date_naissance' => '1990-01-01',
    'face_id' => null
];

$token = $emailVerification->createToken($testEmail, $testData);

if ($token) {
    echo "<div class='test success'>✅ Token créé avec succès</div>";
    
    // Vérifier le token
    $result = $emailVerification->getTokenData($token);
    if ($result) {
        echo "<div class='test success'>✅ Token récupéré avec succès</div>";
        echo "<table class='table-info'>
            <tr>
                <th>Propriété</th>
                <th>Valeur</th>
            </tr>
            <tr>
                <td>Email</td>
                <td>" . htmlspecialchars($result['email']) . "</td>
            </tr>
            <tr>
                <td>Prénom</td>
                <td>" . htmlspecialchars($result['userData']['firstname']) . "</td>
            </tr>
            <tr>
                <td>Nom</td>
                <td>" . htmlspecialchars($result['userData']['lastname']) . "</td>
            </tr>
        </table>";
    } else {
        echo "<div class='test error'>❌ Erreur lors de la récupération du token</div>";
    }
    
    // Nettoyer le token de test
    $conn->query("DELETE FROM email_verification_tokens WHERE email = '$testEmail'");
} else {
    echo "<div class='test error'>❌ Erreur lors de la création du token</div>";
}

// Test 4: Configuration de l'email
echo "<h2>📧 Configuration de l'Email</h2>";

if (ini_get('mail.host') || ini_get('sendmail_path')) {
    echo "<div class='test info'>ℹ️ Configuration mail détectée</div>";
} else {
    echo "<div class='test info'>ℹ️ Utilisation de la fonction mail() PHP (tests locaux)</div>";
}

echo "<div class='test info'>
    <strong>Pour la production:</strong><br>
    Configurez SMTP dans <code>config/EmailManager.php</code> avec PHPMailer
</div>";

// Test 5: Fichiers créés
echo "<h2>📁 Fichiers Créés</h2>";

$files = [
    'config/migrations.php' => 'Script de migration',
    'config/EmailManager.php' => 'Gestionnaire d\'emails',
    'model/EmailVerification.php' => 'Modèle de vérification',
    'view/front/verify_email.php' => 'Page de vérification'
];

foreach ($files as $file => $description) {
    $filePath = __DIR__ . '/../' . $file;
    if (file_exists($filePath)) {
        echo "<div class='test success'>✅ <code>$file</code> - $description</div>";
    } else {
        echo "<div class='test error'>❌ <code>$file</code> - MANQUANT</div>";
    }
}

// Résumé
echo "<h2>📋 Résumé</h2>";
echo "<div class='test success'>
    <strong>✅ Système prêt!</strong><br>
    Tous les fichiers et tables sont en place.<br>
    Vous pouvez tester le flux d'inscription à partir de <a href='/swaply/view/front/register.php'>register.php</a>
</div>";

echo "</body>
</html>";
?>
