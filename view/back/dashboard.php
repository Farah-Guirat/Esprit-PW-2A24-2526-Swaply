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
    <title>Dashboard Admin - Offres</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../src/assets/css/styles.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

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
        /* MAIN CONTENT & NAVBAR STYLES */
        /* ============================================= */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            background: #f8fafc;
        }
        .navbar {
            background: white;
            padding: 16px 32px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 40;
        }
        .page-title {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
        }
        .navbar-right {
            display: flex;
            align-items: center;
            gap: 24px;
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
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .user-info img {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e2e8f0;
        }
        .user-info .name {
            font-weight: 600;
            font-size: 14px;
            color: #0f172a;
        }
        .user-info .role {
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
            transition: all 0.2s;
        }
        .logout-btn:hover {
            background: #fecaca;
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
            <a href="index.php?action=dashboard" class="menu-item active" id="menu-offres">
                <i class="fa-solid fa-briefcase"></i> Offres
            </a>
            <a href="index.php?action=dashboardd" class="menu-item" id="menu-demandes">
                <i class="fa-solid fa-file-signature"></i> Demandes
            </a>
            <a href="#" class="menu-item" id="menu-publications">
                <i class="fa-solid fa-newspaper"></i> Publications
            </a>
            <a href="#" class="menu-item" id="menu-conversations">
                <i class="fa-solid fa-comment-dots"></i> Conversations
            </a>
           <a href="/swaply/view/back/reclamations_admin.php" onclick="showPage('reclamations')" class="menu-item" id="menu-reclamations">
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
        <!-- NAVBAR / HEADER -->
        <!-- ============================================= -->
        <div class="navbar">
            <h2 class="page-title">Gestion des Offres</h2>
            <div class="navbar-right">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" placeholder="Rechercher...">
                </div>
                <div class="notifications">
                    <i class="fa-solid fa-bell fa-lg"></i>
                    <span class="badge">7</span>
                </div>
                <div class="user-info">
                    <?php if ($adminPhoto): ?>
                        <img src="../../uploads/profiles/<?= htmlspecialchars($adminPhoto) ?>" alt="Admin">
                    <?php else: ?>
                        <img src="https://i.pravatar.cc/42?img=12" alt="Admin">
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
        </div>

        <!-- ============================================= -->
        <!-- PAGE CONTENT -->
        <!-- ============================================= -->
        <div class="p-8 max-w-7xl mx-auto w-full">

            <!-- ACTIONS BAR -->
            <div class="w-full flex justify-between items-center mb-6">
                <a href="index.php?action=dashboardd" class="flex items-center gap-2 bg-white border border-gray-300 hover:bg-gray-50 px-4 py-2 rounded-xl text-sm font-medium">
                    <i class="fa-solid fa-arrow-left"></i>
                    Aller aux demandes
                </a>
                <div class="flex gap-3">
                    <button onclick="exportData()" class="flex items-center gap-2 bg-white border border-gray-300 hover:bg-gray-50 px-4 py-2 rounded-xl text-sm font-medium">
                        <i class="fa-solid fa-download"></i>
                        Exporter
                    </button>
                    <button onclick="openStatsModal()" class="flex items-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 text-white px-5 py-2.5 rounded-2xl text-sm font-semibold hover:shadow-lg hover:shadow-emerald-500/30 transition-all active:scale-95">
                        <i class="fa-solid fa-chart-pie"></i>
                        Statistiques
                    </button>
                </div>
            </div>

            <!-- STATS CARDS -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
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

            <!-- FILTERS -->
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
                        <?php foreach ($offres as $offre): 
                            $statutLower = strtolower($offre->getStatut());
                        ?>
                        <tr class="border-b hover:bg-gray-50 transition-colors" data-statut="<?= $statutLower ?>">
                            <td class="p-5 font-medium"><?= htmlspecialchars($offre->getTitre()) ?></td>
                            <td class="p-5 text-center"><?= htmlspecialchars($offre->getCategorie()) ?></td>
                            <td class="p-5 text-center"><?= htmlspecialchars($offre->getNiveau()) ?></td>
                            <td class="p-5 text-center text-sm">
                                <?= $offre->getDateLimite() ? $offre->getDateLimite()->format('d/m/Y') : '-' ?>
                            </td>
                            <td class="p-5 text-center">
                                <?php
                                $badgeClass = match($statutLower) {
                                    'active' => 'bg-emerald-100 text-emerald-700',
                                    'fermée', 'fermee' => 'bg-orange-100 text-orange-700',
                                    'expirée', 'expiree' => 'bg-red-100 text-red-700',
                                    'bloque', 'bloquée' => 'bg-gray-100 text-gray-700',
                                    default => 'bg-gray-100 text-gray-700'
                                };
                                ?>
                                <span class="<?= $badgeClass ?> status-badge">
                                    <?= htmlspecialchars($offre->getStatut()) ?>
                                </span>
                            </td>
                            <td class="p-5 text-center">
                                <div class="flex justify-center gap-4 text-lg">
                                    <a href="index.php?action=detailsoffre&id=<?= $offre->getIdOffre() ?>" 
                                       class="text-blue-600 hover:text-blue-700" title="Détails">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <button onclick="openBlockModal(<?= $offre->getIdOffre() ?>)" 
                                            class="text-amber-600 hover:text-amber-700" title="Bloquer/Débloquer">
                                        <i class="fa-solid fa-ban"></i>
                                    </button>
                                    <button onclick="openDeleteModal(<?= $offre->getIdOffre() ?>)" 
                                            class="text-red-600 hover:text-red-700" title="Supprimer">
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
    </div>
</div>

<!-- ============================================= -->
<!-- MODALS -->
<!-- ============================================= -->

<!-- Delete Modal -->
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

<!-- Block Modal -->
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

<!-- Stats Modal -->
<div id="statsModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-[60] backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl max-w-6xl w-full mx-4 max-h-[94vh] overflow-hidden flex flex-col">
        <div class="px-8 py-6 border-b flex items-center justify-between bg-gradient-to-r from-slate-50 to-white">
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center text-3xl">📊</div>
                <div> 
                    <h2 class="text-2xl font-bold text-gray-800">Statistiques des Offres</h2>
                    <p class="text-gray-500 text-sm">Analyse visuelle en temps réel</p>
                </div>
            </div>
            <button onclick="closeStatsModal()" class="w-11 h-11 flex items-center justify-center text-3xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-2xl transition-all">×</button>
        </div>
        <div class="p-8 flex-1 overflow-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white border border-gray-100 rounded-3xl p-7 shadow-sm">
                    <h3 class="text-lg font-semibold mb-6 text-gray-700">Répartition par Statut</h3>
                    <div class="h-80">
                        <canvas id="doughnutChart"></canvas>
                    </div>
                </div>
                <div class="bg-white border border-gray-100 rounded-3xl p-7 shadow-sm">
                    <h3 class="text-lg font-semibold mb-6 text-gray-700">Comparaison des Offres</h3>
                    <div class="h-80">
                        <canvas id="barChart"></canvas>
                    </div>
                </div>
                <div class="lg:col-span-2 bg-white border border-gray-100 rounded-3xl p-7 shadow-sm">
                    <h3 class="text-lg font-semibold mb-6 text-gray-700">Vue détaillée des Statuts</h3>
                    <div class="h-80">
                        <canvas id="horizontalChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="px-8 py-5 border-t text-xs text-gray-400 text-center bg-gray-50 rounded-b-3xl">
            JobBoard Admin • Statistiques générées le <?= date('d/m/Y à H:i') ?>
        </div>
    </div>
</div>

<!-- ============================================= -->
<!-- SCRIPTS -->
<!-- ============================================= -->

<script>
// Toastr config
toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: 3000 };

// Variables
let currentDeleteId = null;
let currentBlockId = null;

// ========== FILTER TABLE ==========
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
    if (currentDeleteId) window.location.href = `index.php?action=dashboard_delete&id=${currentDeleteId}`;
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
    if (currentBlockId) window.location.href = `index.php?action=dashboard_block&id=${currentBlockId}`;
}

// ========== EXPORT DATA ==========
function exportData() {
    console.log('exportData appelée');
    try {
        const tableBody = document.getElementById('tableBody');
        if (!tableBody) {
            toastr.error("Tableau non trouvé!");
            return;
        }
        const visibleRows = Array.from(tableBody.querySelectorAll('tr'))
            .filter(row => row.style.display !== 'none');
        if (!visibleRows.length) {
            toastr.warning("Aucune offre trouvée pour l'exportation.");
            return;
        }
        const now = new Date();
        const day = String(now.getDate()).padStart(2, '0');
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const year = now.getFullYear();
        const hours = String(now.getHours()).padStart(2, '0');
        const mins = String(now.getMinutes()).padStart(2, '0');
        const formattedDate = day + '/' + month + '/' + year;
        const formattedTime = hours + ':' + mins;
        const headerCells = Array.from(document.querySelectorAll('table thead th'))
            .map(th => th.textContent.trim())
            .filter(text => text !== 'Actions');
        let rowsHtml = '';
        visibleRows.forEach(row => {
            rowsHtml += '<tr>';
            Array.from(row.querySelectorAll('td')).forEach((td, index) => {
                if (index === 5) return;
                const content = td.textContent.trim();
                let cellStyle = 'padding:15px 14px;border-bottom:1px solid #e2e8f0;vertical-align:middle;font-size:13px;color:#1f2937;';
                if (index === 0) {
                    cellStyle += 'text-align:left;font-weight:600;';
                } else {
                    cellStyle += 'text-align:center;';
                }
                let cellContent = content;
                if (index === 4) {
                    if (content.toLowerCase().includes('active')) {
                        cellContent = '<span style="background:#d1fae5;color:#065f46;padding:8px 14px;border-radius:8px;font-weight:700;font-size:12px;display:inline-block;">✓ ACTIVE</span>';
                    } else if (content.toLowerCase().includes('fermée') || content.toLowerCase().includes('fermee')) {
                        cellContent = '<span style="background:#fed7aa;color:#92400e;padding:8px 14px;border-radius:8px;font-weight:700;font-size:12px;display:inline-block;">◉ FERMÉE</span>';
                    } else if (content.toLowerCase().includes('expirée') || content.toLowerCase().includes('expiree')) {
                        cellContent = '<span style="background:#fee2e2;color:#991b1b;padding:8px 14px;border-radius:8px;font-weight:700;font-size:12px;display:inline-block;">✕ EXPIRÉE</span>';
                    } else if (content.toLowerCase().includes('bloque')) {
                        cellContent = '<span style="background:#f3f4f6;color:#374151;padding:8px 14px;border-radius:8px;font-weight:700;font-size:12px;display:inline-block;">⊗ BLOQUÉE</span>';
                    }
                }
                rowsHtml += '<td style="' + cellStyle + '">' + cellContent + '</td>';
            });
            rowsHtml += '</tr>';
        });
        let headerRow = '';
        headerCells.forEach(text => {
            headerRow += '<th>' + text + '</th>';
        });
        const htmlContent = '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Export Offres</title><style>*{margin:0;padding:0;box-sizing:border-box;}body{font-family:system-ui,-apple-system,"Segoe UI","Helvetica Neue",sans-serif;padding:40px;color:#0f172a;background:#f0f4f8;}.export-container{background:#fff;border-radius:20px;padding:48px;box-shadow:0 32px 64px rgba(15,23,42,0.12);max-width:1200px;margin:0 auto;}.logo-section{display:flex;align-items:center;gap:16px;margin-bottom:32px;padding-bottom:24px;border-bottom:2px solid #e2e8f0;}.logo-box{width:70px;height:70px;border-radius:20px;background:linear-gradient(135deg,#2563eb 0%,#1e40af 100%);color:#fff;display:flex;align-items:center;justify-content:center;font-size:32px;box-shadow:0 20px 50px rgba(37,99,235,0.25);}.logo-text h1{font-size:24px;font-weight:900;color:#0f172a;letter-spacing:-0.5px;margin:0;}.logo-text p{font-size:13px;color:#64748b;margin-top:4px;}.export-title{font-size:32px;font-weight:900;letter-spacing:-1px;color:#0f172a;margin-bottom:10px;}.export-subtitle{color:#64748b;font-size:14px;margin-bottom:20px;}.stats-box{background:linear-gradient(135deg,#eff6ff 0%,#f0f9ff 100%);border-left:5px solid #2563eb;padding:16px 18px;border-radius:10px;margin-bottom:28px;display:inline-flex;align-items:center;gap:12px;}.stats-label{font-weight:600;color:#1e40af;}.stats-value{font-weight:800;font-size:18px;color:#2563eb;}.export-table{width:100%;border-collapse:collapse;margin-top:24px;}.export-table thead{background:linear-gradient(135deg,#2563eb 0%,#1e40af 100%);}.export-table thead th{color:#fff;font-weight:800;padding:18px 14px;text-align:left;font-size:12px;letter-spacing:0.05em;}.export-table tbody tr:nth-child(odd){background:#f8fafc;}.export-table tbody tr:nth-child(even){background:#fff;}.export-footer{margin-top:32px;padding-top:24px;border-top:2px solid #e2e8f0;font-size:12px;color:#64748b;text-align:center;}@media print{body{background:#fff;padding:20px;}.export-container{box-shadow:none;padding:30px;}}@page{size:A4 landscape;margin:1.5cm;}</style></head><body><div class="export-container"><div class="logo-section"><div class="logo-box">📋</div><div class="logo-text"><h1>JobBoard Admin</h1><p>Export des offres • ' + formattedDate + ' à ' + formattedTime + '</p></div></div><h1 class="export-title">Liste des offres exportées</h1><div class="stats-box"><span class="stats-label">Total de lignes :</span><span class="stats-value">' + visibleRows.length + '</span></div><table class="export-table"><thead><tr>' + headerRow + '</thead><tbody>' + rowsHtml + '</tbody>\\x3C/table><div class="export-footer">Export généré le ' + formattedDate + ' à ' + formattedTime + '</div></div><script>window.onload=function(){setTimeout(function(){window.print();},600);}<\/script></body></html>';
        const blob = new Blob([htmlContent], { type: 'text/html' });
        const url = URL.createObjectURL(blob);
        const newWindow = window.open(url, '_blank');
        if (!newWindow) {
            toastr.error("Ouverture de fenêtre bloquée. Vérifiez vos paramètres de navigateur.");
            return;
        }
        toastr.success("Export généré! Vous pouvez maintenant imprimer ou enregistrer en PDF.");
    } catch (e) {
        console.error('Erreur export:', e);
        toastr.error("Erreur: " + e.message);
    }
}

// ========== CHART FUNCTIONS ==========
let doughnutChart = null;
let barChart = null;
let horizontalChart = null;

function openStatsModal() {
    const modal = document.getElementById('statsModal');
    modal.classList.remove('hidden');
    setTimeout(() => {
        initAllCharts();
    }, 450);
}
function closeStatsModal() {
    const modal = document.getElementById('statsModal');
    modal.classList.add('hidden');
    destroyCharts();
}
function destroyCharts() {
    if (doughnutChart) { doughnutChart.destroy(); doughnutChart = null; }
    if (barChart) { barChart.destroy(); barChart = null; }
    if (horizontalChart) { horizontalChart.destroy(); horizontalChart = null; }
}
function initAllCharts() {
    const total = <?= json_encode($stats['total'] ?? 0) ?>;
    const actives = <?= json_encode($stats['offresActives'] ?? 0) ?>;
    const expirees = <?= json_encode($stats['expirees'] ?? 0) ?>;
    const ctxDoughnut = document.getElementById('doughnutChart');
    if (doughnutChart) doughnutChart.destroy();
    doughnutChart = new Chart(ctxDoughnut, {
        type: 'doughnut',
        data: {
            labels: ['Actives', 'Expirées / Bloquées', 'Autres'],
            datasets: [{
                data: [actives, expirees, Math.max(0, total - actives - expirees)],
                backgroundColor: ['#10b981', '#ef4444', '#64748b'],
                borderColor: '#ffffff',
                borderWidth: 5,
                hoverOffset: 30
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: { position: 'bottom', labels: { padding: 25, font: { size: 15 }, usePointStyle: true } }
            }
        }
    });
    const ctxBar = document.getElementById('barChart');
    if (barChart) barChart.destroy();
    barChart = new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: ['Total', 'Actives', 'Expirées/Bloquées'],
            datasets: [{
                label: 'Nombre de demandes',
                data: [total, actives, expirees],
                backgroundColor: ['#3b82f6', '#10b981', '#ef4444'],
                borderRadius: 12,
                barThickness: 75,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true }, x: { grid: { display: false } } }
        }
    });
    const ctxHorizontal = document.getElementById('horizontalChart');
    if (horizontalChart) horizontalChart.destroy();
    horizontalChart = new Chart(ctxHorizontal, {
        type: 'bar',
        data: {
            labels: ['Demandes Actives', 'Expirées / Bloquées', 'Total des Demandes'],
            datasets: [{
                label: 'Quantité',
                data: [actives, expirees, total],
                backgroundColor: ['#10b981', '#ef4444', '#6366f1'],
                borderRadius: 10,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true }, y: { grid: { display: false } } }
        }
    });
    setTimeout(() => {
        if (doughnutChart) doughnutChart.resize();
        if (barChart) barChart.resize();
        if (horizontalChart) horizontalChart.resize();
    }, 100);
}

// ========== ANIMATION ==========
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

// Close modal on outside click
document.getElementById('statsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeStatsModal();
    }
});
</script>
<script src="script.js"></script>

</body>
</html>