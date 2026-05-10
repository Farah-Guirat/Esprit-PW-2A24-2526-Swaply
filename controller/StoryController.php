<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/Story.php';

class StoryController
{
    private $db;
    private $storyModel;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
        $this->storyModel = new Story($this->db);
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

        $this->storyModel->deleteExpired();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formType = $_POST['form_type'] ?? '';

            if ($formType === 'story') {
                return $this->handleStorySubmit($userId);
            }

            if ($formType === 'toggle_story_like') {
                return $this->handleToggleStoryLike($userId);
            }

            if ($formType === 'delete_story') {
                return $this->handleDeleteStory($userId);
            }

            if ($formType === 'story_comment') {
                return $this->handleStoryCommentSubmit($userId);
            }

            if ($formType === 'delete_story_comment') {
                return $this->handleDeleteStoryComment($userId);
            }

            if ($formType === 'toggle_story_comment_like') {
                return $this->handleToggleStoryCommentLike($userId);
            }
        }

        return null;
    }

    private function handleStorySubmit($userId)
    {
        $contenu = trim($_POST['story_contenu'] ?? '');
        $hasImage = !empty($_FILES['story_image']['name']);

        if ($contenu === '' && !$hasImage) {
            return 'Une story doit contenir du texte ou une image.';
        }

        $imageError = null;
        $imagePath = '';
        if ($hasImage) {
            $imagePath = $this->handleImageUpload('story_image', $imageError);
            if ($imageError !== null) {
                return $imageError;
            }

            if ($imagePath === '') {
                return 'Impossible de traiter l’image de la story. Vérifiez le type de fichier et réessayez.';
            }
        }

        $story = new Story($this->db);
        $story->id_client = $userId;
        $story->contenu = htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8');
        $story->image = $imagePath;

        if ($story->create()) {
            header('Location: listepublication.php?success=story_created');
            exit();
        }

        return 'Impossible de créer la story.';
    }

    private function handleToggleStoryLike($userId)
    {
        $storyId = (int) ($_POST['id_story'] ?? 0);
        if ($storyId <= 0) {
            return 'Story invalide.';
        }

        $story = new Story($this->db);
        $story->id_story = $storyId;
        $story->toggleLike($userId);
        header('Location: listepublication.php');
        exit();
    }

    private function handleDeleteStory($userId)
    {
        $storyId = (int) ($_POST['id_story'] ?? 0);
        if ($storyId <= 0) {
            return 'Story invalide.';
        }

        $story = new Story($this->db);
        $story->id_story = $storyId;
        $storyData = $story->getById($storyId);
        if (!$storyData || $storyData['id_client'] !== $userId) {
            return 'Vous ne pouvez supprimer que vos propres stories.';
        }

        if (!empty($storyData['image'])) {
            $filePath = __DIR__ . '/../' . trim($storyData['image']);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        if ($story->delete()) {
            header('Location: listepublication.php?success=story_deleted');
            exit();
        }

        return 'Impossible de supprimer la story.';
    }

    private function handleStoryCommentSubmit($userId)
    {
        $storyId = (int) ($_POST['id_story'] ?? 0);
        $commentText = trim($_POST['story_comment_text'] ?? '');
        if ($storyId <= 0 || $commentText === '') {
            return 'Le commentaire est requis.';
        }

        if ($this->storyModel->addComment($storyId, $userId, htmlspecialchars($commentText, ENT_QUOTES, 'UTF-8'))) {
            header('Location: listepublication.php');
            exit();
        }

        return 'Impossible d\'ajouter le commentaire.';
    }

    private function handleDeleteStoryComment($userId)
    {
        $commentId = (int) ($_POST['id_com'] ?? 0);
        if ($commentId <= 0) {
            return 'Commentaire invalide.';
        }

        if ($this->storyModel->deleteComment($commentId, $userId)) {
            header('Location: listepublication.php?success=story_comment_deleted');
            exit();
        }

        return 'Impossible de supprimer le commentaire.';
    }

    private function handleToggleStoryCommentLike($userId)
    {
        $commentId = (int) ($_POST['id_com'] ?? 0);
        if ($commentId <= 0) {
            return 'Commentaire invalide.';
        }

        $this->storyModel->toggleCommentLike($commentId, $userId);
        header('Location: listepublication.php');
        exit();
    }

    private function handleImageUpload($fieldName, ?string &$error = null)
    {
        $error = null;

        if (empty($_FILES[$fieldName]['name'])) {
            return '';
        }

        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            $error = 'Erreur lors du téléchargement de l’image. Vérifiez la taille et réessayez.';
            return '';
        }

        if (!is_uploaded_file($_FILES[$fieldName]['tmp_name'])) {
            $error = 'Le fichier image est invalide.';
            return '';
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($_FILES[$fieldName]['type'], $allowedTypes, true)) {
            $error = 'Type de fichier non pris en charge. Utilisez JPG, PNG, GIF ou WEBP.';
            return '';
        }

        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = 'uploads/story_' . uniqid() . '_' . basename($_FILES[$fieldName]['name']);
        $destination = __DIR__ . '/../' . $filename;
        if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $destination)) {
            return $filename;
        }

        $error = 'Impossible d’enregistrer l’image de la story.';
        return '';
    }

    public function getActiveStories()
    {
        return $this->storyModel->readAllActive();
    }

    public function getUserLikedStoryIds($userId)
    {
        $stories = new Story($this->db);
        return $stories->getUserLikedStoryIds($userId);
    }

    public function isLikedByUser($storyId, $userId)
    {
        return $this->storyModel->isLikedByUser($storyId, $userId);
    }

    public function getStoryComments($storyId)
    {
        return $this->storyModel->getComments($storyId);
    }

    public function getUserLikedStoryCommentIds($userId)
    {
        return $this->storyModel->getUserLikedCommentIds($userId);
    }

    public function getStoryById($storyId)
    {
        return $this->storyModel->getById($storyId);
    }
}
