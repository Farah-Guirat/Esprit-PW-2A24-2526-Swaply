<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Back Office - JobBoard</title>
  <link rel="stylesheet" href="../../assets/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  
  <style>
    /* Spinner simple et propre */
    .spinner {
      width: 44px;
      height: 44px;
      border: 4px solid #e2e8f0;
      border-top-color: #14b8a6;
      border-radius: 50%;
      animation: adm-spin 0.7s linear infinite;
    }
    @keyframes adm-spin {
      to { transform: rotate(360deg); }
    }
  </style>
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
      <a href="#" onclick="showPage('dashboard'); return false;" class="menu-item active" id="menu-dashboard">
        <i class="fa-solid fa-house"></i> Dashboard
      </a>
      <a href="#" onclick="showPage('users'); return false;" class="menu-item" id="menu-users">
        <i class="fa-solid fa-users"></i> Utilisateurs
      </a>
      <a href="#" onclick="showPage('profiles'); return false;" class="menu-item" id="menu-profiles">
        <i class="fa-solid fa-user"></i> Profils & Portfolios
      </a>
      <a href="#" onclick="showPage('offres'); return false;" class="menu-item" id="menu-offres">
        <i class="fa-solid fa-briefcase"></i> Offres & Demandes
      </a>
      <a href="#" onclick="showPage('publications'); return false;" class="menu-item" id="menu-publications">
        <i class="fa-solid fa-newspaper"></i> Publications
      </a>
      <a href="#" onclick="showPage('conversations'); return false;" class="menu-item" id="menu-conversations">
        <i class="fa-solid fa-comment-dots"></i> Conversations
      </a>
      <a href="#" onclick="showPage('reclamations'); return false;" class="menu-item" id="menu-reclamations">
        <i class="fa-solid fa-exclamation-triangle"></i> Réclamations
      </a>
      <a href="#" onclick="showPage('stats'); return false;" class="menu-item" id="menu-stats">
        <i class="fa-solid fa-chart-bar"></i> Statistiques
      </a>
      <a href="#" onclick="showPage('settings'); return false;" class="menu-item" id="menu-settings">
        <i class="fa-solid fa-gear"></i> Paramètres
      </a>
    </div>
  </div>

  <!-- MAIN CONTENT -->
  <div class="main-content">

    <!-- HEADER -->
    <header class="header" id="main-header">
      <h2 id="page-title">Dashboard</h2>
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

      <!-- Dashboard (statique) -->
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
            <p class="kpi-change positive">+23 aujourd'hui</p>
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

      <!-- Contenu dynamique des autres pages -->
      <div id="other-pages" class="hidden"></div>

    </div>
  </div>
</div>

<script src="../../assets/script.js"></script>
</body>
</html>