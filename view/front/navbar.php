<?php
// Navbar réutilisable pour tous les pages front
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/helpers.php';

// Déterminer la page active
$currentFile = basename($_SERVER['PHP_SELF']);
$navItems = [
    'home.php' => ['label' => 'Accueil', 'icon' => 'fa-home'],
    'listepublication.php' => ['label' => 'Publications', 'icon' => 'fa-newspaper'],
    'Profil.php' => ['label' => 'Profils', 'icon' => 'fa-users'],
    'projets.php' => ['label' => 'Projets', 'icon' => 'fa-briefcase'],
    'demandes.php' => ['label' => 'Demandes', 'icon' => 'fa-handshake'],
    'offres.php' => ['label' => 'Offres', 'icon' => 'fa-gift'],
    'messages.php' => ['label' => 'Messages', 'icon' => 'fa-envelope'],
    'reclamations.php' => ['label' => 'Réclamations', 'icon' => 'fa-exclamation-triangle'],
];
?>

<header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-3 flex items-center justify-between">
        <!-- Logo et Bienvenue -->
        <div class="flex items-center gap-3 min-w-fit">
            <div class="w-10 h-10 bg-teal-500 rounded-full flex items-center justify-center text-white font-bold text-lg">S</div>
            <div class="hidden sm:block">
                <div class="text-lg font-semibold">Swaply</div>
                <div class="text-xs text-slate-600">Bienvenue, <?= htmlspecialchars(substr($_SESSION['user']['prenom'] ?? '', 0, 1) . '. ' . ($_SESSION['user']['nom'] ?? 'Utilisateur')) ?></div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="hidden lg:flex flex-1 gap-1 mx-6 justify-center">
            <?php foreach ($navItems as $page => $item): ?>
                <a href="<?= $page ?>" 
                   class="px-3 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap
                   <?= $currentFile === $page 
                       ? 'bg-teal-600 text-white' 
                       : 'text-slate-700 hover:bg-slate-100' ?>">
                    <i class="fas <?= $item['icon'] ?>"></i>
                    <span class="hidden xl:inline-block ml-2"><?= $item['label'] ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <!-- Dropdown menu pour mobile -->
        <div class="lg:hidden flex items-center gap-2">
            <div class="relative group">
                <button class="px-3 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg hidden group-hover:block">
                    <?php foreach ($navItems as $page => $item): ?>
                        <a href="<?= $page ?>" 
                           class="block px-4 py-2 text-sm <?= $currentFile === $page ? 'bg-teal-100 text-teal-700 font-semibold' : 'text-slate-700 hover:bg-slate-100' ?>">
                            <i class="fas <?= $item['icon'] ?> mr-2"></i><?= $item['label'] ?>
                        </a>
                    <?php endforeach; ?>
                    <hr class="my-1">
                    <a href="../../controller/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                        <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                    </a>
                </div>
            </div>
        </div>

        <!-- Bouton Déconnexion (desktop) -->
        <div class="hidden lg:flex items-center gap-3">
            <!-- Profile Avatar -->
            <div class="relative group">
                <div class="cursor-pointer" title="Voir mon profil">
                    <?php 
                    $userName = htmlspecialchars(($_SESSION['user']['nom'] ?? 'U') . ' ' . ($_SESSION['user']['prenom'] ?? ''));
                    $userPhoto = $_SESSION['user']['photo'] ?? null;
                    $photoData = getProfilePhotoUrl($userPhoto, $_SESSION['user']['nom'] ?? 'U');
                    ?>
                    <?php if ($photoData['hasImage']): ?>
                        <img src="<?= $photoData['url'] ?>" alt="<?= $userName ?>" 
                             class="w-10 h-10 rounded-full object-cover border-2 border-teal-200 hover:border-teal-400 transition-colors"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="w-10 h-10 rounded-full bg-teal-500 flex items-center justify-center text-white font-bold text-sm hidden border-2 border-teal-200">
                            <?= $photoData['initial'] ?>
                        </div>
                    <?php else: ?>
                        <div class="w-10 h-10 rounded-full bg-teal-500 flex items-center justify-center text-white font-bold text-sm border-2 border-teal-200 hover:border-teal-400 transition-colors">
                            <?= $photoData['initial'] ?>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- Dropdown menu -->
                <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg hidden group-hover:block z-50">
                    <a href="Profil.php" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 rounded-t-lg">
                        <i class="fas fa-user mr-2"></i>Mon profil
                    </a>
                    <hr class="my-1">
                    <a href="../../controller/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-b-lg">
                        <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
