<?php
session_start();

require_once "../config/Database.php";
require_once "../model/User.php";

$db = new Database();
$conn = $db->connect();
$userModel = new User($conn);


// ======================
// 🔵 SIGNUP
// ======================
if (isset($_POST['signup'])) {

    $nom = $_POST['firstname'];
    $prenom = $_POST['lastname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $genre = $_POST['gender'];
    $telephone = $_POST['phone'];
    $date_naissance = $_POST['date_naissance'];

    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        header("Location: ../view/front/register.php?error=empty");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../view/front/register.php?error=email");
        exit();
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $result = $userModel->register(
        $nom,
        $prenom,
        $email,
        $passwordHash,
        $genre,
        $telephone,
        $date_naissance
    );

    if ($result) {
        // Récupérer les données de l'utilisateur nouvellement créé
        $user = $userModel->login($email, $password); // Utilise la même logique que login
        
        if ($user) {
            $_SESSION['user'] = $user;
            header("Location: ../view/front/swaplyf.php?account_created=1");
            exit();
        } else {
            // En cas d'erreur, rediriger vers login
            header("Location: ../view/front/login.php?success=1");
            exit();
        }
    }
}


// ======================
// 🔵 LOGIN
// ======================
if (isset($_POST['login'])) {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $user = $userModel->login($email, $password);

    if ($user) {

        $_SESSION['user'] = $user;

        if ($email === 'klai.aziz@admin.tn') {
            header("Location: ../view/back/swaplyB.php");
            exit();
        }

        header("Location: ../view/front/swaplyf.php");
        exit();

    } else {
        header("Location: ../view/front/login.php?error=1");
        exit();
    }
}


// ======================
// 🔵 UPDATE PROFILE (FIXED)
// ======================
if (isset($_POST['update_profile'])) {

    $id = $_POST['id_u'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $date_naissance = $_POST['date_naissance'];

    if (empty($nom) || empty($prenom) || empty($email)) {
        echo "empty";
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "invalid_email";
        exit();
    }

    $result = $userModel->updateUser(
        $id,
        $nom,
        $prenom,
        $email,
        $telephone,
        $date_naissance
    );

    if ($result) {
        echo "success";
    } else {
        echo "sql_error";
    }

    exit();
}




if (isset($_POST['change_password'])) {



    $db = new Database();
    $conn = $db->connect();

    $id = $_SESSION['user']['id_u'];
    $current = $_POST['current'];
    $new = $_POST['newpwd'];

    $stmt = $conn->prepare("SELECT password FROM utilisateurs WHERE id_u = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($current, $user['password'])) {
        echo "wrong_password";
        exit();
    }

    $hash = password_hash($new, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE utilisateurs SET password = ? WHERE id_u = ?");
    $stmt->execute([$hash, $id]);

    echo "success";
    exit();
}
?>