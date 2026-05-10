<?php
/**
 * Back Office - Statistiques des conversations et messages
 */
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/Message.php';
require_once __DIR__ . '/../model/Conversation.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Vérifier que l'utilisateur est admin (à adapter selon votre système de rôles)
// Pour l'instant, on va juste laisser accès à cette page
$id_user = (int)($_SESSION['id_user'] ?? 0);

// Récupérer les statistiques
$pdo = Database::getInstance();
$messageModel = new Message();
$conversationModel = new Conversation();

// Statistiques globales
try {
    // Total conversations
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM conversations WHERE statut = 'active'");
    $totalConvs = $stmt->fetchColumn();

    // Total messages
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM messages");
    $totalMessages = $stmt->fetchColumn();

    // Moyenne de messages par conversation
    $avgMessagesPerConv = $totalConvs > 0 ? round($totalMessages / $totalConvs, 2) : 0;

    // Conversations les plus actives (top 10)
    $stmt = $pdo->query(
        "SELECT c.id_conversation,
                CONCAT(u1.prenom, ' ', u1.nom) as user1,
                CONCAT(u2.prenom, ' ', u2.nom) as user2,
                COUNT(m.id_message) as nb_messages,
                MAX(m.date_envoi) as last_message_date
         FROM conversations c
         JOIN utilisateurs u1 ON u1.id_u = c.id_user1
         JOIN utilisateurs u2 ON u2.id_u = c.id_user2
         LEFT JOIN messages m ON m.id_conversation = c.id_conversation
         WHERE c.statut = 'active'
         GROUP BY c.id_conversation
         ORDER BY nb_messages DESC
         LIMIT 10"
    );
    $topConversations = $stmt->fetchAll();

    // Utilisateurs les plus actifs (top 10)
    $stmt = $pdo->query(
        "SELECT u.id_u, CONCAT(u.prenom, ' ', u.nom) as nom_complet, COUNT(m.id_message) as nb_messages
         FROM utilisateurs u
         LEFT JOIN messages m ON m.id_expediteur = u.id_u
         GROUP BY u.id_u
         ORDER BY nb_messages DESC
         LIMIT 10"
    );
    $topUsers = $stmt->fetchAll();

    // Messages par jour (derniers 30 jours)
    $stmt = $pdo->query(
        "SELECT DATE(date_envoi) as date, COUNT(*) as nb_messages
         FROM messages
         WHERE date_envoi >= DATE_SUB(NOW(), INTERVAL 30 DAY)
         GROUP BY DATE(date_envoi)
         ORDER BY date DESC"
    );
    $messagesPerDay = $stmt->fetchAll();

    // Pourcentage de messages lus
    $stmt = $pdo->query(
        "SELECT COUNT(CASE WHEN lu = 1 THEN 1 END) as lus, COUNT(*) as total
         FROM messages"
    );
    $readStats = $stmt->fetch();
    $percentageLus = $readStats['total'] > 0 ? round(($readStats['lus'] / $readStats['total']) * 100, 2) : 0;

    // Utilisateurs avec les plus de conversations
    $stmt = $pdo->query(
        "SELECT u.id_u, CONCAT(u.prenom, ' ', u.nom) as nom_complet, 
                COUNT(DISTINCT CASE WHEN c.id_user1 = u.id_u THEN c.id_conversation END) +
                COUNT(DISTINCT CASE WHEN c.id_user2 = u.id_u THEN c.id_conversation END) as nb_conversations
         FROM utilisateurs u
         LEFT JOIN conversations c ON (c.id_user1 = u.id_u OR c.id_user2 = u.id_u) AND c.statut = 'active'
         GROUP BY u.id_u
         ORDER BY nb_conversations DESC
         LIMIT 10"
    );
    $usersWithMostConvs = $stmt->fetchAll();

} catch (Exception $e) {
    die("Erreur lors de la récupération des statistiques: " . $e->getMessage());
}

// Format pour afficher la taille des données
function formatSize($bytes) {
    if ($bytes == 0) return '0 B';
    $k = 1024;
    $sizes = ['B', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes, $k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques des Conversations - Swaply Back Office</title>
    <link rel="stylesheet" href="../../asset/css/style.css">
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Segoe UI',sans-serif; background:#f5f7fa; color:#333; }
        
        .navbar { display:flex; align-items:center; padding:0 32px; height:64px; background:#fff; border-bottom:1px solid #e5e7eb; box-shadow:0 1px 3px rgba(0,0,0,0.1); }
        .navbar-brand { display:flex; align-items:center; gap:8px; font-size:18px; font-weight:700; color:#1a1a1a; text-decoration:none; margin-right:auto; }
        .navbar-logo { width:34px; height:34px; border-radius:50%; background:#1D9E75; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; font-size:15px; }
        .navbar-link { font-size:14px; color:#4b5563; text-decoration:none; margin-left:24px; }
        .navbar-link:hover { color:#1D9E75; }

        .container { max-width:1400px; margin:0 auto; padding:32px 16px; }
        .header { margin-bottom:32px; }
        .header h1 { font-size:28px; font-weight:700; color:#1a1a1a; margin-bottom:4px; }
        .header p { color:#6b7280; font-size:14px; }

        .stats-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:20px; margin-bottom:32px; }
        .stat-card { background:#fff; border-radius:12px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.1); }
        .stat-card .label { font-size:12px; text-transform:uppercase; letter-spacing:0.5px; color:#9ca3af; font-weight:600; margin-bottom:8px; }
        .stat-card .value { font-size:32px; font-weight:700; color:#1D9E75; }
        .stat-card .subtext { font-size:12px; color:#6b7280; margin-top:8px; }
        .stat-card.blue .value { color:#0066cc; }
        .stat-card.purple .value { color:#7c3aed; }
        .stat-card.orange .value { color:#f97316; }

        .section { background:#fff; border-radius:12px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.1); margin-bottom:24px; }
        .section h2 { font-size:18px; font-weight:700; color:#1a1a1a; margin-bottom:16px; border-bottom:2px solid #f0f0f0; padding-bottom:12px; }

        table { width:100%; border-collapse:collapse; font-size:14px; }
        table thead { background:#f9fafb; }
        table th { padding:12px; text-align:left; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb; }
        table td { padding:12px; border-bottom:1px solid #f3f4f6; }
        table tbody tr:hover { background:#f9fafb; }
        table tr:last-child td { border-bottom:none; }

        .badge { display:inline-block; padding:4px 8px; border-radius:20px; font-size:12px; font-weight:600; }
        .badge-green { background:#d1fae5; color:#065f46; }
        .badge-blue { background:#dbeafe; color:#1e40af; }
        .badge-purple { background:#e9d5ff; color:#6b21a8; }

        .chart { height:300px; background:#f9fafb; border-radius:8px; padding:16px; }
        .empty-state { text-align:center; padding:32px; color:#9ca3af; }
        .empty-state-icon { font-size:48px; margin-bottom:12px; }

        .action-button { display:inline-block; padding:8px 16px; background:#1D9E75; color:#fff; text-decoration:none; border-radius:6px; font-size:13px; cursor:pointer; border:none; }
        .action-button:hover { background:#178a64; }
        .action-button.secondary { background:#f3f4f6; color:#4b5563; border:1px solid #e5e7eb; }
        .action-button.secondary:hover { background:#e5e7eb; }

        .stat-row { display:flex; align-items:center; justify-content:space-between; padding:12px 0; border-bottom:1px solid #f3f4f6; }
        .stat-row:last-child { border-bottom:none; }
        .stat-row-label { flex:1; }
        .stat-row-value { font-weight:600; color:#1D9E75; }

        @media (max-width:768px) {
            .stats-grid { grid-template-columns:1fr; }
            .container { padding:16px; }
            .stat-card .value { font-size:24px; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <a class="navbar-brand" href="indexf.php">
        <div class="navbar-logo">S</div>
        Swaply Back Office
    </a>
    <a href="messagerie.php" class="navbar-link">Messages</a>
    <a href="back_office_stats.php" class="navbar-link" style="color:#1D9E75; font-weight:600;">Statistiques</a>
</nav>

<!-- CONTAINER -->
<div class="container">
    <div class="header">
        <h1>📊 Statistiques des Conversations</h1>
        <p>Vue d'ensemble de l'activité des messages et des conversations</p>
    </div>

    <!-- CARTES STATISTIQUES PRINCIPALES -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="label">Total Conversations</div>
            <div class="value"><?= number_format($totalConvs) ?></div>
            <div class="subtext">Conversations actives</div>
        </div>

        <div class="stat-card blue">
            <div class="label">Total Messages</div>
            <div class="value"><?= number_format($totalMessages) ?></div>
            <div class="subtext">Tous les messages envoyés</div>
        </div>

        <div class="stat-card orange">
            <div class="label">Moyenne par Conversation</div>
            <div class="value"><?= $avgMessagesPerConv ?></div>
            <div class="subtext">Messages en moyenne</div>
        </div>

        <div class="stat-card purple">
            <div class="label">Messages Lus</div>
            <div class="value"><?= $percentageLus ?>%</div>
            <div class="subtext"><?= $readStats['lus'] ?> sur <?= $readStats['total'] ?> messages</div>
        </div>
    </div>

    <!-- CONVERSATIONS LES PLUS ACTIVES -->
    <div class="section">
        <h2>🔥 Conversations les Plus Actives (Top 10)</h2>
        <?php if (!empty($topConversations)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Participants</th>
                        <th>Nombre de Messages</th>
                        <th>Dernier Message</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topConversations as $conv): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($conv['user1']) ?></strong> ↔️
                                <strong><?= htmlspecialchars($conv['user2']) ?></strong>
                            </td>
                            <td><span class="badge badge-green"><?= (int)$conv['nb_messages'] ?> messages</span></td>
                            <td><?= $conv['last_message_date'] ? date('d/m/Y H:i', strtotime($conv['last_message_date'])) : 'N/A' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">💬</div>
                <p>Aucune conversation trouvée</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- UTILISATEURS LES PLUS ACTIFS -->
    <div class="section">
        <h2>👤 Utilisateurs les Plus Actifs (Top 10)</h2>
        <?php if (!empty($topUsers)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Nombre de Messages</th>
                        <th>Pourcentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalMessagesCount = array_sum(array_column($topUsers, 'nb_messages'));
                    foreach ($topUsers as $user): 
                        $percentage = $totalMessagesCount > 0 ? round(($user['nb_messages'] / $totalMessagesCount) * 100, 1) : 0;
                    ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($user['nom_complet']) ?></strong></td>
                            <td><span class="badge badge-blue"><?= (int)$user['nb_messages'] ?></span></td>
                            <td>
                                <div style="width:200px; height:20px; background:#f0f0f0; border-radius:10px; overflow:hidden;">
                                    <div style="width:<?= $percentage ?>%; height:100%; background:#1D9E75; display:flex; align-items:center; justify-content:center; font-size:10px; color:#fff;">
                                        <?php if ($percentage > 10) echo $percentage . '%'; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">👤</div>
                <p>Aucun utilisateur trouvé</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- UTILISATEURS AVEC LE PLUS DE CONVERSATIONS -->
    <div class="section">
        <h2>🔗 Utilisateurs les Plus Sociaux (Top 10)</h2>
        <?php if (!empty($usersWithMostConvs)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Nombre de Conversations</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usersWithMostConvs as $user): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($user['nom_complet']) ?></strong></td>
                            <td><span class="badge badge-purple"><?= (int)$user['nb_conversations'] ?> conversations</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">🔗</div>
                <p>Aucune donnée disponible</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- ACTIVITÉ PAR JOUR (DERNIERS 30 JOURS) -->
    <div class="section">
        <h2>📈 Activité par Jour (Derniers 30 Jours)</h2>
        <?php if (!empty($messagesPerDay)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Nombre de Messages</th>
                        <th>Visualisation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $maxMessages = max(array_column($messagesPerDay, 'nb_messages'));
                    foreach ($messagesPerDay as $day): 
                        $percentage = $maxMessages > 0 ? ($day['nb_messages'] / $maxMessages) * 100 : 0;
                    ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($day['date'])) ?></td>
                            <td><strong><?= (int)$day['nb_messages'] ?></strong></td>
                            <td>
                                <div style="width:300px; height:30px; background:#f0f0f0; border-radius:4px; overflow:hidden;">
                                    <div style="width:<?= $percentage ?>%; height:100%; background:linear-gradient(90deg, #1D9E75, #0d7d63); display:flex; align-items:center; justify-content:flex-end; padding-right:8px;">
                                        <span style="color:#fff; font-size:11px; font-weight:600;"><?= (int)$day['nb_messages'] ?></span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">📈</div>
                <p>Aucune activité enregistrée</p>
            </div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
