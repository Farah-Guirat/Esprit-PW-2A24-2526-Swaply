<!DOCTYPE html>
<html>
<head>
  <title>Détails demande</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-gray-100 to-gray-200 min-h-screen flex items-center justify-center">

<div class="w-full max-w-3xl bg-white rounded-3xl shadow-xl p-8">

  <!-- Header -->
  <div class="flex items-start justify-between border-b pb-4">
    <h1 class="text-3xl font-bold text-gray-800">
      <?= htmlspecialchars($demande->getTitre()) ?>
    </h1>

    <!-- Badge statut -->
    <?php
      $statut = strtolower($demande->getStatut());
      $color = match($statut) {
        'active' => 'bg-green-100 text-green-700',
        'pending' => 'bg-yellow-100 text-yellow-700',
        'inactive' => 'bg-gray-200 text-gray-700',
        default => 'bg-blue-100 text-blue-700'
      };
    ?>

    <span class="px-3 py-1 rounded-full text-sm font-semibold <?= $color ?>">
      <?= htmlspecialchars($demande->getStatut()) ?>
    </span>
  </div>

  <!-- Description -->
  <div class="mt-6">
    <h2 class="text-lg font-semibold text-gray-700 mb-2">Description</h2>
    <p class="text-gray-600 leading-relaxed">
      <?= nl2br(htmlspecialchars($demande->getDescription())) ?>
    </p>
  </div>

  <!-- Infos grid -->
  <div class="grid grid-cols-2 gap-4 mt-8">

    <div class="bg-gray-50 p-4 rounded-xl">
      <p class="text-sm text-gray-500">Catégorie</p>
      <p class="font-semibold text-gray-800">
        <?= htmlspecialchars($demande->getCategorie()) ?>
      </p>
    </div>

    <div class="bg-gray-50 p-4 rounded-xl">
      <p class="text-sm text-gray-500">Niveau</p>
      <p class="font-semibold text-gray-800">
        <?= htmlspecialchars($demande->getNiveau()) ?>
      </p>
    </div>

    <div class="bg-gray-50 p-4 rounded-xl col-span-2">
      <p class="text-sm text-gray-500">Date de création</p>
      <p class="font-semibold text-gray-800">
        <?= $demande->getDateCreation()?->format('Y-m-d') ?>
      </p>
    </div>

  </div>

  <!-- Button -->
  <div class="mt-8 flex justify-end">
    <a href="index.php?action=showd"
       class="bg-gray-800 text-white px-5 py-2 rounded-xl hover:bg-gray-700 transition">
      ← Retour
    </a>
  </div>

</div>

</body>
</html>