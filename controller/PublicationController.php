<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/Publication.php';
require_once __DIR__ . '/../model/Commentaire.php';
require_once __DIR__ . '/../model/Report.php';
require_once __DIR__ . '/../config/EmailManager.php';

class PublicationController
{
    private $db;
    private $publicationModel;
    private $commentModel;
    private $reportModel;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
        $this->publicationModel = new Publication($this->db);
        $this->commentModel = new Commentaire($this->db);
        $this->reportModel = new Report($this->db);
    }

    public function getUserId()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return (int) ($_SESSION['user']['id_u'] ?? $_SESSION['id_user'] ?? 0);
    }

    public function handleRequest()
    {
        $userId = $this->getUserId();
        if ($userId <= 0) {
            return null;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formType = $_POST['form_type'] ?? '';

            if ($formType === 'publication') {
                return $this->handlePublicationSubmit($userId);
            }

            if ($formType === 'update_publication') {
                return $this->handleUpdatePublication($userId);
            }

            if ($formType === 'delete_publication') {
                return $this->handleDeletePublication($userId);
            }

            if ($formType === 'update_comment') {
                return $this->handleUpdateComment($userId);
            }

            if ($formType === 'delete_comment') {
                return $this->handleDeleteComment($userId);
            }

            if ($formType === 'toggle_comment_like') {
                return $this->handleToggleCommentLike($userId);
            }

            if ($formType === 'toggle_pub_like') {
                return $this->handleTogglePublicationLike($userId);
            }

            if ($formType === 'report_publication') {
                return $this->handleReportPublication($userId);
            }

            if ($formType === 'comment') {
                return $this->handleCommentSubmit($userId);
            }
        }

        return null;
    }

    private function handlePublicationSubmit($userId)
    {
        $titre = trim($_POST['titre'] ?? '');
        $contenu = trim($_POST['contenu'] ?? '');
        if ($titre === '' || $contenu === '') {
            return 'Le titre et le contenu sont requis.';
        }

        $publication = new Publication($this->db);
        $publication->titre = $titre;
        $publication->contenu = $contenu;
        $publication->id_client = $userId;
        $publication->image = $this->handleImageUpload('pub_image');
        $publication->musique = $this->handleMusicUpload('pub_musique');

        if ($publication->create()) {
            header('Location: listepublication.php?success=publication_created');
            exit();
        }

        return 'Impossible de créer la publication.';
    }

    private function handleUpdatePublication($userId)
    {
        $publicationId = (int) ($_POST['id_pub'] ?? 0);
        if ($publicationId <= 0) {
            return 'Publication invalide.';
        }

        if (!$this->publicationModel->isOwner($publicationId, $userId)) {
            return 'Vous pouvez uniquement modifier vos propres publications.';
        }

        $publication = $this->publicationModel->getById($publicationId);
        if (!$publication) {
            return 'Publication non trouvée.';
        }

        $this->publicationModel->id_pub = $publicationId;
        $this->publicationModel->titre = trim($_POST['titre'] ?? $publication['titre']);
        $this->publicationModel->contenu = trim($_POST['contenu'] ?? $publication['contenu']);
        
        // Handle image update
        $newImage = $this->handleImageUpload('pub_image');
        $this->publicationModel->image = !empty($newImage) ? $newImage : $publication['image'];
        
        // Handle music update
        $newMusic = $this->handleMusicUpload('pub_musique');
        $this->publicationModel->musique = !empty($newMusic) ? $newMusic : $publication['musique'];

        if ($this->publicationModel->update()) {
            header('Location: listepublication.php?success=publication_updated');
            exit();
        }

        return 'Impossible de mettre à jour la publication.';
    }

    private function handleDeletePublication($userId)
    {
        $publicationId = (int) ($_POST['id_pub'] ?? 0);
        if ($publicationId <= 0) {
            return 'Publication invalide.';
        }

        if (!$this->publicationModel->isOwner($publicationId, $userId)) {
            return 'Vous ne pouvez supprimer que vos propres publications.';
        }

        $publication = $this->publicationModel->getById($publicationId);
        if (!empty($publication['image'])) {
            $images = explode(',', $publication['image']);
            foreach ($images as $image) {
                $filePath = __DIR__ . '/../' . trim($image);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }

        $this->publicationModel->id_pub = $publicationId;
        if ($this->publicationModel->delete()) {
            header('Location: listepublication.php?success=publication_deleted');
            exit();
        }

        return 'Impossible de supprimer cette publication.';
    }

    private function handleCommentSubmit($userId)
    {
        $publicationId = (int) ($_POST['id_pub'] ?? 0);
        $commentText = trim($_POST['comment_text'] ?? '');
        if ($publicationId <= 0 || $commentText === '') {
            return 'Le commentaire est requis.';
        }

        $comment = new Commentaire($this->db);
        $comment->id_pub = $publicationId;
        $comment->id_client = $userId;
        $comment->contenu = htmlspecialchars($commentText, ENT_QUOTES, 'UTF-8');
        if ($comment->create()) {
            header('Location: listepublication.php');
            exit();
        }

        return 'Impossible d’ajouter le commentaire.';
    }

    private function handleUpdateComment($userId)
    {
        $commentId = (int) ($_POST['id_com'] ?? 0);
        $commentText = trim($_POST['comment_text'] ?? '');
        if ($commentId <= 0 || $commentText === '') {
            return 'Le commentaire est requis.';
        }

        // Check if user is comment owner
        if (!$this->commentModel->isOwner($commentId, $userId)) {
            return 'Vous ne pouvez modifier que vos propres commentaires.';
        }

        if ($this->commentModel->update($commentId, htmlspecialchars($commentText, ENT_QUOTES, 'UTF-8'))) {
            header('Location: listepublication.php');
            exit();
        }

        return 'Impossible de modifier le commentaire.';
    }
    private function handleDeleteComment($userId)
    {
        $commentId = (int) ($_POST['id_com'] ?? 0);
        if ($commentId <= 0) {
            return 'Commentaire invalide.';
        }

        // Check if user is comment owner OR publication owner
        $comment = $this->commentModel->getById($commentId);
        if (!$comment) {
            return 'Commentaire non trouvé.';
        }

        $isCommentOwner = $this->commentModel->isOwner($commentId, $userId);
        $isPublicationOwner = $this->publicationModel->isOwner($comment['id_pub'], $userId);

        if (!$isCommentOwner && !$isPublicationOwner) {
            return 'Vous ne pouvez supprimer que vos propres commentaires ou ceux de vos publications.';
        }

        if ($this->commentModel->delete($commentId)) {
            header('Location: listepublication.php?success=comment_deleted');
            exit();
        }

        return 'Impossible de supprimer le commentaire.';
    }

    private function handleToggleCommentLike($userId)
    {
        $commentId = (int) ($_POST['id_com'] ?? 0);
        if ($commentId <= 0) {
            return 'Commentaire invalide.';
        }

        $this->commentModel->toggleLike($commentId, $userId);
        header('Location: listepublication.php');
        exit();
    }
    private function handleTogglePublicationLike($userId)
    {
        $publicationId = (int) ($_POST['id_pub'] ?? 0);
        if ($publicationId <= 0) {
            return 'Publication invalide.';
        }

        $publication = new Publication($this->db);
        $publication->id_pub = $publicationId;
        $publication->toggleLike($userId);
        header('Location: listepublication.php');
        exit();
    }

    private function handleReportPublication($userId)
    {
        $publicationId = (int) ($_POST['id_pub'] ?? 0);
        $reason = trim($_POST['reason'] ?? 'Contenu inapproprié');
        $description = trim($_POST['description'] ?? '');

        if ($publicationId <= 0) {
            return 'Publication invalide.';
        }

        // Allow multiple reports from same user (as requested by user)
        // This means the same user can report the same publication multiple times
        // and it will count towards the 3-report threshold

        if ($this->reportModel->createReport($userId, $reason, $description, $publicationId, null)) {
            // Check if publication should be deleted (3+ reports TOTAL, regardless of who reported)
            $reportCount = $this->reportModel->getReportCountForPublication($publicationId);

            // Log for debugging
            error_log("Publication $publicationId reported. Total reports: $reportCount");

            if ($reportCount >= 3) {
                // Get publication details and owner
                $publication = $this->publicationModel->getById($publicationId);
                if ($publication) {
                    // Send notification email to publication owner about deletion
                    $this->sendPublicationDeletedEmail($publication);

                    // Delete the publication
                    $this->publicationModel->id_pub = $publicationId;
                    $this->publicationModel->delete();

                    // Mark reports as processed
                    $this->reportModel->markReportsAsProcessed($publicationId);

                    header('Location: listepublication.php?success=publication_auto_deleted');
                    exit();
                }
            } else {
                // Send notification email to publication owner about the report
                $publication = $this->publicationModel->getById($publicationId);
                if ($publication) {
                    $emailSent = $this->sendPublicationReportedEmail($publication, $reason, $description);
                    error_log("Report notification email sent to publication owner: " . ($emailSent ? 'YES' : 'NO'));
                }

                header('Location: listepublication.php?success=publication_reported');
                exit();
            }
        }

        return 'Impossible de signaler cette publication.';
    }

    private function handleImageUpload($fieldName)
    {
        if (empty($_FILES[$fieldName]['name'])) {
            return '';
        }

        if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            return '';
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($_FILES[$fieldName]['type'], $allowedTypes, true)) {
            return '';
        }

        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = 'uploads/pub_' . uniqid() . '_' . basename($_FILES[$fieldName]['name']);
        if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], __DIR__ . '/../' . $filename)) {
            return $filename;
        }

        return '';
    }

    private function handleMusicUpload($fieldName)
    {
        if (empty($_FILES[$fieldName]['name'])) {
            return '';
        }

        if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            return '';
        }

        $allowedTypes = ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp3', 'audio/x-m4a', 'audio/x-wav'];
        if (!in_array($_FILES[$fieldName]['type'], $allowedTypes, true)) {
            return '';
        }

        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = 'uploads/music_' . uniqid() . '_' . basename($_FILES[$fieldName]['name']);
        if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], __DIR__ . '/../' . $filename)) {
            return $filename;
        }

        return '';
    }

    public function getPublications($search = '')
    {
        return $this->publicationModel->readAll($search);
    }

    public function getCommentsByPublication($publicationId)
    {
        $query = "SELECT c.*, u.nom, u.prenom, u.photo,
                         COALESCE(cl.like_count, 0) AS likes
                  FROM commentaires c
                  JOIN utilisateurs u ON c.id_client = u.id_u
                  LEFT JOIN (SELECT id_com, COUNT(*) AS like_count FROM comment_likes GROUP BY id_com) cl ON c.id_com = cl.id_com
                  WHERE c.id_pub = ?
                  ORDER BY c.date_com DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$publicationId]);
        return $stmt;
    }

    public function getUserLikedPublicationIds($userId)
    {
        return $this->publicationModel->getUserLikedPublicationIds($userId);
    }

    public function getUserLikedCommentIds($userId)
    {
        return $this->commentModel->getUserLikedCommentIds($userId);
    }

    public function getPublicationById($publicationId)
    {
        return $this->publicationModel->getById($publicationId);
    }

    private function sendPublicationReportedEmail($publication, $reason, $description)
    {
        $emailManager = new EmailManager();
        
        // Get publication owner's email
        $ownerQuery = "SELECT email FROM utilisateurs WHERE id_u = ?";
        $stmt = $this->db->prepare($ownerQuery);
        $stmt->execute([$publication['id_client']]);
        $owner = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$owner) return;
        
        $subject = "Votre publication sur Swaply a été signalée";
        
        $message = "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; background: #f9f9f9; padding: 20px; border-radius: 8px; }
        .header { background: #dc3545; color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; }
        .content { background: white; padding: 30px; border-radius: 0 0 8px 8px; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #999; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>Publication Signalée</h1>
        </div>
        <div class='content'>
            <p>Bonjour,</p>
            <p>Une de vos publications sur Swaply a été signalée par un utilisateur.</p>
            
            <div class='warning'>
                <strong>Détails du signalement :</strong><br>
                <strong>Raison :</strong> " . htmlspecialchars($reason) . "<br>
                <strong>Description :</strong> " . htmlspecialchars($description) . "<br>
                <strong>Date :</strong> " . date('d/m/Y à H:i') . "
            </div>
            
            <p><strong>Contenu de votre publication :</strong></p>
            <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                <strong>Titre :</strong> " . htmlspecialchars($publication['titre']) . "<br>
                <strong>Contenu :</strong> " . nl2br(htmlspecialchars($publication['contenu'])) . "
            </div>
            
            <p>Si cette publication enfreint nos règles communautaires, elle pourrait être supprimée automatiquement après plusieurs signalements.</p>
            
            <p>Nous vous recommandons de vérifier le contenu de votre publication et de le modifier si nécessaire.</p>
            
            <p>Cordialement,<br><strong>L'équipe Swaply</strong></p>
        </div>
        <div class='footer'>
            <p>© 2026 Swaply. Tous droits réservés.</p>
            <p>Cet email a été envoyé automatiquement.</p>
        </div>
    </div>
</body>
</html>";
        
        return $emailManager->sendEmail($owner['email'], $subject, $message);
    }

    private function sendPublicationDeletedEmail($publication)
    {
        $emailManager = new EmailManager();
        
        // Get publication owner's email
        $ownerQuery = "SELECT email FROM utilisateurs WHERE id_u = ?";
        $stmt = $this->db->prepare($ownerQuery);
        $stmt->execute([$publication['id_client']]);
        $owner = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$owner) return;
        
        // Get all reports for this publication
        $reports = $this->reportModel->getReportsForPublication($publication['id_pub']);
        
        $subject = "Votre publication sur Swaply a été supprimée";
        
        $reportsHtml = "";
        while ($report = $reports->fetch(PDO::FETCH_ASSOC)) {
            $reportsHtml .= "<li><strong>" . htmlspecialchars($report['nom'] . ' ' . $report['prenom']) . "</strong> - " . htmlspecialchars($report['reason']) . " (" . htmlspecialchars($report['created_at']) . ")</li>";
        }
        
        $message = "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; background: #f9f9f9; padding: 20px; border-radius: 8px; }
        .header { background: #dc3545; color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; }
        .content { background: white; padding: 30px; border-radius: 0 0 8px 8px; }
        .warning { background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .reports { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #999; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>Publication Supprimée</h1>
        </div>
        <div class='content'>
            <p>Bonjour,</p>
            
            <div class='warning'>
                <strong>⚠️ Votre publication a été supprimée de Swaply</strong><br>
                Cette action a été effectuée automatiquement suite à plusieurs signalements d'utilisateurs.
            </div>
            
            <p><strong>Contenu de la publication supprimée :</strong></p>
            <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #dc3545;'>
                <strong>Titre :</strong> " . htmlspecialchars($publication['titre']) . "<br>
                <strong>Contenu :</strong> " . nl2br(htmlspecialchars($publication['contenu'])) . "<br>
                <strong>Date de publication :</strong> " . htmlspecialchars($publication['date_pub']) . "
            </div>
            
            <p><strong>Raisons des signalements :</strong></p>
            <div class='reports'>
                <ul>
                    " . $reportsHtml . "
                </ul>
            </div>
            
            <p>Nous prenons les signalements au sérieux pour maintenir une communauté saine. Si vous pensez que cette suppression est une erreur, vous pouvez nous contacter pour faire appel de cette décision.</p>
            
            <p>Nous vous encourageons à respecter nos règles communautaires lors de vos prochaines publications.</p>
            
            <p>Cordialement,<br><strong>L'équipe Swaply</strong></p>
        </div>
        <div class='footer'>
            <p>© 2026 Swaply. Tous droits réservés.</p>
            <p>Cet email a été envoyé automatiquement.</p>
        </div>
    </div>
</body>
</html>";
        
        return $emailManager->sendEmail($owner['email'], $subject, $message);
    }
}
