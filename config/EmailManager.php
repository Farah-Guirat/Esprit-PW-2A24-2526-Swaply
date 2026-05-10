<?php
/**
 * Gestionnaire d'emails pour Swaply
 * Utilise PHPMailer pour une meilleure compatibilité
 */

class EmailManager {
    private $fromEmail = "klaiaziz07@gmail.com";
    private $fromName = "Swaply - Vérification Email";
    private $smtpConfig = null;

    public function __construct() {
        if (file_exists(__DIR__ . '/smtp-config.php')) {
            $this->smtpConfig = require __DIR__ . '/smtp-config.php';
        }
    }

    public function sendEmail($toEmail, $subject, $message) {
        error_log("EmailManager: Tentative d'envoi à $toEmail - Sujet: $subject");

        try {
            // Utiliser PHPMailer
            require_once __DIR__ . '/../lib/PHPMailer/src/PHPMailer.php';
            require_once __DIR__ . '/../lib/PHPMailer/src/SMTP.php';
            require_once __DIR__ . '/../lib/PHPMailer/src/Exception.php';

            $mail = new PHPMailer\PHPMailer\PHPMailer(true);

            // Configuration du serveur
            $mail->isSMTP();
            $mail->Host = $this->smtpConfig['smtp']['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtpConfig['smtp']['username'];
            $mail->Password = $this->smtpConfig['smtp']['password'];
            $mail->SMTPSecure = $this->smtpConfig['smtp']['encryption'];
            $mail->Port = $this->smtpConfig['smtp']['port'];
            $mail->CharSet = 'UTF-8';
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ];

            // Debug SMTP
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = function($str, $level) {
                error_log("PHPMailer Debug: $str");
            };

            // Destinataires
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addReplyTo($this->fromEmail, $this->fromName);
            $mail->addAddress($toEmail);

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->AltBody = strip_tags($message);

            $result = $mail->send();
            if ($result) {
                error_log("EmailManager: PHPMailer réussi pour $toEmail");
            } else {
                error_log("EmailManager: PHPMailer échoué pour $toEmail: " . $mail->ErrorInfo);
            }
            return $result;

        } catch (Exception $e) {
            error_log("EmailManager: Exception PHPMailer: " . $e->getMessage());
            return false;
        }
    }

    public function sendVerificationEmail($toEmail, $verificationLink) {
        $subject = "Vérification de votre compte Swaply";

        $message = "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; background: #f9f9f9; padding: 20px; border-radius: 8px; }
        .header { background: #4FD1C5; color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; }
        .content { background: white; padding: 30px; border-radius: 0 0 8px 8px; }
        .button-container { text-align: center; margin: 30px 0; }
        .btn { display: inline-block; padding: 12px 30px; margin: 5px; border-radius: 5px; text-decoration: none; font-weight: bold; cursor: pointer; }
        .btn-yes { background: #4FD1C5; color: white; }
        .btn-no { background: #e74c3c; color: white; }
        .btn:hover { opacity: 0.9; }
        .footer { text-align: center; padding: 20px; color: #999; font-size: 12px; }
        .email-info { background: #ecf0f1; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>Vérification de Compte Swaply</h1>
        </div>
        <div class='content'>
            <p>Bonjour,</p>
            <p>Quelqu'un a tenté de créer un compte Swaply avec l'adresse email <strong>" . htmlspecialchars($toEmail) . "</strong></p>
            <p>Si c'est vous qui avez lancé cette demande, veuillez vérifier votre identité en cliquant sur le bouton ci-dessous :</p>

            <div class='button-container'>
                <a href='" . htmlspecialchars($verificationLink . "&action=confirm") . "' class='btn btn-yes'>✓ Oui, c'est moi</a>
                <a href='" . htmlspecialchars($verificationLink . "&action=reject") . "' class='btn btn-no'>✗ Non, ce n'est pas moi</a>
            </div>

            <div class='email-info'>
                <strong>Email utilisé :</strong> " . htmlspecialchars($toEmail) . "<br>
                <strong>Compte créé :</strong> " . date('d/m/Y à H:i') . "
            </div>

            <p style='color: #666; font-size: 12px;'>
                Ces liens sont valides pendant <strong>24 heures</strong>. Après ce délai, vous devrez relancer votre demande d'inscription.
            </p>

            <p>Si vous n'avez pas initié cette demande, ignorez cet email.</p>

            <p>Cordialement,<br><strong>L'équipe Swaply</strong></p>
        </div>
        <div class='footer'>
            <p>© 2026 Swaply. Tous droits réservés.</p>
            <p>Cet email a été envoyé automatiquement. Ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>";

        return $this->sendEmail($toEmail, $subject, $message);
    }

    public function sendWelcomeEmail($toEmail) {
        $subject = "Votre compte Swaply a été créé";
        $loginLink = isset($_SERVER['HTTP_HOST']) ? 'http://' . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_QUOTES, 'UTF-8') . '/swaply/view/front/login.php' : '/swaply/view/front/login.php';

        $message = "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; background: #f9f9f9; padding: 20px; border-radius: 8px; }
        .header { background: #4FD1C5; color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; }
        .content { background: white; padding: 30px; border-radius: 0 0 8px 8px; }
        .button-container { text-align: center; margin: 30px 0; }
        .btn { display: inline-block; padding: 12px 30px; border-radius: 5px; text-decoration: none; font-weight: bold; color: white; background: #4FD1C5; }
        .footer { text-align: center; padding: 20px; color: #999; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>Bienvenue sur Swaply</h1>
        </div>
        <div class='content'>
            <p>Félicitations ! Votre compte Swaply a été créé avec succès.</p>
            <p>Vous pouvez maintenant vous connecter et commencer à échanger des compétences avec la communauté.</p>

            <div class='button-container'>
                <a href='" . $loginLink . "' class='btn'>Se connecter</a>
            </div>

            <p><strong>Quelques conseils pour bien débuter :</strong></p>
            <ul>
                <li>Complétez votre profil avec vos compétences</li>
                <li>Publiez vos premières offres/demandes</li>
                <li>Explorez les publications des autres membres</li>
                <li>Participez aux conversations</li>
            </ul>

            <p>Si vous avez des questions, n'hésitez pas à nous contacter.</p>

            <p>Bienvenue dans la communauté Swaply !</p>

            <p>Cordialement,<br><strong>L'équipe Swaply</strong></p>
        </div>
        <div class='footer'>
            <p>© 2026 Swaply. Tous droits réservés.</p>
            <p>Cet email a été envoyé automatiquement.</p>
        </div>
    </div>
</body>
</html>";

        return $this->sendEmail($toEmail, $subject, $message);
    }
}
?>