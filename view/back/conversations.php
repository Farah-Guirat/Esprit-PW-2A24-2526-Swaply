<?php
// Point d'entrée back – Conversations
// Appelle le controller qui charge les données puis require cette vue
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../model/Conversation.php';
require_once __DIR__ . '/../../model/Message.php';
require_once __DIR__ . '/../../controller/ConversationController.php';

if (!isset($conversations)) {
    $ctrl = new ConversationController();
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
    <title>Conversations – Swaply Admin</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* ── Main ── */
        .main { flex:1; display:flex; flex-direction:column; overflow-y:auto; background:#f8fafc; }
        .topbar { height:70px; background:#fff; border-bottom:1px solid #e2e8f0; display:flex; align-items:center; padding:0 30px; gap:16px; position:sticky; top:0; z-index:40; justify-content:space-between; }
        .topbar-title { font-size:22px; font-weight:600; color:#0f766e; flex:1; }
        .topbar-search { display:flex; align-items:center; gap:8px; background:#f1f5f9; border-radius:40px; padding:8px 16px; width:240px; }
        .topbar-search input { border:none; background:none; font-size:14px; outline:none; width:100%; }
        .topbar-user { display:flex; align-items:center; gap:12px; }
        .topbar-avatar { width:38px; height:38px; border-radius:50%; background:#d1d5db; display:flex; align-items:center; justify-content:center; font-size:14px; color:#6b7280; border:2px solid #e2e8f0; }
        .topbar-name strong { display:block; font-size:14px; color:#0f172a; }
        .topbar-name span { font-size:12px; color:#64748b; }
        .content { padding:28px; }

        /* ── Alertes ── */
        .alert { padding:10px 16px; border-radius:8px; font-size:13px; margin-bottom:16px; }
        .alert-success { background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; }
        .alert-info    { background:#eff6ff; border:1px solid #bfdbfe; color:#1e40af; }
        .alert-danger  { background:#fef2f2; border:1px solid #fecaca; color:#991b1b; }

        /* ── Table card ── */
        .table-card { background:#fff; border-radius:12px; border:1px solid #e5e7eb; overflow:hidden; }
        .table-card-header { padding:16px 20px; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; justify-content:space-between; }
        .table-card-header h2 { font-size:15px; font-weight:700; color:#111827; }
        .table-card-header span { font-size:12px; color:#9ca3af; }
        table { width:100%; border-collapse:collapse; }
        thead { background:#f9fafb; }
        th { padding:11px 16px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #e5e7eb; }
        td { padding:13px 16px; font-size:13px; color:#374151; border-bottom:1px solid #f3f4f6; vertical-align:middle; }
        tr:last-child td { border-bottom:none; }
        tr:hover td { background:#f9fafb; }

        /* ── Badges ── */
        .badge { display:inline-block; padding:2px 10px; border-radius:20px; font-size:11px; font-weight:600; }
        .badge-green { background:#dcfce7; color:#166534; }
        .badge-red   { background:#fef2f2; color:#991b1b; }

        /* ── Avatars participants ── */
        .user-cell { display:flex; align-items:center; gap:8px; }
        .user-av { width:30px; height:30px; border-radius:50%; background:#E1F5EE; color:#0F6E56; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; flex-shrink:0; }

        /* ── Boutons actions ── */
        .btn-icon { display:inline-flex; align-items:center; gap:4px; padding:5px 11px; border-radius:6px; font-size:12px; text-decoration:none; border:1px solid; cursor:pointer; font-family:inherit; }
        .btn-view   { color:#0369a1; border-color:#bae6fd; background:#f0f9ff; }
        .btn-view:hover { background:#e0f2fe; }
        .btn-close  { color:#d97706; border-color:#fde68a; background:#fffbeb; }
        .btn-close:hover { background:#fef3c7; }
        .btn-delete { color:#dc2626; border-color:#fecaca; background:#fef2f2; }
        .btn-delete:hover { background:#fee2e2; }

        .empty-state { padding:48px; text-align:center; color:#9ca3af; font-size:14px; }

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

<?php $currentPage = 'conversations'; require __DIR__ . '/sidebar.php'; ?>

<!-- ── MAIN ── -->
<div class="main">
    <div class="topbar">
        <div class="topbar-title">Conversations</div>
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

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-danger">🗑 Conversation supprimée avec tous ses messages.</div>
        <?php endif; ?>
        <?php if (isset($_GET['closed'])): ?>
            <div class="alert alert-info">🔒 Conversation fermée.</div>
        <?php endif; ?>

        <!-- Statistiques conversations -->
        <?php if (!empty($stats)): ?>
        <div class="kpi-grid">
            <div class="kpi-card">
                <p class="kpi-label">💬 Total</p>
                <p class="kpi-number"><?= $stats['total'] ?></p>
                <p class="kpi-subtitle">Conversations</p>
            </div>
            <div class="kpi-card">
                <p class="kpi-label">🟢 Actives</p>
                <p class="kpi-number"><?= $stats['actives'] ?></p>
                <p class="kpi-subtitle"><?= round(($stats['actives'] / max($stats['total'], 1)) * 100, 0) ?>% du total</p>
            </div>
            <div class="kpi-card">
                <p class="kpi-label">🔴 Fermées</p>
                <p class="kpi-number"><?= $stats['fermees'] ?></p>
                <p class="kpi-subtitle"><?= round(($stats['fermees'] / max($stats['total'], 1)) * 100, 0) ?>% du total</p>
            </div>
            <div class="kpi-card">
                <p class="kpi-label">📅 Ce mois</p>
                <p class="kpi-number"><?= $stats['ce_mois'] ?></p>
                <p class="kpi-subtitle">Nouvelles conv</p>
            </div>
            <div class="kpi-card">
                <p class="kpi-label">✉️ Messages</p>
                <p class="kpi-number"><?= $stats['total_messages'] ?></p>
                <p class="kpi-subtitle">Au total</p>
            </div>
            <div class="kpi-card">
                <p class="kpi-label">✓ Lus</p>
                <p class="kpi-number"><?= $stats['taux_lus'] ?>%</p>
                <p class="kpi-subtitle"><?= $stats['lus'] ?>/<?= $stats['total_messages'] ?> messages</p>
            </div>
        </div>
        <?php endif; ?>

        <div class="table-card">
            <div class="table-card-header">
                <h2>💬 Toutes les conversations</h2>
                <span><?= count($conversations) ?> conversation<?= count($conversations) > 1 ? 's' : '' ?></span>
            </div>

            <?php if (!empty($conversations)): ?>
            <table id="convsTable">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Participant 1</th>
                        <th>Participant 2</th>
                        <th>Statut</th>
                        <th>Messages</th>
                        <th>Date création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($conversations as $conv): ?>
                    <tr>
                        <td><strong>#<?= $conv['id_conversation'] ?></strong></td>
                        <td>
                            <div class="user-cell">
                                <div class="user-av">
                                    <?= strtoupper(substr($conv['prenom_user1'],0,1).substr($conv['nom_user1'],0,1)) ?>
                                </div>
                                <?= htmlspecialchars($conv['prenom_user1'].' '.$conv['nom_user1']) ?>
                            </div>
                        </td>
                        <td>
                            <div class="user-cell">
                                <div class="user-av" style="background:#E6F1FB;color:#185FA5;">
                                    <?= strtoupper(substr($conv['prenom_user2'],0,1).substr($conv['nom_user2'],0,1)) ?>
                                </div>
                                <?= htmlspecialchars($conv['prenom_user2'].' '.$conv['nom_user2']) ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge <?= $conv['statut']==='active' ? 'badge-green' : 'badge-red' ?>">
                                <?= $conv['statut']==='active' ? '🟢 Active' : '🔴 Fermée' ?>
                            </span>
                        </td>
                        <td><?= $conv['nb_messages'] ?> msg<?= $conv['nb_messages']>1 ? 's' : '' ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($conv['date_creation'])) ?></td>
                        <td>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                <!-- Voir les messages de la conv -->
                                <a href="../../controller/ConversationController.php?action=viewBack&id=<?= $conv['id_conversation'] ?>"
                                   class="btn-icon btn-view">👁 Voir</a>
                                <!-- Fermer (si active) -->
                                <?php if ($conv['statut']==='active'): ?>
                                    <a href="../../controller/ConversationController.php?action=closeBack&id=<?= $conv['id_conversation'] ?>"
                                       class="btn-icon btn-close"
                                       onclick="return confirm('Fermer cette conversation ?')">🔒 Fermer</a>
                                <?php endif; ?>
                                <!-- Supprimer -->
                                <button class="btn-icon btn-delete"
                                    onclick="confirmDelete(<?= $conv['id_conversation'] ?>, '<?= htmlspecialchars($conv['prenom_user1'].' '.$conv['nom_user1'], ENT_QUOTES) ?>', '<?= htmlspecialchars($conv['prenom_user2'].' '.$conv['nom_user2'], ENT_QUOTES) ?>')">
                                    🗑 Supprimer
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="empty-state">Aucune conversation enregistrée.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── MODAL suppression ── -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <div class="modal-icon">💬</div>
        <h3>Supprimer cette conversation ?</h3>
        <p id="modalDesc">Tous les messages associés seront supprimés. Cette action est irréversible.</p>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal()">Annuler</button>
            <a id="confirmDeleteBtn" href="#" class="btn-confirm-del">Supprimer</a>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, u1, u2) {
    document.getElementById('modalDesc').textContent =
        'La conversation entre ' + u1 + ' et ' + u2 + ' et tous ses messages seront supprimés définitivement.';
    document.getElementById('confirmDeleteBtn').href =
        '../../controller/ConversationController.php?action=deleteBack&id=' + id;
    document.getElementById('deleteModal').classList.add('open');
}
function closeModal() { document.getElementById('deleteModal').classList.remove('open'); }
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
function filterTable(q) {
    const rows = document.querySelectorAll('#convsTable tbody tr');
    q = q.toLowerCase();
    rows.forEach(row => { row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none'; });
}
</script>
</div><!-- /flex wrapper -->
</body>
</html>