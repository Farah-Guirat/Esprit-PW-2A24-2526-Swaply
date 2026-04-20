<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../src/assets/css/styles.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <style>
        .stat-card {
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
        }
        .status-badge {
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }
    </style>
</head>

<body class="bg-gray-50 font-sans">

<div class="flex h-screen overflow-hidden">

  <!-- SIDEBAR -->
  <div class="sidebar">
    <div class="logo">
      <span class="icon">📋</span>
      <h1>JobBoard Admin</h1>
    </div>

    <div class="menu">
      <a href="index.php?action=back" onclick="showPage('dashboard')" class="menu-item " id="menu-dashboard">
        <i class="fa-solid fa-house"></i> Dashboard
      </a>
      <a href="#" onclick="showPage('users')" class="menu-item" id="menu-users">
        <i class="fa-solid fa-users"></i> Utilisateurs
      </a>
      <a href="#" onclick="showPage('profiles')" class="menu-item" id="menu-profiles">
        <i class="fa-solid fa-user"></i> Profils & Portfolios
      </a>
      <a href="index.php?action=dashboard" onclick="showPage('offres')" class="menu-item active" id="menu-offres">
        <i class="fa-solid fa-briefcase"></i> Offres & Demandes
      </a>
      <a href="#" onclick="showPage('publications')" class="menu-item" id="menu-publications">
        <i class="fa-solid fa-newspaper"></i> Publications
      </a>
      <a href="#" onclick="showPage('conversations')" class="menu-item" id="menu-conversations">
        <i class="fa-solid fa-comment-dots"></i> Conversations
      </a>
      <a href="#" onclick="showPage('reclamations')" class="menu-item" id="menu-reclamations">
        <i class="fa-solid fa-exclamation-triangle"></i> Réclamations
      </a>
      <a href="#" onclick="showPage('stats')" class="menu-item" id="menu-stats">
        <i class="fa-solid fa-chart-bar"></i> Statistiques
      </a>
      <a href="#" onclick="showPage('settings')" class="menu-item" id="menu-settings">
        <i class="fa-solid fa-gear"></i> Paramètres
      </a>
    </div>
  </div>




<div class="p-8 max-w-7xl mx-auto">
    <div class="w-full flex justify-end">
    <button onclick="exportData()" 
        class="flex items-center gap-2 bg-white border border-gray-300 hover:bg-gray-50 px-4 py-2 rounded-xl text-sm font-medium">
        <i class="fa-solid fa-download"></i>
        Exporter
    </button>
</div>

<div class="w-full flex justify-start">
    <a href="index.php?action=dashboard"
       class="flex items-center gap-2 bg-white border border-gray-300 hover:bg-gray-50 px-4 py-2 rounded-xl text-sm font-medium">
        <i class="fa-solid fa-arrow-left"></i>
        Aller aux Offres
    </a>
</div>


    <!-- STATS -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <div class="stat-card bg-white p-6 rounded-2xl shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 text-sm">Total des offres</p>
                    <h2 class="text-4xl font-bold text-gray-800 mt-2"><?= $stats['total'] ?? 0 ?></h2>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center text-2xl">📊</div>
            </div>
        </div>

        <div class="stat-card bg-white p-6 rounded-2xl shadow border border-emerald-100">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 text-sm">Offres Actives</p>
                    <h2 class="text-4xl font-bold text-emerald-600 mt-2"><?= $stats['offresActives'] ?? 0 ?></h2>
                </div>
                <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center text-2xl">✅</div>
            </div>
        </div>

        <div class="stat-card bg-white p-6 rounded-2xl shadow border border-amber-100">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 text-sm">Demandes Actives</p>
                    <h2 class="text-4xl font-bold text-amber-600 mt-2"><?= $stats['demandesActives'] ?? 0 ?></h2>
                </div>
                <div class="w-12 h-12 bg-amber-100 rounded-2xl flex items-center justify-center text-2xl">📬</div>
            </div>
        </div>

        <div class="stat-card bg-white p-6 rounded-2xl shadow border border-red-100">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 text-sm">Expirées / Bloquées</p>
                    <h2 class="text-4xl font-bold text-red-600 mt-2"><?= $stats['expirees'] ?? 0 ?></h2>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-2xl flex items-center justify-center text-2xl">⛔</div>
            </div>
        </div>
    </div>

    <!-- FILTRES -->
    <div class="flex flex-col md:flex-row gap-4 mb-6">
        <div class="flex-1 relative">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-gray-400"></i>
            <input type="text" id="search" onkeyup="filterTable()" 
                   placeholder="Rechercher par titre..." 
                   class="w-full pl-11 pr-4 py-3 bg-white border border-gray-200 rounded-2xl focus:outline-none focus:border-emerald-500">
        </div>

        <select id="statusFilter" onchange="filterTable()" 
                class="bg-white border border-gray-200 rounded-2xl px-4 py-3 focus:outline-none focus:border-emerald-500">
            <option value="">Tous les statuts</option>
            <option value="active">Active</option>
            <option value="fermée">Fermée</option>
            <option value="expirée">Expirée</option>
            <option value="bloque">Bloquée</option>
        </select>
    </div>

    <!-- TABLE -->
    <div class="bg-white rounded-3xl shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="p-5 text-left">Titre</th>
                    <th class="p-5 text-center">Catégorie</th>
                    <th class="p-5 text-center">Niveau</th>
                    <th class="p-5 text-center">Date limite</th>
                    <th class="p-5 text-center">Statut</th>
                    <th class="p-5 text-center">Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php foreach ($demandes as $demande): 
    $statutLower = strtolower($demande->getStatut());
?>
<tr class="border-b hover:bg-gray-50 transition-colors" data-statut="<?= $statutLower ?>">

    <td class="p-5 font-medium">
        <?= htmlspecialchars($demande->getTitre()) ?>
    </td>

    <td class="p-5 text-center">
        <?= htmlspecialchars($demande->getCategorie()) ?>
    </td>

    <td class="p-5 text-center">
        <?= htmlspecialchars($demande->getNiveau()) ?>
    </td>

    <td class="p-5 text-center text-sm">
        <?= $demande->getDateCreation() 
            ? $demande->getDateCreation()->format('d/m/Y') 
            : '-' ?>
    </td>

    <td class="p-5 text-center">
        <?php
        if ($statutLower === 'active') {
            $badgeClass = 'bg-emerald-100 text-emerald-700';
        } elseif ($statutLower === 'fermée' || $statutLower === 'fermee') {
            $badgeClass = 'bg-orange-100 text-orange-700';
        } elseif ($statutLower === 'expirée' || $statutLower === 'expiree') {
            $badgeClass = 'bg-red-100 text-red-700';
        } elseif ($statutLower === 'bloque') {
            $badgeClass = 'bg-gray-100 text-gray-700';
        } else {
            $badgeClass = 'bg-gray-100 text-gray-700';
        }
        ?>
        <span class="<?= $badgeClass ?> status-badge">
            <?= htmlspecialchars($demande->getStatut()) ?>
        </span>
    </td>

    <td class="p-5 text-center">
        <div class="flex justify-center gap-4 text-lg">

            <!-- 👁️ DETAILS -->
            <a href="index.php?action=detailsdemande&id=<?= $demande->getIdDemande() ?>" 
               class="text-blue-600 hover:text-blue-700">
                <i class="fa-solid fa-eye"></i>
            </a>

            <!-- 🚫 BLOCK -->
            <button onclick="openBlockModal(<?= $demande->getIdDemande() ?>)" 
                    class="text-amber-600 hover:text-amber-700">
                <i class="fa-solid fa-ban"></i>
            </button>

            <!-- 🗑️ DELETE -->
            <button onclick="openDeleteModal(<?= $demande->getIdDemande() ?>)" 
                    class="text-red-600 hover:text-red-700">
                <i class="fa-solid fa-trash"></i>
            </button>

        </div>
    </td>

</tr>
<?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODALS -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50">
    <div class="bg-white rounded-3xl p-8 max-w-md w-full mx-4">
        <h3 class="text-xl font-semibold mb-2">Confirmer la suppression ?</h3>
        <p class="text-gray-600 mb-8">Cette action est irréversible.</p>
        <div class="flex gap-3">
            <button onclick="cancelDelete()" class="flex-1 py-3 border rounded-2xl hover:bg-gray-50">Annuler</button>
            <button onclick="confirmDelete()" class="flex-1 py-3 bg-red-600 text-white rounded-2xl hover:bg-red-700">Supprimer</button>
        </div>
    </div>
</div>

<div id="blockModal" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50">
    <div class="bg-white rounded-3xl p-8 max-w-md w-full mx-4">
        <h3 class="text-xl font-semibold mb-2" id="blockTitle">Bloquer cette offre ?</h3>
        <p class="text-gray-600 mb-8" id="blockMessage">L'offre ne sera plus visible publiquement.</p>
        <div class="flex gap-3">
            <button onclick="cancelBlock()" class="flex-1 py-3 border rounded-2xl hover:bg-gray-50">Annuler</button>
            <button onclick="confirmBlock()" class="flex-1 py-3 bg-amber-600 text-white rounded-2xl hover:bg-amber-700" id="blockBtn">Bloquer</button>
        </div>
    </div>
</div>

<script>
// Toastr config
toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: 3000 };

// Variables
let currentDeleteId = null;
let currentBlockId = null;

// Filtrage
function filterTable() {
    const search = document.getElementById('search').value.toLowerCase().trim();
    const status = document.getElementById('statusFilter').value.toLowerCase();

    document.querySelectorAll('#tableBody tr').forEach(row => {
        const title = row.cells[0].textContent.toLowerCase();
        const rowStatut = row.getAttribute('data-statut');

        const matchSearch = title.includes(search);
        const matchStatus = !status || rowStatut === status;

        row.style.display = (matchSearch && matchStatus) ? '' : 'none';
    });
}

// Modals Delete
function openDeleteModal(id) {
    currentDeleteId = id;
    document.getElementById('deleteModal').classList.remove('hidden');
}
function cancelDelete() {
    currentDeleteId = null;
    document.getElementById('deleteModal').classList.add('hidden');
}
function confirmDelete() {
window.location.href = `index.php?action=dashboard_delete_demande&id=${currentDeleteId}`;}

// Modals Block
function openBlockModal(id) {
    currentBlockId = id;
    document.getElementById('blockModal').classList.remove('hidden');
}
function cancelBlock() {
    currentBlockId = null;
    document.getElementById('blockModal').classList.add('hidden');
}
function confirmBlock() {
window.location.href = `index.php?action=dashboard_block_demande&id=${currentBlockId}`;}

// Export
function exportData() {
    toastr.info("Exportation en cours...");
}

// Toastr from PHP

// Animation
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('#tableBody tr').forEach((row, i) => {
        row.style.opacity = '0';
        row.style.transform = 'translateY(15px)';
        setTimeout(() => {
            row.style.transition = 'all 0.4s ease';
            row.style.opacity = '1';
            row.style.transform = 'translateY(0)';
        }, i * 40);
    });
});
</script>
</body>
</html>