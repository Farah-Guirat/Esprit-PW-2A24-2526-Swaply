<?php
// ── PROBLÈME 1 CORRIGÉ ────────────────────────────────────────────────────────
// Cette vue reçoit TOUJOURS $conversations (toutes les convs de l'utilisateur)
// ET $messages (messages de la conv active) depuis le controller.
// La sidebar reste donc toujours affichée avec toutes les conversations.
// ─────────────────────────────────────────────────────────────────────────────

if (session_status() === PHP_SESSION_NONE) session_start();

// ✅ CRUCIAL : Forcer l'utilisation de l'utilisateur RÉELLEMENT connecté au login
if (!isset($_SESSION['user']) || empty($_SESSION['user']['id_u'])) {
    header('Location: login.php');
    exit;
}

$id_user = (int)$_SESSION['user']['id_u'];
$_SESSION['id_user'] = $id_user;  // Assurer que $_SESSION['id_user'] = l'ID réel de l'utilisateur connecté

$photo = $_SESSION['user']['photo'] ?? null;

// Helper pour formater la taille des fichiers
function formatFileSize($bytes) {
    if ($bytes == 0) return '0 B';
    $k = 1024;
    $sizes = ['B', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes, $k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

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
    <link rel="stylesheet" href="../../assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Segoe UI',sans-serif; background:#f5f7fa; }

        /* Layout */
        .msg-layout { display:flex; height:calc(100vh - 64px); }

        /* ── SIDEBAR ── */
        .conv-sidebar { width:300px; background:#fff; border-right:1px solid #e5e7eb; display:flex; flex-direction:column; flex-shrink:0; }
        .conv-sidebar-header { padding:16px; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; justify-content:space-between; }
        .conv-sidebar-header h2 { font-size:15px; font-weight:600; color:#111827; }
        .btn-new-conv { background:#1D9E75; color:#fff; border:none; border-radius:8px; padding:6px 12px; font-size:12px; cursor:pointer; text-decoration:none; display:inline-block; }
        .btn-new-conv:hover { background:#178a64; }
        .conv-search { padding:10px 14px; border-bottom:1px solid #e5e7eb; display:flex; gap:8px; align-items:center; }
        .conv-search input { flex:1; padding:8px 12px; border-radius:20px; border:1px solid #e5e7eb; background:#f9fafb; font-size:12px; outline:none; }
        .conv-sort-select { padding:6px 10px; border-radius:8px; border:1px solid #e5e7eb; background:#f9fafb; font-size:12px; outline:none; cursor:pointer; }
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
        .online-dot.offline { background:#9ca3af; }
        .typing-indicator { font-size:12px; color:#6b7280; font-style:italic; margin-top:2px; }
        .typing-dot { display:inline-block; width:4px; height:4px; border-radius:50%; background:#6b7280; margin:0 2px; animation:bounce 1.4s infinite; }
        .typing-dot:nth-child(1) { animation-delay:0s; }
        .typing-dot:nth-child(2) { animation-delay:0.2s; }
        .typing-dot:nth-child(3) { animation-delay:0.4s; }
        @keyframes bounce { 0%, 60%, 100% { transform:translateY(0); opacity:0.6; } 30% { transform:translateY(-8px); opacity:1; } }
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
        .msg-time { font-size:10px; color:#9ca3af; margin-top:3px; display:flex; align-items:center; gap:4px; }
        .msg-row.mine .msg-time { text-align:right; justify-content:flex-end; }
        .msg-status { display:inline-block; font-weight:bold; }
        .msg-status.unread { color:#999; }
        .msg-status.read { color:#1D9E75; }

        /* Bouton ⋮ sur mes messages */
        .msg-options-btn { 
            display: none; 
            background: none; 
            border: none; 
            cursor: pointer; 
            color: #9ca3af; 
            font-size: 18px; 
            padding: 2px 6px; 
            line-height: 1; 
            align-self: flex-end; 
            margin-bottom: 4px;
            transition: all 0.2s;
        }

        .msg-row.reactions-active .msg-options-btn {
            display: block;
        }

        .msg-options-btn:hover {
            color: #6b7280;
        }

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
        .file-input-wrapper { position:relative; display:inline-block; }
        .btn-file-attach { width:38px; height:38px; border-radius:50%; background:#f0f0f0; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; color:#666; font-size:18px; transition:all 0.2s; }
        .btn-file-attach:hover { background:#e0e0e0; }
        .btn-file-attach.has-file { background:#1D9E75; color:white; }
        #fileInput { display:none; }
        .file-attachment-preview { padding:8px 12px; background:#f0f7ff; border-radius:8px; margin-top:8px; display:flex; align-items:center; justify-content:space-between; font-size:12px; }
        .file-attachment-preview .file-name { flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:#0066cc; }
        .file-attachment-preview .btn-remove-file { background:none; border:none; color:#dc2626; cursor:pointer; font-size:14px; padding:0 4px; }
        .msg-file-attachment { background:#f0f7ff; border-left:3px solid #0066cc; padding:10px 12px; border-radius:6px; margin-top:6px; }
        .msg-file-attachment a { color:#0066cc; text-decoration:none; font-weight:500; display:flex; align-items:center; gap:6px; }
        .msg-file-attachment a:hover { text-decoration:underline; }
        .msg-file-icon { font-size:16px; }
        .msg-file-size { font-size:10px; color:#999; margin-top:3px; }
        .char-count.warn { color:#f59e0b; }
        .char-count.over { color:#dc2626; }

        /* Bouton d'enregistrement vocal */
        .btn-voice-record { width:38px; height:38px; border-radius:50%; background:#f0f0f0; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; color:#666; font-size:18px; transition:all 0.2s; position:relative; }
        .btn-voice-record:hover { background:#e0e0e0; }
        .btn-voice-record.recording { background:#dc2626; color:white; animation:pulse 1.5s infinite; }
        @keyframes pulse { 0%, 100% { box-shadow:0 0 0 0 rgba(220, 38, 38, 0.7); } 50% { box-shadow:0 0 0 10px rgba(220, 38, 38, 0); } }
        
        /* Aperçu du message vocal */
        .voice-preview { padding:10px 12px; background:#f0f7ff; border-radius:8px; margin-top:8px; display:flex; align-items:center; gap:8px; }
        .voice-preview .voice-duration { font-size:12px; color:#0066cc; font-weight:500; }
        .voice-preview .btn-remove-voice { background:none; border:none; color:#dc2626; cursor:pointer; font-size:14px; padding:0 4px; }
        .voice-waveform { display:flex; align-items:flex-end; gap:2px; height:30px; }
        .voice-waveform .bar { width:2px; background:#0066cc; border-radius:1px; }
        
        /* Affichage des messages vocaux */
        .msg-voice-player { background:#f0f7ff; border-left:3px solid #0066cc; padding:10px 12px; border-radius:6px; margin-top:6px; }
        .msg-voice-controls { display:flex; align-items:center; gap:8px; }
        .voice-play-btn { width:32px; height:32px; border-radius:50%; background:#1D9E75; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; color:white; font-size:14px; transition:all 0.2s; }
        .voice-play-btn:hover { background:#178a64; transform:scale(1.05); }
        .voice-duration-display { font-size:12px; color:#666; min-width:40px; }
        .voice-timestamp { font-size:10px; color:#999; margin-top:3px; }
        .audio-player { flex:1; }

        /* Avatars colorés */
        .av-blue   { background:#E6F1FB; color:#185FA5; }
        .av-coral  { background:#FAECE7; color:#993C1D; }
        .av-purple { background:#EEEDFE; color:#534AB7; }
        .av-amber  { background:#FAEEDA; color:#854F0B; }
        .av-teal   { background:#E1F5EE; color:#0F6E56; }

        /* Réactions aux messages */
        .reaction-btn {
            background: none;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 4px 8px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
            color: #6b7280;
            margin-top: 6px;
            display: none;
        }

        .msg-row.reactions-active .reaction-btn {
            display: inline-block;
        }

        .reaction-btn:hover {
            background-color: #f3f4f6;
            border-color: #1D9E75;
            color: #1D9E75;
        }

        .reactions-container {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            margin-top: 6px;
        }

        .reaction-badge {
            display: inline-block;
            background-color: #f3f4f6;
            border-radius: 12px;
            padding: 4px 8px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.2s;
            border: 1px solid #e5e7eb;
        }

        .reaction-badge:hover {
            background-color: #e5e7eb;
            transform: scale(1.05);
        }

        .reaction-badge.user-reacted {
            background-color: #ecf5f1;
            color: #1D9E75;
            border-color: #1D9E75;
        }

        .reaction-badge.user-reacted:hover {
            background-color: #d1e8e0;
        }

        .emoji-picker {
            position: fixed;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
            max-width: 260px;
            justify-content: center;
        }

        .emoji-picker button {
            background: #f3f4f6;
            border: none;
            border-radius: 4px;
            padding: 6px 10px;
            cursor: pointer;
            font-size: 18px;
            transition: background-color 0.2s;
        }

        .emoji-picker button:hover {
            background: #e5e7eb;
        }
    </style>
</head>
<body>

<!-- Header -->
<header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-8 py-5 flex items-center justify-between">
      <div class="flex items-center gap-3">
          <span class="text-gray-700 font-medium">
              <?= htmlspecialchars($_SESSION['user']['nom'] ?? '') ?>
              <?= htmlspecialchars($_SESSION['user']['prenom'] ?? '') ?>
          </span>
        <div class="w-9 h-9 bg-teal-500 rounded-2xl flex items-center justify-center text-white font-bold text-2xl">S</div>
        <h1 class="text-2xl font-bold text-gray-800">Swaply</h1>
      </div>

      <nav class="flex items-center gap-8 text-sm font-medium">
        <a href="swaplyf.php" class="nav-link">Accueil</a>
        <a href="Profil.php" class="nav-link">Profils</a>
        <a href="projets.php" class="nav-link">Projets</a>
        <a href="/swaply/public/index.php?action=choice" class="nav-link">Demandes</a>
        <a href="/swaply/public/index.php?action=choicee" class="nav-link">Offres</a>
        <a href="listepublication.php" class="nav-link">Publications</a>
        <a href="Messages.php" class="nav-link">Messages</a>
        <a href="reclamations.php" class="nav-link">Réclamations</a>
      </nav>

      <div onclick="window.location.href='Profil.php'" class="w-10 h-10 bg-teal-100 rounded-2xl overflow-hidden border-2 border-white shadow cursor-pointer relative">
        <?php if ($photo ?? null): ?>
          <img src="/swaply/uploads/profiles/<?= htmlspecialchars($photo) ?>" alt="Profil" class="w-full h-full object-cover" style="width: 100%; height: 100%; object-fit: cover; display: block;">
        <?php else: ?>
          <div class="w-full h-full flex items-center justify-center text-teal-600 font-bold text-lg">
            <?= strtoupper(substr($_SESSION['user']['nom'] ?? '', 0, 1) . substr($_SESSION['user']['prenom'] ?? '', 0, 1)) ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </header>

<div class="msg-layout">

    <!-- ── SIDEBAR : toujours visible avec TOUTES les conversations ── -->
    <div class="conv-sidebar">
        <div class="conv-sidebar-header">
            <h2>Messages</h2>
            <a href="ajouter_message.php" class="btn-new-conv">+ Nouveau</a>
        </div>
        <div class="conv-search">
            <input type="text" id="searchConv" placeholder="Rechercher..." oninput="filterConvs(this.value)">
            <select id="sortConv" class="conv-sort-select" onchange="sortConvs(this.value)">
                <option value="newest">📅 Plus récent</option>
                <option value="oldest">📅 Plus ancien</option>
                <option value="az">🔤 A→Z</option>
                <option value="za">🔤 Z→A</option>
            </select>
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
                    <a href="/swaply/view/front/messagerie.php?id=<?= $conv['id_conversation'] ?>"
                       class="conv-item <?= $isActive ? 'active' : '' ?>"
                       data-name="<?= htmlspecialchars($conv['interlocuteur_prenom'].' '.$conv['interlocuteur_nom']) ?>"
                       data-time="<?= strtotime($conv['date_dernier_message'] ?? 'now') ?>">
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
            // ID de l'autre utilisateur (interlocuteur)
            $interlocuteur_id = ($conversation['id_user1'] == $id_user) ? $conversation['id_user2'] : $conversation['id_user1'];
            ?>

            <!-- Header de la conv active -->
            <div class="chat-header">
                <div class="chat-header-avatar <?= $av_hdr ?>"><?= $ii ?></div>
                <div class="chat-header-info">
                    <h3><?= htmlspecialchars($ip.' '.$in) ?></h3>
                    <p>
                        <span class="online-dot" id="onlineIndicator"></span>
                        <span id="onlineStatus">En ligne</span>
                    </p>
                    <div class="typing-indicator" id="typingIndicator" style="display:none;">
                        En train d'écrire
                        <span class="typing-dot"></span>
                        <span class="typing-dot"></span>
                        <span class="typing-dot"></span>
                    </div>
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
                        <div class="msg-row <?= $isMine ? 'mine' : 'other' ?>" data-message-id="<?= $msg['id_message']; ?>">>
                            <?php if (!$isMine): ?>
                                <div class="msg-avatar-sm <?= $av_other ?>">
                                    <?= strtoupper(substr($msg['prenom'], 0, 1) . substr($msg['nom'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>

                            <div class="msg-wrap">
                                <div class="msg-bubble"><?= nl2br(htmlspecialchars($msg['contenu'])) ?></div>
                                <?php if (!empty($msg['fichier_path']) && ($msg['type_message'] ?? 'texte') === 'fichier'): ?>
                                    <div class="msg-file-attachment">
                                        <a href="<?= htmlspecialchars('../..' . '/' . $msg['fichier_path']) ?>" download="<?= htmlspecialchars($msg['fichier_nom_original']) ?>">
                                            <span class="msg-file-icon">📄</span>
                                            <span><?= htmlspecialchars($msg['fichier_nom_original']) ?></span>
                                        </a>
                                        <div class="msg-file-size"><?= formatFileSize($msg['fichier_taille']) ?></div>
                                    </div>
                                <?php elseif (!empty($msg['fichier_path']) && ($msg['type_message'] ?? 'texte') === 'voix'): ?>
                                    <div class="msg-voice-player">
                                        <div class="msg-voice-controls">
                                            <button class="voice-play-btn" onclick="toggleAudioPlay(event)" title="Écouter">▶</button>
                                            <audio class="audio-player" controls style="flex:1;">
                                                <source src="<?= htmlspecialchars('../..' . '/' . $msg['fichier_path']) ?>" type="<?= htmlspecialchars($msg['fichier_type']) ?>">
                                                Votre navigateur ne supporte pas la lecture audio.
                                            </audio>
                                            <span class="voice-duration-display" id="duration-<?= $msg['id_message'] ?>">
                                                <?php
                                                $duree = isset($msg['voix_duree']) ? $msg['voix_duree'] : 0;
                                                $mins = floor($duree / 60);
                                                $secs = $duree % 60;
                                                echo sprintf('%d:%02d', $mins, $secs);
                                                ?>
                                            </span>
                                        </div>
                                        <div class="voice-timestamp"><?= formatFileSize($msg['fichier_taille']) ?></div>
                                    </div>
                                <?php elseif (!empty($msg['fichier_path'])): ?>
                                    <div class="msg-file-attachment">
                                        <a href="<?= htmlspecialchars('../..' . '/' . $msg['fichier_path']) ?>" download="<?= htmlspecialchars($msg['fichier_nom_original']) ?>">
                                            <span class="msg-file-icon">📄</span>
                                            <span><?= htmlspecialchars($msg['fichier_nom_original']) ?></span>
                                        </a>
                                        <div class="msg-file-size"><?= formatFileSize($msg['fichier_taille']) ?></div>
                                    </div>
                                <?php endif; ?>
                                <div class="msg-time">
                                    <?= date('H:i', strtotime($msg['date_envoi'])) ?>
                                    <?php if ($isMine): ?>
                                        <span class="msg-status <?= $msg['lu'] ? 'read' : 'unread' ?>" title="<?= $msg['lu'] ? 'Lu' : 'Non lu' ?>">
                                            <?= $msg['lu'] ? '✓✓' : '✓' ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <button class="reaction-btn" data-id-message="<?= $msg['id_message']; ?>" title="Ajouter une réaction">😊 Réagir</button>
                                <div class="reactions-container"></div>
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
                <form method="POST" action="../../controller/MessageController.php?action=send" id="msgForm" novalidate enctype="multipart/form-data">
                    <input type="hidden" name="id_conversation" value="<?= $id_active_conv ?>">
                    
                    <!-- Aperçu du fichier attaché -->
                    <div id="filePreview" class="file-attachment-preview" style="display:none;">
                        <span class="file-name" id="fileName"></span>
                        <button type="button" class="btn-remove-file" onclick="removeFileAttachment()">✕</button>
                    </div>
                    
                    <!-- Aperçu du message vocal -->
                    <div id="voicePreview" class="voice-preview" style="display:none;">
                        <div class="voice-waveform" id="voiceWaveform"></div>
                        <span class="voice-duration" id="voiceDuration">0:00</span>
                        <button type="button" class="btn-remove-voice" onclick="removeVoiceRecording()" title="Annuler l'enregistrement">✕</button>
                    </div>
                    
                    <div class="chat-form-row">
                        <div class="file-input-wrapper">
                            <button type="button" class="btn-file-attach" id="btnFileAttach" title="Joindre un fichier" onclick="document.getElementById('fileInput').click()">📎</button>
                            <input type="file" id="fileInput" name="fichier" onchange="handleFileSelect(this)">
                        </div>
                        <button type="button" class="btn-voice-record" id="btnVoiceRecord" title="Enregistrer un message vocal" onclick="toggleVoiceRecording(event)">🎤</button>
                        <textarea name="contenu" id="msgTextarea" rows="1"
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


document.getElementById('msgTextarea')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); validateAndSend(); }
});


function filterConvs(q) {
    document.querySelectorAll('.conv-item').forEach(item => {
        const name = (item.getAttribute('data-name') || '').toLowerCase();
        item.style.display = name.includes(q.toLowerCase()) ? '' : 'none';
    });
}

// ── Tri des conversations ──────────────────────────────────────────────────
function sortConvs(sortBy) {
    const convList = document.getElementById('convList');
    const items = Array.from(document.querySelectorAll('.conv-item'));
    
    items.sort((a, b) => {
        const nameA = (a.getAttribute('data-name') || '').toLowerCase();
        const nameB = (b.getAttribute('data-name') || '').toLowerCase();
        const timeA = a.getAttribute('data-time') || '9999999999';
        const timeB = b.getAttribute('data-time') || '9999999999';
        
        switch(sortBy) {
            case 'az':
                return nameA.localeCompare(nameB);
            case 'za':
                return nameB.localeCompare(nameA);
            case 'newest':
                return parseInt(timeB) - parseInt(timeA);
            case 'oldest':
                return parseInt(timeA) - parseInt(timeB);
            default:
                return 0;
        }
    });
    
    items.forEach(item => {
        if (item.style.display !== 'none') {
            convList.appendChild(item);
        }
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

// ── Real-time Status Updates ──────────────────────────────────────────────
<?php if ($id_active_conv > 0): ?>
const currentConvId = <?= $id_active_conv ?>;
const currentUserId = <?= $id_user ?>;
const interlocuteurId = <?= $interlocuteur_id ?? 0 ?>;

// Mettre à jour le statut "en train d'écrire" quand l'utilisateur tape
let typingTimeout;
const msgTextarea = document.getElementById('msgTextarea');
if (msgTextarea) {
    msgTextarea.addEventListener('input', () => {
        clearTimeout(typingTimeout);
        
        // Envoyer le statut "typing"
        fetch('../../controller/RealtimeController.php?action=updateTyping', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_conversation=' + currentConvId + '&is_typing=1'
        });
        
        // Arrêter le typing après 1 seconde d'inactivité
        typingTimeout = setTimeout(() => {
            fetch('../../controller/RealtimeController.php?action=updateTyping', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_conversation=' + currentConvId + '&is_typing=0'
            });
        }, 1000);
    });
}

// Mettre à jour le statut "en ligne" toutes les 10 secondes
setInterval(() => {
    fetch('../../controller/RealtimeController.php?action=updateOnline', {
        method: 'POST'
    });
}, 10000);

// Vérifier le statut en ligne et le typing toutes les 2 secondes
setInterval(() => {
    // Vérifier si l'autre utilisateur est en ligne
    fetch('../../controller/RealtimeController.php?action=getOnline&id_user=' + interlocuteurId)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const indicator = document.getElementById('onlineIndicator');
                const status = document.getElementById('onlineStatus');
                if (data.online) {
                    indicator.classList.remove('offline');
                    status.textContent = 'En ligne';
                } else {
                    indicator.classList.add('offline');
                    status.textContent = data.last_seen_ago ? 'Vu ' + data.last_seen_ago : 'Hors ligne';
                }
            }
        });
    
    // Vérifier qui est en train de taper
    fetch('../../controller/RealtimeController.php?action=getTyping&id_conversation=' + currentConvId)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const typingDiv = document.getElementById('typingIndicator');
                // Si au moins un utilisateur tape
                if (data.typing_users.length > 0) {
                    typingDiv.style.display = 'block';
                } else {
                    typingDiv.style.display = 'none';
                }
            }
        });
}, 2000);

// Mettre à jour le statut au démarrage
fetch('../../controller/RealtimeController.php?action=updateOnline', {
    method: 'POST'
});
<?php endif; ?>

// ── Gestion des fichiers attachés ────────────────────────────────────────────
function handleFileSelect(input) {
    if (!input.files || input.files.length === 0) return;
    
    const file = input.files[0];
    const maxSize = 10 * 1024 * 1024; // 10 MB
    
    if (file.size > maxSize) {
        alert('Le fichier est trop volumineux (max 10 MB).');
        input.value = '';
        return;
    }
    
    const preview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const btn = document.getElementById('btnFileAttach');
    
    fileName.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
    preview.style.display = 'flex';
    btn.classList.add('has-file');
}

function removeFileAttachment() {
    const input = document.getElementById('fileInput');
    const preview = document.getElementById('filePreview');
    const btn = document.getElementById('btnFileAttach');
    
    input.value = '';
    preview.style.display = 'none';
    btn.classList.remove('has-file');
}

function formatFileSize(bytes) {
    if (bytes == 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes, k));
    return (bytes / Math.pow(k, i)).toFixed(2) + ' ' + sizes[i];
}

// ── User Menu Dropdown ──────────────────────────────────────────────────────
const userMenuBtn = document.getElementById('userMenuBtn');
const userDropdown = document.getElementById('userDropdown');

if (userMenuBtn && userDropdown) {
    // Ouvrir/Fermer le menu au clic
    userMenuBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        userDropdown.classList.toggle('show');
    });

    // Fermer le menu au clic ailleurs
    document.addEventListener('click', () => {
        userDropdown.classList.remove('show');
    });

    // Ne pas fermer le menu au clic dedans
    userDropdown.addEventListener('click', (e) => {
        e.stopPropagation();
    });
}

</script>

<!-- ── Gestion des réactions aux messages ── -->
<script src="../../assets/js/reactions.js"></script>
<script>
    // ── Afficher le bouton de réaction au clic sur le message ──
    document.addEventListener('click', function(e) {
        // Ne pas réagir si c'est un clic sur un élément interactif
        if (e.target.closest('.reaction-btn') || 
            e.target.closest('.msg-options-btn') ||
            e.target.closest('.reactions-container') ||
            e.target.closest('.emoji-picker') ||
            e.target.closest('.ctx-menu')) {
            return;
        }

        const msgRow = e.target.closest('.msg-row');
        if (msgRow) {
            e.stopPropagation();
            // Retirer la classe active de tous les messages
            document.querySelectorAll('.msg-row.reactions-active').forEach(row => {
                if (row !== msgRow) {
                    row.classList.remove('reactions-active');
                }
            });
            // Ajouter la classe active au message cliqué
            msgRow.classList.toggle('reactions-active');
        } else {
            // Clic en dehors du message - masquer tous les boutons
            document.querySelectorAll('.msg-row.reactions-active').forEach(row => {
                row.classList.remove('reactions-active');
            });
        }
    });

    <?php if ($id_active_conv > 0): ?>
    // Attendre que le gestionnaire soit chargé et initialiser
    if (typeof currentUserId !== 'undefined') {
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initialisation du gestionnaire de réactions pour l\'utilisateur:', currentUserId);
            
            // Attendre que le ReactionManager soit chargé
            const checkReactionManager = setInterval(() => {
                if (typeof ReactionManager !== 'undefined') {
                    clearInterval(checkReactionManager);
                    window.reactionManager = new ReactionManager(currentUserId);
                    
                    // Charger les réactions de tous les messages
                    const messageElements = document.querySelectorAll('[data-id-message]');
                    console.log('Chargement des réactions pour ' + messageElements.length + ' messages');
                    
                    messageElements.forEach(msgElement => {
                        const msgId = msgElement.getAttribute('data-id-message');
                        if (msgId) {
                            window.reactionManager.loadReactions(msgId);
                        }
                    });
                }
            }, 100);
        });
    }
    <?php endif; ?>
</script>

<!-- ── Gestion des messages vocaux ── -->
<script>
// ── Variables globales pour l'enregistrement audio ────────────────────────────
let mediaRecorder = null;
let audioChunks = [];
let recordingStartTime = null;
let recordingInterval = null;

// ── Basculer l'enregistrement vocal ──────────────────────────────────────────
async function toggleVoiceRecording(event) {
    event.preventDefault();
    const btn = document.getElementById('btnVoiceRecord');
    
    if (mediaRecorder && mediaRecorder.state === 'recording') {
        // Arrêter l'enregistrement
        stopVoiceRecording();
    } else {
        // Démarrer l'enregistrement
        btn.textContent = '⏳';
        btn.disabled = true;
        startVoiceRecording();
    }
}

// ── Démarrer l'enregistrement ────────────────────────────────────────────────
async function startVoiceRecording() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const analyser = audioContext.createAnalyser();
        const microphone = audioContext.createMediaStreamSource(stream);
        
        microphone.connect(analyser);
        analyser.fftSize = 256;
        
        mediaRecorder = new MediaRecorder(stream);
        audioChunks = [];
        recordingStartTime = Date.now();
        
        const btn = document.getElementById('btnVoiceRecord');
        btn.classList.add('recording');
        btn.title = 'Cliquez pour arrêter l\'enregistrement';
        
        // Mettre à jour le waveform
        const waveformDiv = document.getElementById('voiceWaveform');
        waveformDiv.innerHTML = '';
        
        const bufferLength = analyser.frequencyBinCount;
        const dataArray = new Uint8Array(bufferLength);
        
        recordingInterval = setInterval(() => {
            analyser.getByteFrequencyData(dataArray);
            waveformDiv.innerHTML = '';
            
            for (let i = 0; i < Math.min(20, bufferLength); i += 3) {
                const bar = document.createElement('div');
                bar.className = 'bar';
                const height = (dataArray[i] / 255) * 100;
                bar.style.height = Math.max(5, height) + '%';
                waveformDiv.appendChild(bar);
            }
        }, 100);
        
        mediaRecorder.ondataavailable = (e) => {
            if (e.data.size > 0) {
                audioChunks.push(e.data);
            }
        };
        
        mediaRecorder.onstop = () => {
            stream.getTracks().forEach(track => track.stop());
            audioContext.close();
        };
        
        mediaRecorder.start();
        const btn2 = document.getElementById('btnVoiceRecord');
        btn2.textContent = '🎤';
        btn2.disabled = false;
        showVoicePreview();
        
    } catch (error) {
        console.error('Erreur accès microphone:', error);
        const btn3 = document.getElementById('btnVoiceRecord');
        btn3.textContent = '🎤';
        btn3.disabled = false;
        alert('Impossible d\'accéder au microphone. Vérifiez les permissions.');
    }
}

// ── Arrêter l'enregistrement ─────────────────────────────────────────────────
function stopVoiceRecording() {
    if (mediaRecorder && mediaRecorder.state === 'recording') {
        mediaRecorder.stop();
        
        const btn = document.getElementById('btnVoiceRecord');
        btn.classList.remove('recording');
        btn.title = 'Enregistrer un message vocal';
        
        if (recordingInterval) {
            clearInterval(recordingInterval);
        }
        
        mediaRecorder.onstop = () => {
            const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
            window.recordedAudioBlob = audioBlob;
            window.recordedAudioDuration = Math.floor((Date.now() - recordingStartTime) / 1000);
            updateVoicePreview();
        };
    }
}

// ── Afficher l'aperçu du message vocal ─────────────────────────────────────
function showVoicePreview() {
    const preview = document.getElementById('voicePreview');
    const textarea = document.getElementById('msgTextarea');
    const filePreview = document.getElementById('filePreview');
    
    // Masquer le fichier si présent
    if (filePreview.style.display !== 'none') {
        removeFileAttachment();
    }
    
    // Masquer la textarea pendant l'enregistrement
    textarea.style.display = 'none';
    preview.style.display = 'flex';
}

// ── Mettre à jour l'aperçu du message vocal ────────────────────────────────
function updateVoicePreview() {
    const duration = window.recordedAudioDuration || 0;
    const mins = Math.floor(duration / 60);
    const secs = duration % 60;
    const durationText = `${mins}:${secs.toString().padStart(2, '0')}`;
    
    const durationEl = document.getElementById('voiceDuration');
    durationEl.textContent = durationText;
}

// ── Supprimer l'enregistrement vocal ───────────────────────────────────────
function removeVoiceRecording() {
    window.recordedAudioBlob = null;
    window.recordedAudioDuration = 0;
    audioChunks = [];
    recordingStartTime = null;
    
    const preview = document.getElementById('voicePreview');
    const textarea = document.getElementById('msgTextarea');
    const waveform = document.getElementById('voiceWaveform');
    
    preview.style.display = 'none';
    textarea.style.display = 'block';
    waveform.innerHTML = '';
    
    const btn = document.getElementById('btnVoiceRecord');
    btn.classList.remove('recording');
}

// ── Valider et envoyer le message ───────────────────────────────────────────
function validateAndSend() {
    const textarea = document.getElementById('msgTextarea');
    const textarea_val = textarea.value.trim();
    const hasFile = document.getElementById('fileInput').files.length > 0;
    const hasVoice = window.recordedAudioBlob !== null && window.recordedAudioBlob !== undefined;
    const errors = [];
    
    // Validation
    if (!textarea_val && !hasFile && !hasVoice) {
        errors.push('Le message ne peut pas être vide.');
    }
    
    if (textarea_val && textarea_val.length > 2000) {
        errors.push('Le message ne peut pas dépasser 2000 caractères.');
    }
    
    if (errors.length > 0) {
        const errorDiv = document.getElementById('frontError');
        errorDiv.innerHTML = errors.map(e => '<span>⚠ ' + e + '</span>').join('<br>');
        return;
    }
    
    // Envoyer le message
    if (hasVoice) {
        sendVoiceMessage();
    } else {
        // Envoi classique (texte + fichier optionnel)
        document.getElementById('msgForm').submit();
    }
}

// ── Envoyer le message vocal via AJAX ──────────────────────────────────────
function sendVoiceMessage() {
    if (!window.recordedAudioBlob) {
        alert('Aucun enregistrement vocal disponible');
        return;
    }
    
    const formData = new FormData();
    const idConv = document.querySelector('input[name="id_conversation"]').value;
    
    formData.append('id_conversation', idConv);
    formData.append('voix', window.recordedAudioBlob, 'voice_message.webm');
    formData.append('duree', window.recordedAudioDuration || 0);
    
    // Afficher un indicateur de chargement
    const btn = document.querySelector('.btn-send');
    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '⏳ Envoi...';
    
    // Déterminer le chemin correct vers le contrôleur
    const basePath = window.location.pathname.split('/view/')[0];
    const controllerUrl = basePath + '/controller/MessageController.php?action=sendVoice';
    
    fetch(controllerUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Réponse du serveur:', data);
        btn.disabled = false;
        btn.innerHTML = originalContent;
        
        if (data.success) {
            console.log('Message vocal envoyé avec succès:', data.voix_path);
            removeVoiceRecording();
            // Actualiser la page après un court délai
            setTimeout(() => {
                location.reload();
            }, 500);
        } else {
            console.error('Erreur serveur:', data.message);
            alert('❌ Erreur: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Erreur AJAX:', err);
        btn.disabled = false;
        btn.innerHTML = originalContent;
        alert('❌ Erreur lors de l\'envoi du message vocal: ' + err.message);
    });
}

// ── Basculer la lecture audio ──────────────────────────────────────────────
function toggleAudioPlay(event) {
    event.preventDefault();
    const btn = event.target.closest('.voice-play-btn');
    const audio = btn.nextElementSibling;
    
    if (audio && audio.classList.contains('audio-player')) {
        if (audio.paused) {
            audio.play();
            btn.textContent = '⏸';
        } else {
            audio.pause();
            btn.textContent = '▶';
        }
    }
}

// ── Event listeners pour les contrôles audio ───────────────────────────────
document.addEventListener('play', (e) => {
    if (e.target.classList.contains('audio-player')) {
        const btn = e.target.previousElementSibling;
        if (btn && btn.classList.contains('voice-play-btn')) {
            btn.textContent = '⏸';
        }
    }
}, true);

document.addEventListener('pause', (e) => {
    if (e.target.classList.contains('audio-player')) {
        const btn = e.target.previousElementSibling;
        if (btn && btn.classList.contains('voice-play-btn')) {
            btn.textContent = '▶';
        }
    }
}, true);

// ── Intercepter Entrée pour envoyer ────────────────────────────────────────
const textarea = document.getElementById('msgTextarea');
if (textarea) {
    textarea.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            validateAndSend();
        }
    });
}
</script>