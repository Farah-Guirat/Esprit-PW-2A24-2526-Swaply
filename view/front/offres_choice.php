
<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$photo = $_SESSION['user']['photo'] ?? null;
?>




<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Swaply - Offres</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
      <link rel="stylesheet" href="../src/assets/css/style.css">


  <style>
    .choice-card {
      transition: all 0.3s ease;
    }

    .choice-card:hover {
      transform: translateY(-8px);
    }
  </style>
</head>

<body class="bg-gray-50 min-h-screen">

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
<!-- MAIN -->
<main class="max-w-5xl mx-auto px-8 py-24">

  <!-- TITLE -->
  <div class="text-center mb-16">
    <h1 class="text-5xl font-bold text-gray-800">Offres</h1>
    <p class="text-gray-500 mt-5 text-xl">Choisissez ce que vous voulez faire</p>
  </div>

  <!-- CARDS -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-10 max-w-4xl mx-auto">

    <!-- EXPLORER -->
    <a href="index.php?action=list"
       class="choice-card group bg-gradient-to-br from-teal-500 to-cyan-600 text-white rounded-3xl p-16 flex flex-col items-center justify-center text-center min-h-[420px] shadow-lg">

      <div class="w-28 h-28 bg-white/25 backdrop-blur-xl rounded-3xl flex items-center justify-center text-7xl mb-10 group-hover:scale-110 transition">
        🔍
      </div>

      <h2 class="text-4xl font-semibold mb-3">Explorer</h2>
      <p class="text-teal-100 text-lg max-w-xs">
        Parcourir toutes les offres disponibles
      </p>

    </a>

    <!-- PUBLIER -->
    <a href="index.php?action=add"
       class="choice-card group bg-gradient-to-br from-blue-600 to-indigo-700 text-white rounded-3xl p-16 flex flex-col items-center justify-center text-center min-h-[420px] shadow-lg">

      <div class="w-28 h-28 bg-white/25 backdrop-blur-xl rounded-3xl flex items-center justify-center text-7xl mb-10 group-hover:scale-110 transition">
        ✍️
      </div>

      <h2 class="text-4xl font-semibold mb-3">Publier</h2>
      <p class="text-blue-100 text-lg max-w-xs">
        Publier une nouvelle offre
      </p>

    </a>

  </div>

</main>

</body>
</html>