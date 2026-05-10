<?php
// ── PROBLÈME 3 CORRIGÉ ────────────────────────────────────────────────────────
// $users est filtré par le controller :
//   - exclut soi-même
//   - exclut les users avec qui il existe DÉJÀ une conv active
//   - INCLUT les users dont la conv a été supprimée → ils réapparaissent ici
// ─────────────────────────────────────────────────────────────────────────────

if (session_status() === PHP_SESSION_NONE) session_start();
$id_user = (int)($_SESSION['id_user'] ?? 0);
$errors  = $errors ?? [];

// Fallback si appelée directement sans passer par le controller
if (!isset($users)) {
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../model/Conversation.php';
    $cm  = new Conversation();
    $all = $cm->getAllUsers();
    // Filtrage : exclure soi-même + exclure users avec conv active existante
    $users = [];
    foreach ($all as $u) {
        $uid = (int)$u['id_u'];
        if ($uid === $id_user) continue;
        $existing = $cm->existsBetween($id_user, $uid);
        if ($existing) continue; // conv active → pas dans la liste
        $users[] = $u;           // conv absente/supprimée → dans la liste
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle conversation – Swaply</title>
    <link rel="stylesheet" href="../../asset/css/style.css">
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Segoe UI',sans-serif; background:#f5f7fa; }
        .navbar { display:flex; align-items:center; padding:0 32px; height:64px; background:#fff; border-bottom:1px solid #e5e7eb; position:sticky; top:0; z-index:100; }
        .navbar-brand { display:flex; align-items:center; gap:8px; font-size:18px; font-weight:700; color:#1a1a1a; text-decoration:none; margin-right:auto; }
        .navbar-logo { width:34px; height:34px; border-radius:50%; background:#1D9E75; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; font-size:15px; }
        .navbar-links { display:flex; gap:28px; }
        .navbar-links a { font-size:14px; color:#4b5563; text-decoration:none; }
        .navbar-links a:hover { color:#1D9E75; }
        .navbar-links a.active { color:#1D9E75; font-weight:600; border-bottom:2px solid #1D9E75; padding-bottom:2px; }
        .navbar-avatar { width:34px; height:34px; border-radius:50%; background:#d1d5db; display:flex; align-items:center; justify-content:center; font-size:13px; color:#6b7280; margin-left:24px; }
        .page-content { max-width:560px; margin:48px auto; padding:0 16px; }
        .card { background:#fff; border-radius:12px; border:1px solid #e5e7eb; padding:32px; }
        .card-title { font-size:18px; font-weight:700; color:#111827; margin-bottom:24px; }
        .form-group { margin-bottom:20px; }
        .form-label { display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px; }
        .form-control { width:100%; padding:10px 14px; border:1px solid #e5e7eb; border-radius:8px; font-size:13px; font-family:inherit; outline:none; background:#f9fafb; color:#111827; }
        .form-control:focus { border-color:#1D9E75; background:#fff; }
        .form-control.error-field { border-color:#dc2626; }
        textarea.form-control { resize:vertical; min-height:100px; }
        .error-list { margin-bottom:16px; padding:10px 14px; background:#fef2f2; border:1px solid #fecaca; border-radius:8px; }
        .error-list p { color:#dc2626; font-size:12px; margin-bottom:2px; }
        .field-error { color:#dc2626; font-size:11px; margin-top:4px; display:none; }
        .field-error.visible { display:block; }
        .char-info { font-size:11px; color:#9ca3af; text-align:right; margin-top:4px; }
        .char-info.warn { color:#f59e0b; }
        .char-info.over { color:#dc2626; }
        .form-actions { display:flex; gap:10px; margin-top:24px; }
        .btn-primary { flex:1; padding:11px; background:#1D9E75; color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; }
        .btn-primary:hover { background:#178a64; }
        .btn-secondary { padding:11px 20px; background:none; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; color:#6b7280; cursor:pointer; text-decoration:none; display:inline-block; text-align:center; }
        .btn-secondary:hover { background:#f9fafb; }
        .no-users { text-align:center; padding:24px 0; color:#9ca3af; }
        .no-users .icon { font-size:40px; margin-bottom:12px; }
    </style>
</head>
<body>

<nav class="navbar">
    <a class="navbar-brand" href="indexf.php">
        <div class="navbar-logo">S</div>
        Swaply
    </a>
    <div class="navbar-links">
        <a href="indexf.php">Accueil</a>
        <a href="#">Profils</a>
        <a href="#">Projets</a>
        <a href="#">Offres</a>
        <a href="#">Demandes</a>
        <a href="#">Publications</a>
        <a href="messagerie.php" class="active">Messages</a>
        <a href="#">Réclamations</a>
    </div>
    <div class="navbar-avatar"><?= strtoupper(substr($_SESSION['prenom'] ?? 'U', 0, 1)) ?></div>
</nav>

<div class="page-content">
    <div class="card">
        <h1 class="card-title">💬 Nouvelle conversation</h1>

        <?php if (!empty($errors)): ?>
            <div class="error-list">
                <?php foreach ($errors as $e): ?>
                    <p>⚠ <?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($users)): ?>
            <!-- Aucun utilisateur disponible (tous ont déjà une conv active) -->
            <div class="no-users">
                <div class="icon">👥</div>
                <p style="font-size:14px;margin-bottom:8px;">
                    Vous avez déjà une conversation avec tous les utilisateurs.
                </p>
                <p style="font-size:12px;margin-bottom:16px;">
                    Supprimez une conversation existante pour pouvoir en recréer une.
                </p>
                <a href="messagerie.php" class="btn-secondary">← Retour aux messages</a>
            </div>
        <?php else: ?>
            <form method="POST" action="../../controller/MessageController.php?action=createConversation"
                  id="newConvForm" novalidate>

                <div class="form-group">
                    <label class="form-label" for="id_destinataire">Destinataire *</label>
                    <select name="id_destinataire" id="id_destinataire"
                            class="form-control" onchange="validateDestinataire()">
                        <option value="">-- Choisir un utilisateur --</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= (int)$u['id_u'] ?>"
                                <?= (isset($_POST['id_destinataire']) && (int)$_POST['id_destinataire'] === (int)$u['id_u']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['prenom'].' '.$u['nom']) ?>
                                <?= !empty($u['email']) ? ' — '.htmlspecialchars($u['email']) : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="field-error" id="err-destinataire">
                        Veuillez sélectionner un destinataire.
                    </span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="contenu_init">Premier message *</label>
                    <textarea name="contenu_init" id="contenu_init" class="form-control"
                        placeholder="Écrivez votre message..."
                        oninput="updateCharInfo(); validateContenu()"><?= htmlspecialchars($_POST['contenu_init'] ?? '') ?></textarea>
                    <div class="char-info" id="charInfo">0 / 2000</div>
                    <span class="field-error" id="err-contenu">
                        Le message ne peut pas être vide (max 2000 caractères).
                    </span>
                </div>

                <div class="form-actions">
                    <a href="messagerie.php" class="btn-secondary">Annuler</a>
                    <button type="button" class="btn-primary" onclick="validateForm()">Envoyer →</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script src="../../asset/js/validation.js"></script>
<script>
// Validation JS (sans HTML5) ──────────────────────────────────────────────────
function validateDestinataire() {
    const sel = document.getElementById('id_destinataire');
    const err = document.getElementById('err-destinataire');
    if (!sel || !sel.value) {
        if (sel) sel.classList.add('error-field');
        if (err) err.classList.add('visible');
        return false;
    }
    sel.classList.remove('error-field');
    err.classList.remove('visible');
    return true;
}

function validateContenu() {
    const ta  = document.getElementById('contenu_init');
    const err = document.getElementById('err-contenu');
    const val = ta ? ta.value.trim() : '';
    if (!val || val.length > 2000) {
        if (ta)  ta.classList.add('error-field');
        if (err) err.classList.add('visible');
        return false;
    }
    if (ta)  ta.classList.remove('error-field');
    if (err) err.classList.remove('visible');
    return true;
}

function updateCharInfo() {
    const ta  = document.getElementById('contenu_init');
    const el  = document.getElementById('charInfo');
    if (!ta || !el) return;
    const n   = ta.value.length;
    el.textContent = n + ' / 2000';
    el.className   = 'char-info' + (n > 1800 ? ' warn' : '') + (n >= 2000 ? ' over' : '');
}

function validateForm() {
    const v1 = validateDestinataire();
    const v2 = validateContenu();
    if (v1 && v2) document.getElementById('newConvForm').submit();
}
</script>
</body>
</html>