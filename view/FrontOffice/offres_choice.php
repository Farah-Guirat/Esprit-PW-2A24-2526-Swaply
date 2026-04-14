<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Swaply - Offres</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

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

<!-- HEADER -->
<header class="bg-white shadow-sm sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-8 py-5 flex items-center justify-between">

    <!-- LOGO -->
    <div class="flex items-center gap-3">
      <div class="w-9 h-9 bg-teal-500 rounded-2xl flex items-center justify-center text-white font-bold text-2xl">
        S
      </div>
      <h1 class="text-2xl font-bold text-gray-800">Swaply</h1>
    </div>

    <!-- NAV -->
    <nav class="flex items-center gap-8 text-sm font-medium">
      <a href="index.php?action=home" class="nav-link">Accueil</a>
      <a href="index.php?action=list" class="nav-link">Offres</a>
      <a href="#" class="nav-link">Messages</a>
    </nav>

    <!-- PROFILE -->
    <div class="w-10 h-10 bg-teal-100 rounded-2xl overflow-hidden border-2 border-white shadow">
      <img src="https://i.pravatar.cc/150?img=68" alt="Profil" class="w-full h-full object-cover">
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