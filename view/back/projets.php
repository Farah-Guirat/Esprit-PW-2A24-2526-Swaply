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
  <link rel="stylesheet" href="styles.css">
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
      <a href="/swaply/view/back/swaplyB.php" class="menu-item" id="menu-dashboard">
        <i class="fa-solid fa-house"></i> Dashboard
      </a>
      <a href="ProfilsB.php" class="menu-item" id="menu-profiles">
        <i class="fa-solid fa-user"></i> Profils
      </a>
      <a href="/swaply/view/back/projets.php" class="menu-item active" id="menu-projets">
        <i class="fa-solid fa-file"></i> Projets
      </a>
       <a href="/swaply/public/index.php?action=dashboard" class="menu-item" id="menu-dashboard">
    <i class="fa-solid fa-briefcase"></i> Offres & Demandes
</a>
      <a href="#" class="menu-item" id="menu-publications">
        <i class="fa-solid fa-newspaper"></i> Publications
      </a>
      <a href="#" class="menu-item" id="menu-conversations">
        <i class="fa-solid fa-comment-dots"></i> Conversations
      </a>
       <a href="reclamations_admin.php" onclick="showPage('reclamations')" class="menu-item" id="menu-reclamations">
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
           <a href="/swaply/controller/logout.php" class="logout-btn" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?');">
                    <i class="fa-solid fa-sign-out-alt"></i> Déconnexion
                </a>
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

      <!-- STATS SECTION -->
      <div class="stats-container" style="margin-bottom: 2rem;">

        <style>
          .stats-wrap { padding: 1.5rem 0; }
          .stats-section-title { font-size: 13px; font-weight: 500; color: #888; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 1.5rem; }
          .metric-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; margin-bottom: 1.5rem; }
          .metric-card { background: #f5f5f5; border-radius: 8px; padding: 1rem 1.25rem; }
          .metric-label { font-size: 12px; color: #888; margin-bottom: 6px; }
          .metric-value { font-size: 28px; font-weight: 500; color: #111; line-height: 1; }
          .big-card { background: #fff; border: 0.5px solid #e0e0e0; border-radius: 12px; padding: 1.5rem; margin-bottom: 1rem; }
          .big-card-title { font-size: 13px; font-weight: 500; color: #888; margin-bottom: 1.25rem; }
          .bar-row { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
          .bar-label { font-size: 13px; color: #555; min-width: 90px; }
          .bar-track { flex: 1; height: 10px; background: #f0f0f0; border-radius: 5px; overflow: hidden; }
          .bar-fill { height: 100%; border-radius: 5px; width: 0; transition: width 1s cubic-bezier(.4,0,.2,1); }
          .bar-count { font-size: 13px; font-weight: 500; color: #111; min-width: 28px; text-align: right; }
          .bar-pct { font-size: 11px; color: #aaa; min-width: 36px; text-align: right; }
        </style>

        <div class="stats-wrap">
          <p class="stats-section-title">statistiques des projets</p>

          <?php
            $totalProjets = array_sum(array_column($statsStatut, 'total'));
            $enCours = 0; $termines = 0;
            foreach ($statsStatut as $s) {
              if ($s['statut'] == 'En cours') $enCours = $s['total'];
              if ($s['statut'] == 'Terminé') $termines = $s['total'];
            }
          ?>

          <!-- METRIC CARDS -->
          <div class="metric-row">
            <div class="metric-card">
              <p class="metric-label">total projets</p>
              <p class="metric-value" data-target="<?= $totalProjets ?>">0</p>
            </div>
            <div class="metric-card">
              <p class="metric-label">en cours</p>
              <p class="metric-value" data-target="<?= $enCours ?>">0</p>
            </div>
            <div class="metric-card">
              <p class="metric-label">terminés</p>
              <p class="metric-value" data-target="<?= $termines ?>">0</p>
            </div>
          </div>

          <!-- STATUT BARS — full width card -->
          <div class="big-card">
            <p class="big-card-title">répartition par statut</p>
            <?php
              $colors = ['#1D9E75','#378ADD','#EF9F27','#D85A30'];
              $maxVal = !empty($statsStatut) ? max(array_column($statsStatut, 'total')) : 1;
              $i = 0;
              foreach ($statsStatut as $s) {
                $pct = $totalProjets > 0 ? round(($s['total'] / $totalProjets) * 100) : 0;
                $barPct = $maxVal > 0 ? round(($s['total'] / $maxVal) * 100) : 0;
                $color = $colors[$i % count($colors)];
            ?>
              <div class="bar-row">
                <span class="bar-label"><?= $s['statut'] ?></span>
                <div class="bar-track">
                  <div class="bar-fill" style="background:<?= $color ?>" data-pct="<?= $barPct ?>"></div>
                </div>
                <span class="bar-count"><?= $s['total'] ?></span>
                <span class="bar-pct"><?= $pct ?>%</span>
              </div>
            <?php $i++; } ?>
          </div>

          <!-- MOIS CHART — full width card -->
          <div class="big-card">
            <p class="big-card-title">projets créés par mois</p>
            <div style="position:relative;width:100%;height:200px;">
              <canvas id="moisChart" role="img" aria-label="Projets par mois">Projets créés par mois.</canvas>
            </div>
          </div>

        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
        <script>
          // Animate counters
          document.querySelectorAll('.metric-value').forEach(el => {
            const target = parseInt(el.dataset.target);
            let start = 0;
            const step = Math.ceil(target / 30) || 1;
            const timer = setInterval(() => {
              start = Math.min(start + step, target);
              el.textContent = start;
              if (start >= target) clearInterval(timer);
            }, 30);
          });

          // Animate bars
          setTimeout(() => {
            document.querySelectorAll('.bar-fill').forEach(el => {
              el.style.width = el.dataset.pct + '%';
            });
          }, 150);

          // Mois chart
          const moisLabels = <?= json_encode(array_column($statsDate, 'mois')) ?>;
          const moisData   = <?= json_encode(array_column($statsDate, 'total')) ?>;

          new Chart(document.getElementById('moisChart'), {
            type: 'bar',
            data: {
              labels: moisLabels,
              datasets: [{
                label: 'Projets',
                data: moisData,
                backgroundColor: '#1D9E75',
                borderRadius: 6,
                borderSkipped: false
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              animation: { duration: 900, easing: 'easeOutQuart' },
              plugins: { legend: { display: false } },
              scales: {
                x: { grid: { display: false }, ticks: { font: { size: 12 }, color: '#999' } },
                y: { grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { size: 12 }, color: '#999', stepSize: 1 }, beginAtZero: true }
              }
            }
          });
        </script>

      </div>
      <!-- END STATS SECTION -->

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