<?php
// ── PROBLÈME 1 CORRIGÉ ────────────────────────────────────────────────────────
// Cette vue reçoit TOUJOURS $conversations (toutes les convs de l'utilisateur)
// ET $messages (messages de la conv active) depuis le controller.
// La sidebar reste donc toujours affichée avec toutes les conversations.
// ─────────────────────────────────────────────────────────────────────────────

if (session_status() === PHP_SESSION_NONE) session_start();

$id_user        = (int)($_SESSION['id_user'] ?? 0);
$id_active_conv = $id_active_conv ?? (isset($_GET['id']) ? (int)$_GET['id'] : 0);

// Fallback si appelée directement sans passer par le controller
if (!isset($conversations)) {
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../model/Message.php';
    require_once __DIR__ . '/../../model/Conversation.php';
    $conversationModel = new Conversation();
    $messageModel      = new Message();
    // Toutes les conversations pour la sidebar
    $conversations = $conversationModel->getByUser($id_user);
    // Messages de la conv active
    if ($id_active_conv > 0) {
        $conversation = $conversationModel->getById($id_active_conv);
        if ($conversation &&
            ($conversation['id_user1'] == $id_user || $conversation['id_user2'] == $id_user)) {
            $messageModel->markAsRead($id_active_conv, $id_user);
            $messages = $messageModel->getByConversation($id_active_conv);
        } else {
            $id_active_conv = 0;
        }
    }
}

$errors       = $errors ?? [];
$conversation = $conversation ?? null;
$messages     = $messages ?? [];
$av_classes   = ['av-blue', 'av-coral', 'av-purple', 'av-amber', 'av-teal'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages – Swaply</title>
    <link rel="stylesheet" href="../../asset/css/style.css">
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Segoe UI',sans-serif; background:#f5f7fa; }

        /* Navbar */
        .navbar { display:flex; align-items:center; padding:0 32px; height:64px; background:#fff; border-bottom:1px solid #e5e7eb; position:sticky; top:0; z-index:100; }
        .navbar-brand { display:flex; align-items:center; gap:8px; font-size:18px; font-weight:700; color:#1a1a1a; text-decoration:none; margin-right:auto; }
        .navbar-logo { width:34px; height:34px; border-radius:50%; background:#1D9E75; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; font-size:15px; }
        .navbar-links { display:flex; gap:28px; }
        .navbar-links a { font-size:14px; color:#4b5563; text-decoration:none; }
        .navbar-links a:hover { color:#1D9E75; }
        .navbar-links a.active { color:#1D9E75; font-weight:600; border-bottom:2px solid #1D9E75; padding-bottom:2px; }
        .navbar-avatar { width:34px; height:34px; border-radius:50%; background:#d1d5db; display:flex; align-items:center; justify-content:center; font-size:13px; color:#6b7280; margin-left:24px; }

        /* Layout */
        .msg-layout { display:flex; height:calc(100vh - 64px); }

        /* ── SIDEBAR ── */
        .conv-sidebar { width:300px; background:#fff; border-right:1px solid #e5e7eb; display:flex; flex-direction:column; flex-shrink:0; }
        .conv-sidebar-header { padding:16px; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; justify-content:space-between; }
        .conv-sidebar-header h2 { font-size:15px; font-weight:600; color:#111827; }
        .btn-new-conv { background:#1D9E75; color:#fff; border:none; border-radius:8px; padding:6px 12px; font-size:12px; cursor:pointer; text-decoration:none; display:inline-block; }
        .btn-new-conv:hover { background:#178a64; }
        .conv-search { padding:10px 14px; border-bottom:1px solid #e5e7eb; }
        .conv-search input { width:100%; padding:8px 12px; border-radius:20px; border:1px solid #e5e7eb; background:#f9fafb; font-size:12px; outline:none; }
        .conv-list { flex:1; overflow-y:auto; }

        /* Item conversation dans la sidebar */
        .conv-item { display:flex; align-items:center; gap:10px; padding:12px 14px; border-bottom:1px solid #f3f4f6; text-decoration:none; color:inherit; position:relative; }
        .conv-item:hover { background:#f9fafb; }
        /* PROBLÈME 1 : la classe active met en évidence la conv ouverte sans masquer les autres */
        .conv-item.active { background:#f0fdf8; border-left:3px solid #1D9E75; }
        .conv-avatar { width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:600; flex-shrink:0; }
        .conv-info { flex:1; min-width:0; padding-right:28px; }
        .conv-name { font-size:13px; font-weight:600; color:#111827; }
        .conv-preview { font-size:11px; color:#6b7280; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-top:2px; }
        .conv-meta { display:flex; flex-direction:column; align-items:flex-end; gap:3px; flex-shrink:0; }
        .conv-time { font-size:10px; color:#9ca3af; }
        .conv-badge { background:#1D9E75; color:#fff; font-size:10px; border-radius:10px; padding:1px 6px; font-weight:600; }
        /* Bouton supprimer conversation dans la sidebar */
        .conv-del-btn { position:absolute; right:8px; top:50%; transform:translateY(-50%); display:none; background:#fef2f2; border:none; border-radius:6px; width:26px; height:26px; cursor:pointer; color:#dc2626; font-size:12px; align-items:center; justify-content:center; z-index:2; }
        .conv-item:hover .conv-del-btn { display:flex; }

        /* ── CHAT ZONE ── */
        .chat-zone { flex:1; display:flex; flex-direction:column; background:#f9fafb; overflow:hidden; }
        .chat-empty { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; color:#9ca3af; }
        .chat-empty-icon { font-size:48px; margin-bottom:12px; }

        .chat-header { padding:14px 20px; background:#fff; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; gap:12px; flex-shrink:0; }
        .chat-header-avatar { width:38px; height:38px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:600; }
        .chat-header-info h3 { font-size:14px; font-weight:600; color:#111827; }
        .chat-header-info p { font-size:12px; color:#6b7280; }
        .online-dot { display:inline-block; width:7px; height:7px; border-radius:50%; background:#1D9E75; margin-right:4px; }
        .btn-del-conv-header { margin-left:auto; padding:6px 12px; background:#fef2f2; border:1px solid #fecaca; border-radius:8px; color:#dc2626; font-size:12px; cursor:pointer; text-decoration:none; white-space:nowrap; }
        .btn-del-conv-header:hover { background:#fee2e2; }

        /* Messages */
        .chat-messages { flex:1; overflow-y:auto; padding:20px; display:flex; flex-direction:column; gap:10px; }
        .msg-row { display:flex; align-items:flex-end; gap:8px; position:relative; }
        .msg-row.mine { flex-direction:row-reverse; }
        .msg-wrap { display:flex; flex-direction:column; max-width:65%; }
        .msg-row.mine .msg-wrap { align-items:flex-end; }
        .msg-bubble { padding:10px 14px; border-radius:14px; font-size:13px; line-height:1.5; word-break:break-word; }
        .msg-row.other .msg-bubble { background:#fff; color:#111827; border-bottom-left-radius:4px; box-shadow:0 1px 2px rgba(0,0,0,.06); }
        .msg-row.mine  .msg-bubble { background:#1D9E75; color:#fff; border-bottom-right-radius:4px; }
        .msg-avatar-sm { width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:600; flex-shrink:0; }
        .msg-time { font-size:10px; color:#9ca3af; margin-top:3px; }
        .msg-row.mine .msg-time { text-align:right; }

        /* Bouton ⋮ sur mes messages */
        .msg-options-btn { display:none; background:none; border:none; cursor:pointer; color:#9ca3af; font-size:18px; padding:2px 6px; line-height:1; align-self:flex-end; margin-bottom:4px; }
        .msg-row:hover .msg-options-btn { display:block; }

        /* Menu contextuel */
        .ctx-menu { display:none; position:fixed; background:#fff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 4px 20px rgba(0,0,0,.12); z-index:1000; min-width:155px; overflow:hidden; }
        .ctx-menu.show { display:block; }
        .ctx-menu a { display:flex; align-items:center; gap:9px; padding:10px 16px; font-size:13px; text-decoration:none; color:#374151; border-bottom:1px solid #f3f4f6; }
        .ctx-menu a:last-child { border-bottom:none; }
        .ctx-menu a:hover { background:#f9fafb; }
        .ctx-menu a.ctx-del { color:#dc2626; }
        .ctx-menu a.ctx-del:hover { background:#fef2f2; }

        /* Modals */
        .modal-bg { display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:1500; align-items:center; justify-content:center; }
        .modal-bg.open { display:flex; }
        .modal-box { background:#fff; border-radius:16px; padding:28px; max-width:360px; width:90%; text-align:center; box-shadow:0 8px 32px rgba(0,0,0,.15); }
        .modal-box .modal-icon { font-size:2.5rem; margin-bottom:12px; }
        .modal-box h3 { font-size:16px; font-weight:700; color:#111827; margin-bottom:8px; }
        .modal-box p { font-size:13px; color:#6b7280; margin-bottom:20px; }
        .modal-actions { display:flex; gap:10px; justify-content:center; }
        .btn-cancel { padding:9px 22px; border:1px solid #e5e7eb; border-radius:8px; background:none; cursor:pointer; font-size:13px; color:#6b7280; }
        .btn-cancel:hover { background:#f9fafb; }
        .btn-confirm-del { padding:9px 22px; background:#dc2626; color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:13px; font-weight:600; }
        .btn-confirm-del:hover { background:#b91c1c; }

        /* Formulaire envoi */
        .chat-form { padding:14px 16px; background:#fff; border-top:1px solid #e5e7eb; flex-shrink:0; }
        .chat-form .error-msg { color:#dc2626; font-size:12px; margin-bottom:8px; padding:6px 10px; background:#fef2f2; border-radius:6px; }
        .chat-form-row { display:flex; align-items:center; gap:10px; }
        .chat-form-row textarea { flex:1; padding:10px 14px; border-radius:20px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px; outline:none; resize:none; font-family:inherit; line-height:1.4; }
        .chat-form-row textarea:focus { border-color:#1D9E75; }
        .btn-send { width:38px; height:38px; border-radius:50%; background:#1D9E75; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .btn-send:hover { background:#178a64; }
        .btn-send svg { width:16px; height:16px; fill:white; }
        .char-count { font-size:11px; color:#9ca3af; text-align:right; margin-top:4px; }
        .char-count.warn { color:#f59e0b; }
        .char-count.over { color:#dc2626; }

        /* Avatars colorés */
        .av-blue   { background:#E6F1FB; color:#185FA5; }
        .av-coral  { background:#FAECE7; color:#993C1D; }
        .av-purple { background:#EEEDFE; color:#534AB7; }
        .av-amber  { background:#FAEEDA; color:#854F0B; }
        .av-teal   { background:#E1F5EE; color:#0F6E56; }
    </style>
</head>
<body>

<!-- NAVBAR -->
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

<div class="msg-layout">

    <!-- ── SIDEBAR : toujours visible avec TOUTES les conversations ── -->
    <div class="conv-sidebar">
        <div class="conv-sidebar-header">
            <h2>Messages</h2>
            <a href="ajouter_message.php" class="btn-new-conv">+ Nouveau</a>
        </div>
        <div class="conv-search">
            <input type="text" id="searchConv" placeholder="Rechercher..." oninput="filterConvs(this.value)">
        </div>
        <div class="conv-list" id="convList">
            <?php if (!empty($conversations)): ?>
                <?php foreach ($conversations as $conv):
                    $initials = strtoupper(
                        substr($conv['interlocuteur_prenom'], 0, 1) .
                        substr($conv['interlocuteur_nom'], 0, 1)
                    );
                    // PROBLÈME 1 : isActive surligne la conv ouverte, mais toutes sont listées
                    $isActive = ($conv['id_conversation'] == $id_active_conv);
                    $preview  = $conv['dernier_message'] ?? 'Démarrer la conversation';
                    $date     = $conv['date_dernier_message']
                                ? date('H:i', strtotime($conv['date_dernier_message']))
                                : '';
                    $av       = $av_classes[$conv['id_conversation'] % 5];
                ?>
                    <a href="messagerie.php?id=<?= $conv['id_conversation'] ?>"
                       class="conv-item <?= $isActive ? 'active' : '' ?>"
                       data-name="<?= htmlspecialchars($conv['interlocuteur_prenom'].' '.$conv['interlocuteur_nom']) ?>">
                        <div class="conv-avatar <?= $av ?>"><?= htmlspecialchars($initials) ?></div>
                        <div class="conv-info">
                            <div class="conv-name"><?= htmlspecialchars($conv['interlocuteur_prenom'].' '.$conv['interlocuteur_nom']) ?></div>
                            <div class="conv-preview"><?= htmlspecialchars(substr($preview, 0, 38)) ?>...</div>
                        </div>
                        <div class="conv-meta">
                            <span class="conv-time"><?= $date ?></span>
                            <?php if ($conv['non_lus'] > 0): ?>
                                <span class="conv-badge"><?= (int)$conv['non_lus'] ?></span>
                            <?php endif; ?>
                        </div>
                        <!-- Bouton supprimer dans la sidebar -->
                        <button class="conv-del-btn" title="Supprimer cette conversation"
                            onclick="openModalConv(event, <?= $conv['id_conversation'] ?>, '<?= htmlspecialchars($conv['interlocuteur_prenom'].' '.$conv['interlocuteur_nom'], ENT_QUOTES) ?>')">
                            🗑
                        </button>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="padding:20px;text-align:center;color:#9ca3af;font-size:13px;">
                    Aucune conversation.<br><br>
                    <a href="ajouter_message.php" style="color:#1D9E75;">Démarrer une conversation</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── ZONE CHAT ── -->
    <div class="chat-zone">
        <?php if ($id_active_conv > 0 && $conversation): ?>
            <?php
            $ip     = ($conversation['id_user1'] == $id_user) ? $conversation['prenom_user2'] : $conversation['prenom_user1'];
            $in     = ($conversation['id_user1'] == $id_user) ? $conversation['nom_user2']    : $conversation['nom_user1'];
            $ii     = strtoupper(substr($ip, 0, 1) . substr($in, 0, 1));
            $av_hdr = $av_classes[$conversation['id_conversation'] % 5];
            ?>

            <!-- Header de la conv active -->
            <div class="chat-header">
                <div class="chat-header-avatar <?= $av_hdr ?>"><?= $ii ?></div>
                <div class="chat-header-info">
                    <h3><?= htmlspecialchars($ip.' '.$in) ?></h3>
                    <p><span class="online-dot"></span>En ligne</p>
                </div>
                <a href="#" class="btn-del-conv-header"
                   onclick="openModalConv(event, <?= $id_active_conv ?>, '<?= htmlspecialchars($ip.' '.$in, ENT_QUOTES) ?>')">
                   🗑 Supprimer la conversation
                </a>
            </div>

            <!-- Messages de la conversation -->
            <div class="chat-messages" id="chatMessages">
                <?php if (!empty($messages)): ?>
                    <?php
                    $av_other = $av_classes[$conversation['id_conversation'] % 5];
                    foreach ($messages as $msg):
                        $isMine = ($msg['id_expediteur'] == $id_user);
                    ?>
                        <div class="msg-row <?= $isMine ? 'mine' : 'other' ?>">
                            <?php if (!$isMine): ?>
                                <div class="msg-avatar-sm <?= $av_other ?>">
                                    <?= strtoupper(substr($msg['prenom'], 0, 1) . substr($msg['nom'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>

                            <div class="msg-wrap">
                                <div class="msg-bubble"><?= nl2br(htmlspecialchars($msg['contenu'])) ?></div>
                                <div class="msg-time">
                                    <?= date('H:i', strtotime($msg['date_envoi'])) ?>
                                    <?= ($isMine && $msg['lu']) ? ' <span style="color:#1D9E75;">✓✓</span>' : '' ?>
                                </div>
                            </div>

                            <!-- Bouton ⋮ uniquement sur MES messages -->
                            <?php if ($isMine): ?>
                                <button class="msg-options-btn"
                                    onclick="openCtxMenu(event, <?= $msg['id_message'] ?>, <?= $id_active_conv ?>)">⋮</button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align:center;color:#9ca3af;font-size:13px;margin-top:40px;">
                        Envoyez le premier message !
                    </div>
                <?php endif; ?>
            </div>

            <!-- Formulaire envoi (POST → controller → INSERT en BDD) -->
            <div class="chat-form">
                <?php if (!empty($errors)): ?>
                    <div class="error-msg">
                        <?php foreach ($errors as $e): ?>
                            <div>⚠ <?= htmlspecialchars($e) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="../../controller/MessageController.php?action=send" id="msgForm" novalidate>
                    <input type="hidden" name="id_conversation" value="<?= $id_active_conv ?>">
                    <div class="chat-form-row">
                        <textarea name="contenu" id="msgTextarea" rows="1" maxlength="2000"
                            placeholder="Écrire un message... (Entrée pour envoyer)"
                            oninput="autoResize(this); updateCharCount(this)"></textarea>
                        <button type="button" class="btn-send" onclick="validateAndSend()">
                            <svg viewBox="0 0 24 24"><path d="M2 21l21-9L2 3v7l15 2-15 2z"/></svg>
                        </button>
                    </div>
                    <div class="char-count" id="charCount">0 / 2000</div>
                    <div id="frontError" style="color:#dc2626;font-size:12px;margin-top:4px;"></div>
                </form>
            </div>

        <?php else: ?>
            <!-- Aucune conv sélectionnée — sidebar visible, zone centrale vide -->
            <div class="chat-empty">
                <div class="chat-empty-icon">💬</div>
                <p>Sélectionnez une conversation ou</p>
                <a href="ajouter_message.php" style="color:#1D9E75;margin-top:8px;font-size:14px;">
                    démarrez-en une nouvelle
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── MENU CONTEXTUEL (⋮ sur mes messages) ── -->
<div id="ctxMenu" class="ctx-menu">
    <a href="#" id="ctxEdit">✏️ Modifier</a>
    <a href="#" class="ctx-del" id="ctxDelete">🗑️ Supprimer</a>
</div>

<!-- ── MODAL suppression message ── -->
<div class="modal-bg" id="modalMsg">
    <div class="modal-box">
        <div class="modal-icon">🗑️</div>
        <h3>Supprimer ce message ?</h3>
        <p>Cette action est irréversible.</p>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModalMsg()">Annuler</button>
            <a id="confirmMsgBtn" href="#" class="btn-confirm-del">Supprimer</a>
        </div>
    </div>
</div>

<!-- ── MODAL suppression conversation ── -->
<div class="modal-bg" id="modalConv">
    <div class="modal-box">
        <div class="modal-icon">💬</div>
        <h3>Supprimer la conversation ?</h3>
        <p id="modalConvDesc">Tous les messages seront supprimés définitivement.</p>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModalConv()">Annuler</button>
            <a id="confirmConvBtn" href="#" class="btn-confirm-del">Supprimer</a>
        </div>
    </div>
</div>

<script src="../../asset/js/validation.js"></script>
<script>
// ── Scroll bas automatique ────────────────────────────────────────────────────
const cm = document.getElementById('chatMessages');
if (cm) cm.scrollTop = cm.scrollHeight;

// ── Auto-resize textarea ──────────────────────────────────────────────────────
function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

// ── Compteur de caractères ────────────────────────────────────────────────────
function updateCharCount(el) {
    const n   = el.value.length;
    const el2 = document.getElementById('charCount');
    el2.textContent = n + ' / 2000';
    el2.className   = 'char-count' + (n > 1800 ? ' warn' : '') + (n >= 2000 ? ' over' : '');
}

// ── Validation & envoi (sans HTML5) ──────────────────────────────────────────
function validateAndSend() {
    const ta  = document.getElementById('msgTextarea');
    const err = document.getElementById('frontError');
    const val = ta ? ta.value.trim() : '';
    if (err) err.textContent = '';
    if (!val) {
        if (err) err.textContent = '⚠ Le message ne peut pas être vide.';
        if (ta)  ta.focus();
        return;
    }
    if (val.length > 2000) {
        if (err) err.textContent = '⚠ Le message ne peut pas dépasser 2000 caractères.';
        return;
    }
    document.getElementById('msgForm').submit();
}

// Entrée = envoyer (Shift+Entrée = saut de ligne)
document.getElementById('msgTextarea')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); validateAndSend(); }
});

// ── Filtre conversations ──────────────────────────────────────────────────────
function filterConvs(q) {
    document.querySelectorAll('.conv-item').forEach(item => {
        const name = (item.getAttribute('data-name') || '').toLowerCase();
        item.style.display = name.includes(q.toLowerCase()) ? '' : 'none';
    });
}

// ── Menu contextuel ⋮ (modifier / supprimer mes messages) ────────────────────
const ctxMenu = document.getElementById('ctxMenu');

function openCtxMenu(e, msgId, convId) {
    e.preventDefault();
    e.stopPropagation();
    // Positionner le menu près du bouton
    let x = e.clientX, y = e.clientY;
    if (x + 160 > window.innerWidth)  x = window.innerWidth  - 170;
    if (y + 100 > window.innerHeight) y = y - 100;
    ctxMenu.style.left = x + 'px';
    ctxMenu.style.top  = y + 'px';
    ctxMenu.classList.add('show');
    // Lien modifier
    document.getElementById('ctxEdit').href = '../../controller/MessageController.php?action=editMessage&id=' + msgId;
    // Supprimer → modal de confirmation
    document.getElementById('ctxDelete').onclick = function(ev) {
        ev.preventDefault();
        ctxMenu.classList.remove('show');
        openModalMsg(msgId);
    };
}

document.addEventListener('click', () => ctxMenu.classList.remove('show'));

// ── Modal suppression MESSAGE ─────────────────────────────────────────────────
function openModalMsg(msgId) {
    document.getElementById('confirmMsgBtn').href =
        '../../controller/MessageController.php?action=deleteMessage&id=' + msgId;
    document.getElementById('modalMsg').classList.add('open');
}
function closeModalMsg() { document.getElementById('modalMsg').classList.remove('open'); }
document.getElementById('modalMsg').addEventListener('click', e => {
    if (e.target === document.getElementById('modalMsg')) closeModalMsg();
});

// ── Modal suppression CONVERSATION ───────────────────────────────────────────
function openModalConv(e, convId, name) {
    e.preventDefault();
    e.stopPropagation();
    document.getElementById('confirmConvBtn').href =
        '../../controller/MessageController.php?action=deleteConversation&id=' + convId;
    document.getElementById('modalConvDesc').textContent =
        'La conversation avec ' + name + ' et tous ses messages seront supprimés.';
    document.getElementById('modalConv').classList.add('open');
}
function closeModalConv() { document.getElementById('modalConv').classList.remove('open'); }
document.getElementById('modalConv').addEventListener('click', e => {
    if (e.target === document.getElementById('modalConv')) closeModalConv();
});
</script>
</body>
</html>