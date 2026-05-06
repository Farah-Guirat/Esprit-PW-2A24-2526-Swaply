<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Swaply - Offres</title>
      <link rel="stylesheet" href="../src/assets/css/style.css">
      <link rel="stylesheet" href="../src/assets/css/offres.css">

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
       <a href="/swaply/public/index.php?action=choice">Demandes</a>
<a href="/swaply/public/index.php?action=choicee"   class="nav-link active">Offres</a>
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
           placeholder="🔍 Rechercher une offre..."
           class="w-full md:flex-1 border rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-400">

    <select id="filterSelect"
            class="w-full md:w-64 border rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-400">

      <option value="">Tous les statuts</option>
      <option value="active">Active</option>
      <option value="inactive">Inactive</option>
      <option value="bloque">Bloqué</option>

    </select>

  </div>

  <!-- OFFERS GRID -->
  <div class="grid md:grid-cols-2 gap-6">

    <?php foreach ($offres as $a): ?>

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
          <p>📅 <?= $a->getDateLimite()?->format('Y-m-d') ?></p>
        </div>

        <div class="mt-6 flex gap-3">

          <a href="index.php?action=show&id=<?= $a->getIdOffre() ?>"
             class="flex-1 bg-teal-500 hover:bg-teal-600 text-white py-3 rounded-2xl text-center shadow-md hover:shadow-xl transition">
            Voir détails
          </a>
          <button onclick="shareOffer(<?= $a->getIdOffre() ?>, '<?= addslashes($a->getTitre()) ?>')"
        class="flex-1 bg-yellow-200 text-yellow-900 py-3 rounded-2xl text-center">
  Share
</button>
          <?php if ($a->getIdU() == $_SESSION['user']['id_u']): ?>


          <a href="index.php?action=edit&id=<?= $a->getIdOffre() ?>"
             class="flex-1 bg-blue-200 text-blue-900 py-3 rounded-2xl text-center shadow-md hover:shadow-lg transition">
            Modifier
          </a>

          <button onclick="openDeleteModal(<?= $a->getIdOffre() ?>)"
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
        <a href="?action=list&page=<?= $page - 1 ?>" class="px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600">Précédent</a>
      <?php endif; ?>

      <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
        <a href="?action=list&page=<?= $i ?>" class="px-4 py-2 <?= $i == $page ? 'bg-teal-600 text-white' : 'bg-gray-200 text-gray-700' ?> rounded-lg hover:bg-teal-500 hover:text-white">
          <?= $i ?>
        </a>
      <?php endfor; ?>

      <?php if ($page < $total_pages): ?>
        <a href="?action=list&page=<?= $page + 1 ?>" class="px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600">Suivant</a>
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
        "index.php?action=delete&id=" + id;
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



function shareOffer(id, titre) {

    const url = window.location.origin + "/swaply/public/index.php?action=show&id=" + id;

    const text = "SWAPLY 🚀\n" + titre + "\n" + url;

    if (navigator.share) {

        navigator.share({
            title: "Swaply Offer",
            text: text,
            url: url
        }).catch(err => console.log(err));

    } else {

        //  fallback: copy link
        navigator.clipboard.writeText(url)
            .then(() => alert("Lien copié !"))
            .catch(() => alert("Impossible de copier"));
    }
}
</script>
<style>



  /* NAV LINKS */
.nav-link {
  position: relative;
  color: #374151;
  transition: all 0.3s ease;
}

.nav-link:hover {
  color: #14b8a6;
}

.nav-link::after {
  content: "";
  position: absolute;
  left: 0;
  bottom: -4px;
  width: 0%;
  height: 2px;
  background: #14b8a6;
  transition: width 0.3s ease;
}

.nav-link:hover::after {
  width: 100%;
}


/* HEADER */
header {
  backdrop-filter: blur(10px);
  background: rgba(255, 255, 255, 0.85);
}


/* SEARCH BAR */
#searchInput, #filterSelect {
  transition: all 0.3s ease;
}

#searchInput:focus, #filterSelect:focus {
  transform: scale(1.02);
}


/* OFFER CARD */
.offer-card {
  transition: all 0.3s ease;
  border: 1px solid transparent;
}

.offer-card:hover {
  transform: translateY(-8px) scale(1.01);
  box-shadow: 0 20px 40px rgba(0,0,0,0.1);
  border: 1px solid #14b8a6;
}


/* STATUS COLORS */
.offer-status {
  padding: 5px 10px;
  border-radius: 999px;
  font-size: 12px;
}

/* dynamique selon statut */
.offer-status:contains("active") {
  background: #d1fae5;
  color: #065f46;
}

.offer-status:contains("inactive") {
  background: #fef3c7;
  color: #92400e;
}

.offer-status:contains("bloqué"),
.offer-status:contains("bloque") {
  background: #fee2e2;
  color: #991b1b;
}


/* BUTTONS */
button, a {
  transition: all 0.3s ease;
}

button:hover, a:hover {
  transform: translateY(-2px);
}


/* MODAL */
#deleteModal {
  animation: fadeIn 0.3s ease;
}

#deleteModal > div {
  animation: popUp 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes popUp {
  from {
    transform: scale(0.8) translateY(20px);
    opacity: 0;
  }
  to {
    transform: scale(1) translateY(0);
    opacity: 1;
  }
}


/* PAGINATION */
nav a {
  transition: all 0.3s ease;
}

nav a:hover {
  transform: scale(1.1);
}


/* PROFILE IMAGE */
header .w-10 {
  transition: all 0.3s ease;
}

header .w-10:hover {
  transform: scale(1.1) rotate(3deg);
  box-shadow: 0 10px 20px rgba(0,0,0,0.15);
}


/* SCROLL SMOOTH */

</style>
</body>
</html>