<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

function sendProjetEmail($toEmail, $toName, $projetNom, $projetDesc, $projetStatut, $historique) {

  $mail = new PHPMailer(true);

  try {
    // SMTP Config
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'farahguirat4@gmail.com'; // 👈 change this
    $mail->Password   = '';     // 👈 change this
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // Email info
    $mail->setFrom('farahguirat4@gmail.com', 'Swaply');
    $mail->addAddress($toEmail, $toName);
    $mail->CharSet = 'UTF-8';

    // Content
    $mail->isHTML(true);
    $mail->Subject = '✅ Nouveau projet ajouté — Swaply';

    // Build historique HTML
    $historiqueHtml = '';
    foreach ($historique as $h) {
      $color = $h['statut'] === 'Terminé' ? '#10b981' : '#3b82f6';
      $historiqueHtml .= "
        <tr>
          <td style='padding:10px;border-bottom:1px solid #f0f0f0;font-size:14px;color:#333'>{$h['nom_projet']}</td>
          <td style='padding:10px;border-bottom:1px solid #f0f0f0;font-size:14px;color:#666'>{$h['description']}</td>
          <td style='padding:10px;border-bottom:1px solid #f0f0f0;'>
            <span style='background:{$color}20;color:{$color};padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600'>
              {$h['statut']}
            </span>
          </td>
        </tr>";
    }

    $mail->Body = "
    <div style='font-family:sans-serif;max-width:600px;margin:auto;background:#f9fafb;padding:30px;border-radius:16px;'>
      
      <div style='text-align:center;margin-bottom:30px;'>
        <div style='display:inline-block;background:#14b8a6;color:white;font-size:24px;font-weight:bold;width:50px;height:50px;line-height:50px;border-radius:16px;'>S</div>
        <h1 style='color:#1f2937;font-size:22px;margin-top:10px;'>Swaply</h1>
      </div>

      <div style='background:white;border-radius:16px;padding:24px;margin-bottom:20px;'>
        <h2 style='color:#1f2937;font-size:18px;margin-bottom:16px;'>✅ Projet ajouté avec succès !</h2>
        <p style='color:#6b7280;font-size:14px;margin-bottom:16px;'>Voici les détails de votre nouveau projet :</p>
        
        <div style='background:#f0fdf9;border-left:4px solid #14b8a6;padding:16px;border-radius:8px;margin-bottom:16px;'>
          <p style='margin:0 0 8px;font-size:14px;'><strong style='color:#374151;'>Nom :</strong> <span style='color:#14b8a6;'>{$projetNom}</span></p>
          <p style='margin:0 0 8px;font-size:14px;'><strong style='color:#374151;'>Description :</strong> {$projetDesc}</p>
          <p style='margin:0;font-size:14px;'><strong style='color:#374151;'>Statut :</strong> {$projetStatut}</p>
        </div>
      </div>

      <div style='background:white;border-radius:16px;padding:24px;margin-bottom:20px;'>
        <h2 style='color:#1f2937;font-size:16px;margin-bottom:16px;'>📋 Historique de vos projets</h2>
        <table style='width:100%;border-collapse:collapse;'>
          <thead>
            <tr style='background:#f9fafb;'>
              <th style='padding:10px;text-align:left;font-size:13px;color:#6b7280;border-bottom:2px solid #f0f0f0;'>Nom</th>
              <th style='padding:10px;text-align:left;font-size:13px;color:#6b7280;border-bottom:2px solid #f0f0f0;'>Description</th>
              <th style='padding:10px;text-align:left;font-size:13px;color:#6b7280;border-bottom:2px solid #f0f0f0;'>Statut</th>
            </tr>
          </thead>
          <tbody>
            {$historiqueHtml}
          </tbody>
        </table>
      </div>

      <p style='text-align:center;color:#9ca3af;font-size:12px;'>© 2026 Swaply — Tous droits réservés</p>
    </div>";

    $mail->send();
    return true;

  } catch (Exception $e) {
    return false;
  }
}