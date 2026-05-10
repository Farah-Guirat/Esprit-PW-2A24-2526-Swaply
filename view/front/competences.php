<?php
require_once "../../model/competence.php";
$id_projet = isset($_GET['id_projet']) ? $_GET['id_projet'] : null;
if (!$id_projet) {
    $id_projet = 0;
}
$c = new Competence();
$data = ($id_projet > 0) ? $c->getByProjet($id_projet) : [];
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
        <a href="index.php" class="nav-link">Accueil</a>
        <a href="profils.html" class="nav-link">Profils</a>
        <a href="projets.php" class="nav-link active">Projets</a>
        <a href="offres.html" class="nav-link">Offres</a>
        <a href="demandes.html" class="nav-link">Demandes</a>
        <a href="publications.html" class="nav-link">Publications</a>
        <a href="messages.html" class="nav-link">Messages</a>
        <a href="reclamations.html" class="nav-link">Réclamations</a>
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


  <main class="max-w-7xl mx-auto px-8 py-6">

    <!-- HERO -->
    <div class="hero-bg rounded-3xl px-12 py-8 text-white mb-6">
      <div class="max-w-2xl">
        <a href="projets.php" class="inline-flex items-center gap-2 text-white/70 hover:text-white text-sm mb-2 transition">
          ← Retour aux projets
        </a>
        <h2 class="text-4xl font-bold leading-tight">Compétences du projet</h2>
        <p class="mt-2 text-gray-900 font-bold text-3xl">Ajoutez et gérez les compétences requises!</p>
      </div>
    </div>

    <!-- FORMULAIRE AJOUT -->
    <div class="bg-white rounded-3xl p-8 shadow-sm mb-8">
      <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
        <span class="w-8 h-8 bg-teal-100 text-teal-600 rounded-xl flex items-center justify-center text-sm">➕</span>
        Ajouter une compétence
      </h3>

      <form method="POST" action="../../controller/CompetenceController.php" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <input type="hidden" name="id_projet" value="<?= $id_projet ?>">
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Nom de la compétence</label>
          <input type="text" name="nom" placeholder="Ex: JavaScript, Design..."
            class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:outline-none focus:border-teal-400 text-sm">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Niveau</label>
          <input type="text" name="niveau" placeholder="Ex: Débutant, Expert..."
            class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:outline-none focus:border-teal-400 text-sm">
        </div>
        <div class="flex items-end">
          <button name="add"
            class="w-full bg-teal-500 hover:bg-teal-600 text-white px-8 py-3 rounded-2xl font-semibold text-sm transition border-0 cursor-pointer">
            Ajouter la compétence
          </button>
        </div>
      </form>
    </div>

    <!-- LISTE DES COMPETENCES -->
    <!-- RECHERCHE -->
    <div class="mb-6">
      <input 
        type="text" 
        id="searchInput"
        placeholder="🔍 Rechercher une compétence (nom, niveau)..."
        class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:outline-none focus:border-teal-400 text-sm"
      >
    </div>

    <h3 class="text-xl font-semibold text-gray-800 mb-6">Compétences du projet</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

      <?php foreach($data as $row) { ?>

      <div class="card-hover competence-card bg-white rounded-3xl p-6 border border-transparent hover:border-teal-200 shadow-sm"
        data-nom="<?= htmlspecialchars(strtolower($row['nom_competence']), ENT_QUOTES) ?>"
        data-niveau="<?= htmlspecialchars(strtolower($row['niveau']), ENT_QUOTES) ?>">

        <!-- EN-TÊTE -->
        <div class="flex items-start justify-between mb-4">
          <div class="w-12 h-12 bg-teal-100 text-teal-600 rounded-2xl flex items-center justify-center text-2xl">🎯</div>
          <span class="text-xs font-medium px-3 py-1 rounded-full bg-purple-100 text-purple-600">
            <?= $row['niveau'] ?>
          </span>
        </div>

        <!-- NOM -->
        <h4 class="text-lg font-semibold text-gray-800 mb-4"><?= $row['nom_competence'] ?></h4>

        <!-- ACTIONS -->
        <div class="flex gap-2 mb-0">
          <button onclick="toggleForm(<?= $row['id_competence'] ?>)"
            class="flex-1 flex items-center justify-center bg-amber-50 hover:bg-amber-100 text-amber-600 text-xs font-semibold px-3 py-2 rounded-xl transition border-0 cursor-pointer">
            ✏️ Modifier
          </button>
          <a href="../../controller/CompetenceController.php?delete=<?= $row['id_competence'] ?>"
            onclick="return confirm('Supprimer cette compétence ?')"
            class="flex-1 flex items-center justify-center bg-red-50 hover:bg-red-100 text-red-500 text-xs font-semibold px-3 py-2 rounded-xl transition">
            🗑 Supprimer
          </a>
        </div>

        <!-- FORM MODIFIER CACHÉ -->
        <form id="form-<?= $row['id_competence'] ?>" class="hidden mt-4 pt-4 border-t border-gray-100"
          method="POST" action="../../controller/CompetenceController.php">
          <input type="hidden" name="id" value="<?= $row['id_competence'] ?>">
          <input type="text" name="nom" value="<?= $row['nom_competence'] ?>"
            class="w-full px-3 py-2 mb-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-teal-400">
          <input type="text" name="niveau" value="<?= $row['niveau'] ?>"
            class="w-full px-3 py-2 mb-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-teal-400">
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
      let niveau = form.querySelector("[name='niveau']");
      if (!nom || !niveau) return;

      let valid = true;

      clearError(nom);
      clearError(niveau);

      if (nom.value.trim().length < 3) {
        showError(nom, "Le nom doit contenir au moins 3 caractères.");
        valid = false;
      }

      if (niveau.value.trim().length < 2) {
        showError(niveau, "Le niveau doit contenir au moins 2 caractères.");
        valid = false;
      }

      if (!valid) e.preventDefault();
    });
  });

  let searchInput = document.getElementById("searchInput");

  if (searchInput) {
    searchInput.addEventListener("keyup", function () {
      let value = this.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

      let cards = document.querySelectorAll(".competence-card");

      cards.forEach(card => {
        let nom    = (card.getAttribute("data-nom")    || "").normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        let niveau = (card.getAttribute("data-niveau") || "").normalize("NFD").replace(/[\u0300-\u036f]/g, "");

        if (nom.includes(value) || niveau.includes(value)) {
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