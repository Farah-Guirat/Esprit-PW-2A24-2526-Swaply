<?php
require_once "../../model/projet.php";

$p = new Projet();
$data = $p->getAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Swaply - Projets</title>

  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/front.css">
</head>
<body>

  <!-- HEADER -->
  <header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-8 py-5 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 bg-teal-500 rounded-2xl flex items-center justify-center text-white font-bold text-2xl">S</div>
        <h1 class="text-2xl font-bold text-gray-800">Swaply</h1>
      </div>

      <nav class="flex items-center gap-8 text-sm font-medium">
        <a href="index.php" class="nav-link">Accueil</a>
        <a href="profils.html" class="nav-link">Profils</a>
        <a href="projets.php" class="nav-link active">Projets</a>
        <a href="offres.html" class="nav-link">Offres</a>
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



  <main class="max-w-7xl mx-auto px-8 py-6">

    <!-- HERO -->
    <div class="hero-bg rounded-3xl px-12 py-8 text-white mb-6">
      <div class="max-w-2xl">
        <h2 class="text-4xl font-bold leading-tight">Gérez vos projets et compétences</h2>
        <p class="mt-2 text-gray-900 font-bold text-3xl">Ajoutez, modifiez et suivez tous vos projets collaboratifs!</p>
      </div>
    </div>

    <!-- FORMULAIRE AJOUT -->
    <div class="bg-white rounded-3xl p-8 shadow-sm mb-8">
      <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
        <span class="w-8 h-8 bg-teal-100 text-teal-600 rounded-xl flex items-center justify-center text-sm">➕</span>
        Ajouter un projet
      </h3>

      <form method="POST" action="../../controller/ProjetController.php" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Nom du projet</label>
          <input type="text" name="nom" placeholder="Ex: Application mobile..."
            class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:outline-none focus:border-teal-400 text-sm">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Description</label>
          <input type="text" name="desc" placeholder="Décrivez le projet..."
            class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:outline-none focus:border-teal-400 text-sm">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Statut</label>
          <select name="statut"
            class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:outline-none focus:border-teal-400 text-sm bg-white">
            <option value="En cours">En cours</option>
            <option value="Terminé">Terminé</option>
          </select>
        </div>
        <div class="md:col-span-3 flex justify-end">
          <button name="add"
            class="bg-teal-500 hover:bg-teal-600 text-white px-8 py-3 rounded-2xl font-semibold text-sm transition">
            Ajouter le projet
          </button>
        </div>
      </form>
    </div>

    <!-- LISTE DES PROJETS -->
      <!-- RECHERCHE -->
<div class="mb-6">
  <input 
    type="text" 
    id="searchInput"
    placeholder="🔍 Rechercher un projet (nom, description, statut)..."
    class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:outline-none focus:border-teal-400 text-sm"
  >
</div>
    <h3 class="text-xl font-semibold text-gray-800 mb-6">Tous les projets</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

      <?php foreach($data as $row) { ?>

    <div class="card-hover project-card bg-white rounded-3xl p-6 border border-transparent hover:border-teal-200 shadow-sm"
     data-nom="<?= htmlspecialchars(strtolower($row['nom_projet']), ENT_QUOTES) ?>"
     data-desc="<?= htmlspecialchars(strtolower($row['description']), ENT_QUOTES) ?>"
     data-statut="<?= htmlspecialchars(strtolower($row['statut']), ENT_QUOTES) ?>">

        <!-- EN-TÊTE CARTE -->
        <div class="flex items-start justify-between mb-4">
          <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center text-2xl">📁</div>
          <span class="text-xs font-medium px-3 py-1 rounded-full
            <?= $row['statut'] === 'Terminé' ? 'bg-emerald-100 text-emerald-600' :
               ($row['statut'] === 'En cours' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-500') ?>">
            <?= $row['statut'] ?>
          </span>
        </div>

        <!-- NOM + DESCRIPTION -->
        <h4 class="text-lg font-semibold text-gray-800 mb-1"><?= $row['nom_projet'] ?></h4>
        <p class="text-sm text-gray-500 mb-4"><?= $row['description'] ?></p>

        <!-- COMPETENCES -->
        <div class="flex flex-wrap gap-2 mb-5">
          <?php
            $comps = $p->getCompetences($row['id_projet']);
            foreach($comps as $c) { ?>
              <span class="bg-teal-50 text-teal-600 text-xs px-3 py-1 rounded-full font-medium">
                <?= $c['nom_competence'] ?>
              </span>
          <?php } ?>
        </div>

        <!-- ACTIONS -->
        <div class="flex gap-2 flex-wrap">
          <a href="competences.php?id_projet=<?= $row['id_projet'] ?>"
            class="flex-1 text-center bg-teal-50 hover:bg-teal-100 text-teal-600 text-xs font-semibold px-3 py-2 rounded-xl transition">
            ➕ Compétences
          </a>
          <button onclick="toggleForm(<?= $row['id_projet'] ?>)"
            class="flex-1 bg-amber-50 hover:bg-amber-100 text-amber-600 text-xs font-semibold px-3 py-2 rounded-xl transition border-0 cursor-pointer">
            ✏️ Modifier
          </button>
          <a href="../../controller/ProjetController.php?delete=<?= $row['id_projet'] ?>"
            onclick="return confirm('Supprimer ce projet ?')"
            class="flex-1 text-center bg-red-50 hover:bg-red-100 text-red-500 text-xs font-semibold px-3 py-2 rounded-xl transition">
            🗑 Supprimer
          </a>
        </div>

        <!-- FORM MODIFIER CACHÉ -->
        <form id="form-<?= $row['id_projet'] ?>" class="hidden mt-4 pt-4 border-t border-gray-100"
          method="POST" action="../../controller/ProjetController.php">
          <input type="hidden" name="id" value="<?= $row['id_projet'] ?>">
          <input type="text" name="nom" value="<?= $row['nom_projet'] ?>"
            class="w-full px-3 py-2 mb-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-teal-400">
          <input type="text" name="desc" value="<?= $row['description'] ?>"
            class="w-full px-3 py-2 mb-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-teal-400">
          <select name="statut"
            class="w-full px-3 py-2 mb-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-teal-400 bg-white">
            <option value="En cours" <?= $row['statut'] === 'En cours' ? 'selected' : '' ?>>En cours</option>
            <option value="Terminé" <?= $row['statut'] === 'Terminé' ? 'selected' : '' ?>>Terminé</option>
          </select>
          <button name="update"
            class="w-full bg-teal-500 hover:bg-teal-600 text-white text-sm font-semibold py-2 rounded-xl transition border-0 cursor-pointer">
            ✔ Enregistrer
          </button>
        </form>

      </div>

      <?php } ?>

    </div>
    

  </main>

  <script>
function toggleForm(id) {
  let form = document.getElementById("form-" + id);
  form.classList.toggle("hidden");
}

function showError(input, message) {
  let existing = input.parentNode.querySelector(".error-msg");
  if (existing) existing.remove();
  input.classList.add("border-red-400");
  let msg = document.createElement("p");
  msg.className = "error-msg text-red-500 text-xs mt-1";
  msg.textContent = message;
  input.parentNode.appendChild(msg);
}

function clearError(input) {
  input.classList.remove("border-red-400");
  let existing = input.parentNode.querySelector(".error-msg");
  if (existing) existing.remove();
}

document.addEventListener("DOMContentLoaded", function () {
  let forms = document.querySelectorAll("form");
  forms.forEach(form => {
    form.addEventListener("submit", function(e) {
      let nom = form.querySelector("[name='nom']");
      let desc = form.querySelector("[name='desc']");
      if (!nom || !desc) return;

      let valid = true;

      clearError(nom);
      clearError(desc);

      if (nom.value.trim().length < 3) {
        showError(nom, "Le nom doit contenir au moins 3 caractères.");
        valid = false;
      }

      if (desc.value.trim().length < 10) {
        showError(desc, "La description doit contenir au moins 10 caractères.");
        valid = false;
      }

      if (!valid) e.preventDefault();
    });
  });

  let searchInput = document.getElementById("searchInput");

  if (searchInput) {
    searchInput.addEventListener("keyup", function () {
      let value = this.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

      let cards = document.querySelectorAll(".project-card");

      cards.forEach(card => {
        let nom    = (card.getAttribute("data-nom")    || "").normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        let desc   = (card.getAttribute("data-desc")   || "").normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        let statut = (card.getAttribute("data-statut") || "").normalize("NFD").replace(/[\u0300-\u036f]/g, "");

        if (nom.includes(value) || desc.includes(value) || statut.includes(value)) {
          card.style.display = "";
        } else {
          card.style.display = "none";
        }
      });
    });
  }
});
</script>
</body>
</html>