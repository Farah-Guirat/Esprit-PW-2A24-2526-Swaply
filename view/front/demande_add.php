<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Swaply - Ajouter une offre</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="../src/assets/css/style.css">


  <style>
    .input-focus {
      transition: all 0.25s ease;
    }
    .input-focus:focus {
      box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.15);
      transform: translateY(-1px);
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateX(40px); }
      to { opacity: 1; transform: translateX(0); }
    }

    .animate-fade-in {
      animation: fadeIn 0.3s ease-out;
    }
  </style>
</head>

<body class="bg-gradient-to-br from-gray-50 via-white to-teal-50 min-h-screen">

<!-- Header -->
  <?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$photo = $_SESSION['user']['photo'] ?? null;
?>


<!-- Header -->
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
          <img src="/swaply/uploads/profiles/<?= htmlspecialchars($photo) ?>">>" alt="Profil" class="w-full h-full object-cover" style="width: 100%; height: 100%; object-fit: cover; display: block;">
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

<!-- CONTENT -->
<div class="max-w-3xl mx-auto px-4 py-12">

  <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">

    <!-- HEADER -->
    <div class="bg-gradient-to-r from-teal-600 to-cyan-600 px-8 py-10 text-white">
      <h1 class="text-3xl font-bold">Créer une nouvelle demande</h1>
      <p class="text-teal-100 mt-1">Publiez une opportunité en quelques secondes</p>
    </div>

    <!-- FORM -->
    <form id="demForm" method="POST" action="index.php?action=ajoutd" class="p-8 space-y-6">

      <!-- TITRE -->
      <div>
        <label class="text-sm font-semibold text-gray-700">Titre de la demande</label>
        <input id="titre" name="titre" type="text"
               class="input-focus w-full mt-2 px-4 py-3 rounded-2xl bg-gray-50 border border-gray-200"
               placeholder="Ex: Développeur Full Stack Senior">
        <small id="error-titre" class="text-red-500 text-sm"></small>
      </div>

      <!-- DESCRIPTION -->
      <div>
        <label class="text-sm font-semibold text-gray-700">Description</label>
        <textarea id="description" name="description" rows="5"
                  class="input-focus w-full mt-2 px-4 py-3 rounded-2xl bg-gray-50 border border-gray-200"
                  placeholder="Décris le poste..."></textarea>
        <small id="error-description" class="text-red-500 text-sm"></small>
      </div>

      <!-- GRID -->
      <div class="grid md:grid-cols-2 gap-5">

        <div>
          <label class="text-sm font-semibold text-gray-700">Catégorie</label>
          <select id="categorie" name="categorie"
                  class="input-focus w-full mt-2 px-4 py-3 rounded-2xl bg-gray-50 border border-gray-200">
            <option value="">Sélectionner</option>
            <option>Développement</option>
            <option>Design</option>
            <option>Marketing</option>
            <option>Data</option>
          </select>
          <small id="error-categorie" class="text-red-500 text-sm"></small>
        </div>

        <div>
          <label class="text-sm font-semibold text-gray-700">Niveau</label>
          <select id="niveau" name="niveau"
                  class="input-focus w-full mt-2 px-4 py-3 rounded-2xl bg-gray-50 border border-gray-200">
            <option value="">Sélectionner</option>
            <option>Débutant</option>
            <option>Intermédiaire</option>
                        <option>Senior</option>

            <option>Expert</option>
          </select>
          <small id="error-niveau" class="text-red-500 text-sm"></small>
        </div>

      </div>

      

      <!-- BUTTON -->
      <button id="submitBtn" type="submit"
              class="w-full mt-6 bg-gradient-to-r from-teal-600 to-cyan-600 text-white py-4 rounded-2xl font-semibold">
        <i class="fa-solid fa-paper-plane"></i> Publier la demande
      </button>

    </form>
  </div>
</div>

<!-- LOADER -->
<div id="loader"
     class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">

  <div class="bg-white px-8 py-6 rounded-2xl shadow-xl flex items-center gap-4">
    <div class="w-10 h-10 border-4 border-teal-500 border-t-transparent rounded-full animate-spin"></div>
    <div class="text-gray-700 font-medium">Publication en cours...</div>
  </div>
</div>

<!-- TOAST -->
<div id="toast-container"
     class="fixed top-6 right-6 space-y-3 z-50"></div>

<!-- JS -->
<script src="../src/assets/demande_add.js"></script>

</body>
</html>