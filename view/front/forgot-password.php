<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Swaply – Mot de passe oublié</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Syne:wght@700;800&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --teal: #3ec9b6;
      --teal-dark: #2aab98;
      --teal-bg: #3ec9b6;
      --dark: #1e2d3d;
      --text: #444;
      --muted: #999;
      --border: #e4e9ef;
      --white: #ffffff;
    }

    html, body {
      height: 100%;
      font-family: 'DM Sans', sans-serif;
    }

    body {
      display: grid;
      grid-template-columns: 1fr 1fr;
      min-height: 100vh;
      background: var(--white);
    }

    /* ── LEFT PANEL ── */
    .left {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: flex-start;
      padding: 64px 80px;
      animation: fadeUp .55s ease both;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(28px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 13px;
      font-weight: 500;
      color: var(--muted);
      text-decoration: none;
      margin-bottom: 48px;
      letter-spacing: .02em;
      transition: color .2s;
    }
    .back-link:hover { color: var(--teal); }
    .back-link svg { width: 16px; height: 16px; }

    .heading {
      font-family: 'Syne', sans-serif;
      font-size: 32px;
      font-weight: 800;
      color: var(--teal);
      line-height: 1.15;
      margin-bottom: 10px;
    }

    .subtext {
      font-size: 14px;
      color: var(--muted);
      margin-bottom: 40px;
      line-height: 1.6;
      max-width: 360px;
    }
    .message {
      font-size: 14px;
      margin-bottom: 20px;
      line-height: 1.5;
    }

    .message.error {
      color: #e53e3e;
    }

    .message.success {
      color: #2f855a;
    }
    .field-group {
      width: 100%;
      max-width: 380px;
    }

    label {
      display: block;
      font-size: 13px;
      font-weight: 500;
      color: var(--dark);
      margin-bottom: 8px;
      letter-spacing: .01em;
    }

    .input-wrap {
      position: relative;
      margin-bottom: 28px;
    }

    .input-icon {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: #bbb;
      pointer-events: none;
      display: flex;
    }

    input[type="text"] {
      width: 100%;
      padding: 13px 16px 13px 42px;
      border: 1.5px solid var(--border);
      border-radius: 8px;
      font-family: 'DM Sans', sans-serif;
      font-size: 14px;
      color: var(--dark);
      background: var(--white);
      outline: none;
      transition: border-color .2s, box-shadow .2s;
    }

    input[type="text"]::placeholder { color: #bbb; }

    input[type="text"]:focus {
      border-color: var(--teal);
      box-shadow: 0 0 0 3px rgba(62,201,182,.15);
    }

    .btn-send {
      width: 100%;
      max-width: 380px;
      padding: 14px;
      background: var(--teal);
      color: var(--white);
      border: none;
      border-radius: 8px;
      font-family: 'DM Sans', sans-serif;
      font-size: 14px;
      font-weight: 600;
      letter-spacing: .06em;
      text-transform: uppercase;
      cursor: pointer;
      transition: background .2s, transform .15s, box-shadow .2s;
      position: relative;
      overflow: hidden;
    }

    .btn-send::after {
      content: '';
      position: absolute;
      inset: 0;
      background: rgba(255,255,255,0);
      transition: background .2s;
    }

    .btn-send:hover {
      background: var(--teal-dark);
      box-shadow: 0 6px 20px rgba(62,201,182,.35);
      transform: translateY(-1px);
    }

    .btn-send:active { transform: translateY(0); }

    .btn-send.loading .btn-label { opacity: 0; }
    .btn-send.loading .spinner { display: block; }

    .spinner {
      display: none;
      position: absolute;
      top: 50%; left: 50%;
      width: 20px; height: 20px;
      border: 2px solid rgba(255,255,255,.4);
      border-top-color: #fff;
      border-radius: 50%;
      transform: translate(-50%, -50%);
      animation: spin .7s linear infinite;
    }

    @keyframes spin { to { transform: translate(-50%,-50%) rotate(360deg); } }

    /* success state */
    .success-box {
      display: none;
      flex-direction: column;
      align-items: flex-start;
      gap: 12px;
      background: rgba(62,201,182,.08);
      border: 1.5px solid rgba(62,201,182,.3);
      border-radius: 10px;
      padding: 20px 22px;
      margin-top: 20px;
      max-width: 380px;
      animation: fadeUp .4s ease both;
    }

    .success-box.visible { display: flex; }

    .success-icon {
      width: 38px; height: 38px;
      background: var(--teal);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
    }

    .success-icon svg { width: 18px; height: 18px; color: #fff; }

    .success-title {
      font-family: 'Syne', sans-serif;
      font-size: 15px;
      font-weight: 700;
      color: var(--dark);
    }

    .success-msg {
      font-size: 13px;
      color: var(--muted);
      line-height: 1.55;
    }

    /* ── RIGHT PANEL ── */
    .right {
      background: var(--teal-bg);
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }

    .right::before,
    .right::after {
      content: '';
      position: absolute;
      border-radius: 50%;
      border: 1px solid rgba(255,255,255,.18);
    }

    .right::before { width: 520px; height: 520px; top: -100px; right: -100px; }
    .right::after  { width: 340px; height: 340px; bottom: -80px; left: -80px; }

    .logo-wrap {
      position: relative;
      z-index: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 16px;
    }

    .logo-circle {
      width: 180px; height: 180px;
      background: var(--white);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 20px 60px rgba(0,0,0,.15);
    }

    .logo-circle img {
      width: 140px;
      height: 140px;
      object-fit: contain;
    }

    .logo-name {
      font-family: 'Syne', sans-serif;
      font-size: 22px;
      font-weight: 800;
      color: var(--white);
      letter-spacing: .1em;
    }

    /* nav top */
    .topnav {
      position: fixed;
      top: 0; right: 0;
      padding: 16px 32px;
      display: flex;
      gap: 24px;
      align-items: center;
      font-size: 13px;
      font-weight: 500;
      z-index: 10;
    }

    .topnav a {
      text-decoration: none;
      color: var(--muted);
      letter-spacing: .04em;
      display: flex; align-items: center; gap: 4px;
      transition: color .2s;
    }
    .topnav a:hover { color: var(--teal); }
    .topnav a.active { color: var(--dark); font-weight: 600; }

    /* responsive */
    @media (max-width: 768px) {
      body { grid-template-columns: 1fr; }
      .right { display: none; }
      .left { padding: 48px 28px; }
    }
  </style>
</head>
<body>

  <!-- top nav -->
  <nav class="topnav">
    <a href="#">👤 PROFILE</a>
    <a href="register.php">SIGN UP</a>
    <a href="login.php" class="active">→ SIGN IN</a>
  </nav>

  <!-- LEFT -->
  <section class="left">
    <a href="login.php" class="back-link">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="15 18 9 12 15 6"/>
      </svg>
      Retour à la connexion
    </a>

    <h1 class="heading">Mot de passe oublié ?</h1>
    <p class="subtext">
      Saisissez l'adresse e-mail liée à votre compte. Nous vous enverrons un lien pour réinitialiser votre mot de passe.
    </p>

    <div class="field-group">
      <?php if (isset($_GET['error'])): ?>
        <div class="message error">
          <?php if ($_GET['error'] === 'no_email'): ?>Aucun compte n’est associé à cette adresse e-mail.
          <?php elseif ($_GET['error'] === 'invalid_email'): ?>Adresse e-mail invalide.
          <?php elseif ($_GET['error'] === 'send_failed'): ?>Impossible d'envoyer le lien de réinitialisation. Réessayez plus tard.
          <?php else: ?>Une erreur est survenue. Réessayez.
          <?php endif; ?>
        </div>
      <?php elseif (isset($_GET['status']) && $_GET['status'] === 'sent'): ?>
        <div class="message success">
          Si cet e-mail existe, un lien de réinitialisation a été envoyé.
        </div>
      <?php endif; ?>

      <form method="POST" action="/swaply/controller/UserC.php" novalidate>
        <label for="email">Adresse e-mail</label>
        <div class="input-wrap">
          <span class="input-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="4" width="20" height="16" rx="2"/>
              <path d="M2 7l10 7 10-7"/>
            </svg>
          </span>
          <input type="text" id="email" name="email" placeholder="Votre adresse e-mail" />
        </div>

        <input type="hidden" name="forgot_password" value="1" />

        <button class="btn-send" type="submit">
          <span class="btn-label">Envoyer le lien</span>
        </button>
      </form>
    </div>
  </section>

  <!-- RIGHT -->
  <section class="right">
    <div class="logo-wrap">
      <div class="logo-circle">
      <img src="logo.png" alt="Logo" />
    </div>
    </div>
  </section>

