<?php
require_once '../../controller/ReclamationController.php';

$controller = new ReclamationController();
$recs = $controller->afficher();

function normalizeStatut(string $s): string {
    $s = strtolower(trim($s));
    return str_replace(
        ['é','è','ê','ë','à','â','î','ï','ô','û','ù','ç'],
        ['e','e','e','e','a','a','i','i','o','u','u','c'],
        $s
    );
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Swaply - Réclamations</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>

<header class="bg-white shadow-sm sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-8 py-5 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <div class="w-9 h-9 bg-teal-500 rounded-2xl flex items-center justify-center text-white font-bold text-2xl">S</div>
      <h1 class="text-2xl font-bold text-gray-800">Swaply</h1>
    </div>
    <nav class="flex items-center gap-8 text-sm font-medium">
      <a href="index.php" class="nav-link">Accueil</a>
      <a href="profils.html" class="nav-link">Profils</a>
      <a href="projets.html" class="nav-link">Projets</a>
      <a href="offres.html" class="nav-link">Offres</a>
      <a href="demandes.html" class="nav-link">Demandes</a>
      <a href="publications.html" class="nav-link">Publications</a>
      <a href="messages.html" class="nav-link">Messages</a>
      <a href="reclamations.php" class="nav-link active">Réclamations</a>
    </nav>
    <div class="w-10 h-10 bg-teal-100 rounded-2xl overflow-hidden border-2 border-white shadow">
      <img src="https://i.pravatar.cc/150?img=68" class="w-full h-full object-cover">
    </div>
  </div>
</header>

<main class="max-w-5xl mx-auto px-8 py-10">

  <div class="flex justify-between items-center mb-8">
    <h2 class="text-2xl font-semibold text-gray-800">Mes Réclamations</h2>
    <a href="add_reclamation.php"
       class="bg-teal-500 text-white px-5 py-2 rounded-xl hover:bg-teal-600 transition shadow">
       + Ajouter Réclamation
    </a>
  </div>

  <div class="grid gap-6">

  <?php foreach($recs as $rec): 
    $sNorm = normalizeStatut($rec['statut'] ?? 'en attente');

    if (str_contains($sNorm, 'traite')) {
        $badgeClass = 'bg-green-100 text-green-600';
        $dot        = 'bg-green-500';
    } elseif (str_contains($sNorm, 'cours')) {
        $badgeClass = 'bg-orange-100 text-orange-500';
        $dot        = 'bg-orange-400';
    } else {
        $badgeClass = 'bg-red-100 text-red-500';
        $dot        = 'bg-red-400';
    }
  ?>

  <div class="card-hover bg-white p-6 rounded-3xl shadow-sm">

    <div class="flex items-start justify-between gap-4">
      <h3 class="text-lg font-semibold text-gray-800">
        <?= htmlspecialchars($rec['description']) ?>
      </h3>

      <!-- Badge statut coloré -->
      <span class="<?= $badgeClass ?> text-xs font-semibold px-3 py-1 rounded-full whitespace-nowrap flex items-center gap-1.5">
        <span class="w-1.5 h-1.5 rounded-full <?= $dot ?>"></span>
        <?= htmlspecialchars($rec['statut'] ?? 'en attente') ?>
      </span>
    </div>

    <div class="mt-4 flex justify-between items-center">
      <span class="text-sm text-gray-400"><?= htmlspecialchars($rec['date_creation']) ?></span>
      <a href="detail_reclamation.php?id=<?= $rec['id_reclamation'] ?>"
         class="text-teal-500 hover:underline text-sm">
         Voir détails →
      </a>
    </div>

  </div>

  <?php endforeach; ?>

  </div>

</main>

<script src="../../assets/js/main.js"></script>
</body>
</html>