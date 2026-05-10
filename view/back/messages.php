<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../model/Message.php';
require_once __DIR__ . '/../../model/Conversation.php';
require_once __DIR__ . '/../../controller/MessageController.php';

if (!isset($messages)) {
    $ctrl = new MessageController();
    $ctrl->indexBack();
    exit;
}
$stats = $stats ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages – Swaply Admin</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background:#f8fafc; }

        /* ── Main ── */
        .main { flex:1; display:flex; flex-direction:column; overflow-y:auto; }
        .topbar { height:70px; background:#fff; border-bottom:1px solid #e2e8f0; display:flex; align-items:center; padding:0 30px; gap:16px; position:sticky; top:0; z-index:50; justify-content:space-between; }
        .topbar-title { font-size:22px; font-weight:600; color:#0f766e; flex:1; }
        .topbar-search { display:flex; align-items:center; gap:8px; background:#f1f5f9; border-radius:40px; padding:8px 16px; width:240px; }
        .topbar-search input { border:none; background:none; font-size:14px; outline:none; width:100%; }
        .topbar-user { display:flex; align-items:center; gap:12px; }
        .topbar-avatar { width:38px; height:38px; border-radius:50%; background:#d1d5db; display:flex; align-items:center; justify-content:center; font-size:14px; color:#6b7280; border:2px solid #e2e8f0; }
        .topbar-name strong { display:block; font-size:14px; color:#0f172a; }
        .topbar-name span { font-size:12px; color:#64748b; }
        .content { padding:28px; }
        .alert { padding:10px 16px; border-radius:8px; font-size:13px; margin-bottom:16px; }
        .alert-success { background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; }
        .alert-danger  { background:#fef2f2; border:1px solid #fecaca; color:#991b1b; }
        .table-card { background:#fff; border-radius:12px; border:1px solid #e5e7eb; overflow:hidden; }
        .table-card-header { padding:16px 20px; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; justify-content:space-between; }
        .table-card-header h2 { font-size:15px; font-weight:700; color:#111827; }
        .table-card-header span { font-size:12px; color:#9ca3af; }
        table { width:100%; border-collapse:collapse; }
        thead { background:#f9fafb; }
        th { padding:11px 16px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #e5e7eb; }
        td { padding:12px 16px; font-size:13px; color:#374151; border-bottom:1px solid #f3f4f6; vertical-align:middle; }
        tr:last-child td { border-bottom:none; }
        tr:hover td { background:#f9fafb; }
        .badge { display:inline-block; padding:2px 10px; border-radius:20px; font-size:11px; font-weight:600; }
        .badge-green { background:#dcfce7; color:#166534; }
        .badge-gray  { background:#f3f4f6; color:#6b7280; }
        .user-cell { display:flex; align-items:center; gap:8px; }
        .user-av { width:28px; height:28px; border-radius:50%; background:#E1F5EE; color:#0F6E56; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:700; flex-shrink:0; }
        .msg-preview { max-width:260px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; color:#4b5563; }
        .conv-link { color:#0369a1; text-decoration:none; font-weight:600; }
        .conv-link:hover { text-decoration:underline; }
        .btn-icon { display:inline-flex; align-items:center; gap:4px; padding:5px 11px; border-radius:6px; font-size:12px; text-decoration:none; border:1px solid; cursor:pointer; font-family:inherit; }
        .btn-edit   { color:#2563eb; border-color:#bfdbfe; background:#eff6ff; }
        .btn-edit:hover { background:#dbeafe; }
        .btn-delete { color:#dc2626; border-color:#fecaca; background:#fef2f2; }
        .btn-delete:hover { background:#fee2e2; }
        .empty-state { padding:48px; text-align:center; color:#9ca3af; font-size:14px; }
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
        
        /* ── KPI Cards ── */
        .kpi-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:14px; margin-bottom:20px; }
        .kpi-card { background:#fff; border-radius:12px; border:1px solid #e5e7eb; padding:16px; }
        .kpi-label { font-size:11px; color:#9ca3af; text-transform:uppercase; letter-spacing:.5px; margin-bottom:8px; }
        .kpi-number { font-size:28px; font-weight:700; color:#111827; margin-bottom:6px; }
        .kpi-subtitle { font-size:12px; color:#6b7280; }
    </style>
</head>
<body>
<div class="flex h-screen overflow-hidden">

<div class="sidebar">
    <div class="logo">
        <span class="icon">📋</span>
        <h1>JobBoard Admin</h1>
    </div>
    <div class="menu">
        <a href="/swaply/view/back/swaplyB.php"                   class="menu-item"><i class="fa-solid fa-house"></i> Dashboard</a>
        <a href="/swaply/view/back/ProfilsB.php"                  class="menu-item"><i class="fa-solid fa-user"></i> Profils</a>
        <a href="/swaply/view/back/projets.php"                   class="menu-item"><i class="fa-solid fa-file"></i> Projets</a>
        <a href="/swaply/public/index.php?action=dashboard"       class="menu-item"><i class="fa-solid fa-briefcase"></i> Offres &amp; Demandes</a>
        <a href="#"                                               class="menu-item"><i class="fa-solid fa-newspaper"></i> Publications</a>
        <a href="/swaply/view/back/conversations.php"             class="menu-item"><i class="fa-solid fa-comment-dots"></i> Conversations</a>
        <a href="/swaply/view/back/messages.php"                  class="menu-item active"><i class="fa-solid fa-envelope"></i> Messages</a>
        <a href="/swaply/view/back/reclamations_admin.php"        class="menu-item"><i class="fa-solid fa-exclamation-triangle"></i> Réclamations</a>
        <a href="#"                                               class="menu-item"><i class="fa-solid fa-chart-bar"></i> Statistiques</a>
        <a href="#"                                               class="menu-item"><i class="fa-solid fa-gear"></i> Paramètres</a>
    </div>
</div>

<div class="main">
    <div class="topbar">
        <div class="topbar-title">Messages</div>
        <div class="topbar-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Rechercher..." oninput="filterTable(this.value)">
        </div>
        <div class="topbar-user">
            <i class="fa-solid fa-bell fa-lg" style="color:#64748b;cursor:pointer;"></i>
            <div class="topbar-avatar">A</div>
            <div class="topbar-name"><strong>Admin</strong><span>Super Admin</span></div>
            <a href="/swaply/controller/logout.php" style="display:flex;align-items:center;gap:8px;background:#fee2e2;color:#dc2626;padding:8px 16px;border-radius:30px;font-size:13px;font-weight:500;text-decoration:none;" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?');">
                <i class="fa-solid fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </div>

    <div class="content">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">✅ Message modifié avec succès.</div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-danger">🗑 Message supprimé.</div>
        <?php endif; ?>

        <!-- Statistiques messages -->
        <?php if (!empty($stats)): ?>
        <div class="kpi-grid">
            <div class="kpi-card">
                <p class="kpi-label">✉️ Total</p>
                <p class="kpi-number"><?= $stats['total'] ?></p>
                <p class="kpi-subtitle">Messages</p>
            </div>
            <div class="kpi-card">
                <p class="kpi-label">📅 Ce mois</p>
                <p class="kpi-number"><?= $stats['ce_mois'] ?></p>
                <p class="kpi-subtitle">Messages</p>
            </div>
            <div class="kpi-card">
                <p class="kpi-label">📆 Aujourd'hui</p>
                <p class="kpi-number"><?= $stats['aujourd_hui'] ?></p>
                <p class="kpi-subtitle">Messages</p>
            </div>
            <div class="kpi-card">
                <p class="kpi-label">✓ Lus</p>
                <p class="kpi-number"><?= $stats['taux_lus'] ?>%</p>
                <p class="kpi-subtitle"><?= $stats['lus'] ?>/<?= $stats['total'] ?> messages</p>
            </div>
            <div class="kpi-card">
                <p class="kpi-label">👤 Non lus</p>
                <p class="kpi-number"><?= $stats['non_lus'] ?></p>
                <p class="kpi-subtitle"><?= round(($stats['non_lus'] / max($stats['total'], 1)) * 100, 0) ?>% du total</p>
            </div>
            <div class="kpi-card">
                <p class="kpi-label">📎 Fichiers</p>
                <p class="kpi-number"><?= $stats['avec_fichiers'] ?></p>
                <p class="kpi-subtitle"><?= round(($stats['avec_fichiers'] / max($stats['total'], 1)) * 100, 1) ?>% avec pièce</p>
            </div>
        </div>
        <?php endif; ?>

        <div class="table-card">
            <div class="table-card-header">
                <h2>✉️ Tous les messages</h2>
                <span><?= count($messages) ?> message<?= count($messages)>1?'s':'' ?></span>
            </div>

            <?php if (!empty($messages)): ?>
            <table id="messagesTable">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Expéditeur</th>
                        <th>Conversation</th>
                        <th>Contenu</th>
                        <th>Date envoi</th>
                        <th>Lu</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $msg): ?>
                    <tr>
                        <td><strong>#<?= $msg['id_message'] ?></strong></td>
                        <td>
                            <div class="user-cell">
                                <div class="user-av"><?= strtoupper(substr($msg['prenom'],0,1).substr($msg['nom'],0,1)) ?></div>
                                <?= htmlspecialchars($msg['prenom'].' '.$msg['nom']) ?>
                            </div>
                        </td>
                        <td>
                            <a href="../../controller/ConversationController.php?action=viewBack&id=<?= $msg['id_conversation'] ?>"
                               class="conv-link">Conv. #<?= $msg['id_conversation'] ?></a>
                        </td>
                        <td><div class="msg-preview" title="<?= htmlspecialchars($msg['contenu']) ?>"><?= htmlspecialchars($msg['contenu']) ?></div></td>
                        <td><?= date('d/m/Y H:i', strtotime($msg['date_envoi'])) ?></td>
                        <td>
                            <span class="badge <?= $msg['lu'] ? 'badge-green' : 'badge-gray' ?>">
                                <?= $msg['lu'] ? '✓ Lu' : 'Non lu' ?>
                            </span>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <a href="edit_message.php?id=<?= $msg['id_message'] ?>"
                                   class="btn-icon btn-edit">✏️ Modifier</a>
                                <button class="btn-icon btn-delete"
                                    onclick="confirmDelete(<?= $msg['id_message'] ?>)">🗑 Supprimer</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="empty-state">Aucun message trouvé.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <div class="modal-icon">🗑️</div>
        <h3>Supprimer ce message ?</h3>
        <p>Cette action est irréversible.</p>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal()">Annuler</button>
            <a id="confirmDeleteBtn" href="#" class="btn-confirm-del">Supprimer</a>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    document.getElementById('confirmDeleteBtn').href =
        '../../controller/MessageController.php?action=deleteBack&id=' + id;
    document.getElementById('deleteModal').classList.add('open');
}
function closeModal() { document.getElementById('deleteModal').classList.remove('open'); }
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
function filterTable(q) {
    const rows = document.querySelectorAll('#messagesTable tbody tr');
    q = q.toLowerCase();
    rows.forEach(row => { row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none'; });
}
</script>
</div><!-- /flex wrapper -->
</body>
</html>