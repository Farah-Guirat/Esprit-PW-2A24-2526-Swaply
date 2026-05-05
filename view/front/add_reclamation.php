<?php
require_once '../../controller/ReclamationController.php';

if (isset($_POST['submit'])) {
    $controller = new ReclamationController();

    $controller->ajouter(
        1,
        $_POST['description'],
        $_POST['rating'],
        $_POST['type'],
        $_POST['username_cible'] ?? ''
    );

    header("Location: reclamations.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Swaply - Nouvelle Réclamation</title>
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

<main class="max-w-3xl mx-auto py-16 px-8">

    <div class="bg-white p-10 rounded-3xl shadow">

        <h2 class="text-2xl font-bold mb-6">Nouvelle Réclamation</h2>

        <form method="POST">

            <!-- Type -->
            <label class="block mb-2 font-medium text-gray-700">Type</label>
            <select name="type" class="w-full p-3 border rounded-xl mb-4 focus:outline-none focus:ring-2 focus:ring-teal-400">
                <option value="person">Personne</option>
                <option value="company">Entreprise</option>
            </select>

            <!-- Username ciblé -->
            <label class="block mb-2 font-medium text-gray-700">Nom d'utilisateur concerné</label>
            <div class="relative mb-6">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-teal-500 font-bold select-none text-base">@</span>
                <input
                    type="text"
                    name="username_cible"
                    required
                    class="w-full pl-9 pr-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-400"
                    placeholder="username"
                    oninput="this.value = this.value.replace(/[@\s]/g, '')"
                    title="Sans @ ni espace"
                >
            </div>

            <!-- Description -->
            <label class="block mb-2 font-medium text-gray-700">Description</label>
            <textarea
                name="description"
                required
                class="w-full p-4 border rounded-xl mb-6 focus:outline-none focus:ring-2 focus:ring-teal-400"
                placeholder="Décrivez votre problème..."
                rows="4"
            ></textarea>

            <!-- Note -->
            <label class="block mb-2 font-medium text-gray-700">Note</label>
            <select name="rating" class="w-full p-3 border rounded-xl mb-6 focus:outline-none focus:ring-2 focus:ring-teal-400">
                <option value="1">⭐</option>
                <option value="2">⭐⭐</option>
                <option value="3">⭐⭐⭐</option>
                <option value="4">⭐⭐⭐⭐</option>
                <option value="5">⭐⭐⭐⭐⭐</option>
            </select>

            <button
                type="submit"
                name="submit"
                class="bg-teal-500 text-white px-6 py-3 rounded-xl hover:bg-teal-600 transition font-medium"
            >
                Envoyer
            </button>

        </form>
        
    </div>

</main>

</body>
</html>