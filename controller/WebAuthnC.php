<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../model/User.php";
require_once __DIR__ . "/webauthn_utils.php";

$db = new Database();
$conn = $db->connect();
$userModel = new User($conn);

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$rpId = explode(':', $_SERVER['HTTP_HOST'])[0];
$origin = '';
if (!empty($_SERVER['HTTP_ORIGIN'])) {
    $origin = $_SERVER['HTTP_ORIGIN'];
} else {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $origin = $scheme . '://' . $_SERVER['HTTP_HOST'];
}

function jsonResponse(array $data): void {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

if ($action === 'registerOptions') {
    $challenge = base64url_encode(random_bytes(32));
    $_SESSION['webauthn_register_challenge'] = $challenge;
    $_SESSION['webauthn_register_rpId'] = $rpId;
    $_SESSION['webauthn_register_origin'] = $origin;

    $publicKey = [
        'challenge' => $challenge,
        'rp' => [
            'name' => 'Swaply',
            'id' => $rpId,
        ],
        'user' => [
            'id' => base64url_encode(random_bytes(16)),
            'name' => 'new-user',
            'displayName' => 'New Swaply User',
        ],
        'pubKeyCredParams' => [
            ['type' => 'public-key', 'alg' => -7],
        ],
        'timeout' => 60000,
        'attestation' => 'none',
        'authenticatorSelection' => [
            'authenticatorAttachment' => 'platform',
            'userVerification' => 'required',
            'residentKey' => 'discouraged',
        ],
        'extensions' => [
            'credProps' => true,
        ],
    ];

    jsonResponse(['status' => 'ok', 'publicKey' => $publicKey]);
}

if ($action === 'verifyRegistration') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body)) {
        jsonResponse(['status' => 'error', 'message' => 'Données invalides']);
    }

    $attestationObject = $body['attestationObject'] ?? '';
    $clientDataJSON = $body['clientDataJSON'] ?? '';
    $challenge = $_SESSION['webauthn_register_challenge'] ?? '';
    $storedRpId = $_SESSION['webauthn_register_rpId'] ?? $rpId;

    if (empty($attestationObject) || empty($clientDataJSON) || empty($challenge)) {
        jsonResponse(['status' => 'error', 'message' => 'Challenge requis']);
    }

    $result = verify_registration_response($attestationObject, $clientDataJSON, $challenge, $storedRpId);
    if ($result === null) {
        error_log("WebAuthn registration verification failed for challenge: $challenge, rpId: $storedRpId");
        jsonResponse(['status' => 'error', 'message' => 'Vérification de l’inscription échouée']);
    }

    $_SESSION['webauthn_registration'] = $result;
    jsonResponse(['status' => 'ok', 'credentialId' => $result['credentialId'], 'publicKeyPem' => $result['publicKeyPem'], 'signCount' => $result['signCount']]);
}

if ($action === 'authenticateOptions') {
    $faceId = $_GET['face_id'] ?? '';
    $email = $_GET['email'] ?? '';

    if (empty($faceId) && empty($email)) {
        jsonResponse(['status' => 'error', 'message' => 'Face ID absent et email manquant.']);
    }

    if (!empty($email)) {
        $user = $userModel->getUserByEmail($email);
        if (!$user || empty($user['face_id'])) {
            jsonResponse(['status' => 'error', 'message' => 'Aucun Face ID enregistré pour cet email.']);
        }
        $faceId = $user['face_id'];
    }

    $challenge = base64url_encode(random_bytes(32));
    $_SESSION['webauthn_authenticate_challenge'] = $challenge;
    $_SESSION['webauthn_authenticate_rpId'] = $rpId;
    $_SESSION['webauthn_authenticate_origin'] = $origin;

    $publicKey = [
        'challenge' => $challenge,
        'timeout' => 60000,
        'rpId' => $rpId,
        'allowCredentials' => [
            [
                'type' => 'public-key',
                'id' => $faceId,
            ],
        ],
        'userVerification' => 'preferred',
    ];
    jsonResponse(['status' => 'ok', 'publicKey' => $publicKey]);
}

if ($action === 'verifyAuthentication') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body)) {
        jsonResponse(['status' => 'error', 'message' => 'Données invalides']);
    }

    $credentialId = $body['credentialId'] ?? '';
    $authenticatorData = $body['authenticatorData'] ?? '';
    $clientDataJSON = $body['clientDataJSON'] ?? '';
    $signature = $body['signature'] ?? '';
    $challenge = $_SESSION['webauthn_authenticate_challenge'] ?? '';
    $storedRpId = $_SESSION['webauthn_authenticate_rpId'] ?? $rpId;

    if (empty($credentialId) || empty($authenticatorData) || empty($clientDataJSON) || empty($signature) || empty($challenge)) {
        jsonResponse(['status' => 'error', 'message' => 'Données manquantes']);
    }

    $user = $userModel->getUserByCredentialId($credentialId);
    if (!$user) {
        jsonResponse(['status' => 'error', 'message' => 'Compte introuvable']);
    }

    $signCount = verify_authentication_response($authenticatorData, $clientDataJSON, $signature, $challenge, $storedRpId, $user['face_pubkey'], (int) $user['face_sign_count']);
    if ($signCount === null) {
        jsonResponse(['status' => 'error', 'message' => 'Échec de l’authentification Face ID']);
    }

    $userModel->updateFaceSignCount($user['id_u'], $signCount);
    $_SESSION['user'] = $user;
    jsonResponse(['status' => 'ok', 'redirect' => ($user['email'] === 'klai.aziz@admin.tn') ? '/swaply/view/back/swaplyB.php' : '/swaply/view/front/swaplyf.php']);
}

jsonResponse(['status' => 'error', 'message' => 'Action inconnue']);