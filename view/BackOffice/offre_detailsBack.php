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

<a href="index.php?action=dashboard"
"
     class="mt-6 inline-block bg-gray-300 px-4 py-2 rounded-xl">
    Retour
  </a>

</div>

</body>
</html>