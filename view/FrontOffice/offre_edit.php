<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Swaply - Modifier l'offre</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

  <style>
    .input-focus {
      transition: all 0.25s ease;
    }
    .input-focus:focus {
      box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.15);
      transform: translateY(-1px);
    }
  </style>
</head>

<body class="bg-gradient-to-br from-gray-50 via-white to-teal-50 min-h-screen">

<!-- NAVBAR -->
<header class="bg-white/80 backdrop-blur-md shadow-sm sticky top-0 z-50 border-b border-gray-100">
  <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 bg-gradient-to-br from-teal-500 to-cyan-500 rounded-2xl flex items-center justify-center text-white font-bold text-xl shadow">
        S
      </div>
      <h1 class="text-xl font-bold text-gray-800">Swaply</h1>
    </div>

    <nav class="hidden md:flex items-center gap-8 text-sm font-medium">
      <a href="index.php?action=home" class="text-gray-600 hover:text-teal-600 transition">Accueil</a>
      <a href="index.php?action=list" class="text-gray-600 hover:text-teal-600 transition">Offres</a>
      <a href="#" class="text-gray-600 hover:text-teal-600 transition">Messages</a>
    </nav>

    <img src="https://i.pravatar.cc/150?img=68" class="w-10 h-10 rounded-2xl border shadow object-cover">
  </div>
</header>

<div class="max-w-3xl mx-auto px-4 py-12">
  <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">

    <div class="bg-gradient-to-r from-teal-600 to-cyan-600 px-8 py-10 text-white">
      <h1 class="text-3xl font-bold flex items-center gap-3">
        <i class="fa-solid fa-pen"></i>
        Modifier l'offre
      </h1>
      <p class="text-teal-100 mt-1">Mettez à jour votre offre</p>
    </div>

    <form id="offreForm" method="POST" action="index.php?action=update" class="p-8 space-y-6">
      
      <input type="hidden" name="id_offre" value="<?= htmlspecialchars($offre['id_offre']) ?>">

      <!-- Titre -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Titre de l'offre</label>
        <input id="titre" name="titre" type="text" value="<?= htmlspecialchars($offre['titre'] ?? '') ?>"
               class="input-focus w-full px-5 py-3.5 rounded-2xl bg-gray-50 border border-gray-200 focus:border-teal-500">
        <small id="error-titre" class="text-red-500 text-sm"></small>
      </div>

      <!-- Description -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
        <textarea id="description" name="description" rows="6"
                  class="input-focus w-full px-5 py-3.5 rounded-3xl bg-gray-50 border border-gray-200 focus:border-teal-500"><?= htmlspecialchars($offre['description'] ?? '') ?></textarea>
        <small id="error-description" class="text-red-500 text-sm"></small>
      </div>

      <div class="grid md:grid-cols-2 gap-6">
        <!-- Catégorie -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Catégorie</label>
          <select id="categorie" name="categorie" 
                  class="input-focus w-full px-5 py-3.5 rounded-2xl bg-gray-50 border border-gray-200 focus:border-teal-500">
            <option value="">Sélectionner une catégorie</option>
            <option value="Développement" <?= ($offre['categorie'] ?? '') === 'Développement' ? 'selected' : '' ?>>Développement</option>
            <option value="Design" <?= ($offre['categorie'] ?? '') === 'Design' ? 'selected' : '' ?>>Design</option>
            <option value="Marketing" <?= ($offre['categorie'] ?? '') === 'Marketing' ? 'selected' : '' ?>>Marketing</option>
            <option value="Data" <?= ($offre['categorie'] ?? '') === 'Data' ? 'selected' : '' ?>>Data & Analytics</option>
            <option value="Autre" <?= ($offre['categorie'] ?? '') === 'Autre' ? 'selected' : '' ?>>Autre</option>
          </select>
          <small id="error-categorie" class="text-red-500 text-sm"></small>
        </div>

        <!-- Niveau -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Niveau d'expérience</label>
          <select id="niveau" name="niveau"
                  class="input-focus w-full px-5 py-3.5 rounded-2xl bg-gray-50 border border-gray-200 focus:border-teal-500">
            <option value="">Sélectionner le niveau</option>
            <option value="Débutant" <?= ($offre['niveau'] ?? '') === 'Débutant' ? 'selected' : '' ?>>Débutant</option>

            <option value="Intermédiaire" <?= ($offre['niveau'] ?? '') === 'Intermédiaire' ? 'selected' : '' ?>>Intermédiaire</option>
            <option value="Expert" <?= ($offre['niveau'] ?? '') === 'Expert' ? 'selected' : '' ?>>Expert </option>
                        <option value="Expert" <?= ($offre['niveau'] ?? '') === 'Senior' ? 'selected' : '' ?>>Senior </option>

          </select>
          <small id="error-niveau" class="text-red-500 text-sm"></small>
        </div>
      </div>

      <!-- Date limite -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Date limite de candidature</label>
        <input type="date" id="dateLimite" name="date_limite" value="<?= $offre['date_limite'] ?? '' ?>"
               class="input-focus w-full px-5 py-3.5 rounded-2xl bg-gray-50 border border-gray-200 focus:border-teal-500">
        <small id="error-date" class="text-red-500 text-sm"></small>
      </div>

      <button id="submitBtn" type="submit"
        class="w-full bg-gradient-to-r from-teal-600 to-cyan-600 hover:from-teal-700 hover:to-cyan-700 text-white py-4 rounded-2xl font-semibold text-lg transition-all">
  Enregistrer les modifications
</button>
    </form>
  </div>
</div>






<div id="loader"
     class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">

  <div class="bg-white px-8 py-6 rounded-2xl shadow-xl flex items-center gap-4">
    <div class="w-10 h-10 border-4 border-teal-500 border-t-transparent rounded-full animate-spin"></div>
    <div class="text-gray-700 font-medium">Modification en cours...</div>
  </div>
</div>

<!-- TOAST -->
<div id="toast-container"
     class="fixed top-6 right-6 space-y-3 z-50"></div>

<script src="../src/assets/updateoffre.js"></script>
</body>
</html>