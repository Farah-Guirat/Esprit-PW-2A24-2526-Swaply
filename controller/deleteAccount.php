<?php
session_start();

require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../model/User.php";

/* ✅ لازم يكون POST */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    error_log("Tentative de suppression sans POST");
    header("Location: ../view/front/Profil.php");
    exit();
}

/* ✅ تأكد session */
if (!isset($_SESSION['user']['id_u'])) {
    error_log("Tentative de suppression sans session valide");
    header("Location: ../view/front/login.php");
    exit();
}

$id = (int) $_SESSION['user']['id_u'];

error_log("Début de suppression du compte ID: $id");

try {

    $database = new Database();
    $conn = $database->connect();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $userModel = new User($conn);
    $deleted = $userModel->deleteUser($id);

    if ($deleted) {

        $_SESSION = [];
        session_destroy();

        header("Location: ../view/front/login.php?deleted=1");
        exit();

    } else {
        // Ajouter un log d'erreur
        error_log("Échec de la suppression du compte utilisateur ID: $id");
        echo "Erreur suppression: Impossible de supprimer le compte.";
        exit();
    }

} catch (Exception $e) {
    error_log("Exception lors de la suppression: " . $e->getMessage());
    echo "Erreur : " . $e->getMessage();
    exit();
}
