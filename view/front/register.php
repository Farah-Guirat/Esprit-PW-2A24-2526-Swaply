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
$_SESSION['captcha_answer_register'] = $captchaAnswer;

$errorMessage = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'empty':
            $errorMessage = 'Tous les champs sont obligatoires.';
            break;
        case 'email':
            $errorMessage = 'Adresse email invalide.';
            break;
        case 'captcha':
            $errorMessage = 'Captcha incorrect.';
            break;
        case 'duplicate':
            $errorMessage = 'Cette adresse email est déjà utilisée.';
            break;
        case 'verification_failed':
            $errorMessage = 'Erreur lors de l\'envoi de l\'email de vérification.';
            break;
        case 'local_creation_failed':
            $errorMessage = 'Impossible de créer le compte en local. Vérifiez les logs et réessayez.';
            break;
        default:
            $errorMessage = 'Erreur inconnue.';
    }
}

$verificationSent = false;
$verificationEmail = '';
if (isset($_GET['verification_sent']) && $_GET['verification_sent'] === '1') {
    $verificationSent = true;
    $verificationEmail = isset($_GET['email']) ? $_GET['email'] : '';
}

$rejectionMessage = false;
if (isset($_GET['rejected']) && $_GET['rejected'] === '1') {
    $rejectionMessage = true;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Swaply - Sign Up</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f7f8fa;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .navbar {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      padding: 12px 28px;
      background: #4FD1C5;
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .nav-link {
      font-size: 11px;
      color: rgba(255,255,255,0.85);
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 4px;
      text-decoration: none;
    }

    .nav-link.active {
      color: #fff;
      font-weight: 700;
    }

    .hero {
      background: #4FD1C5;
      position: relative;
      overflow: hidden;
      padding: 36px 20px 80px;
      text-align: center;
    }

    .hero svg.waves {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      opacity: 0.2;
      pointer-events: none;
    }

    .hero-title {
      font-size: 26px;
      font-weight: 700;
      color: #fff;
      margin-bottom: 10px;
      position: relative;
      z-index: 2;
    }

    .hero-sub {
      font-size: 13px;
      color: rgba(255,255,255,0.85);
      max-width: 280px;
      margin: 0 auto;
      line-height: 1.6;
      position: relative;
      z-index: 2;
    }

    .card-wrap {
      display: flex;
      justify-content: center;
      margin-top: -50px;
      padding: 0 20px 40px;
      background: #f7f8fa;
    }

    .card {
      background: #fff;
      border-radius: 16px;
      border: 1px solid #e2e8f0;
      padding: 28px 32px;
      width: 100%;
      max-width: 420px;
    }

    .register-title {
      font-size: 15px;
      font-weight: 700;
      color: #222;
      text-align: center;
      margin-bottom: 22px;
    }

    .row2 {
      display: flex;
      gap: 12px;
    }

    .row2 > div {
      flex: 1;
    }

    .form-label {
      font-size: 12px;
      font-weight: 600;
      color: #555;
      margin-bottom: 5px;
      display: block;
    }

    .form-input {
      width: 100%;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 10px 13px;
      font-size: 13px;
      color: #333;
      background: #fff;
      margin-bottom: 16px;
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

    select.form-input {
      color: #bbb;
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23bbb' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 12px center;
      cursor: pointer;
    }

    select.form-input.selected {
      color: #333;
    }

    .btn-signup {
      width: 100%;
      background: #4FD1C5;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 13px;
      font-size: 13px;
      font-weight: 700;
      letter-spacing: 0.5px;
      cursor: pointer;
      margin-bottom: 16px;
      transition: background 0.2s, transform 0.1s;
    }

    .btn-signup:hover {
      background: #38B2AC;
    }

    .btn-signup:active {
      transform: scale(0.98);
    }

    .signin-row {
      text-align: center;
      font-size: 13px;
      color: #aaa;
    }

    .signin-link {
      color: #4FD1C5;
      cursor: pointer;
      font-weight: 600;
      text-decoration: none;
    }

    .signin-link:hover {
      text-decoration: underline;
    }

    .verification-message {
      background: #d1ecf1;
      border: 1px solid #4FD1C5;
      border-radius: 8px;
      padding: 16px 14px;
      margin-bottom: 20px;
      color: #0c5460;
      text-align: center;
      font-weight: 600;
    }

    .verification-message .email {
      color: #4FD1C5;
      font-weight: 700;
      display: block;
      margin-top: 8px;
    }

    .rejection-message {
      background: #f8d7da;
      border: 1px solid #dc3545;
      border-radius: 8px;
      padding: 16px 14px;
      margin-bottom: 20px;
      color: #721c24;
      text-align: center;
      font-weight: 600;
    }

    @media (max-width: 480px) {
      .row2 { flex-direction: column; gap: 0; }
      .card { padding: 24px 18px; }
    }
  </style>
</head>
<body>

  <nav class="navbar">
    <div class="nav-links">
      <a class="nav-link" href="#">
        <svg width="11" height="11" viewBox="0 0 12 12" fill="currentColor">
          <circle cx="6" cy="4" r="2.5"/>
          <path d="M1 10c0-2.5 2.2-4.5 5-4.5s5 2 5 4.5"/>
        </svg>
        PROFILE
      </a>
      <a class="nav-link active" href="#">SIGN UP</a>
      <a class="nav-link" href="login.php">
        <svg width="11" height="11" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.2">
          <path d="M2 6h8M6 2l4 4-4 4"/>
        </svg>
        SIGN IN
      </a>
    </div>
  </nav>

  <div class="hero">
    <svg class="waves" viewBox="0 0 800 300" fill="none" xmlns="http://www.w3.org/2000/svg">
      <ellipse cx="600" cy="50" rx="350" ry="200" stroke="white" stroke-width="1.5" fill="none"/>
      <ellipse cx="200" cy="250" rx="400" ry="180" stroke="white" stroke-width="1" fill="none"/>
      <ellipse cx="700" cy="200" rx="250" ry="150" stroke="white" stroke-width="1" fill="none"/>
      <ellipse cx="100" cy="100" rx="200" ry="120" stroke="white" stroke-width="1" fill="none"/>
    </svg>
    <h1 class="hero-title">Welcome!</h1>
    <p class="hero-sub">Use these awesome forms to login or create new account in Swaply for free.</p>
  </div>

  <div class="card-wrap">
    <div class="card">

      <?php if ($verificationSent): ?>
        <div class="verification-message">
          ✓ Un email de vérification est envoyé à<br>
          <span class="email"><?= htmlspecialchars($verificationEmail) ?></span>
          <p style="margin-top: 12px; font-size: 12px; font-weight: 400;">
            Veuillez cliquer sur le lien dans l'email pour confirmer votre compte.<br>
            Le lien est valide pendant 24 heures.
          </p>
        </div>
      <?php endif; ?>

      <?php if ($rejectionMessage): ?>
        <div class="rejection-message">
          ✗ Veuillez vérifier vos informations
        </div>
      <?php endif; ?>

      <?php if (isset($_GET['error'])): ?>
        <p id="server-error-msg" style="color:red; margin-bottom:16px; font-weight:700;">
          <?php if ($_GET['error'] === 'empty'): ?>
            Veuillez remplir tous les champs.
          <?php elseif ($_GET['error'] === 'email'): ?>
            Format email invalide.
          <?php elseif ($_GET['error'] === 'duplicate'): ?>
            Cette adresse e-mail existe déjà.
          <?php elseif ($_GET['error'] === 'captcha'): ?>
            Réponse au captcha incorrecte.
          <?php else: ?>
            Une erreur s'est produite. Réessayez.
          <?php endif; ?>
        </p>
      <?php endif; ?>

      <form method="POST" action="/swaply/controller/UserC.php" onsubmit="return validateSignup()" novalidate>

      <p class="register-title">Register with</p>

      

      <div class="row2">
        <div>
          <label class="form-label">First Name</label>
          <input class="form-input" type="text" name="firstname" id="firstname" placeholder="First name" />
        </div>
        <div>
          <label class="form-label">Last Name</label>
          <input class="form-input" type="text" name="lastname" id="lastname" placeholder="Last name" />
        </div>
      </div>

      <label class="form-label">Email</label>
      <input class="form-input" type="text" name="email" id="email" placeholder="Your email address" />

      <label class="form-label">Phone Number</label>
      <input class="form-input" type="text" name="phone" id="phone" placeholder="Your phone number" />

      <div class="row2">
        <div>
          <label class="form-label">Date of Birth</label>
          <input class="form-input" type="text" name="date_naissance" id="date_naissance" placeholder="YYYY-MM-DD" />
        </div>
        <div>
          <label class="form-label">Gender</label>
          <select class="form-input" name="gender" id="gender">
            <option value="" disabled selected>Select</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
          </select>
        </div>
      </div>

      <label class="form-label">Password</label>
      <input class="form-input" type="password" name="password" id="password" placeholder="Your password" />

      <label class="form-label">Captcha de sécurité</label>
      <input class="form-input" type="text" name="captcha" id="captcha" placeholder="<?= htmlspecialchars($captchaQuestion) ?>" />
      <p id="face-msg" style="color:#16a34a; margin-bottom:10px; font-size:13px;"></p>
      <p id="error-msg" style="color:red; margin-bottom:10px;"></p>

      <input type="hidden" name="face_credential_id" id="face_credential_id" value="" />
      <input type="hidden" name="face_pubkey" id="face_pubkey_input" value="" />
      <input type="hidden" name="face_sign_count" id="face_sign_count_input" value="0" />
      <button type="button" class="btn-signup" style="background: #2d3748; margin-bottom: 12px;" onclick="saveFaceId()">Save Code / Finger Print</button>
      <button type="submit" name="signup" class="btn-signup">SIGN UP</button>


      </form>

      <p class="signin-row">
        Already have an account? <a class="signin-link" href="login.php">Sign in</a>
      </p>
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

async function saveFaceId() {
    const faceMsg = document.getElementById('face-msg');
    faceMsg.textContent = 'Préparation du Code / Empreinte...';

    try {
        const response = await fetch('../../controller/WebAuthnC.php?action=registerOptions');
        const data = await response.json();
        if (data.status !== 'ok') {
            faceMsg.textContent = 'Impossible de récupérer les options Code / Empreinte.';
            return;
        }

        const publicKey = data.publicKey;
        publicKey.challenge = base64UrlToBuffer(publicKey.challenge);
        publicKey.user.id = base64UrlToBuffer(publicKey.user.id);

        const credential = await navigator.credentials.create({ publicKey });
        console.log('Credential created:', credential);
        console.log('Credential type:', credential.type);
        console.log('Authenticator attachment:', credential.authenticatorAttachment);
        if (!credential) {
            faceMsg.textContent = 'Création du Code / Empreinte annulée.';
            return;
        }

        const attestationObject = arrayBufferToBase64Url(credential.response.attestationObject);
        const clientDataJSON = arrayBufferToBase64Url(credential.response.clientDataJSON);
        const credentialId = arrayBufferToBase64Url(credential.rawId);

        const verifyResponse = await fetch('../../controller/WebAuthnC.php?action=verifyRegistration', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                attestationObject,
                clientDataJSON,
            }),
        });

        const verifyData = await verifyResponse.json();
        if (verifyData.status !== 'ok') {
            faceMsg.textContent = 'Échec de l’enregistrement : ' + (verifyData.message || 'Erreur inconnue');
            return;
        }

        localStorage.setItem('swaply_face_id', credentialId);

        // Remplir les champs cachés pour les transmettre avec le formulaire
        document.getElementById('face_credential_id').value = verifyData.credentialId || credentialId;
        if (verifyData.publicKeyPem) {
            document.getElementById('face_pubkey_input').value = verifyData.publicKeyPem;
        }
        if (verifyData.signCount !== undefined) {
            document.getElementById('face_sign_count_input').value = verifyData.signCount;
        }

        faceMsg.textContent = 'Code / Empreinte enregistré avec succès. Vous pourrez vous connecter avec Code / Empreinte.';
    } catch (error) {
        faceMsg.textContent = 'Erreur : ' + error.message;
    }
}

window.onload = function() {
};

function validateSignup() {

    let firstname = document.getElementById("firstname").value.trim();
    let lastname = document.getElementById("lastname").value.trim();
    let email = document.getElementById("email").value.trim();
    let password = document.getElementById("password").value.trim();
    let phone = document.getElementById("phone").value.trim();
    let dateNaissance = document.getElementById("date_naissance").value.trim();
    let gender = document.getElementById("gender").value;
    let captcha = document.getElementById("captcha").value.trim();

    let errorMsg = document.getElementById("error-msg");

    errorMsg.innerHTML = "";

    if (firstname === "" || lastname === "" || email === "" || password === "" || phone === "" || dateNaissance === "" || gender === "") {
        errorMsg.innerHTML = "Veuillez remplir tous les champs.";
        return false;
    }

    let emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;
    if (!email.match(emailPattern)) {
        errorMsg.innerHTML = "Format email invalide.";
        return false;
    }

    if (phone.length > 15 || !/^[\+\d\s]+$/.test(phone)) {
        errorMsg.innerHTML = "Numéro de téléphone invalide (chiffres, espaces, + uniquement).";
        return false;
    }

    if (captcha === "" || !/^-?\d+$/.test(captcha)) {
        errorMsg.innerHTML = "Veuillez répondre au captcha avec un nombre valide.";
        return false;
    }

    let datePattern = /^\d{4}-\d{2}-\d{2}$/;
    if (!datePattern.test(dateNaissance)) {
        errorMsg.innerHTML = "Le format de la date doit être YYYY-MM-DD.";
        return false;
    }

    let dateParts = dateNaissance.split("-");
    let year = parseInt(dateParts[0], 10);
    let month = parseInt(dateParts[1], 10);
    let day = parseInt(dateParts[2], 10);
    let testDate = new Date(dateNaissance);

    if (testDate.getFullYear() !== year || testDate.getMonth() + 1 !== month || testDate.getDate() !== day) {
        errorMsg.innerHTML = "Date de naissance invalide.";
        return false;
    }

    if (testDate > new Date()) {
        errorMsg.innerHTML = "La date de naissance ne peut pas être dans le futur.";
        return false;
    }

    return true;
}
</script>

</body>
</html>