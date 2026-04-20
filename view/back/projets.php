<?php
require_once "../../model/projet.php";
 
$p = new Projet();
$data = $p->getAll();
?>
 
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Back Office - Projets</title>
  <link rel="stylesheet" href="../../assets/admin.css">
  <link rel="stylesheet" href="../../assets/projetsb.css">
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
      <a href="/swaply/view/back/admin.php" class="menu-item" id="menu-dashboard">
        <i class="fa-solid fa-house"></i> Dashboard
      </a>
      <a href="#" class="menu-item" id="menu-users">
        <i class="fa-solid fa-users"></i> Utilisateurs
      </a>
      <a href="/swaply/view/back/projets.php" class="menu-item active" id="menu-projets">
        <i class="fa-solid fa-user"></i> Projets
      </a>
      <a href="#" class="menu-item" id="menu-offres">
        <i class="fa-solid fa-briefcase"></i> Offres & Demandes
      </a>
      <a href="#" class="menu-item" id="menu-publications">
        <i class="fa-solid fa-newspaper"></i> Publications
      </a>
      <a href="#" class="menu-item" id="menu-conversations">
        <i class="fa-solid fa-comment-dots"></i> Conversations
      </a>
      <a href="#" class="menu-item" id="menu-reclamations">
        <i class="fa-solid fa-exclamation-triangle"></i> Réclamations
      </a>
      <a href="#" class="menu-item" id="menu-stats">
        <i class="fa-solid fa-chart-bar"></i> Statistiques
      </a>
      <a href="#" class="menu-item" id="menu-settings">
        <i class="fa-solid fa-gear"></i> Paramètres
      </a>
    </div>
  </div>
 
  <!-- MAIN CONTENT -->
  <div class="main-content">
 
    <!-- HEADER -->
    <header class="header" id="main-header">
      <h2 id="page-title">Projets</h2>
 
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
 
      <h1>Administration des projets</h1>
 
      <!-- TABLE DES PROJETS -->
      <table border="1">
        <tr>
          <th>ID</th>
          <th>Nom</th>
          <th>Description</th>
          <th>Statut</th>
          <th>Compétences</th>
          <th>Action</th>
        </tr>
 
        <?php foreach($data as $row) { ?>
        <tr>
          <td><?= $row['id_projet'] ?></td>
          <td><?= $row['nom_projet'] ?></td>
          <td><?= $row['description'] ?></td>
          <td><?= $row['statut'] ?></td>
          <td>
            <?php
              $comps = $p->getCompetences($row['id_projet']);
              foreach($comps as $c) { ?>
                <span>
                  <?= $c['nom_competence'] ?> (<?= $c['niveau'] ?>)
                </span><br>
            <?php } ?>
          </td>
          <td>
            <!-- DELETE -->
            <a href="../../controller/ProjetController.php?delete=<?= $row['id_projet'] ?>"
               onclick="return confirm('Voulez-vous supprimer ce projet ?')">
               🗑 Supprimer
            </a>
          </td>
        </tr>
        <?php } ?>
 
      </table>
 
    </div><!-- end page-content -->
  </div><!-- end main-content -->
</div><!-- end flex -->
 
</body>
</html>
 
