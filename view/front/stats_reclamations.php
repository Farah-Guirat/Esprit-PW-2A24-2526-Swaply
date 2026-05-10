<?php
require_once '../../controller/ReclamationController.php';
require_once '../../controller/ReponseController.php';

session_start();
$photo = $_SESSION['user']['photo'] ?? null;
$controller = new ReclamationController();
$recs       = $controller->afficher()->fetchAll(PDO::FETCH_ASSOC);

function normalizeStatut(string $s): string {
    $s = strtolower(trim($s));
    return str_replace(
        ['é','è','ê','ë','à','â','î','ï','ô','û','ù','ç'],
        ['e','e','e','e','a','a','i','i','o','u','u','c'],
        $s
    );
}

// ── Calculs statistiques ──
$total      = count($recs);
$enAttente  = 0; $enCours = 0; $traite = 0;
$totalRating = 0; $countRating = 0;
$perType    = ['person' => 0, 'company' => 0];
$perMonth   = [];

foreach ($recs as $rec) {
    $sNorm = normalizeStatut($rec['statut'] ?? '');
    if (str_contains($sNorm, 'traite'))       $traite++;
    elseif (str_contains($sNorm, 'cours'))    $enCours++;
    else                                       $enAttente++;

    if (!empty($rec['rating'])) {
        $totalRating += (int)$rec['rating'];
        $countRating++;
    }

    $type = strtolower($rec['type'] ?? 'person');
    if ($type === 'company') $perType['company']++;
    else                     $perType['person']++;

    // Par mois
    $date = $rec['date_creation'] ?? '';
    if ($date) {
        $month = substr($date, 0, 7); // "2026-04"
        $perMonth[$month] = ($perMonth[$month] ?? 0) + 1;
    }
}

ksort($perMonth);
$avgRating  = $countRating > 0 ? round($totalRating / $countRating, 1) : 0;
$tauxTraite = $total > 0 ? round($traite / $total * 100) : 0;

// JSON pour Chart.js
$monthLabels = json_encode(array_keys($perMonth));
$monthData   = json_encode(array_values($perMonth));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Swaply – Statistiques Réclamations</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<link rel="stylesheet" href="../../assets/css/style.css">
<style>
@keyframes fadeUp {
    from { opacity:0; transform:translateY(16px); }
    to   { opacity:1; transform:translateY(0); }
}
.fade-up { animation: fadeUp .4s ease both; }

@keyframes countUp {
    from { opacity:0; transform:scale(.8); }
    to   { opacity:1; transform:scale(1); }
}
.count-anim { animation: countUp .5s cubic-bezier(.34,1.56,.64,1) both; }

.kpi {
    background: white;
    border-radius: 24px;
    padding: 24px 28px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border-top: 4px solid transparent;
}
.chart-card {
    background: white;
    border-radius: 24px;
    padding: 28px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}
</style>
</head>
<body class="bg-gray-50">

<!-- ── NAVBAR ── -->
<header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-8 py-5 flex items-center justify-between">
      <div class="flex items-center gap-3">
          <span class="text-gray-700 font-medium">
              <?php echo $_SESSION['user']['nom']; ?>
              <?php echo $_SESSION['user']['prenom']; ?>
          </span>
        <div class="w-9 h-9 bg-teal-500 rounded-2xl flex items-center justify-center text-white font-bold text-2xl">S</div>
        <h1 class="text-2xl font-bold text-gray-800">Swaply</h1>
      </div>

     
      <nav class="flex items-center gap-8 text-sm font-medium">
        <a href="swaplyf.php" class="nav-link ">Accueil</a>
        <a href="profils.html" class="nav-link">Profils</a>
        <a href="projets.html" class="nav-link">Projets</a>
       <a href="/swaply/public/index.php?action=choice"  class="nav-link active">Demandes</a>
        <a href="/swaply/public/index.php?action=choicee">Offres</a>
        <a href="publications.html" class="nav-link">Publications</a>
        <a href="messages.html" class="nav-link">Messages</a>
        <a href="reclamations.php" class="nav-link">Réclamations</a>
      </nav>

      <div onclick="window.location.href='/swaply/view/front/Profil.php'" class="w-10 h-10 bg-teal-100 rounded-2xl overflow-hidden border-2 border-white shadow cursor-pointer relative" onclick="togglePhotoMenu()">
        <?php if ($photo): ?>
          <img src="/swaply/uploads/profiles/<?= htmlspecialchars($photo) ?>" alt="Profil" class="w-full h-full object-cover" style="width: 100%; height: 100%; object-fit: cover; display: block;">
        <?php else: ?>
          <div class="w-full h-full flex items-center justify-center text-teal-600 font-bold text-lg">
            <?= strtoupper(substr($_SESSION['user']['nom'], 0, 1) . substr($_SESSION['user']['prenom'], 0, 1)) ?>
          </div>
        <?php endif; ?>
        <div id="photo-menu" class="absolute top-full right-0 bg-white border border-gray-300 rounded-lg shadow-lg p-2 hidden z-50">
          <button onclick="uploadFile(event)" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Télécharger un fichier</button>
          <button onclick="takePhoto(event)" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Prendre une photo</button>
          <?php if ($photo): ?>
            <button onclick="deletePhoto(event)" class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-100">Supprimer la photo</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </header>


<main class="max-w-6xl mx-auto px-8 py-10 space-y-8">

  <!-- Breadcrumb -->
  <div class="flex items-center gap-2 text-sm text-gray-400">
    <a href="reclamations.php" class="hover:text-teal-500 transition">← Mes Réclamations</a>
    <span>/</span>
    <span class="text-gray-600 font-medium">Statistiques</span>
  </div>

  <div class="flex items-center justify-between">
    <div>
      <h2 class="text-2xl font-bold text-gray-800">📊 Statistiques de mes réclamations</h2>
      <p class="text-sm text-gray-400 mt-1">Vue d'ensemble de toutes vos réclamations</p>
    </div>
  </div>

  <!-- ── KPI Cards ── -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-5">

    <div class="kpi fade-up" style="border-top-color:#14b8a6; animation-delay:0s">
      <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">Total</p>
      <p class="count-anim text-4xl font-bold text-gray-800" style="animation-delay:.1s"><?= $total ?></p>
      <p class="text-xs text-gray-400 mt-1">réclamations</p>
    </div>

    <div class="kpi fade-up" style="border-top-color:#ef4444; animation-delay:.07s">
      <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">En attente</p>
      <p class="count-anim text-4xl font-bold text-red-500" style="animation-delay:.15s"><?= $enAttente ?></p>
      <p class="text-xs text-gray-400 mt-1">non traitées</p>
    </div>

    <div class="kpi fade-up" style="border-top-color:#f97316; animation-delay:.14s">
      <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">En cours</p>
      <p class="count-anim text-4xl font-bold text-orange-500" style="animation-delay:.2s"><?= $enCours ?></p>
      <p class="text-xs text-gray-400 mt-1">en traitement</p>
    </div>

    <div class="kpi fade-up" style="border-top-color:#22c55e; animation-delay:.21s">
      <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">Traités</p>
      <p class="count-anim text-4xl font-bold text-green-500" style="animation-delay:.25s"><?= $traite ?></p>
      <p class="text-xs text-gray-400 mt-1"><?= $tauxTraite ?>% du total</p>
    </div>

  </div>

  <!-- ── Charts row 1 ── -->
  <div class="grid md:grid-cols-2 gap-6">

    <!-- Donut Statuts -->
    <div class="chart-card fade-up" style="animation-delay:.2s">
      <h3 class="font-semibold text-gray-700 mb-4">Répartition par statut</h3>
      <div class="relative h-56 flex items-center justify-center">
        <canvas id="donutStatut"></canvas>
        <!-- Texte centre -->
        <div class="absolute text-center pointer-events-none">
          <p class="text-3xl font-bold text-gray-800"><?= $tauxTraite ?>%</p>
          <p class="text-xs text-gray-400">traités</p>
        </div>
      </div>
      <!-- Légende -->
      <div class="flex justify-center gap-5 mt-4 text-xs text-gray-500 flex-wrap">
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-red-400 inline-block"></span> En attente (<?= $enAttente ?>)</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-orange-400 inline-block"></span> En cours (<?= $enCours ?>)</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-green-400 inline-block"></span> Traité (<?= $traite ?>)</span>
      </div>
    </div>

    <!-- Donut Types -->
    <div class="chart-card fade-up" style="animation-delay:.27s">
      <h3 class="font-semibold text-gray-700 mb-4">Répartition par type</h3>
      <div class="relative h-56 flex items-center justify-center">
        <canvas id="donutType"></canvas>
      </div>
      <div class="flex justify-center gap-5 mt-4 text-xs text-gray-500 flex-wrap">
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-teal-400 inline-block"></span> Personne (<?= $perType['person'] ?>)</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-violet-400 inline-block"></span> Entreprise (<?= $perType['company'] ?>)</span>
      </div>
    </div>

  </div>

  <!-- ── Chart row 2 ── -->
  <div class="grid md:grid-cols-3 gap-6">

    <!-- Bar Chart par mois -->
    <div class="chart-card fade-up md:col-span-2" style="animation-delay:.32s">
      <h3 class="font-semibold text-gray-700 mb-4">Réclamations par mois</h3>
      <div class="h-52">
        <canvas id="barMois"></canvas>
      </div>
    </div>

    <!-- Note moyenne -->
    <div class="chart-card fade-up flex flex-col items-center justify-center gap-3" style="animation-delay:.38s">
      <h3 class="font-semibold text-gray-700 self-start w-full">Note moyenne</h3>
      <div class="text-6xl font-bold text-teal-500 count-anim" style="animation-delay:.4s">
        <?= $avgRating ?>
      </div>
      <div class="text-2xl">
        <?php
          $full  = floor($avgRating);
          $half  = ($avgRating - $full) >= 0.5;
          echo str_repeat('⭐', $full);
          echo $half ? '✨' : '';
        ?>
      </div>
      <p class="text-xs text-gray-400 text-center">sur <?= $countRating ?> réclamation<?= $countRating>1?'s':'' ?> notée<?= $countRating>1?'s':'' ?></p>

      <!-- Mini bar rating -->
      <?php
        $ratingDist = [1=>0, 2=>0, 3=>0, 4=>0, 5=>0];
        foreach ($recs as $r) {
            $rv = (int)($r['rating'] ?? 0);
            if ($rv >= 1 && $rv <= 5) $ratingDist[$rv]++;
        }
      ?>
      <div class="w-full space-y-1.5 mt-2">
        <?php for ($s = 5; $s >= 1; $s--): ?>
        <?php $pct = $countRating > 0 ? round($ratingDist[$s] / $countRating * 100) : 0; ?>
        <div class="flex items-center gap-2 text-xs text-gray-400">
          <span class="w-4 text-right"><?= $s ?></span>
          <span class="text-yellow-400">★</span>
          <div class="flex-1 bg-gray-100 rounded-full h-1.5">
            <div class="bg-yellow-400 h-1.5 rounded-full transition-all" style="width:<?= $pct ?>%"></div>
          </div>
          <span class="w-6"><?= $ratingDist[$s] ?></span>
        </div>
        <?php endfor; ?>
      </div>
    </div>

  </div>

</main>

<script>
// ── Donut Statuts ──
new Chart(document.getElementById('donutStatut'), {
    type: 'doughnut',
    data: {
        labels: ['En attente', 'En cours', 'Traité'],
        datasets: [{
            data: [<?= $enAttente ?>, <?= $enCours ?>, <?= $traite ?>],
            backgroundColor: ['#f87171', '#fb923c', '#4ade80'],
            borderWidth: 0,
            hoverOffset: 6,
        }]
    },
    options: {
        cutout: '72%',
        plugins: { legend: { display: false } },
        animation: { animateRotate: true, duration: 900 },
    }
});

// ── Donut Types ──
new Chart(document.getElementById('donutType'), {
    type: 'doughnut',
    data: {
        labels: ['Personne', 'Entreprise'],
        datasets: [{
            data: [<?= $perType['person'] ?>, <?= $perType['company'] ?>],
            backgroundColor: ['#2dd4bf', '#a78bfa'],
            borderWidth: 0,
            hoverOffset: 6,
        }]
    },
    options: {
        cutout: '68%',
        plugins: { legend: { display: false } },
        animation: { animateRotate: true, duration: 900 },
    }
});

// ── Bar Mois ──
new Chart(document.getElementById('barMois'), {
    type: 'bar',
    data: {
        labels: <?= $monthLabels ?>,
        datasets: [{
            label: 'Réclamations',
            data: <?= $monthData ?>,
            backgroundColor: '#14b8a6cc',
            borderRadius: 10,
            borderSkipped: false,
            hoverBackgroundColor: '#0f766e',
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1, color: '#94a3b8', font: { size: 11 } },
                grid: { color: '#f1f5f9' },
            },
            x: {
                ticks: { color: '#94a3b8', font: { size: 11 } },
                grid: { display: false },
            }
        },
        animation: { duration: 800 },
    }
});
</script>

<script src="../../assets/js/main.js"></script>
</body>
</html>
