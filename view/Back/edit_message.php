<?php
// Point d'entrée back – Modifier un message
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../model/Message.php';
require_once __DIR__ . '/../../model/Conversation.php';
require_once __DIR__ . '/../../controller/MessageController.php';

if (!isset($message)) {
    $ctrl = new MessageController();
    $ctrl->editBack();
    exit;
}
$errors   = $errors ?? [];
$from_conv = isset($_GET['from_conv']) ? (int)$_GET['from_conv'] : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier message #<?= $message['id_message'] ?> – Swaply Admin</title>
    <link rel="stylesheet" href="../../asset/css/styles.css">
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Segoe UI',sans-serif; background:#f3f4f6; display:flex; min-height:100vh; }
        .sidebar { width:240px; background:#1a1a2e; min-height:100vh; display:flex; flex-direction:column; flex-shrink:0; position:fixed; top:0; left:0; height:100%; }
        .sidebar-brand { padding:20px 16px; display:flex; align-items:center; gap:10px; border-bottom:1px solid rgba(255,255,255,.08); }
        .sidebar-brand-icon { width:36px; height:36px; background:#1D9E75; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:18px; }
        .sidebar-brand-text { font-size:15px; font-weight:700; color:#fff; line-height:1.2; }
        .sidebar-brand-text span { font-size:11px; color:#9ca3af; font-weight:400; display:block; }
        .sidebar-nav { padding:12px 0; flex:1; }
        .nav-item { display:flex; align-items:center; gap:10px; padding:11px 16px; color:#9ca3af; text-decoration:none; font-size:13px; border-left:3px solid transparent; }
        .nav-item:hover { background:rgba(255,255,255,.05); color:#fff; }
        .nav-item.active { background:rgba(29,158,117,.15); color:#1D9E75; border-left-color:#1D9E75; font-weight:600; }
        .nav-icon { width:18px; text-align:center; font-size:15px; }
        .main { margin-left:240px; flex:1; display:flex; flex-direction:column; }
        .topbar { height:60px; background:#fff; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; padding:0 28px; gap:16px; position:sticky; top:0; z-index:50; }
        .topbar-title { font-size:18px; font-weight:700; color:#1D9E75; flex:1; }
        .btn-back { display:inline-flex; align-items:center; gap:6px; padding:7px 14px; border:1px solid #e5e7eb; border-radius:8px; font-size:13px; color:#6b7280; text-decoration:none; background:#fff; }
        .btn-back:hover { background:#f9fafb; }
        .topbar-user { display:flex; align-items:center; gap:10px; }
        .topbar-avatar { width:34px; height:34px; border-radius:50%; background:#d1d5db; display:flex; align-items:center; justify-content:center; font-size:13px; color:#6b7280; }
        .topbar-name strong { display:block; font-size:13px; color:#111827; }
        .topbar-name span { font-size:11px; color:#9ca3af; }
        .content { padding:28px; max-width:680px; }

        /* Carte formulaire */
        .form-card { background:#fff; border-radius:12px; border:1px solid #e5e7eb; padding:28px; }
        .form-card-title { font-size:16px; font-weight:700; color:#111827; margin-bottom:20px; display:flex; align-items:center; gap:8px; }

        /* Infos du message original */
        .meta-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; background:#f9fafb; border-radius:8px; padding:14px 16px; margin-bottom:20px; }
        .meta-item label { font-size:11px; color:#9ca3af; text-transform:uppercase; letter-spacing:.5px; display:block; margin-bottom:3px; }
        .meta-item span { font-size:13px; color:#374151; font-weight:500; }

        /* Contenu original */
        .original-bubble { background:#f3f4f6; border-radius:10px; padding:12px 16px; margin-bottom:20px; }
        .original-label { font-size:11px; color:#9ca3af; margin-bottom:6px; }
        .original-text { font-size:13px; color:#374151; line-height:1.5; }

        /* Formulaire */
        .form-group { margin-bottom:18px; }
        .form-label { display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px; }
        .form-control { width:100%; padding:10px 14px; border:1px solid #e5e7eb; border-radius:8px; font-size:13px; font-family:inherit; outline:none; background:#f9fafb; color:#111827; }
        .form-control:focus { border-color:#1D9E75; background:#fff; }
        .form-control.error-field { border-color:#dc2626; }
        textarea.form-control { resize:vertical; min-height:120px; }

        /* Erreurs */
        .error-list { margin-bottom:16px; padding:10px 14px; background:#fef2f2; border:1px solid #fecaca; border-radius:8px; }
        .error-list p { color:#dc2626; font-size:12px; margin-bottom:2px; }
        .field-error { color:#dc2626; font-size:11px; margin-top:4px; display:none; }
        .field-error.visible { display:block; }
        .char-info { font-size:11px; color:#9ca3af; text-align:right; margin-top:4px; }
        .char-info.warn { color:#f59e0b; }
        .char-info.over { color:#dc2626; }

        /* Boutons */
        .form-actions { display:flex; gap:10px; margin-top:24px; }
        .btn-save { flex:1; padding:11px; background:#1D9E75; color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; }
        .btn-save:hover { background:#178a64; }
        .btn-cancel-lnk { padding:11px 20px; background:none; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; color:#6b7280; cursor:pointer; text-decoration:none; display:inline-block; text-align:center; }
        .btn-cancel-lnk:hover { background:#f9fafb; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">📋</div>
        <div class="sidebar-brand-text">Swaply<span>Admin</span></div>
    </div>
    <nav class="sidebar-nav">
        <a href="index.php"         class="nav-item"><span class="nav-icon">🏠</span> Dashboard</a>
        <a href="#"                 class="nav-item"><span class="nav-icon">👥</span> Utilisateurs</a>
        <a href="#"                 class="nav-item"><span class="nav-icon">👤</span> Profils & Portfolios</a>
        <a href="#"                 class="nav-item"><span class="nav-icon">💼</span> Offres & Demandes</a>
        <a href="#"                 class="nav-item"><span class="nav-icon">📰</span> Publications</a>
        <a href="conversations.php" class="nav-item"><span class="nav-icon">💬</span> Conversations</a>
        <a href="messages.php"      class="nav-item active"><span class="nav-icon">✉️</span> Messages</a>
        <a href="#"                 class="nav-item"><span class="nav-icon">⚠️</span> Réclamations</a>
        <a href="#"                 class="nav-item"><span class="nav-icon">📊</span> Statistiques</a>
        <a href="#"                 class="nav-item"><span class="nav-icon">⚙️</span> Paramètres</a>
    </nav>
</div>

<div class="main">
    <div class="topbar">
        <!-- Retour vers la conv si on vient de là, sinon vers messages -->
        <?php if ($from_conv > 0): ?>
            <a href="../../controller/ConversationController.php?action=viewBack&id=<?= $from_conv ?>" class="btn-back">← Retour à la conversation</a>
        <?php else: ?>
            <a href="messages.php" class="btn-back">← Retour aux messages</a>
        <?php endif; ?>
        <div class="topbar-title">Modifier le message #<?= $message['id_message'] ?></div>
        <div class="topbar-user">
            <span>🔔</span>
            <div class="topbar-avatar">A</div>
            <div class="topbar-name"><strong>Admin</strong><span>Super Admin</span></div>
        </div>
    </div>

    <div class="content">
        <div class="form-card">
            <div class="form-card-title">✏️ Modifier le message</div>

            <!-- Métadonnées du message -->
            <div class="meta-grid">
                <div class="meta-item">
                    <label>ID message</label>
                    <span>#<?= $message['id_message'] ?></span>
                </div>
                <div class="meta-item">
                    <label>Conversation</label>
                    <span>
                        <a href="../../controller/ConversationController.php?action=viewBack&id=<?= $message['id_conversation'] ?>"
                           style="color:#0369a1;">Conv. #<?= $message['id_conversation'] ?></a>
                    </span>
                </div>
                <div class="meta-item">
                    <label>Expéditeur</label>
                    <span><?= htmlspecialchars($message['prenom'].' '.$message['nom']) ?></span>
                </div>
                <div class="meta-item">
                    <label>Date d'envoi</label>
                    <span><?= date('d/m/Y H:i', strtotime($message['date_envoi'])) ?></span>
                </div>
            </div>

            <!-- Contenu original pour référence -->
            <div class="original-bubble">
                <div class="original-label">Contenu original :</div>
                <div class="original-text"><?= nl2br(htmlspecialchars($message['contenu'])) ?></div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="error-list">
                    <?php foreach ($errors as $e): ?>
                        <p>⚠ <?= htmlspecialchars($e) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST"
                  action="../../controller/MessageController.php?action=editBack&id=<?= $message['id_message'] ?><?= $from_conv ? '&from_conv='.$from_conv : '' ?>"
                  id="editForm" novalidate>

                <div class="form-group">
                    <label class="form-label" for="contenu">Nouveau contenu *</label>
                    <textarea name="contenu" id="contenu" class="form-control"
                        oninput="updateCharInfo(); validateContenu()"><?= htmlspecialchars($_POST['contenu'] ?? $message['contenu']) ?></textarea>
                    <div class="char-info" id="charInfo"><?= strlen($message['contenu']) ?> / 2000</div>
                    <span class="field-error" id="err-contenu">Le contenu ne peut pas être vide (max 2000 caractères).</span>
                </div>

                <div class="form-actions">
                    <?php if ($from_conv > 0): ?>
                        <a href="../../controller/ConversationController.php?action=viewBack&id=<?= $from_conv ?>" class="btn-cancel-lnk">Annuler</a>
                    <?php else: ?>
                        <a href="messages.php" class="btn-cancel-lnk">Annuler</a>
                    <?php endif; ?>
                    <button type="button" class="btn-save" onclick="validateForm()">💾 Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Validation JS (sans HTML5) ───────────────────────────────────────────────────
function validateContenu() {
    const ta  = document.getElementById('contenu');
    const err = document.getElementById('err-contenu');
    const val = ta.value.trim();
    if (!val || val.length > 2000) {
        ta.classList.add('error-field');
        err.classList.add('visible');
        return false;
    }
    ta.classList.remove('error-field');
    err.classList.remove('visible');
    return true;
}

function updateCharInfo() {
    const n  = document.getElementById('contenu').value.length;
    const el = document.getElementById('charInfo');
    el.textContent = n + ' / 2000';
    el.className   = 'char-info' + (n > 1800 ? ' warn' : '') + (n >= 2000 ? ' over' : '');
}

function validateForm() {
    if (validateContenu()) document.getElementById('editForm').submit();
}
</script>
</body>
</html>