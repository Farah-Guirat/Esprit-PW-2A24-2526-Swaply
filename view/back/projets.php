<?php
require_once "../../model/projet.php";
 
$p = new Projet();
$data = $p->getAll();
$statsStatut = $p->getStatistiquesStatut();
$statsDate = $p->getStatistiquesDate();


if (isset($_GET['sort'])) {

  if ($_GET['sort'] == 'alpha') {
    usort($data, function($a, $b) {
      return strcmp($a['nom_projet'], $b['nom_projet']);
    });
  }

  if ($_GET['sort'] == 'recent') {
    usort($data, function($a, $b) {
      return strtotime($b['date_creation']) - strtotime($a['date_creation']);
    });
  }

}
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
          <input type="text" id="searchInput" placeholder="Rechercher...">
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
 
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
    <h1>Administration des projets</h1>

    <a href="export_projets.php" target="_blank" 
     style="padding:10px 15px;background:#009688;color:white;border-radius:8px;text-decoration:none;">
     📄 Export PDF
    </a>

    <div style="margin-bottom:15px;">
  <form method="GET">
    <select name="sort" onchange="this.form.submit()"
      style="padding:8px 12px;border-radius:6px;border:1px solid #ccc;">
      
      <option value="">-- Trier par --</option>
      <option value="alpha" <?= (isset($_GET['sort']) && $_GET['sort']=='alpha') ? 'selected' : '' ?>>
        🔤 Nom (A → Z)
      </option>

      <option value="recent" <?= (isset($_GET['sort']) && $_GET['sort']=='recent') ? 'selected' : '' ?>>
        🆕 Plus récents
      </option>

    </select>
  </form>
</div>

    </div>
    <div class="stats-container">

  <h2>📊 Statistiques des projets</h2>

  <!-- STATUT -->
  <div>
    <h3>Par statut</h3>
    <?php foreach ($statsStatut as $s) { ?>
      <p><?= $s['statut'] ?> : <?= $s['total'] ?></p>
    <?php } ?>
  </div>

  <!-- DATE -->
  <div>
    <h3>Par mois</h3>
    <?php foreach ($statsDate as $d) { ?>
      <p><?= $d['mois'] ?> : <?= $d['total'] ?></p>
    <?php } ?>
  </div>

</div>
 
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
        <tr class="project-row">
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
            <a href="../../controller/ProjetController.php?delete=<?= $row['id_projet'] ?>"
               onclick="return confirm('Voulez-vous supprimer ce projet ?')">
               🗑 Supprimer
            </a>
          </td>
        </tr>
        <?php } ?>
 
      </table>
 
    </div>
  </div>
</div>


<script>
document.addEventListener("DOMContentLoaded", function () {

  let searchInput = document.getElementById("searchInput");

  if (searchInput) {
    searchInput.addEventListener("input", function () {

      let value = this.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

      let rows = document.querySelectorAll(".project-row");

      rows.forEach(row => {

        let text = row.textContent.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

        if (text.includes(value)) {
          row.style.display = "";
        } else {
          row.style.display = "none";
        }

      });

    });
  }

});
</script>
 
</body>
</html>