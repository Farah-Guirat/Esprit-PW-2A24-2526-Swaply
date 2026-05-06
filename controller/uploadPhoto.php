<?php
session_start();
require_once "../config/Database.php";
require_once "../model/User.php";

if (!isset($_SESSION['user'])) {
    echo "Non autorisé";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo']) && isset($_POST['id'])) {
    $id = (int) $_POST['id'];
    $file = $_FILES['photo'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/profiles/';
        
        // Créer le répertoire s'il n'existe pas
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = uniqid() . '_' . basename($file['name']);
        $uploadFile = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
            $database = new Database();
            $conn = $database->connect();
            $userModel = new User($conn);
            $userModel->updatePhoto($id, $fileName);
            $_SESSION['user']['photo'] = $fileName;
            echo 'Photo téléchargée avec succès';
        } else {
            echo 'Erreur lors du téléchargement du fichier';
        }
    } else {
        echo 'Erreur de téléchargement';
    }
} else {
    echo 'Requête invalide';
}
?>