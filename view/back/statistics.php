<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controller/StatisticsController.php';

$ctrl = new StatisticsController();
$stats = $ctrl->getStats();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques – Swaply Admin</title>
    <link rel="stylesheet" href="../../asset/css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Segoe UI',sans-serif; background:#f3f4f6; display:flex; min-height:100vh; }
        .sidebar { width:240px; background:#f8f9fa; min-height:100vh; display:flex; flex-direction:column; flex-shrink:0; position:fixed; top:0; left:0; height:100%; border-right:1px solid #e5e7eb; }
        .sidebar-brand { padding:20px 16px; display:flex; align-items:center; gap:10px; border-bottom:1px solid #e5e7eb; }
        .sidebar-brand-icon { width:36px; height:36px; background:#2c3e50; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:18px; color:#fff; }
        .sidebar-brand-text { font-size:15px; font-weight:700; color:#2c3e50; line-height:1.2; }
        .sidebar-brand-text span { font-size:11px; color:#9ca3af; font-weight:400; display:block; }
        .sidebar-nav { padding:12px 0; flex:1; }
        .nav-item { display:flex; align-items:center; gap:10px; padding:11px 16px; color:#666; text-decoration:none; font-size:13px; border-left:3px solid transparent; transition:all 0.2s; }
        .nav-item:hover { background:#f0f0f0; color:#2c3e50; }
        .nav-item.active { background:#e8eef5; color:#2c3e50; border-left-color:#2c3e50; font-weight:600; }
        .nav-icon { width:18px; text-align:center; font-size:15px; }
        .main { margin-left:240px; flex:1; display:flex; flex-direction:column; }
        .topbar { height:60px; background:#fff; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; padding:0 28px; gap:16px; position:sticky; top:0; z-index:50; }
        .topbar-title { font-size:20px; font-weight:700; color:#2c3e50; flex:1; }
        .topbar-user { display:flex; align-items:center; gap:10px; }
        .topbar-avatar { width:34px; height:34px; border-radius:50%; background:#d1d5db; display:flex; align-items:center; justify-content:center; font-size:13px; color:#6b7280; }
        .topbar-name strong { display:block; font-size:13px; color:#111827; }
        .topbar-name span { font-size:11px; color:#9ca3af; }
        .content { padding:28px; overflow-y:auto; }

        /* ── Grille KPI ── */
        .kpi-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:16px; margin-bottom:28px; }
        .kpi-card { background:#fff; border-radius:12px; border:1px solid #e5e7eb; padding:20px; }
        .kpi-label { font-size:12px; color:#9ca3af; text-transform:uppercase; letter-spacing:.5px; margin-bottom:12px; }
        .kpi-number { font-size:32px; font-weight:700; color:#111827; margin-bottom:8px; }
        .kpi-subtitle { font-size:12px; color:#6b7280; }
        .kpi-highlight { color:#2c3e50; font-weight:600; }

        /* ── Graphiques ── */
        .chart-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(400px, 1fr)); gap:20px; margin-bottom:28px; }
        .chart-card { background:#fff; border-radius:12px; border:1px solid #e5e7eb; padding:20px; }
        .chart-title { font-size:14px; font-weight:700; color:#111827; margin-bottom:16px; }
        .chart-container { position:relative; height:300px; }

        /* ── Tables ── */
        .table-card { background:#fff; border-radius:12px; border:1px solid #e5e7eb; overflow:hidden; margin-bottom:20px; }
        .table-card-header { padding:16px 20px; border-bottom:1px solid #e5e7eb; }
        .table-card-header h3 { font-size:14px; font-weight:700; color:#111827; }
        table { width:100%; border-collapse:collapse; }
        thead { background:#f9fafb; }
        th { padding:11px 16px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #e5e7eb; }
        td { padding:12px 16px; font-size:13px; color:#374151; border-bottom:1px solid #f3f4f6; vertical-align:middle; }
        tr:last-child td { border-bottom:none; }
        tr:hover td { background:#f9fafb; }
        .badge { display:inline-block; padding:2px 10px; border-radius:20px; font-size:11px; font-weight:600; }
        .badge-green { background:#dcfce7; color:#166534; }
        .badge-blue { background:#dbeafe; color:#1e40af; }
        .badge-red { background:#fecaca; color:#991b1b; }

        .empty-state { padding:48px; text-align:center; color:#9ca3af; font-size:14px; }
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
        <a href="messages.php"      class="nav-item"><span class="nav-icon">✉️</span> Messages</a>
        <a href="#"                 class="nav-item"><span class="nav-icon">⚠️</span> Réclamations</a>
        <a href="statistics.php"    class="nav-item active"><span class="nav-icon">📊</span> Statistiques</a>
        <a href="#"                 class="nav-item"><span class="nav-icon">⚙️</span> Paramètres</a>
    </nav>
</div>

<div class="main">
    <div class="topbar">
        <div class="topbar-title">📊 Statistiques</div>
        <div class="topbar-user">
            <span>🔔</span>
            <div class="topbar-avatar">A</div>
            <div class="topbar-name"><strong>Admin</strong><span>Super Admin</span></div>
        </div>
    </div>

    <div class="content">
        
        <!-- ═══ CONVERSATIONS ═══ -->
        <h2 style="font-size:18px;color:#111827;margin-bottom:16px;margin-top:28px;">💬 Conversations</h2>
        <div class="kpi-grid">
            <div class="kpi-card">
                <p class="kpi-label">Total conversations</p>
                <p class="kpi-number"><?= $stats['conversations']['total'] ?></p>
                <p class="kpi-subtitle">Ce mois: <span class="kpi-highlight"><?= $stats['conversations']['ce_mois'] ?></span></p>
            </div>
            <div class="kpi-card">
                <p class="kpi-label">Conversations actives</p>
                <p class="kpi-number" style="color:#1D9E75;"><?= $stats['conversations']['actives'] ?></p>
                <p class="kpi-subtitle"><?= round(($stats['conversations']['actives'] / max($stats['conversations']['total'], 1)) * 100, 0) ?>% du total</p>
            </div>
            <div class="kpi-card">
                <p class="kpi-label">Conversations fermées</p>
                <p class="kpi-number" style="color:#dc2626;"><?= $stats['conversations']['fermees'] ?></p>
                <p class="kpi-subtitle"><?= round(($stats['conversations']['fermees'] / max($stats['conversations']['total'], 1)) * 100, 0) ?>% du total</p>
            </div>
            <div class="kpi-card">
                <p class="kpi-label">Utilisateurs impliqués</p>
                <p class="kpi-number"><?= $stats['conversations']['utilisateurs_uniq'] ?></p>
                <p class="kpi-subtitle">Moyenne: <span class="kpi-highlight"><?= $stats['conversations']['msg_moyen_par_conv'] ?></span> msg/conv</p>
            </div>
        </div>

        <!-- ═══ MESSAGES ═══ -->
        <h2 style="font-size:18px;color:#111827;margin-bottom:16px;margin-top:28px;">✉️ Messages</h2>
        <div class="kpi-grid">
            <div class="kpi-card">
                <p class="kpi-label">Total messages</p>
                <p class="kpi-number"><?= $stats['messages']['total'] ?></p>
                <p class="kpi-subtitle">Aujourd'hui: <span class="kpi-highlight"><?= $stats['messages']['aujourd_hui'] ?></span></p>
            </div>
            <div class="kpi-card">
                <p class="kpi-label">Ce mois</p>
                <p class="kpi-number"><?= $stats['messages']['ce_mois'] ?></p>
                <p class="kpi-subtitle">Longueur moy: <span class="kpi-highlight"><?= $stats['messages']['longueur_avg'] ?></span> caractères</p>
            </div>
            <div class="kpi-card">
                <p class="kpi-label">Messages lus</p>
                <p class="kpi-number" style="color:#1D9E75;"><?= $stats['messages']['lus'] ?></p>
                <p class="kpi-subtitle"><?= round(($stats['messages']['lus'] / max($stats['messages']['total'], 1)) * 100, 0) ?>% du total</p>
            </div>
            <div class="kpi-card">
                <p class="kpi-label">Avec fichiers</p>
                <p class="kpi-number"><?= $stats['messages']['avec_fichiers'] ?></p>
                <p class="kpi-subtitle"><?= round(($stats['messages']['avec_fichiers'] / max($stats['messages']['total'], 1)) * 100, 1) ?>% avec pièce jointe</p>
            </div>
        </div>

        <!-- ═══ GRAPHIQUES ACTIVITÉ ═══ -->
        <h2 style="font-size:18px;color:#111827;margin-bottom:16px;margin-top:28px;">📈 Activité</h2>
        <div class="chart-grid">
            <!-- Graphique messages par jour -->
            <div class="chart-card">
                <p class="chart-title">Messages (7 derniers jours)</p>
                <div class="chart-container">
                    <canvas id="messagesChart"></canvas>
                </div>
            </div>

            <!-- Graphique activité par heure -->
            <div class="chart-card">
                <p class="chart-title">Activité par heure (7 derniers jours)</p>
                <div class="chart-container">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top utilisateurs -->
        <?php if (!empty($stats['activite']['top_users'])): ?>
        <div class="table-card">
            <div class="table-card-header">
                <h3>👥 Top 5 utilisateurs</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Messages</th>
                        <th>% du total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['activite']['top_users'] as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></td>
                        <td><strong><?= $user['count'] ?></strong></td>
                        <td>
                            <span class="badge badge-blue"><?= round(($user['count'] / max($stats['messages']['total'], 1)) * 100, 1) ?>%</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- ═══ FICHIERS ═══ -->
        <h2 style="font-size:18px;color:#111827;margin-bottom:16px;margin-top:28px;">📎 Fichiers</h2>
        <div class="kpi-grid">
            <div class="kpi-card">
                <p class="kpi-label">Total fichiers</p>
                <p class="kpi-number"><?= $stats['fichiers']['total'] ?></p>
                <p class="kpi-subtitle">Ce mois: <span class="kpi-highlight"><?= $stats['fichiers']['ce_mois'] ?></span></p>
            </div>
            <div class="kpi-card">
                <p class="kpi-label">Taille totale</p>
                <p class="kpi-number"><?= StatisticsController::formatBytes($stats['fichiers']['taille_tot']) ?></p>
                <p class="kpi-subtitle">Moy: <span class="kpi-highlight"><?= StatisticsController::formatBytes($stats['fichiers']['taille_avg']) ?></span></p>
            </div>
        </div>

        <!-- Fichiers par type -->
        <?php if (!empty($stats['fichiers']['par_type'])): ?>
        <div class="table-card">
            <div class="table-card-header">
                <h3>Fichiers par type</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Nombre</th>
                        <th>Taille totale</th>
                        <th>% du total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['fichiers']['par_type'] as $type): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($type['type']) ?></strong></td>
                        <td><?= $type['count'] ?></td>
                        <td><?= StatisticsController::formatBytes((int)$type['total_size']) ?></td>
                        <td><span class="badge badge-green"><?= round(($type['count'] / max($stats['fichiers']['total'], 1)) * 100, 1) ?>%</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </div>
</div>

<script>
// Chart.js - Messages par jour
<?php 
$labels = [];
$data = [];
foreach ($stats['activite']['messages_par_jour'] as $item) {
    $labels[] = date('d/m', strtotime($item['jour']));
    $data[] = $item['count'];
}
?>
new Chart(document.getElementById('messagesChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Messages',
            data: <?= json_encode($data) ?>,
            borderColor: '#1D9E75',
            backgroundColor: 'rgba(29,158,117,0.1)',
            borderWidth: 2,
            tension: 0.4,
            fill: true,
            pointRadius: 4,
            pointBackgroundColor: '#1D9E75',
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});

// Chart.js - Activité par heure
<?php 
$hoursLabels = [];
$hoursData = [];
for ($h = 0; $h < 24; $h++) {
    $hoursLabels[] = str_pad($h, 2, '0', STR_PAD_LEFT) . 'h';
    $hoursData[] = $stats['activite']['activite_par_heure'][$h] ?? 0;
}
?>
new Chart(document.getElementById('activityChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($hoursLabels) ?>,
        datasets: [{
            label: 'Messages par heure',
            data: <?= json_encode($hoursData) ?>,
            backgroundColor: '#1D9E75',
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>

</body>
</html>
