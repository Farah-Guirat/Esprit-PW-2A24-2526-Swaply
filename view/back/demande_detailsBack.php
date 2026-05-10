<?php
session_start();

require_once __DIR__ . "/../../config/Database.php";
require_once __DIR__ . "/../../model/User.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['email'] !== 'klai.aziz@admin.tn') {
    header("Location: ../front/login.php");
    exit();
}

$database = new Database();
$conn = $database->connect();
$userModel = new User($conn);
$totalUsers = $userModel->countUsersExceptAdmin('klai.aziz@admin.tn');
$adminPhoto = $_SESSION['user']['photo'] ?? null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la demande</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../src/assets/css/styles.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

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

        /* ============================================= */
        /* SIDEBAR STYLES */
        /* ============================================= */
       
      
        /* ============================================= */
        /* HEADER STYLES */
        /* ============================================= */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            background: #f8fafc;
        }
        .header {
            background: white;
            padding: 15px 25px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .search-box {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f1f5f9;
            padding: 8px 16px;
            border-radius: 40px;
        }
        .search-box input {
            background: transparent;
            border: none;
            outline: none;
            font-size: 14px;
            width: 200px;
        }
        .notifications {
            position: relative;
            cursor: pointer;
        }
        .notifications .badge {
            position: absolute;
            top: -6px;
            right: -10px;
            background: #ef4444;
            color: white;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 20px;
        }
        .user {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .user img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .user .name {
            font-weight: 600;
            font-size: 14px;
        }
        .user .role {
            font-size: 12px;
            color: #64748b;
        }
        .logout-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #fee2e2;
            color: #dc2626;
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
        }
        .logout-btn:hover {
            background: #fecaca;
        }

        /* ============================================= */
        /* DETAILS CARD STYLES */
        /* ============================================= */
        .details-container {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 35px -10px rgba(0,0,0,0.1);
            padding: 2rem;
            max-width: 900px;
            width: 100%;
            margin: 0 auto;
        }
        .info-box {
            background: #f8fafc;
            border-radius: 16px;
            padding: 1rem;
            transition: all 0.2s ease;
        }
        .info-box:hover {
            background: #f1f5f9;
            transform: translateY(-2px);
        }
    </style>
</head>

<body class="bg-gray-50 font-sans">

<div class="flex h-screen overflow-hidden">

    <!-- ============================================= -->
    <!-- SIDEBAR -->
    <!-- ============================================= -->
    <div class="sidebar">
        <div class="logo">
            <span class="icon">📋</span>
            <h1>JobBoard Admin</h1>
        </div>
        <div class="menu">
           <a href="/swaply/view/back/swaplyB.php" class="menu-item" id="menu-dashboard">
    <i class="fa-solid fa-house"></i> Dashboard
</a>
            <a href="/swaply/view/back/ProfilsB.php" class="menu-item" id="menu-profiles">
        <i class="fa-solid fa-user"></i> Profils
      </a>

       <a href="/swaply/view/back/projets.php" onclick="showPage('projets')" class="menu-item" id="menu-projets">
        <i class="fa-solid fa-file"></i> Projets
      </a>
            <a href="index.php?action=dashboard" class="menu-item" id="menu-offres">
                <i class="fa-solid fa-briefcase"></i> Offres
            </a>
            <a href="index.php?action=dashboardd" class="menu-item active" id="menu-demandes">
                <i class="fa-solid fa-file-signature"></i> Demandes
            </a>
            <a href="/swaply/view/back/publication_back.php" class="menu-item" id="menu-publications">
                <i class="fa-solid fa-newspaper"></i> Publications
            </a>
            <a href="#" class="menu-item" id="menu-conversations">
                <i class="fa-solid fa-comment-dots"></i> Conversations
            </a>
            <a href="#" class="menu-item" id="menu-reclamations">
                <i class="fa-solid fa-exclamation-triangle"></i> Réclamations
            </a>
            <a href="#" class="menu-item" id="menu-stats">
                <i class="fa-solid fa-chart-bar"></i> Statistiques
            </a>
            <a href="#" class="menu-item" id="menu-settings">
                <i class="fa-solid fa-gear"></i> Paramètres
            </a>
        </div>
    </div>

    <!-- ============================================= -->
    <!-- MAIN CONTENT -->
    <!-- ============================================= -->
    <div class="main-content">

        <!-- ============================================= -->
        <!-- HEADER -->
        <!-- ============================================= -->
        <header class="header" id="main-header">
            <h2 id="page-title">Détails de la demande</h2>
            <div class="header-right">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" placeholder="Rechercher...">
                </div>
                <div class="notifications">
                    <i class="fa-solid fa-bell"></i>
                    <span class="badge">7</span>
                </div>
                <div class="user">
                    <?php if ($adminPhoto): ?>
                        <img src="../../uploads/profiles/<?= htmlspecialchars($adminPhoto) ?>" alt="Admin">
                    <?php else: ?>
                        <img src="https://i.pravatar.cc/40?img=12" alt="Admin">
                    <?php endif; ?>
                    <div>
                        <p class="name">Admin</p>
                        <p class="role">Super Admin</p>
                    </div>
                </div>
                <a href="/swaply/controller/logout.php" class="logout-btn" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?');">
                    <i class="fa-solid fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </header>

        <!-- ============================================= -->
        <!-- PAGE CONTENT -->
        <!-- ============================================= -->
        <div class="flex-1 flex items-center justify-center p-8">
            <div class="details-container">

                <!-- Header with title and status -->
                <div class="flex items-start justify-between border-b pb-5 mb-5">
                    <h1 class="text-3xl font-bold text-gray-800">
                        <i class="fa-solid fa-file-lines text-blue-500 mr-3"></i>
                        <?= htmlspecialchars($demande->getTitre()) ?>
                    </h1>

                    <!-- Statut badge -->
                    <?php
                        $statut = strtolower($demande->getStatut());
                        $color = match($statut) {
                            'active' => 'bg-emerald-100 text-emerald-700',
                            'pending' => 'bg-yellow-100 text-yellow-700',
                            'fermée', 'fermee' => 'bg-orange-100 text-orange-700',
                            'expirée', 'expiree' => 'bg-red-100 text-red-700',
                            'bloque', 'bloquée' => 'bg-gray-100 text-gray-700',
                            default => 'bg-blue-100 text-blue-700'
                        };
                        $icon = match($statut) {
                            'active' => 'fa-check-circle',
                            'pending' => 'fa-clock',
                            'fermée', 'fermee' => 'fa-door-closed',
                            'expirée', 'expiree' => 'fa-calendar-times',
                            'bloque', 'bloquée' => 'fa-shield-haltered',
                            default => 'fa-info-circle'
                        };
                    ?>
                    <span class="px-4 py-2 rounded-full text-sm font-semibold <?= $color ?>">
                        <i class="fa-solid <?= $icon ?> mr-2"></i>
                        <?= htmlspecialchars($demande->getStatut()) ?>
                    </span>
                </div>

                <!-- Description section -->
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-align-left text-blue-500"></i> Description
                    </h2>
                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-100">
                        <p class="text-gray-600 leading-relaxed">
                            <?= nl2br(htmlspecialchars($demande->getDescription())) ?>
                        </p>
                    </div>
                </div>

                <!-- Informations grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="info-box">
                        <p class="text-sm text-gray-500 mb-1">
                            <i class="fa-solid fa-tag text-blue-400 mr-1"></i> Catégorie
                        </p>
                        <p class="font-semibold text-gray-800 text-lg">
                            <?= htmlspecialchars($demande->getCategorie()) ?>
                        </p>
                    </div>

                    <div class="info-box">
                        <p class="text-sm text-gray-500 mb-1">
                            <i class="fa-solid fa-chart-line text-blue-400 mr-1"></i> Niveau
                        </p>
                        <p class="font-semibold text-gray-800 text-lg">
                            <?= htmlspecialchars($demande->getNiveau()) ?>
                        </p>
                    </div>

                    <div class="info-box md:col-span-2">
                        <p class="text-sm text-gray-500 mb-1">
                            <i class="fa-solid fa-calendar text-blue-400 mr-1"></i> Date de création
                        </p>
                        <p class="font-semibold text-gray-800 text-lg">
                            <i class="fa-regular fa-calendar-alt mr-2 text-gray-400"></i>
                            <?= $demande->getDateCreation()?->format('d/m/Y à H:i') ?>
                        </p>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="mt-8 flex justify-between items-center border-t pt-6">
                    <a href="index.php?action=dashboardd"
                       class="flex items-center gap-2 bg-gray-800 text-white px-5 py-2.5 rounded-xl hover:bg-gray-700 transition-all hover:shadow-md">
                        <i class="fa-solid fa-arrow-left"></i>
                        Retour aux demandes
                    </a>
                    
                    <div class="flex gap-3">
                        <button onclick="openBlockModal(<?= $demande->getIdDemande() ?>)" 
                                class="flex items-center gap-2 bg-amber-500 text-white px-5 py-2.5 rounded-xl hover:bg-amber-600 transition-all">
                            <i class="fa-solid fa-ban"></i>
                            Bloquer
                        </button>
                        <button onclick="openDeleteModal(<?= $demande->getIdDemande() ?>)" 
                                class="flex items-center gap-2 bg-red-500 text-white px-5 py-2.5 rounded-xl hover:bg-red-600 transition-all">
                            <i class="fa-solid fa-trash"></i>
                            Supprimer
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- ============================================= -->
<!-- MODALS -->
<!-- ============================================= -->

<!-- Delete Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50">
    <div class="bg-white rounded-3xl p-8 max-w-md w-full mx-4">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                <i class="fa-solid fa-trash text-red-600 text-xl"></i>
            </div>
            <h3 class="text-xl font-semibold">Confirmer la suppression</h3>
        </div>
        <p class="text-gray-600 mb-6">Êtes-vous sûr de vouloir supprimer cette demande ? Cette action est irréversible.</p>
        <div class="flex gap-3">
            <button onclick="cancelDelete()" class="flex-1 py-3 border border-gray-300 rounded-2xl hover:bg-gray-50 transition-all">Annuler</button>
            <button onclick="confirmDelete()" class="flex-1 py-3 bg-red-600 text-white rounded-2xl hover:bg-red-700 transition-all">Supprimer</button>
        </div>
    </div>
</div>

<!-- Block Modal -->
<div id="blockModal" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50">
    <div class="bg-white rounded-3xl p-8 max-w-md w-full mx-4">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
                <i class="fa-solid fa-ban text-amber-600 text-xl"></i>
            </div>
            <h3 class="text-xl font-semibold" id="blockTitle">Bloquer cette demande ?</h3>
        </div>
        <p class="text-gray-600 mb-6" id="blockMessage">La demande ne sera plus visible publiquement après blocage.</p>
        <div class="flex gap-3">
            <button onclick="cancelBlock()" class="flex-1 py-3 border border-gray-300 rounded-2xl hover:bg-gray-50 transition-all">Annuler</button>
            <button onclick="confirmBlock()" class="flex-1 py-3 bg-amber-600 text-white rounded-2xl hover:bg-amber-700 transition-all" id="blockBtn">Bloquer</button>
        </div>
    </div>
</div>

<!-- ============================================= -->
<!-- SCRIPTS -->
<!-- ============================================= -->

<script>
// Variables
let currentDeleteId = null;
let currentBlockId = null;

// ========== DELETE MODAL ==========
function openDeleteModal(id) {
    currentDeleteId = id;
    document.getElementById('deleteModal').classList.remove('hidden');
}
function cancelDelete() {
    currentDeleteId = null;
    document.getElementById('deleteModal').classList.add('hidden');
}
function confirmDelete() {
    if (currentDeleteId) window.location.href = `index.php?action=dashboard_delete_demande&id=${currentDeleteId}`;
}

// ========== BLOCK MODAL ==========
function openBlockModal(id) {
    currentBlockId = id;
    document.getElementById('blockModal').classList.remove('hidden');
}
function cancelBlock() {
    currentBlockId = null;
    document.getElementById('blockModal').classList.add('hidden');
}
function confirmBlock() {
    if (currentBlockId) window.location.href = `index.php?action=dashboard_block_demande&id=${currentBlockId}`;
}

// Close modals on outside click (optional)
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) cancelDelete();
});
document.getElementById('blockModal').addEventListener('click', function(e) {
    if (e.target === this) cancelBlock();
});
</script>

</body>
</html>