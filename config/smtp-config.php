<?php
/**
 * Configuration SMTP pour Gmail
 * 
 * ÉTAPES POUR CONFIGURER:
 * 
 * 1. Allez sur: https://myaccount.google.com/apppasswords
 * 2. Sélectionnez: Mail et Windows
 * 3. Générez un App Password (16 caractères)
 * 4. Remplacez 'YOUR_EMAIL@gmail.com' par votre email Gmail
 * 5. Remplacez 'YOUR_16_CHAR_PASSWORD' par le mot de passe généré
 */

return [
    'smtp' => [
        'enabled' => true, // Changer à true pour activer SMTP
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'klaiaziz07@gmail.com', // ← À REMPLACER
        'password' => 'yojr cxkz wzfw wnvl', // ← À REMPLACER (App Password de Google)
        'encryption' => 'tls',
    ],
    'from_email' => 'klaiaziz07@gmail.com',
    'from_name' => 'Swaply - Vérification Email',
];

?>
