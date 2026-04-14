<?php
// Point d'entrée back – Voir une conversation
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../model/Conversation.php';
require_once __DIR__ . '/../../model/Message.php';
require_once __DIR__ . '/../../controller/ConversationController.php';

if (!isset($conversation)) {
    $ctrl = new ConversationController();
    $ctrl->viewBack();
    exit;
}
$messages = $messages ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversation #<?= $conversation['id_conversation'] ?> – Swaply Admin</title>
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
        .content { padding:28px; }

        /* ── Carte infos conversation ── */
        .info-card { background:#fff; border-radius:12px; border:1px solid #e5e7eb; padding:20px 24px; margin-bottom:20px; display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
        .info-item label { font-size:11px; color:#9ca3af; text-transform:uppercase; letter-spacing:.5px; display:block; margin-bottom:4px; }
        .info-item strong { font-size:14px; color:#111827; display:flex; align-items:center; gap:8px; }
        .user-av { width:28px; height:28px; border-radius:50%; background:#E1F5EE; color:#0F6E56; display:inline-flex; align-items:center; justify-content:center; font-size:10px; font-weight:700; flex-shrink:0; }
        .badge { display:inline-block; padding:2px 10px; border-radius:20px; font-size:11px; font-weight:600; }
        .badge-green { background:#dcfce7; color:#166534; }
        .badge-red   { background:#fef2f2; color:#991b1b; }

        /* ── Zone messages ── */
        .msgs-card { background:#fff; border-radius:12px; border:1px solid #e5e7eb; overflow:hidden; }
        .msgs-header { padding:14px 20px; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; justify-content:space-between; }
        .msgs-header h2 { font-size:15px; font-weight:700; color:#111827; }
        .msgs-header span { font-size:12px; color:#9ca3af; }
        .msgs-list { padding:16px 20px; display:flex; flex-direction:column; gap:12px; max-height:520px; overflow-y:auto; }

        /* ── Bulles messages (style conversation) ── */
        .msg-item { display:flex; flex-direction:column; gap:3px; }
        .msg-item.user1 { align-items:flex-start; }
        .msg-item.user2 { align-items:flex-end; }
        .msg-meta { font-size:11px; color:#9ca3af; display:flex; align-items:center; gap:6px; }
        .msg-av { width:22px; height:22px; border-radius:50%; background:#E1F5EE; color:#0F6E56; display:inline-flex; align-items:center; justify-content:center; font-size:9px; font-weight:700; }
        .msg-bubble-wrap { display:flex; align-items:flex-end; gap:8px; max-width:70%; }
        .msg-item.user2 .msg-bubble-wrap { flex-direction:row-reverse; }
        .msg-bubble { padding:10px 14px; border-radius:14px; font-size:13px; line-height:1.5; word-break:break-word; }
        .msg-item.user1 .msg-bubble { background:#f3f4f6; color:#111827; border-bottom-left-radius:4px; }
        .msg-item.user2 .msg-bubble { background:#1D9E75; color:#fff; border-bottom-right-radius:4px; }
        .msg-time { font-size:10px; color:#9ca3af; }
        .msg-lu { font-size:10px; color:#1D9E75; }

        /* ── Actions sur chaque message ── */
        .msg-actions { display:flex; gap:4px; align-self:center; }
        .btn-sm { display:inline-flex; align-items:center; gap:3px; padding:4px 9px; border-radius:6px; font-size:11px; border:1px solid; cursor:pointer; text-decoration:none; font-family:inherit; }
        .btn-sm-edit   { color:#2563eb; border-color:#bfdbfe; background:#eff6ff; }
        .btn-sm-edit:hover { background:#dbeafe; }
        .btn-sm-del    { color:#dc2626; border-color:#fecaca; background:#fef2f2; }
        .btn-sm-del:hover  { background:#fee2e2; }

        .empty-state { padding:48px; text-align:center; color:#9ca3af; font-size:14px; }

        /* ── Bouton supprimer la conv ── */
        .btn-danger { display:inline-flex; align-items:center; gap:6px; padding:8px 16px; background:#fef2f2; border:1px solid #fecaca; border-radius:8px; color:#dc2626; font-size:13px; cursor:pointer; text-decoration:none; font-weight:600; }
        .btn-danger:hover { background:#fee2e2; }

        /* ── Modal ── */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:200; align-items:center; justify-content:center; }
        .modal-overlay.open { display:flex; }
        .modal-box { background:#fff; border-radius:12px; padding:28px; max-width:380px; width:90%; text-align:center; }
        .modal-icon { font-size:2.2rem; margin-bottom:12px; }
        .modal-box h3 { font-size:16px; font-weight:700; color:#111827; margin-bottom:8px; }
        .modal-box p  { font-size:13px; color:#6b7280; margin-bottom:20px; }
        .modal-actions { display:flex; gap:10px; justify-content:center; }
        .btn-cancel { padding:8px 20px; border:1px solid #e5e7eb; border-radius:8px; background:none; cursor:pointer; font-size:13px; }
        .btn-confirm-del { padding:8px 20px; background:#dc2626; color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:13px; font-weight:600; }
        .btn-confirm-del:hover { background:#b91c1c; }
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
        <a href="conversations.php" class="nav-item active"><span class="nav-icon">💬</span> Conversations</a>
        <a href="messages.php"      class="nav-item"><span class="nav-icon">✉️</span> Messages</a>
        <a href="#"                 class="nav-item"><span class="nav-icon">⚠️</span> Réclamations</a>
        <a href="#"                 class="nav-item"><span class="nav-icon">📊</span> Statistiques</a>
        <a href="#"                 class="nav-item"><span class="nav-icon">⚙️</span> Paramètres</a>
    </nav>
</div>

<div class="main">
    <div class="topbar">
        <a href="conversations.php" class="btn-back">← Retour</a>
        <div class="topbar-title">
            Conversation #<?= $conversation['id_conversation'] ?> —
            <?= htmlspecialchars($conversation['prenom_user1'].' '.$conversation['nom_user1']) ?>
            &harr;
            <?= htmlspecialchars($conversation['prenom_user2'].' '.$conversation['nom_user2']) ?>
        </div>
        <div class="topbar-user">
            <span>🔔</span>
            <div class="topbar-avatar">A</div>
            <div class="topbar-name"><strong>Admin</strong><span>Super Admin</span></div>
        </div>
    </div>

    <div class="content">

        <?php if (isset($_GET['success'])): ?>
            <div style="padding:10px 16px;border-radius:8px;font-size:13px;margin-bottom:16px;background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;">✅ Message modifié avec succès.</div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
            <div style="padding:10px 16px;border-radius:8px;font-size:13px;margin-bottom:16px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;">🗑 Message supprimé.</div>
        <?php endif; ?>

        <!-- Infos conversation -->
        <div class="info-card">
            <div class="info-item">
                <label>Participant 1</label>
                <strong>
                    <div class="user-av"><?= strtoupper(substr($conversation['prenom_user1'],0,1).substr($conversation['nom_user1'],0,1)) ?></div>
                    <?= htmlspecialchars($conversation['prenom_user1'].' '.$conversation['nom_user1']) ?>
                </strong>
            </div>
            <div class="info-item">
                <label>Participant 2</label>
                <strong>
                    <div class="user-av" style="background:#E6F1FB;color:#185FA5;"><?= strtoupper(substr($conversation['prenom_user2'],0,1).substr($conversation['nom_user2'],0,1)) ?></div>
                    <?= htmlspecialchars($conversation['prenom_user2'].' '.$conversation['nom_user2']) ?>
                </strong>
            </div>
            <div class="info-item">
                <label>Statut</label>
                <strong>
                    <span class="badge <?= $conversation['statut']==='active' ? 'badge-green' : 'badge-red' ?>">
                        <?= $conversation['statut']==='active' ? '🟢 Active' : '🔴 Fermée' ?>
                    </span>
                </strong>
            </div>
            <div class="info-item">
                <label>Date création</label>
                <strong><?= date('d/m/Y H:i', strtotime($conversation['date_creation'])) ?></strong>
            </div>
        </div>

        <!-- Messages de la conversation -->
        <div class="msgs-card">
            <div class="msgs-header">
                <h2>💬 Messages (<?= count($messages) ?>)</h2>
                <!-- Admin peut supprimer toute la conversation depuis ici -->
                <button class="btn-danger"
                    onclick="document.getElementById('modalConv').classList.add('open')">
                    🗑 Supprimer la conversation
                </button>
            </div>

            <?php if (!empty($messages)): ?>
            <div class="msgs-list" id="msgsList">
                <?php foreach ($messages as $msg):
                    $isUser1 = ($msg['id_expediteur'] == $conversation['id_user1']);
                    $side    = $isUser1 ? 'user1' : 'user2';
                    $avBg    = $isUser1 ? 'background:#E1F5EE;color:#0F6E56;' : 'background:#E6F1FB;color:#185FA5;';
                    $initials = strtoupper(substr($msg['prenom'],0,1).substr($msg['nom'],0,1));
                ?>
                    <div class="msg-item <?= $side ?>">
                        <div class="msg-meta">
                            <div class="msg-av" style="<?= $avBg ?>"><?= $initials ?></div>
                            <span><?= htmlspecialchars($msg['prenom'].' '.$msg['nom']) ?></span>
                            <span>·</span>
                            <span><?= date('d/m/Y H:i', strtotime($msg['date_envoi'])) ?></span>
                            <?php if ($msg['lu']): ?>
                                <span class="msg-lu">✓✓ Lu</span>
                            <?php endif; ?>
                        </div>
                        <div class="msg-bubble-wrap">
                            <div class="msg-bubble"><?= nl2br(htmlspecialchars($msg['contenu'])) ?></div>
                            <!-- Admin peut modifier ou supprimer chaque message -->
                            <div class="msg-actions">
                                <a href="edit_message.php?id=<?= $msg['id_message'] ?>&from_conv=<?= $conversation['id_conversation'] ?>"
                                   class="btn-sm btn-sm-edit">✏️</a>
                                <button class="btn-sm btn-sm-del"
                                    onclick="confirmDelMsg(<?= $msg['id_message'] ?>)">🗑</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
                <div class="empty-state">Cette conversation ne contient aucun message.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL supprimer la conversation -->
<div class="modal-overlay" id="modalConv">
    <div class="modal-box">
        <div class="modal-icon">💬</div>
        <h3>Supprimer cette conversation ?</h3>
        <p>Tous les messages seront définitivement supprimés. Action irréversible.</p>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="document.getElementById('modalConv').classList.remove('open')">Annuler</button>
            <a href="../../controller/ConversationController.php?action=deleteBack&id=<?= $conversation['id_conversation'] ?>"
               class="btn-confirm-del">Supprimer</a>
        </div>
    </div>
</div>

<!-- MODAL supprimer un message -->
<div class="modal-overlay" id="modalMsg">
    <div class="modal-box">
        <div class="modal-icon">🗑️</div>
        <h3>Supprimer ce message ?</h3>
        <p>Cette action est irréversible.</p>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="document.getElementById('modalMsg').classList.remove('open')">Annuler</button>
            <a id="confirmMsgBtn" href="#" class="btn-confirm-del">Supprimer</a>
        </div>
    </div>
</div>

<script>
// Scroll bas automatique
const ml = document.getElementById('msgsList');
if (ml) ml.scrollTop = ml.scrollHeight;

// Confirmation suppression message
function confirmDelMsg(id) {
    document.getElementById('confirmMsgBtn').href =
        '../../controller/MessageController.php?action=deleteBack&id=' + id +
        '&from_conv=<?= $conversation['id_conversation'] ?>';
    document.getElementById('modalMsg').classList.add('open');
}

// Fermer modals en cliquant outside
['modalConv','modalMsg'].forEach(function(id) {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('open');
    });
});
</script>
</body>
</html>