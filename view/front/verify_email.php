<?php
session_start();

require_once __DIR__ . "/../../config/Database.php";
require_once __DIR__ . "/../../model/EmailVerification.php";
require_once __DIR__ . "/../../model/User.php";

$db = new Database();
$conn = $db->connect();
$emailVerification = new EmailVerification($conn);
$userModel = new User($conn);

// Récupérer le token de l'URL
$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$action = isset($_GET['action']) ? trim($_GET['action']) : '';

$message = '';
$messageType = '';
$showButtons = false;
$tokenData = null;

if (empty($token)) {
    $message = 'Token invalide ou manquant.';
    $messageType = 'error';
} else {
    $tokenData = $emailVerification->getTokenData($token);
    
    if (!$tokenData) {
        $message = 'Token invalide ou expiré.';
        $messageType = 'error';
    } else {
        // Traiter l'action
        if ($action === 'confirm') {
            // L'utilisateur a confirmé
            $result = $emailVerification->verifyToken($token, 'confirm');
            
            if ($result['status'] === 'success') {
                // Créer le compte avec les données du token
                $userData = $result['userData'];
                
                try {
                    $registrationResult = $userModel->register(
                        $userData['firstname'],
                        $userData['lastname'],
                        $userData['email'],
                        $userData['password'],
                        $userData['gender'],
                        $userData['phone'],
                        $userData['date_naissance'],
                        $userData['face_id']
                    );
                    
                    if ($registrationResult) {
                        // Créer directement une connexion sans vérifier le mot de passe
                        // Récupérer l'utilisateur à partir de l'email
                        $sql = "SELECT * FROM utilisateurs WHERE email = :email LIMIT 1";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':email', $userData['email']);
                        $stmt->execute();
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);


                        // Sauvegarder la clé publique WebAuthn si disponible
                        if (!empty($userData['face_pubkey'])) {
                            $userModel->saveWebAuthnCredential(
                                $user['id_u'],
                                $userData['face_id'],
                                $userData['face_pubkey'],
                                isset($userData['face_sign_count']) ? (int)$userData['face_sign_count'] : 0
                            );
                            // Recharger l'utilisateur pour avoir les données à jour en session
                            $stmt2 = $conn->prepare("SELECT * FROM utilisateurs WHERE id_u = :id LIMIT 1");
                            $stmt2->bindParam(':id', $user['id_u']);
                            $stmt2->execute();
                            $user = $stmt2->fetch(PDO::FETCH_ASSOC);
                        }
                        
                        if ($user) {
                            // Marquer l'email comme vérifié
                            $sql = "UPDATE utilisateurs SET email_verified = 1 WHERE id_u = :id";
                            $stmt = $conn->prepare($sql);
                            $stmt->bindParam(':id', $user['id_u']);
                            $stmt->execute();
                            
                            // Enregistrer la session et rediriger
                            $_SESSION['user'] = $user;
                            
                            // Supprimer le token
                            $sql = "DELETE FROM email_verification_tokens WHERE token = :token";
                            $stmt = $conn->prepare($sql);
                            $stmt->bindParam(':token', $token);
                            $stmt->execute();
                            
                            header("Location: /swaply/view/front/swaplyf.php?account_created=1");
                            exit();
                        }
                    }
                } catch (Exception $e) {
                    $message = 'Erreur lors de la création du compte: ' . $e->getMessage();
                    $messageType = 'error';
                }
            } else {
                $message = $result['message'];
                $messageType = 'error';
            }
        } elseif ($action === 'reject') {
            // L'utilisateur a rejeté
            $result = $emailVerification->verifyToken($token, 'reject');
            
            $message = 'Demande d\'inscription annulée. Vous serez redirigé vers la page d\'inscription dans quelques instants...';
            $messageType = 'warning';
        } else {
            // Afficher les boutons pour la première visite
            $message = 'Veuillez confirmer votre identité pour créer votre compte.';
            $messageType = 'info';
            $showButtons = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <?php if ($action === 'reject'): ?>
    <meta http-equiv="refresh" content="4;url=/swaply/view/front/register.php?rejected=1" />
    <?php endif; ?>
    <title>Vérification Email - Swaply</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f7f8fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            padding: 40px 32px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .card-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #222;
            margin-bottom: 10px;
        }

        .message-box {
            padding: 16px 14px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid;
            font-size: 14px;
            line-height: 1.6;
        }

        .message-box.success {
            background-color: #d4edda;
            border-color: #28a745;
            color: #155724;
        }

        .message-box.error {
            background-color: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }

        .message-box.warning {
            background-color: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }

        .message-box.info {
            background-color: #d1ecf1;
            border-color: #4FD1C5;
            color: #0c5460;
        }

        .email-display {
            background: #f9f9f9;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #e2e8f0;
        }

        .email-display label {
            font-size: 12px;
            color: #888;
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .email-display .email {
            font-size: 16px;
            color: #333;
            font-weight: 700;
        }

        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 12px 20px;
            border-radius: 8px;
            border: none;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-confirm {
            background: #28a745;
            color: white;
        }

        .btn-confirm:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
        }

        .btn-reject {
            background: #dc3545;
            color: white;
        }

        .btn-reject:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }

        .btn:active {
            transform: translateY(0);
        }

        .footer-text {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #999;
            line-height: 1.6;
        }

        .footer-text a {
            color: #4FD1C5;
            text-decoration: none;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }

        .timer {
            text-align: center;
            font-size: 12px;
            color: #999;
            margin-top: 20px;
        }

        @media (max-width: 480px) {
            .card {
                padding: 24px 18px;
            }

            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

    <div class="card">
        <div class="card-header">
            <h1>Swaply</h1>
        </div>

        <div class="message-box <?= $messageType ?>">
            <?= htmlspecialchars($message) ?>
        </div>

        <?php if ($showButtons && $tokenData): ?>
            <div class="email-display">
                <label>Email à vérifier :</label>
                <div class="email"><?= htmlspecialchars($tokenData['email']) ?></div>
            </div>

            <div class="button-group">
                <a href="?token=<?= urlencode($token) ?>&action=confirm" class="btn btn-confirm">
                    ✓ Oui, c'est moi
                </a>
                <a href="?token=<?= urlencode($token) ?>&action=reject" class="btn btn-reject">
                    ✗ Non, ce n'est pas moi
                </a>
            </div>

            <div class="timer">
                <p>Ce lien est valide pendant <strong>24 heures</strong>.</p>
                <p>Si vous n'avez pas initié cette demande, vous pouvez ignorer cet email.</p>
            </div>
        <?php endif; ?>

        <div class="footer-text">
            <p>© 2026 Swaply. Tous droits réservés.</p>
            <p><a href="/swaply/view/front/register.php">Retour à l'inscription</a> | <a href="/swaply/view/front/login.php">Se connecter</a></p>
        </div>
    </div>

</body>
</html>