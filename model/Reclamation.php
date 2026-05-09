<?php

$host = "localhost";
$dbname = "swaply";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Erreur: " . $e->getMessage();
}


// 1. Autoload mta' Composer besh yaaref PHPMailer o Twilio
require_once __DIR__ . '/../vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Reclamation {

    // ─────────────────────────────────────────────────
    //  CRUD (Code mte3ek el asly)[cite: 10]
    // ─────────────────────────────────────────────────

    public function getAll() {
        global $conn;
        $sql = "SELECT * FROM reclamations ORDER BY date_creation DESC";
        return $conn->query($sql);
    }

    public function add($id_user, $description, $rating, $type, $username_cible) {
        global $conn;
        $sql  = "INSERT INTO reclamations (id_user, description, rating, type, username_cible)
                 VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $ok   = $stmt->execute([$id_user, $description, $rating, $type, $username_cible]);

        if ($ok) {
            $id_reclamation = $conn->lastInsertId();
            $sql2  = "INSERT INTO reponses (id_reclamation, contenu, status) VALUES (?, ?, ?)";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->execute([$id_reclamation, '', 'en cours']);
        }
        return $ok;
    }

    public function getById($id) {
        global $conn;
        $sql  = "SELECT * FROM reclamations WHERE id_reclamation = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function update($id, $description, $rating, $type) {
        global $conn;
        $sql  = "UPDATE reclamations SET description = ?, rating = ?, type = ?
                 WHERE id_reclamation = ?";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([$description, $rating, $type, $id]);
    }

    public function delete($id) {
        global $conn;
        $sql  = "DELETE FROM reclamations WHERE id_reclamation = ?";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    // ─────────────────────────────────────────────────
    //  NOTIFICATIONS (Email & WhatsApp)[cite: 8, 9]
    // ─────────────────────────────────────────────────

public function sendEmailConfirmation($email, $prenom, $description, $type, $rating, $username_cible) {
    $mail = new PHPMailer(true);
    try {
        // Config SMTP Gmail
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; 
        $mail->SMTPAuth   = true;
        $mail->Username   = 'swaplyswaply@gmail.com';        
        $mail->Password   = 'diff bejc nhwl wrib';        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('swaplyswaply@gmail.com', 'Swaply Support');
        $mail->addAddress($email, $prenom);

        // N7adhrou el stars mta' el rating
        $stars = str_repeat('⭐', $rating);

        $mail->isHTML(true);
        $mail->Subject = "Confirmation de votre reclamation - Swaply";
        
        // El mail body b-format professional
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; border: 1px solid #ddd; border-radius: 10px; padding: 20px;'>
                <h2 style='color: #14b8a6;'>Bonjour {$prenom},</h2>
                <p>Votre réclamation a été reçue avec succès par l'équipe <strong>Swaply</strong>.</p>
                
                <div style='background-color: #f9f9f9; padding: 15px; border-radius: 8px; margin-top: 20px;'>
                    <h3 style='margin-top: 0; color: #555;'>Détails de la réclamation :</h3>
                    <hr style='border: 0; border-top: 1px solid #eee;'>
                    <p><strong>Type :</strong> " . ($type == 'person' ? '👤 Personne' : '🏢 Entreprise') . "</p>
                    <p><strong>Cible :</strong> @{$username_cible}</p>
                    <p><strong>Note :</strong> {$stars} ({$rating}/5)</p>
                    <p><strong>Description :</strong><br>{$description}</p>
                </div>

                <p style='margin-top: 25px;'>L'équipe Swaply va traiter votre demande dans les plus brefs délais.</p>
                <p style='font-size: 12px; color: #999;'>Ceci est un message automatique, merci de ne pas y répondre.</p>
            </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erreur Mail: " . $mail->ErrorInfo);
        return false;
    }
}
public function updateStatut($id, $statut) {
    global $conn;
    $sql  = "UPDATE reclamations SET statut = ? WHERE id_reclamation = ?";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$statut, $id]);
}

}

?>