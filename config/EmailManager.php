<?php
/**
 * Gestionnaire d'emails pour Swaply
 * Support SMTP natif pour Gmail et autres
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

    public function sendPasswordResetEmail($toEmail, $resetLink) {
        $subject = "Réinitialisation de votre mot de passe Swaply";

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
        .btn-reset { background: #4FD1C5; color: white; }
        .btn:hover { opacity: 0.9; }
        .footer { text-align: center; padding: 20px; color: #999; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>Réinitialisation du mot de passe Swaply</h1>
        </div>
        <div class='content'>
            <p>Bonjour,</p>
            <p>Nous avons reçu une demande de réinitialisation du mot de passe pour votre compte.</p>
            <p>Cliquez sur le bouton ci-dessous pour choisir un nouveau mot de passe :</p>
            <div class='button-container'>
                <a href='" . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . "' class='btn btn-reset'>Réinitialiser mon mot de passe</a>
            </div>
            <p>Si vous n'avez pas demandé cette réinitialisation, ignorez cet e-mail.</p>
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
    
    private function sendEmail($toEmail, $subject, $message) {
        if ($this->smtpConfig && $this->smtpConfig['smtp']['enabled']) {
            $result = $this->sendViaSMTP($toEmail, $subject, $message);
            if (!$result) {
                error_log("SMTP failed for: $toEmail — check error_log for details");
            }
            return $result;
        }
        
        // Fallback mail() natif
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "X-Mailer: Swaply\r\n";
        
        $result = @mail($toEmail, $subject, $message, $headers);
        if (!$result) {
            error_log("mail() failed to send to: $toEmail");
        }
        return $result;
    }
    
    private function sendViaSMTP($toEmail, $subject, $message) {
        try {
            $config   = $this->smtpConfig['smtp'];
            $host     = $config['host'];
            $port     = $config['port'];
            $username = $config['username'];
            $password = $config['password'];
            
            // Connexion socket
            $socket = @fsockopen($host, $port, $errno, $errstr, 15);
            if (!$socket) {
                error_log("SMTP Connection Failed: $errstr ($errno)");
                return false;
            }
            
            stream_set_timeout($socket, 15);
            
            // Greeting
            $response = $this->readResponse($socket);
            if (substr($response, 0, 3) !== '220') {
                fclose($socket);
                error_log("SMTP No greeting: $response");
                return false;
            }
            
            // EHLO (réponse multi-lignes — on lit tout)
            $this->writeCommand($socket, "EHLO localhost");
            $response = $this->readResponse($socket);
            if (substr($response, 0, 3) !== '250') {
                fclose($socket);
                error_log("SMTP EHLO failed: $response");
                return false;
            }
            
            // STARTTLS
            $this->writeCommand($socket, "STARTTLS");
            $response = $this->readResponse($socket);
            if (substr($response, 0, 3) !== '220') {
                fclose($socket);
                error_log("SMTP STARTTLS rejected: $response");
                return false;
            }
            
            // Activer TLS
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($socket);
                error_log("SMTP TLS handshake failed");
                return false;
            }
            
            // Nouveau EHLO après TLS
            $this->writeCommand($socket, "EHLO localhost");
            $response = $this->readResponse($socket);
            if (substr($response, 0, 3) !== '250') {
                fclose($socket);
                error_log("SMTP EHLO after TLS failed: $response");
                return false;
            }
            
            // AUTH LOGIN
            $this->writeCommand($socket, "AUTH LOGIN");
            $response = $this->readResponse($socket);
            if (substr($response, 0, 3) !== '334') {
                fclose($socket);
                error_log("SMTP AUTH LOGIN rejected: $response");
                return false;
            }
            
            // Username
            $this->writeCommand($socket, base64_encode($username));
            $response = $this->readResponse($socket);
            if (substr($response, 0, 3) !== '334') {
                fclose($socket);
                error_log("SMTP Username rejected: $response");
                return false;
            }
            
            // Password
            $this->writeCommand($socket, base64_encode($password));
            $response = $this->readResponse($socket);
            if (substr($response, 0, 3) !== '235') {
                fclose($socket);
                error_log("SMTP Auth failed (mauvais mot de passe ou App Password invalide): $response");
                return false;
            }
            
            // MAIL FROM — doit être la même adresse que le compte Gmail authentifié
            $this->writeCommand($socket, "MAIL FROM:<{$this->fromEmail}>");
            $response = $this->readResponse($socket);
            if (substr($response, 0, 3) !== '250') {
                fclose($socket);
                error_log("SMTP MAIL FROM rejected: $response");
                return false;
            }
            
            // RCPT TO
            $this->writeCommand($socket, "RCPT TO:<$toEmail>");
            $response = $this->readResponse($socket);
            if (substr($response, 0, 3) !== '250') {
                fclose($socket);
                error_log("SMTP RCPT TO rejected: $response");
                return false;
            }
            
            // DATA
            $this->writeCommand($socket, "DATA");
            $response = $this->readResponse($socket);
            if (substr($response, 0, 3) !== '354') {
                fclose($socket);
                error_log("SMTP DATA rejected: $response");
                return false;
            }
            
            // Construire et envoyer le message complet
            $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
            $headers  = "From: {$this->fromName} <{$this->fromEmail}>\r\n";
            $headers .= "To: $toEmail\r\n";
            $headers .= "Subject: $encodedSubject\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "Content-Transfer-Encoding: base64\r\n";
            $headers .= "X-Mailer: Swaply\r\n";
            
            $encodedBody = chunk_split(base64_encode($message));
            fputs($socket, $headers . "\r\n" . $encodedBody . "\r\n.\r\n");
            
            $response = $this->readResponse($socket);
            
            // QUIT
            $this->writeCommand($socket, "QUIT");
            $this->readResponse($socket);
            fclose($socket);
            
            if (substr($response, 0, 3) === '250') {
                error_log("Email envoyé avec succès à: $toEmail");
                return true;
            }
            
            error_log("SMTP Send failed: $response");
            return false;
            
        } catch (Exception $e) {
            error_log("SMTP Exception: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Écrire une commande SMTP (sans lire la réponse)
     */
    private function writeCommand($socket, $command) {
        fputs($socket, $command . "\r\n");
    }
    
    /**
     * Lire la réponse SMTP complète (supporte les réponses multi-lignes)
     * Une réponse multi-ligne ressemble à :
     *   250-Param1
     *   250-Param2
     *   250 Last line   ← se termine quand le 4e char est un espace (pas un tiret)
     */
    private function readResponse($socket) {
        $response = '';
        while (true) {
            $line = fgets($socket, 512);
            if ($line === false) break;
            $response .= $line;
            // La dernière ligne d'une réponse SMTP a un espace en position 3 (ex: "250 OK")
            // Les lignes intermédiaires ont un tiret (ex: "250-SIZE 35882577")
            if (strlen($line) >= 4 && $line[3] === ' ') {
                break;
            }
        }
        return $response;
    }
}

?>