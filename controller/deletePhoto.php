<?php
session_start();
require_once "../config/Database.php";
require_once "../model/User.php";

if (!isset($_SESSION['user'])) {
    echo "Non autorisé";
    exit();
}

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $database = new Database();
    $conn = $database->connect();
    $userModel = new User($conn);
    $profile = $userModel->getUserById($id);
    if ($profile && isset($profile['photo']) && $profile['photo']) {
        $filePath = '../uploads/profiles/' . $profile['photo'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $userModel->updatePhoto($id, null);
        $_SESSION['user']['photo'] = null;
    }
    header("Location: ../view/front/Profil.php");
    exit();
}
?>