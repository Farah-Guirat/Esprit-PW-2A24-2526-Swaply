<!DOCTYPE html>
<html>
<head>
  <title>Détails offre</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50">

<div class="max-w-3xl mx-auto p-8 bg-white mt-10 rounded-2xl shadow">

  <h1 class="text-3xl font-bold">
    <?= $offre->getTitre() ?>
  </h1>

  <p class="mt-4 text-gray-600">
    <?= $offre->getDescription() ?>
  </p>

  <div class="mt-6 space-y-2">
    <p><b>Catégorie:</b> <?= $offre->getCategorie() ?></p>
    <p><b>Niveau:</b> <?= $offre->getNiveau() ?></p>
    <p><b>Statut:</b> <?= $offre->getStatut() ?></p>
    <p><b>Date limite:</b> <?= $offre->getDateLimite()?->format('Y-m-d') ?></p>
    <p><b>Vues:</b> <?= $offre->getVues() ?></p>
  </div>


  <h2 class="mt-8 text-xl font-bold">Demandes correspondantes</h2>

<?php if (!empty($demandes)): ?>

  <?php foreach ($demandes as $d): ?>

    <div class="mt-4 p-4 bg-gray-100 rounded-lg shadow-sm">
      
      <h3 class="font-semibold text-lg">
        <?= htmlspecialchars($d['titre']) ?>
      </h3>

      <p class="text-gray-600">
        <?= htmlspecialchars($d['description']) ?>
      </p>

      <p class="text-sm text-gray-500 mt-2">
        <?= htmlspecialchars($d['categorie']) ?> | 
        <?= htmlspecialchars($d['niveau']) ?>
      </p>

    </div>

  <?php endforeach; ?>

<?php else: ?>

  <p class="mt-4 text-gray-500 italic">
    Aucune demande correspondante
  </p>

<?php endif; ?>

  <a href="index.php?action=list"
     class="mt-6 inline-block bg-gray-300 px-4 py-2 rounded-xl">
    Retour
  </a>

</div>

</body>
</html>