<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  
  <title>Swaply - Demandes</title>
  <link rel="stylesheet" href="../src/assets/css/style.css">

  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50">

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
        <a href="index.html" class="nav-link ">Accueil</a>
        <a href="profils.html" class="nav-link">Profils</a>
        <a href="projets.html" class="nav-link">Projets</a>
       <a href="/swaply/public/index.php?action=choice"  class="nav-link active">Demandes</a>
<a href="/swaply/public/index.php?action=choicee">Offres</a>
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

<main class="max-w-6xl mx-auto p-6">

  <!--  SEARCH + FILTER -->
  <div class="mb-6 bg-white p-4 rounded-2xl shadow flex flex-col md:flex-row gap-4">

    <input type="text"
           id="searchInput"
           placeholder="🔍 Rechercher une demande..."
           class="w-full md:flex-1 border rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-400">

    <select id="filterSelect"
            class="w-full md:w-64 border rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-400">

      <option value="">Tous les statuts</option>
      <option value="active">Active</option>
      <option value="inactive">Inactive</option>
      <option value="bloque">Bloqué</option>

    </select>

  </div>

  <!-- DEMANDES GRID -->
  <div class="grid md:grid-cols-2 gap-6">

    <?php foreach ($demandes as $a): ?>

      <div class="bg-white rounded-3xl p-8 shadow-sm offer-card">

        <div class="flex justify-between items-start">
          <h2 class="text-xl font-bold">
            <?= htmlspecialchars($a->getTitre()) ?>
          </h2>

          <span class="text-gray-600 font-bold offer-status">
            <?= htmlspecialchars($a->getStatut()) ?>
          </span>
        </div>

        <p class="text-gray-600 mt-3">
          <?= htmlspecialchars($a->getDescription()) ?>
        </p>

        <div class="mt-4 text-sm space-y-1">
          <p>📂 <?= htmlspecialchars($a->getCategorie()) ?></p>
          <p>🎯 <?= htmlspecialchars($a->getNiveau()) ?></p>
          <p>📅 <?= $a->getDateCreation()?->format('Y-m-d') ?></p>
        </div>

        <div class="mt-6 flex gap-3">

          <a href="index.php?action=showd&id=<?= $a->getIdDemande() ?>"
             class="flex-1 bg-teal-500 hover:bg-teal-600 text-white py-3 rounded-2xl text-center shadow-md hover:shadow-xl transition">
            Voir détails
          </a>

          <a href="#"
   onclick="sharedemande(<?= $a->getIdDemande() ?>, '<?= addslashes($a->getTitre()) ?>')"
   class="flex-1 bg-yellow-200 text-yellow-900 py-3 rounded-2xl text-center">
  Share
</a>


          
<?php if ($a->getIdU() == $_SESSION['user']['id_u']): ?>
  
          <a href="index.php?action=editd&id=<?= $a->getIdDemande() ?>"
             class="flex-1 bg-blue-200 text-blue-900 py-3 rounded-2xl text-center shadow-md hover:shadow-lg transition">
            Modifier
          </a>

          <button onclick="openDeleteModal(<?= $a->getIdDemande() ?>)"
                  class="flex-1 bg-red-200 text-red-900 py-3 rounded-2xl text-center shadow-md hover:bg-red-300 hover:shadow-xl transition">
            Supprimer
          </button>
          <?php endif; ?>

        </div>

      </div>

    <?php endforeach; ?>

  </div>
 <!-- PAGINATION -->
  <div class="mt-8 flex justify-center">
    <nav class="flex items-center space-x-2">
      <?php if ($page > 1): ?>
        <a href="?action=listd&page=<?= $page - 1 ?>" class="px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600">Précédent</a>
      <?php endif; ?>

      <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
        <a href="?action=listd&page=<?= $i ?>" class="px-4 py-2 <?= $i == $page ? 'bg-teal-600 text-white' : 'bg-gray-200 text-gray-700' ?> rounded-lg hover:bg-teal-500 hover:text-white">
          <?= $i ?>
        </a>
      <?php endfor; ?>

      <?php if ($page < $total_pages): ?>
        <a href="?action=listd&page=<?= $page + 1 ?>" class="px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600">Suivant</a>
      <?php endif; ?>
    </nav>
   </div>
</main>

<!-- MODAL DELETE -->
<div id="deleteModal"
     class="hidden fixed inset-0 bg-black/60 flex items-center justify-center">

  <div class="bg-white p-6 rounded-2xl w-96 text-center">

    <h2 class="text-xl font-bold text-red-600">
      Confirmer suppression ?
    </h2>

    <p class="text-gray-500 mt-2">
      Cette action est irréversible.
    </p>

    <div class="flex gap-3 mt-6">

      <button onclick="closeModal()"
              class="flex-1 bg-gray-300 py-2 rounded-xl">
        Annuler
      </button>

      <a id="confirmDeleteBtn"
         class="flex-1 bg-red-500 text-white py-2 rounded-xl">
        Supprimer
      </a>

    </div>

  </div>
</div>

<!-- SCRIPT -->
<script>
let deleteId = null;

function openDeleteModal(id) {
    deleteId = id;

    document.getElementById('deleteModal').classList.remove('hidden');

    document.getElementById('confirmDeleteBtn').href =
        "index.php?action=deleted&id=" + id;
}

function closeModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

/*  SEARCH + FILTER */
const searchInput = document.getElementById("searchInput");
const filterSelect = document.getElementById("filterSelect");
const cards = document.querySelectorAll(".offer-card");

function filterOffers() {

    const search = searchInput.value.toLowerCase();
    const filter = filterSelect.value.toLowerCase();

    cards.forEach(card => {

        const title = card.querySelector("h2").innerText.toLowerCase();
        const status = card.querySelector(".offer-status").innerText.toLowerCase();

        const matchSearch = title.includes(search);
        const matchFilter = filter === "" || status.includes(filter);

        card.style.display = (matchSearch && matchFilter) ? "block" : "none";
    });
}

searchInput.addEventListener("input", filterOffers);
filterSelect.addEventListener("change", filterOffers);


function sharedemande(id, titre) {

    const url = window.location.origin + "/swaply/public/index.php?action=show&id=" + id;

    const text = "SWAPLY 🚀\n" + titre + "\n" + url;

    if (navigator.share) {

        navigator.share({
            title: "Swaply Offer",
            text: text,
            url: url
        }).catch(err => console.log(err));

    } else {

        navigator.clipboard.writeText(url)
            .then(() => alert("Lien copié !"))
            .catch(() => alert("Impossible de copier"));
    }
}
</script>

</body>
</html>