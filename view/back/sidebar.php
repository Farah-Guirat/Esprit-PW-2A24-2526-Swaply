<?php
if (!isset($currentPage)) {
    $currentPage = '';
}
$adminPhoto = $_SESSION['user']['photo'] ?? null;
$menuItems = [
    'dashboard' => ['href' => '/swaply/view/back/swaplyB.php', 'icon' => 'fa-house', 'label' => 'Dashboard'],
    'profiles' => ['href' => '/swaply/view/back/ProfilsB.php', 'icon' => 'fa-user', 'label' => 'Profils'],
    'projets' => ['href' => '/swaply/view/back/projets.php', 'icon' => 'fa-file', 'label' => 'Projets'],
    'offres' => ['href' => '/swaply/public/index.php?action=dashboard', 'icon' => 'fa-briefcase', 'label' => 'Offres & Demandes'],
    'publications' => ['href' => '/swaply/view/back/publication_back.php', 'icon' => 'fa-newspaper', 'label' => 'Publications'],
    'conversations' => ['href' => '/swaply/view/back/conversations.php', 'icon' => 'fa-comment-dots', 'label' => 'Conversations'],
    'messages' => ['href' => '/swaply/view/back/messages.php', 'icon' => 'fa-envelope', 'label' => 'Messages'],
    'reclamations' => ['href' => '/swaply/view/back/reclamations_admin.php', 'icon' => 'fa-exclamation-triangle', 'label' => 'Réclamations'],
    'settings' => ['href' => '/swaply/view/back/settings.php', 'icon' => 'fa-gear', 'label' => 'Paramètres'],
];
?>
<div class="sidebar">
    <div class="logo">
        <span class="icon">📋</span>
        <h1>JobBoard Admin</h1>
    </div>

    <div class="menu">
        <?php foreach ($menuItems as $key => $item): ?>
            <a href="<?= $item['href'] ?>" class="menu-item<?= $currentPage === $key ? ' active' : '' ?>" id="menu-<?= $key ?>">
                <i class="fa-solid <?= $item['icon'] ?>"></i> <?= $item['label'] ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>
