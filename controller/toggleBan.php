<?php
session_start();
require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../model/User.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['email'] !== 'klai.aziz@admin.tn') {
    header("Location: ../view/front/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_u'])) {
    $id = (int) $_POST['id_u'];
    if ($id > 0) {
        $database = new Database();
        $conn = $database->connect();
        $userModel = new User($conn);
        $result = $userModel->toggleBan($id);
        // Optional: log or check $result
    } else {
        $id = 0;
    }
} else {
    $id = 0;
}

header("Location: ../view/back/AfficheP.php?id=" . $id);
exit();
?>