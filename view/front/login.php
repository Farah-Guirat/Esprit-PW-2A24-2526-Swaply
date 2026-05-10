<?php
session_start();
$captchaA = rand(1, 9);
$captchaB = rand(1, 9);
$captchaOperators = ['+', '-', '*'];
$captchaOperator = $captchaOperators[array_rand($captchaOperators)];
$captchaQuestion = "Quel est $captchaA $captchaOperator $captchaB ?";
switch ($captchaOperator) {
    case '+':
        $captchaAnswer = $captchaA + $captchaB;
        break;
    case '-':
        $captchaAnswer = $captchaA - $captchaB;
        break;
    default:
        $captchaAnswer = $captchaA * $captchaB;
        break;
}
$_SESSION['captcha_answer_login'] = $captchaAnswer;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Swaply - Sign In</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f7f8fa;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* NAVBAR */
    .navbar {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      padding: 14px 32px;
      border-bottom: 1px solid #e8e8e8;
      background: #fff;
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 24px;
    }

    .nav-link {
      font-size: 12px;
      color: #888;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 5px;
      text-decoration: none;
      letter-spacing: 0.3px;
    }

    .nav-link.active {
      color: #222;
      font-weight: 600;
    }

    .nav-link svg {
      width: 12px;
      height: 12px;
    }

    /* MAIN LAYOUT */
    .main {
      display: flex;
      flex: 1;
      min-height: calc(100vh - 53px);
    }

    /* LEFT - FORM */
    .left {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 60px 40px;
      background: #fff;
    }

    .form-box {
      width: 100%;
      max-width: 340px;
    }

    .form-title {
      font-size: 28px;
      font-weight: 700;
      color: #4FD1C5;
      margin-bottom: 8px;
    }

    .form-sub {
      font-size: 13px;
      color: #aaa;
      margin-bottom: 32px;
    }

    .form-label {
      font-size: 13px;
      font-weight: 600;
      color: #333;
      margin-bottom: 7px;
      display: block;
    }

    .form-input {
      width: 100%;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      padding: 11px 15px;
      font-size: 13px;
      color: #333;
      background: #fff;
      margin-bottom: 20px;
      outline: none;
      transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-input::placeholder {
      color: #bbb;
    }

    .form-input:focus {
      border-color: #4FD1C5;
      box-shadow: 0 0 0 3px rgba(79, 209, 197, 0.15);
    }

    .btn-signin {
      width: 100%;
      background: #4FD1C5;
      color: #fff;
      border: none;
      border-radius: 10px;
      padding: 13px;
      font-size: 13px;
      font-weight: 700;
      letter-spacing: 1px;
      cursor: pointer;
      margin-bottom: 20px;
      transition: background 0.2s, transform 0.1s;
    }

    .btn-signin:hover {
      background: #38B2AC;
    }

    .btn-signin:active {
      transform: scale(0.98);
    }

    .signup-row {
      text-align: center;
      font-size: 13px;
      color: #aaa;
    }

    .signup-link {
      color: #4FD1C5;
      cursor: pointer;
      font-weight: 600;
      text-decoration: none;
    }

    .signup-link:hover {
      text-decoration: underline;
    }

    /* RIGHT - LOGO PANEL */
    .right {
      width: 45%;
      background: #4FD1C5;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }

    .right svg.waves {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      opacity: 0.25;
    }

    .logo-img {
      width: 200px;
      height: 200px;
      object-fit: contain;
      z-index: 2;
      position: relative;
      border-radius: 50%;
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
      .right { display: none; }
      .left { padding: 40px 24px; }
    }
  </style>
</head>
<body>

  <!-- NAVBAR -->
  <nav class="navbar">
    <div class="nav-links">
      <a class="nav-link" href="#">
        <svg viewBox="0 0 12 12" fill="currentColor"><circle cx="6" cy="4" r="2.5"/><path d="M1 10c0-2.5 2.2-4.5 5-4.5s5 2 5 4.5"/></svg>
        PROFILE
      </a>
      <a class="nav-link" href="register.php">SIGN UP</a>
      <a class="nav-link active" href="#">
        <svg viewBox="0 0 12 12" fill="currentColor"><path d="M2 6h8M6 2l4 4-4 4" stroke="currentColor" stroke-width="1.2" fill="none"/></svg>
        SIGN IN
      </a>
    </div>
  </nav>

  <!-- MAIN -->
  <div class="main">




    <!-- FORM -->
    <div class="left">
      <div class="form-box">
        <h2 class="form-title">Welcome Back</h2>



        <p class="form-sub">Enter your email and password to sign in</p>

    <form method="POST" action="/swaply/controller/UserC.php" onsubmit="return validateLogin()">

    <label class="form-label">Email</label>
    <input class="form-input" type="text" name="email" id="email" placeholder="Your email address" />

    <label class="form-label">Password</label>
    <input class="form-input" type="password" name="password" id="password" placeholder="Your password" />



    <label class="form-label">Captcha de sécurité</label>
    <input class="form-input" type="text" name="captcha" id="captcha" placeholder="<?= htmlspecialchars($captchaQuestion) ?>" />

    <p id="error-msg" style="color:red; margin-bottom:10px;"></p>

    <button type="button" class="btn-signin" style="background: #2d3748; margin-bottom: 12px;" onclick="submitFaceLogin()">Code / Finger Print</button>

   <!-- Forgot Password -->
    <div style="text-align: right; margin-top: -12px; margin-bottom: 20px;">
        <a href="forgot-password.php"
           style="font-size: 12px; color: #4FD1C5; text-decoration: underline; font-weight: 600;">
            Forgot password?
        </a>
    </div>

    <button type="submit" name="login" class="btn-signin">SIGN IN</button>

    </form>



        

        <p class="signup-row">
          Don't have an account? <a class="signup-link" href="register.php">Sign up</a>
        </p>
      </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="right">
      <svg class="waves" viewBox="0 0 400 500" fill="none" xmlns="http://www.w3.org/2000/svg">
        <ellipse cx="300" cy="100" rx="200" ry="300" stroke="white" stroke-width="1.5" fill="none"/>
        <ellipse cx="100" cy="400" rx="250" ry="200" stroke="white" stroke-width="1" fill="none"/>
        <ellipse cx="350" cy="350" rx="150" ry="220" stroke="white" stroke-width="1" fill="none"/>
      </svg>
      <!-- Remplacez le src ci-dessous par le chemin de votre logo -->
      <img class="logo-img" src="logo.png" alt="Swaply Logo" />
    </div>

  </div>

<script>
function arrayBufferToBase64Url(buffer) {
    const bytes = new Uint8Array(buffer);
    let binary = '';
    bytes.forEach((b) => binary += String.fromCharCode(b));
    return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
}

function base64UrlToBuffer(base64url) {
    const padding = '==='.slice(0, (4 - (base64url.length % 4)) % 4);
    const base64 = base64url.replace(/-/g, '+').replace(/_/g, '/') + padding;
    const str = atob(base64);
    const buffer = new ArrayBuffer(str.length);
    const view = new Uint8Array(buffer);
    for (let i = 0; i < str.length; i++) {
        view[i] = str.charCodeAt(i);
    }
    return buffer;
}

function validateLogin() {
    let email = document.getElementById("email").value.trim();
    let password = document.getElementById("password").value.trim();
    let captcha = document.getElementById("captcha").value.trim();
    let errorMsg = document.getElementById("error-msg");

    errorMsg.innerHTML = "";

    if (email === "" || password === "" || captcha === "") {
        errorMsg.innerHTML = "Veuillez remplir tous les champs.";
        return false;
    }

    let emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;
    if (!email.match(emailPattern)) {
        errorMsg.innerHTML = "Format email invalide.";
        return false;
    }

    if (!/^-?\d+$/.test(captcha)) {
        errorMsg.innerHTML = "Le captcha doit être un nombre.";
        return false;
    }

    return true;
}

async function submitFaceLogin() {
    let faceId = localStorage.getItem('swaply_face_id');
    let email = document.getElementById('email').value.trim();
    let errorMsg = document.getElementById("error-msg");
    errorMsg.innerHTML = "";

    let queryParams = '';
    if (faceId) {
        queryParams = `face_id=${encodeURIComponent(faceId)}`;
    } else if (email) {
        queryParams = `email=${encodeURIComponent(email)}`;
    } else {
        errorMsg.innerHTML = "Aucun Face ID enregistré. Entrez votre email ou utilisez Save Face ID dans l'inscription.";
        return false;
    }

    try {
        const response = await fetch(`../../controller/WebAuthnC.php?action=authenticateOptions&${queryParams}`);
        const data = await response.json();
        if (data.status !== 'ok') {
            errorMsg.innerHTML = data.message || 'Impossible de lancer Face ID.';
            return false;
        }

        const publicKey = data.publicKey;
        publicKey.challenge = base64UrlToBuffer(publicKey.challenge);
        publicKey.allowCredentials = publicKey.allowCredentials.map(cred => ({
            type: cred.type,
            id: base64UrlToBuffer(cred.id),
        }));

        const assertion = await navigator.credentials.get({ publicKey });
        if (!assertion) {
            errorMsg.innerHTML = 'Face ID annulé.';
            return false;
        }

        const authData = arrayBufferToBase64Url(assertion.response.authenticatorData);
        const clientDataJSON = arrayBufferToBase64Url(assertion.response.clientDataJSON);
        const signature = arrayBufferToBase64Url(assertion.response.signature);
        const credentialId = arrayBufferToBase64Url(assertion.rawId);

        const verifyResponse = await fetch('../../controller/WebAuthnC.php?action=verifyAuthentication', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                credentialId,
                authenticatorData: authData,
                clientDataJSON,
                signature,
            }),
        });

        const verifyData = await verifyResponse.json();
        if (verifyData.status !== 'ok') {
            errorMsg.innerHTML = verifyData.message || 'Échec de la connexion Face ID.';
            return false;
        }

        window.location.href = verifyData.redirect;
    } catch (error) {
        errorMsg.innerHTML = 'Erreur Face ID : ' + error.message;
        return false;
    }
}

window.onload = function() {
    let params = new URLSearchParams(window.location.search);
    let errorMsg = document.getElementById("error-msg");

    if (params.get("error") == "1") {
        errorMsg.innerHTML = "Veuillez vérifier les informations de connexion.";
    } else if (params.get("error") == "captcha") {
        errorMsg.innerHTML = "Réponse au captcha incorrecte.";
    }

    if (params.get("error")) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }
};
</script>

</body>
</html>