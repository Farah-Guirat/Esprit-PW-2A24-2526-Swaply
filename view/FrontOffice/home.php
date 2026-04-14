<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Swaply - Accueil</title>
  
  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  
  <!-- Custom CSS -->
  <link rel="stylesheet" href="../src/assets/css/style.css">
  
  <style>
    .hero-bg {
      background: linear-gradient(135deg, #14b8a6, #0f766e);
    }
  </style>
</head>
<body>

  <!-- Header -->
  <header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-8 py-5 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 bg-teal-500 rounded-2xl flex items-center justify-center text-white font-bold text-2xl">S</div>
        <h1 class="text-2xl font-bold text-gray-800">Swaply</h1>
      </div>

      <nav class="flex items-center gap-8 text-sm font-medium">
        <a href="index.html" class="nav-link active">Accueil</a>
        <a href="profils.html" class="nav-link">Profils</a>
        <a href="projets.html" class="nav-link">Projets</a>
        <a href="index.php?action=choicee" class="nav-link">Offres</a>
        <a href="demandes.html" class="nav-link">Demandes</a>
        <a href="publications.html" class="nav-link">Publications</a>
        <a href="messages.html" class="nav-link">Messages</a>
        <a href="reclamations.html" class="nav-link">Réclamations</a>
      </nav>

      <div class="w-10 h-10 bg-teal-100 rounded-2xl overflow-hidden border-2 border-white shadow">
        <img src="https://i.pravatar.cc/150?img=68" alt="Profil" class="w-full h-full object-cover">
      </div>
    </div>
  </header>

  <main class="max-w-7xl mx-auto px-8 py-10">

    <!-- Hero Section -->
    <div class="hero-bg rounded-3xl p-12 text-white mb-12">
      <div class="max-w-2xl">
        <h2 class="text-5xl font-bold leading-tight">Échangez, collaborez et développez<br>vos projets ensemble</h2>
        <p class="mt-4 text-teal-100 text-lg">La plateforme pour connecter talents, projets et opportunités.</p>
        <div class="mt-8 flex gap-4">
          <a href="profils.html" class="bg-white text-teal-700 px-8 py-4 rounded-3xl font-semibold text-lg shadow-lg hover:shadow-xl transition">
            Explorer les profils
          </a>
          <a href="projets.html" class="border border-white/70 px-8 py-4 rounded-3xl font-semibold text-lg hover:bg-white/10 transition">
            Voir les projets
          </a>
        </div>
      </div>
    </div>

    <!-- Nos sections -->
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Nos sections</h2>
    
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">

      <a href="profils.html" class="card-hover bg-white rounded-3xl p-6 border border-transparent hover:border-teal-200">
        <div class="w-14 h-14 bg-teal-100 text-teal-600 rounded-2xl flex items-center justify-center text-3xl mb-5">👥</div>
        <h3 class="text-xl font-semibold">Profils</h3>
        <p class="text-gray-500 mt-2">Découvrez les membres de la communauté</p>
      </a>

      <a href="projets.html" class="card-hover bg-white rounded-3xl p-6 border border-transparent hover:border-teal-200">
        <div class="w-14 h-14 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center text-3xl mb-5">📁</div>
        <h3 class="text-xl font-semibold">Projets</h3>
        <p class="text-gray-500 mt-2">Explorez les projets et réalisations</p>
      </a>

<a href="index.php?action=choicee" class="card-hover bg-white rounded-3xl p-6 border border-transparent hover:border-teal-200">        <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-3xl mb-5">💼</div>
        <h3 class="text-xl font-semibold">Offres</h3>
        <p class="text-gray-500 mt-2">Consultez les offres de collaboration</p>
      </a>

      <a href="demandes.html" class="card-hover bg-white rounded-3xl p-6 border border-transparent hover:border-teal-200">
        <div class="w-14 h-14 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center text-3xl mb-5">📢</div>
        <h3 class="text-xl font-semibold">Demandes</h3>
        <p class="text-gray-500 mt-2">Trouvez les demandes de services</p>
      </a>

      <a href="publications.html" class="card-hover bg-white rounded-3xl p-6 border border-transparent hover:border-teal-200">
        <div class="w-14 h-14 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center text-3xl mb-5">📰</div>
        <h3 class="text-xl font-semibold">Publications</h3>
        <p class="text-gray-500 mt-2">Actualités et partages de la communauté</p>
      </a>

      <a href="messages.html" class="card-hover bg-white rounded-3xl p-6 border border-transparent hover:border-teal-200">
        <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center text-3xl mb-5">💬</div>
        <h3 class="text-xl font-semibold">Messages</h3>
        <p class="text-gray-500 mt-2">Vos conversations privées</p>
      </a>

      <a href="reclamations.html" class="card-hover bg-white rounded-3xl p-6 border border-transparent hover:border-teal-200">
        <div class="w-14 h-14 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center text-3xl mb-5">⚠️</div>
        <h3 class="text-xl font-semibold">Réclamations</h3>
        <p class="text-gray-500 mt-2">Signalez un problème ou une réclamation</p>
      </a>

    </div>

  </main>

  <!-- Custom JS -->
  <script src="../src/assets/main.js"></script>
</body>
</html>