<?php
session_start();

require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../model/User.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../view/back/ProfilsB.php");
    exit();
}

if (!isset($_SESSION['user']) || $_SESSION['user']['email'] !== 'klai.aziz@admin.tn') {
    header("Location: ../view/front/login.php");
    exit();
}

$id = isset($_POST['id_u']) ? (int) $_POST['id_u'] : 0;
if ($id <= 0) {
    header("Location: ../view/back/ProfilsB.php");
    exit();
}

$database = new Database();
$conn = $database->connect();
$userModel = new User($conn);

$profile = $userModel->getUserById($id);
if (!$profile || $profile['email'] === 'klai.aziz@admin.tn') {
    header("Location: ../view/back/ProfilsB.php");
    exit();
}

$deleted = $userModel->deleteUser($id);
if ($deleted) {
    header("Location: ../view/back/ProfilsB.php?deleted=1");
    exit();
}

header("Location: ../view/back/ProfilsB.php?deleted=0");
exit();
