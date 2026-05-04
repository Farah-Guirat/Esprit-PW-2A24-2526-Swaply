<?php
session_start();

require_once __DIR__ . "/../../config/Database.php";
require_once __DIR__ . "/../../model/EmailVerification.php";

$db = new Database();
$conn = $db->connect();
$emailVerification = new EmailVerification($conn);

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$tokenValid = false;
$errorMsg = '';

if ($token === '') {
    $errorMsg = 'Lien invalide ou manquant.';
} else {
    $tokenData = $emailVerification->getResetTokenData($token);
    if (!$tokenData) {
        $errorMsg = 'Ce lien de réinitialisation est invalide ou a expiré.';
    } else {
        $tokenValid = true;
    }
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'empty_password':
            $errorMsg = 'Veuillez saisir et confirmer votre nouveau mot de passe.';
            break;
        case 'mismatch':
            $errorMsg = 'Les mots de passe ne correspondent pas.';
            break;
        case 'short_password':
            $errorMsg = 'Le mot de passe doit contenir au moins 8 caractères.';
            break;
        case 'invalid_token':
            $errorMsg = 'Ce lien de réinitialisation n’est plus valide.';
            break;
        default:
            if ($errorMsg === '') {
                $errorMsg = 'Une erreur est survenue. Veuillez réessayer.';
            }
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Swaply – Réinitialiser le mot de passe</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'DM Sans', sans-serif;
      min-height: 100vh;
      background: #d1d5db;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* ── MODAL ── */
    .modal {
      background: #fff;
      border-radius: 18px;
      padding: 36px 40px 32px;
      width: 100%;
      max-width: 440px;
      box-shadow: 0 24px 60px rgba(0,0,0,.12);
      position: relative;
      animation: popIn .3s cubic-bezier(.34,1.56,.64,1) both;
    }

    @keyframes popIn {
      from { opacity: 0; transform: scale(.92) translateY(12px); }
      to   { opacity: 1; transform: scale(1) translateY(0); }
    }

    /* close btn */
    .close-btn {
      position: absolute;
      top: 16px; right: 16px;
      width: 30px; height: 30px;
      border: 1.5px solid #e2e8f0;
      border-radius: 50%;
      background: none;
      cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      color: #9ca3af;
      transition: border-color .2s, color .2s;
    }
    .close-btn:hover { border-color: #3ec9b6; color: #3ec9b6; }

    /* header */
    .modal-header {
      display: flex;
      align-items: flex-start;
      gap: 14px;
      margin-bottom: 28px;
    }

    .lock-icon {
      width: 46px; height: 46px;
      background: rgba(62,201,182,.12);
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
    }

    .lock-icon svg { width: 22px; height: 22px; color: #3ec9b6; }

    .modal-title {
      font-size: 18px;
      font-weight: 700;
      color: #1e2d3d;
      margin-bottom: 4px;
    }

    .modal-sub {
      font-size: 13px;
      color: #9ca3af;
      line-height: 1.5;
    }

    .message {
      font-size: 14px;
      color: #e53e3e;
      margin-bottom: 20px;
      line-height: 1.5;
    }

    /* fields */
    .field { margin-bottom: 18px; }

    .field label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: #374151;
      margin-bottom: 8px;
    }

    .input-wrap {
      position: relative;
      background: #f8fafc;
      border: 1.5px solid #e9eef4;
      border-radius: 10px;
      display: flex;
      align-items: center;
      transition: border-color .2s, box-shadow .2s;
    }

    .input-wrap:focus-within {
      border-color: #3ec9b6;
      box-shadow: 0 0 0 3px rgba(62,201,182,.13);
      background: #fff;
    }

    .input-wrap.error {
      border-color: #f87171;
      box-shadow: 0 0 0 3px rgba(248,113,113,.1);
    }

    .input-icon {
      padding: 0 10px 0 14px;
      color: #9ca3af;
      display: flex;
      flex-shrink: 0;
    }

    .input-icon svg { width: 16px; height: 16px; }

    input[type="password"],
    input[type="text"] {
      flex: 1;
      border: none;
      background: transparent;
      font-family: 'DM Sans', sans-serif;
      font-size: 14px;
      color: #1e2d3d;
      padding: 13px 0;
      outline: none;
    }

    input::placeholder { color: #c4cdd6; }

    .toggle-eye {
      background: none;
      border: none;
      cursor: pointer;
      padding: 0 14px;
      color: #9ca3af;
      display: flex;
      transition: color .2s;
      flex-shrink: 0;
    }
    .toggle-eye:hover { color: #3ec9b6; }
    .toggle-eye svg { width: 17px; height: 17px; }

    /* strength bar */
    .strength-bar {
      display: flex;
      gap: 5px;
      margin-top: 8px;
    }

    .bar-seg {
      flex: 1;
      height: 3px;
      border-radius: 99px;
      background: #e9eef4;
      transition: background .35s;
    }

    .strength-label {
      font-size: 11px;
      color: #9ca3af;
      margin-top: 5px;
      min-height: 16px;
      transition: color .3s;
    }

    /* error hint */
    .hint {
      font-size: 12px;
      color: #f87171;
      margin-top: 5px;
      display: none;
    }
    .hint.visible { display: block; }

    /* divider */
    .divider {
      height: 1px;
      background: #f1f5f9;
      margin: 24px 0 20px;
    }

    /* actions */
    .actions {
      display: flex;
      gap: 12px;
    }

    .btn-cancel {
      flex: 1;
      padding: 13px;
      border: 1.5px solid #e2e8f0;
      border-radius: 10px;
      background: none;
      font-family: 'DM Sans', sans-serif;
      font-size: 14px;
      font-weight: 600;
      color: #374151;
      cursor: pointer;
      transition: border-color .2s, color .2s;
    }
    .btn-cancel:hover { border-color: #9ca3af; color: #1e2d3d; }

    .btn-save {
      flex: 1.6;
      padding: 13px;
      background: #3ec9b6;
      border: none;
      border-radius: 10px;
      font-family: 'DM Sans', sans-serif;
      font-size: 14px;
      font-weight: 600;
      color: #fff;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 7px;
      transition: background .2s, transform .15s, box-shadow .2s;
      position: relative;
      overflow: hidden;
    }

    .btn-save:hover {
      background: #2aab98;
      box-shadow: 0 6px 18px rgba(62,201,182,.3);
      transform: translateY(-1px);
    }

    .btn-save:active { transform: translateY(0); }
    .btn-save.loading .btn-label { opacity: 0; }
    .btn-save.loading .spinner { display: block; }

    .btn-save svg { width: 15px; height: 15px; }

    .spinner {
      display: none;
      position: absolute;
      width: 18px; height: 18px;
      border: 2px solid rgba(255,255,255,.4);
      border-top-color: #fff;
      border-radius: 50%;
      animation: spin .7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* success overlay */
    .success-overlay {
      display: none;
      flex-direction: column;
      align-items: center;
      text-align: center;
      padding: 20px 0 8px;
      animation: fadeUp .4s ease both;
    }
    .success-overlay.visible { display: flex; }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(16px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .success-circle {
      width: 64px; height: 64px;
      background: rgba(62,201,182,.12);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 16px;
    }
    .success-circle svg { width: 30px; height: 30px; color: #3ec9b6; }

    .success-title {
      font-size: 17px;
      font-weight: 700;
      color: #1e2d3d;
      margin-bottom: 6px;
    }
    .success-msg {
      font-size: 13px;
      color: #9ca3af;
      line-height: 1.55;
      margin-bottom: 24px;
    }

    .btn-done {
      width: 100%;
      padding: 13px;
      background: #3ec9b6;
      border: none;
      border-radius: 10px;
      font-family: 'DM Sans', sans-serif;
      font-size: 14px;
      font-weight: 600;
      color: #fff;
      cursor: pointer;
      transition: background .2s;
    }
    .btn-done:hover { background: #2aab98; }
  </style>
</head>
<body>

<div class="modal" id="modal">

  <button class="close-btn" onclick="closeModal()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
      <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
    </svg>
  </button>

  <!-- ── FORM VIEW ── -->
  <div id="formView">
    <div class="modal-header">
      <div class="lock-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="11" width="18" height="11" rx="2"/>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
      </div>
      <div>
        <div class="modal-title">Réinitialiser le mot de passe</div>
        <div class="modal-sub">Votre nouveau mot de passe doit être différent du précédent</div>
      </div>
    </div>

    <?php if ($errorMsg !== ''): ?>
      <div class="message error"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <?php if ($tokenValid): ?>
      <form method="POST" action="/swaply/controller/UserC.php" novalidate>
        <input type="hidden" name="reset_password" value="1" />
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>" />

        <!-- New password -->
        <div class="field">
          <label>Nouveau mot de passe</label>
          <div class="input-wrap" id="newWrap">
            <span class="input-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
            </span>
            <input type="password" id="newPass" name="new_password" placeholder="Nouveau mot de passe" oninput="checkStrength()" />
            <button class="toggle-eye" type="button" onclick="toggleVis('newPass', this)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
          <div class="strength-bar">
            <div class="bar-seg" id="s1"></div>
            <div class="bar-seg" id="s2"></div>
            <div class="bar-seg" id="s3"></div>
            <div class="bar-seg" id="s4"></div>
          </div>
          <div class="strength-label" id="strengthLabel"></div>
        </div>

        <!-- Confirm password -->
        <div class="field">
          <label>Confirmer le nouveau mot de passe</label>
          <div class="input-wrap" id="confirmWrap">
            <span class="input-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
            </span>
            <input type="password" id="confirmPass" name="confirm_password" placeholder="Confirmer le mot de passe" />
            <button class="toggle-eye" type="button" onclick="toggleVis('confirmPass', this)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
          <div class="hint" id="matchHint">Les mots de passe ne correspondent pas.</div>
        </div>

        <div class="divider"></div>

        <div class="actions">
          <button class="btn-cancel" type="button" onclick="window.location.href='/swaply/view/front/login.php'">Annuler</button>
          <button class="btn-save" type="submit">
            <span class="btn-label">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:4px">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
              Enregistrer
            </span>
          </button>
        </div>
      </form>
    <?php else: ?>
      <div class="actions">
        <button class="btn-cancel" type="button" onclick="window.location.href='/swaply/view/front/login.php'">Retour à la connexion</button>
      </div>
    <?php endif; ?>
  </div>

  <!-- ── SUCCESS VIEW ── -->
  <div class="success-overlay" id="successView">
    <div class="success-circle">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="20 6 9 17 4 12"/>
      </svg>
    </div>
    <div class="success-title">Mot de passe réinitialisé !</div>
    <div class="success-msg">Votre mot de passe a été mis à jour avec succès.<br>Vous pouvez maintenant vous connecter.</div>
    <button class="btn-done" type="button" onclick="window.location.href='/swaply/view/front/login.php'">Se connecter</button>
  </div>

</div>

<script>
  // toggle password visibility
  function toggleVis(id, btn) {
    const input = document.getElementById(id);
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    btn.innerHTML = isText
      ? `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="17" height="17"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`
      : `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="17" height="17"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`;
  }

  // strength checker
  function checkStrength() {
    const val = document.getElementById('newPass').value;
    const segs = [document.getElementById('s1'), document.getElementById('s2'), document.getElementById('s3'), document.getElementById('s4')];
    const label = document.getElementById('strengthLabel');

    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const colors = ['#f87171','#fb923c','#facc15','#3ec9b6'];
    const labels = ['Très faible','Faible','Moyen','Fort'];
    const labelColors = ['#f87171','#fb923c','#ca8a04','#3ec9b6'];

    segs.forEach((s, i) => {
      s.style.background = i < score ? colors[score - 1] : '#e9eef4';
    });

    if (val.length === 0) {
      label.textContent = '';
    } else {
      label.textContent = labels[score - 1] || 'Très faible';
      label.style.color = labelColors[score - 1] || '#f87171';
    }
  }

  function closeModal() {
    window.location.href = '/swaply/view/front/login.php';
  }
</script>
</body>
</html>
