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
  <title>Back Office - JobBoard</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="flex h-screen overflow-hidden">

  <!-- SIDEBAR -->
  <div class="sidebar">
    <div class="logo">
      <span class="icon">📋</span>
      <h1>JobBoard Admin</h1>
    </div>

    <div class="menu">
      <a href="#" onclick="showPage('dashboard')" class="menu-item active" id="menu-dashboard">
        <i class="fa-solid fa-house"></i> Dashboard
      </a>
      <a href="#" onclick="showPage('users')" class="menu-item" id="menu-users">
        <i class="fa-solid fa-users"></i> Utilisateurs
      </a>
      <a href="#" onclick="showPage('profiles')" class="menu-item" id="menu-profiles">
        <i class="fa-solid fa-user"></i> Profils & Portfolios
      </a>
      <a href="#" onclick="showPage('offres')" class="menu-item" id="menu-offres">
        <i class="fa-solid fa-briefcase"></i> Offres & Demandes
      </a>
      <a href="/swaply/view/back/publication_back.php" class="menu-item" id="menu-publications">
        <i class="fa-solid fa-newspaper"></i> Publications
      </a>
      <a href="conversations.php" onclick="showPage('conversations')" class="menu-item" id="menu-conversations">
        <i class="fa-solid fa-comment-dots"></i> Conversations
      </a>
      <a href="#" onclick="showPage('reclamations')" class="menu-item" id="menu-reclamations">
        <i class="fa-solid fa-exclamation-triangle"></i> Réclamations
      </a>
      <a href="statistics.php" class="menu-item" id="menu-stats">
        <i class="fa-solid fa-chart-bar"></i> Statistiques
      </a>
      <a href="#" onclick="showPage('settings')" class="menu-item" id="menu-settings">
        <i class="fa-solid fa-gear"></i> Paramètres
      </a>
    </div>
  </div>

  <!-- MAIN CONTENT -->
  <div class="main-content">
    <!-- HEADER -->
<header class="header" id="main-header">      <h2 id="page-title">Dashboard</h2>
      
      <div class="header-right">
        <div class="search-box">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" placeholder="Rechercher...">
        </div>
        
        <div class="notifications">
          <i class="fa-solid fa-bell"></i>
          <span class="badge">7</span>
        </div>
        
        <div class="user">
          <img src="https://i.pravatar.cc/40?img=12" alt="Admin">
          <div>
            <p class="name">Admin</p>
            <p class="role">Super Admin</p>
          </div>
        </div>
      </div>
    </header>

    <!-- PAGE CONTENT -->
    <div class="page-content" id="main-content">
      <!-- Dashboard par défaut -->
      <div id="dashboard-page">
        <div class="kpi-grid">
          <div class="kpi-card">
            <p class="kpi-label">💬 Conversations</p>
            <p class="kpi-number"><?= $stats['conversations']['total'] ?></p>
            <p class="kpi-change positive"><?= $stats['conversations']['actives'] ?> actives</p>
          </div>
          <div class="kpi-card">
            <p class="kpi-label">✉️ Messages</p>
            <p class="kpi-number"><?= $stats['messages']['total'] ?></p>
            <p class="kpi-change positive">+<?= $stats['messages']['aujourd_hui'] ?> aujourd'hui</p>
          </div>
          <div class="kpi-card">
            <p class="kpi-label">👥 Utilisateurs</p>
            <p class="kpi-number"><?= $stats['conversations']['utilisateurs_uniq'] ?></p>
            <p class="kpi-change positive">En conversations</p>
          </div>
          <div class="kpi-card">
            <p class="kpi-label">📎 Fichiers</p>
            <p class="kpi-number"><?= $stats['fichiers']['total'] ?></p>
            <p class="kpi-change positive"><?= StatisticsController::formatBytes($stats['fichiers']['taille_tot']) ?></p>
          </div>
          <div class="kpi-card">
            <p class="kpi-label">📊 Ce mois</p>
            <p class="kpi-number"><?= $stats['messages']['ce_mois'] ?></p>
            <p class="kpi-change positive">messages</p>
          </div>
          <div class="kpi-card">
            <p class="kpi-label">👤 Lus</p>
            <p class="kpi-number"><?= round(($stats['messages']['lus'] / max($stats['messages']['total'], 1)) * 100, 0) ?>%</p>
            <p class="kpi-change positive"><?= $stats['messages']['lus'] ?> / <?= $stats['messages']['total'] ?></p>
          </div>
        </div>
      </div>

      <!-- Autres pages -->
      <div id="other-pages" class="hidden">
        
      </div>
    </div>
  </div>
</div>

<script src="script.js"></script>
<script>
// Ajouter un lien aux statistiques complètes
const dashboardPageDiv = document.querySelector('#dashboard-page');
if (dashboardPageDiv) {
  const link = document.createElement('div');
  link.style.textAlign = 'center';
  link.style.marginTop = '32px';
  link.innerHTML = '<a href="statistics.php" style="display:inline-block;padding:10px 20px;background:#2c3e50;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">📊 Voir les statistiques complètes</a>';
  dashboardPageDiv.appendChild(link);
}
</script>
</body>
</html>