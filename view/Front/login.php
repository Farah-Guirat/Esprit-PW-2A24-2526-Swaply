<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../config/database.php';

$errors = [];
$email = '';
$debug = false; // À mettre à false en production

// Si l'utilisateur est déjà connecté, rediriger vers messagerie
if (isset($_SESSION['id_user'])) {
    header('Location: messagerie.php');
    exit;
}

// Traiter la connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($email)) {
        $errors[] = "L'email est requis";
    }
    if (empty($password)) {
        $errors[] = "Le mot de passe est requis";
    }

    if (empty($errors)) {
        try {
            $pdo = Database::getInstance()->getConnection();
            
            // Chercher l'utilisateur par email
            $stmt = $pdo->prepare("SELECT id_u, prenom, nom, email, password FROM utilisateurs WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Vérifier le mot de passe
                // Essayer d'abord bcrypt, puis texte clair (pour compatibilité)
                $isBcrypt = (strlen($user['password']) === 60 && strpos($user['password'], '$2') === 0);
                $pwdMatch = false;
                
                if ($isBcrypt) {
                    // Password hachés en bcrypt
                    $pwdMatch = password_verify($password, $user['password']);
                } else {
                    // Password en texte clair (avant hachage)
                    $pwdMatch = ($password === $user['password']);
                }
                
                if ($debug) {
                    error_log("Email trouvé: {$email}");
                    error_log("Password en DB: " . substr($user['password'], 0, 30) . "...");
                    error_log("Format: " . ($isBcrypt ? "BCRYPT" : "TEXTE CLAIR"));
                    error_log("Comparaison: " . ($pwdMatch ? "OK ✓" : "FAIL ✗"));
                }
                
                if ($pwdMatch) {
                    // Connexion réussie - créer la session
                    $_SESSION['id_user'] = (int)$user['id_u'];
                    $_SESSION['prenom'] = $user['prenom'];
                    $_SESSION['nom'] = $user['nom'];
                    $_SESSION['email'] = $user['email'];
                    
                    // Redirection vers messagerie
                    header('Location: messagerie.php');
                    exit;
                } else {
                    $errors[] = "Email ou mot de passe incorrect";
                }
            } else {
                $errors[] = "Email ou mot de passe incorrect";
            }
        } catch (Exception $e) {
            $errors[] = "Erreur de base de données: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Swaply Messagerie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 450px;
        }

        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 50px 40px;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo i {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 10px;
        }

        .logo h1 {
            color: #333;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .logo p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            color: #667eea;
            pointer-events: none;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 12px 12px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        input[type="email"]::placeholder,
        input[type="password"]::placeholder {
            color: #999;
        }

        .error-box {
            background: #fee;
            color: #c33;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
            animation: shake 0.4s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .error-box ul {
            list-style: none;
            margin: 0;
        }

        .error-box li {
            margin: 5px 0;
            font-size: 14px;
        }

        .error-box li:before {
            content: "✕ ";
            font-weight: bold;
            margin-right: 5px;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
        }

        .divider:before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e0e0e0;
        }

        .divider span {
            background: white;
            padding: 0 10px;
            color: #999;
            font-size: 14px;
            position: relative;
        }

        .demo-credentials {
            background: #f5f5f5;
            border: 1px solid #e0e0e0;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .demo-credentials h3 {
            color: #333;
            font-size: 13px;
            margin-bottom: 10px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .demo-credentials p {
            margin: 5px 0;
            font-size: 13px;
            color: #666;
            font-family: 'Courier New', monospace;
        }

        .demo-credentials strong {
            color: #333;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #999;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 30px 20px;
            }

            .logo h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card">
            <div class="logo">
                <i class="fas fa-comments"></i>
                <h1>Swaply</h1>
                <p>Messagerie Instantanée</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="error-box">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?php echo htmlspecialchars($email); ?>"
                            placeholder="votre.email@example.com" 
                            required 
                            autofocus
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Votre mot de passe" 
                            required
                        >
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Se Connecter
                </button>
            </form>

            <div class="divider">
                <span>Utilisateurs disponibles</span>
            </div>

            <div class="demo-credentials">
                <?php 
                try {
                    $pdo = Database::getInstance()->getConnection();
                    $stmt = $pdo->query("SELECT id_u, prenom, nom, email FROM utilisateurs ORDER BY prenom LIMIT 6");
                    $allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (!empty($allUsers)):
                        foreach ($allUsers as $idx => $usr):
                            if ($idx > 0 && $idx % 2 == 0) echo '<hr style="margin: 15px 0; border: none; border-top: 1px solid #e0e0e0;">';
                ?>
                            <h3><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($usr['prenom'] . ' ' . $usr['nom']); ?></h3>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($usr['email']); ?></p>
                            <p><strong>Mot de passe:</strong> Votre mot de passe</p>
                <?php 
                        endforeach;
                    else:
                        echo '<p style="color: #999;">Aucun utilisateur trouvé</p>';
                    endif;
                } catch (Exception $e) {
                    echo '<p style="color: #c33;">Erreur: ' . htmlspecialchars($e->getMessage()) . '</p>';
                }
                ?>
            </div>

            <div class="footer">
                <p>💡 Ouvrez cette page dans 2 onglets pour tester avec 2 utilisateurs différents!</p>
                <p style="margin-top: 10px; font-size: 11px; color: #bbb;">Entrez votre email et le mot de passe créé lors de votre inscription</p>
            </div>
        </div>
    </div>
</body>
</html>
