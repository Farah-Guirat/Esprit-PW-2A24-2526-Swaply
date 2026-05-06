<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Back Office - JobBoard</title>
<link rel="stylesheet" href="../src/assets/css/styles.css">
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
      <a href="index.php?action=back" class="menu-item active" id="menu-dashboard">
        <i class="fa-solid fa-house"></i> Dashboard
      </a>
      <a href="#" onclick="showPage('users')" class="menu-item" id="menu-users">
        <i class="fa-solid fa-users"></i> Utilisateurs
      </a>
      <a href="#" onclick="showPage('profiles')" class="menu-item" id="menu-profiles">
        <i class="fa-solid fa-user"></i> Profils & Portfolios
      </a>
      <a href="index.php?action=dashboard" onclick="showPage('offres')" class="menu-item" id="menu-offres">
        <i class="fa-solid fa-briefcase"></i> Offres & Demandes
      </a>
      <a href="#" onclick="showPage('publications')" class="menu-item" id="menu-publications">
        <i class="fa-solid fa-newspaper"></i> Publications
      </a>
      <a href="#" onclick="showPage('conversations')" class="menu-item" id="menu-conversations">
        <i class="fa-solid fa-comment-dots"></i> Conversations
      </a>
      <a href="#" onclick="showPage('reclamations')" class="menu-item" id="menu-reclamations">
        <i class="fa-solid fa-exclamation-triangle"></i> Réclamations
      </a>
      <a href="#" onclick="showPage('stats')" class="menu-item" id="menu-stats">
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
            <p class="kpi-label">Total Utilisateurs</p>
            <p class="kpi-number">12 458</p>
            <p class="kpi-change positive">+8% ce mois</p>
          </div>
          <div class="kpi-card">
            <p class="kpi-label">Offres publiées</p>
            <p class="kpi-number">847</p>
            <p class="kpi-change positive">+23 aujourd’hui</p>
          </div>
          <div class="kpi-card">
            <p class="kpi-label">Demandes en cours</p>
            <p class="kpi-number">312</p>
            <p class="kpi-change negative">-5%</p>
          </div>
          <div class="kpi-card">
            <p class="kpi-label">Conversations actives</p>
            <p class="kpi-number">184</p>
            <p class="kpi-change positive">+12</p>
          </div>
          <div class="kpi-card">
            <p class="kpi-label">Réclamations en attente</p>
            <p class="kpi-number red">27</p>
            <p class="kpi-change negative">Attention</p>
          </div>
          <div class="kpi-card">
            <p class="kpi-label">Publications totales</p>
            <p class="kpi-number">1 392</p>
            <p class="kpi-change positive">+41 ce mois</p>
          </div>
        </div>
      </div>

      <!-- Autres pages -->
      <div id="other-pages" class="hidden">
        
      </div>
    </div>
  </div>
</div>

<script src="../src/assets/script.js"></script>
</body>
</html>