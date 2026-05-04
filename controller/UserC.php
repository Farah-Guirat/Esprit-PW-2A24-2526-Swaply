<?php
session_start();

require_once "../config/Database.php";
require_once "../config/EmailManager.php";
require_once "../model/User.php";
require_once "../model/EmailVerification.php";

$db = new Database();
$conn = $db->connect();
$userModel = new User($conn);
$emailVerification = new EmailVerification($conn);
$emailManager = new EmailManager();


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
    $captcha = isset($_POST['captcha']) ? trim($_POST['captcha']) : '';

    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        header("Location: /swaply/view/front/register.php?error=empty");
        exit();
    }

    if (empty($captcha) || !isset($_SESSION['captcha_answer_register']) || intval($captcha) !== intval($_SESSION['captcha_answer_register'])) {
        unset($_SESSION['captcha_answer_register']);
        header("Location: /swaply/view/front/register.php?error=captcha");
        exit();
    }

    unset($_SESSION['captcha_answer_register']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: /swaply/view/front/register.php?error=email");
        exit();
    }

    // Vérifier si l'email existe déjà
    if ($userModel->getUserByEmail($email)) {
        header("Location: /swaply/view/front/register.php?error=duplicate");
        exit();
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $face_id = null;
    $face_pubkey = null;
    $face_sign_count = 0;

    if (isset($_SESSION['webauthn_registration'])) {
        $face_id = $_SESSION['webauthn_registration']['credentialId'];
        $face_pubkey = $_SESSION['webauthn_registration']['publicKeyPem'];
        $face_sign_count = (int) $_SESSION['webauthn_registration']['signCount'];
    } elseif (!empty($_POST['face_credential_id'])) {
        // Fallback : lire depuis les champs cachés du formulaire
        $face_id = trim($_POST['face_credential_id']);
        $face_pubkey = !empty($_POST['face_pubkey']) ? trim($_POST['face_pubkey']) : null;
        $face_sign_count = isset($_POST['face_sign_count']) ? (int) $_POST['face_sign_count'] : 0;
    }

    // Préparer les données de l'utilisateur
    $userData = array(
        'firstname' => $nom,
        'lastname' => $prenom,
        'email' => $email,
        'password' => $passwordHash,
        'gender' => $genre,
        'phone' => $telephone,
        'date_naissance' => $date_naissance,
        'face_id' => $face_id,
        'face_pubkey' => $face_pubkey,
        'face_sign_count' => $face_sign_count
    );

    // Créer un token de vérification
    $token = $emailVerification->createToken($email, $userData);

    if ($token) {
        // Générer le lien de vérification
        $verificationLink = "http://" . $_SERVER['HTTP_HOST'] . "/swaply/view/front/verify_email.php?token=" . $token;
        
        // Envoyer l'email
        $emailManager->sendVerificationEmail($email, $verificationLink);
        
        // Stocker le token et l'email en session pour afficher le message
        $_SESSION['verification_email'] = $email;
        $_SESSION['verification_token'] = $token;
        
        // Rediriger vers la page d'enregistrement avec un message
        header("Location: /swaply/view/front/register.php?verification_sent=1&email=" . urlencode($email));
        exit();
    } else {
        header("Location: /swaply/view/front/register.php?error=verification_failed");
        exit();
    }
}


// ======================
// 🔵 LOGIN
// ======================
if (!empty($_POST['face_login'])) {
    $face_id = isset($_POST['face_id']) ? trim($_POST['face_id']) : '';
    if (empty($face_id)) {
        header("Location: /swaply/view/front/login.php?error=captcha");
        exit();
    }

    $user = $userModel->loginByFaceId($face_id);

    if ($user) {
        $_SESSION['user'] = $user;
        if ($user['email'] === 'klai.aziz@admin.tn') {
            header("Location: /swaply/view/back/swaplyB.php");
            exit();
        }
        header("Location: /swaply/view/front/swaplyf.php");
        exit();
    }

    header("Location: /swaply/view/front/login.php?error=1");
    exit();
}

if (isset($_POST['login'])) {

    $email = $_POST['email'];
    $password = $_POST['password'];
    $captcha = isset($_POST['captcha']) ? trim($_POST['captcha']) : '';

    if (empty($captcha) || !isset($_SESSION['captcha_answer_login']) || intval($captcha) !== intval($_SESSION['captcha_answer_login'])) {
        unset($_SESSION['captcha_answer_login']);
        header("Location: /swaply/view/front/login.php?error=captcha");
        exit();
    }

    unset($_SESSION['captcha_answer_login']);

    $user = $userModel->login($email, $password);

    if ($user) {
        $_SESSION['user'] = $user;

        if ($email === 'klai.aziz@admin.tn') {
            header("Location: /swaply/view/back/swaplyB.php");
            exit();
        }

        header("Location: /swaply/view/front/swaplyf.php");
        exit();
    } else {
        header("Location: /swaply/view/front/login.php?error=1");
        exit();
    }
}

if (isset($_POST['forgot_password'])) {
    $email = trim($_POST['email'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: /swaply/view/front/forgot-password.php?error=invalid_email");
        exit();
    }

    $user = $userModel->getUserByEmail($email);
    if (!$user) {
        header("Location: /swaply/view/front/forgot-password.php?error=no_email");
        exit();
    }

    $token = $emailVerification->createResetToken($email);
    if (!$token) {
        header("Location: /swaply/view/front/forgot-password.php?error=send_failed");
        exit();
    }

    $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/swaply/view/front/reset-password.php?token=" . urlencode($token);
    if (!$emailManager->sendPasswordResetEmail($email, $resetLink)) {
        header("Location: /swaply/view/front/forgot-password.php?error=send_failed");
        exit();
    }

    header("Location: /swaply/view/front/forgot-password.php?status=sent");
    exit();
}

if (isset($_POST['reset_password'])) {
    $token = trim($_POST['token'] ?? '');
    $newPassword = trim($_POST['new_password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    if ($token === '') {
        header("Location: /swaply/view/front/reset-password.php?error=invalid_token");
        exit();
    }

    if ($newPassword === '' || $confirmPassword === '') {
        header("Location: /swaply/view/front/reset-password.php?token=" . urlencode($token) . "&error=empty_password");
        exit();
    }

    if ($newPassword !== $confirmPassword) {
        header("Location: /swaply/view/front/reset-password.php?token=" . urlencode($token) . "&error=mismatch");
        exit();
    }

    if (strlen($newPassword) < 8) {
        header("Location: /swaply/view/front/reset-password.php?token=" . urlencode($token) . "&error=short_password");
        exit();
    }

    $resetData = $emailVerification->getResetTokenData($token);
    if (!$resetData || !isset($resetData['email'])) {
        header("Location: /swaply/view/front/reset-password.php?error=invalid_token");
        exit();
    }

    $email = $resetData['email'];
    $user = $userModel->getUserByEmail($email);
    if (!$user) {
        header("Location: /swaply/view/front/reset-password.php?error=invalid_token");
        exit();
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    if (!$userModel->updatePasswordByEmail($email, $hashedPassword)) {
        header("Location: /swaply/view/front/reset-password.php?token=" . urlencode($token) . "&error=server_error");
        exit();
    }

    $emailVerification->deleteResetToken($token);
    $_SESSION['user'] = $userModel->getUserByEmail($email);
    header("Location: /swaply/view/front/swaplyf.php?password_reset=1");
    exit();
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