<?php
require_once "../../model/projet.php";
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$p = new Projet();
$projets = $p->getAll($_SESSION['user']['id_u'] ?? 0);

// ============ CALCUL DES BADGES ============

$totalProjets = count($projets);
$totalTermines = 0;
$totalEnCours = 0;
$maxCompsInOneProjet = 0;
$projetsThisMonth = 0;
$projetsThisWeek = 0;
$totalComps = 0;

$currentMonth = date('Y-m');
$currentWeek = date('Y-W');

foreach ($projets as $proj) {
    if ($proj['statut'] === 'Terminé') $totalTermines++;
    if ($proj['statut'] === 'En cours') $totalEnCours++;

    $comps = $p->getCompetences($proj['id_projet']);
    $nbComps = count($comps);
    $totalComps += $nbComps;
    if ($nbComps > $maxCompsInOneProjet) $maxCompsInOneProjet = $nbComps;

    $moisProjet = date('Y-m', strtotime($proj['date_creation']));
    $semaineProjet = date('Y-W', strtotime($proj['date_creation']));

    if ($moisProjet === $currentMonth) $projetsThisMonth++;
    if ($semaineProjet === $currentWeek) $projetsThisWeek++;
}

// ============ DEFINITION DES BADGES ============
$allBadges = [
    [
        'id'       => 'premier_pas',
        'icon'     => '🚀',
        'nom'      => 'Premier Pas',
        'desc'     => 'Créer ton premier projet',
        'couleur'  => '#14b8a6',
        'bg'       => '#e0f2f1',
        'unlocked' => $totalProjets >= 1,
        'progress' => min($totalProjets, 1),
        'total'    => 1,
    ],
    [
        'id'       => 'collectionneur',
        'icon'     => '📁',
        'nom'      => 'Collectionneur',
        'desc'     => 'Avoir 3 projets ou plus',
        'couleur'  => '#3b82f6',
        'bg'       => '#eff6ff',
        'unlocked' => $totalProjets >= 3,
        'progress' => min($totalProjets, 3),
        'total'    => 3,
    ],
    [
        'id'       => 'productif',
        'icon'     => '⚡',
        'nom'      => 'Productif',
        'desc'     => 'Créer 2 projets dans le même mois',
        'couleur'  => '#f59e0b',
        'bg'       => '#fffbeb',
        'unlocked' => $projetsThisMonth >= 2,
        'progress' => min($projetsThisMonth, 2),
        'total'    => 2,
    ],
    [
        'id'       => 'expert',
        'icon'     => '💎',
        'nom'      => 'Expert',
        'desc'     => 'Avoir un projet avec 3 compétences ou plus',
        'couleur'  => '#8b5cf6',
        'bg'       => '#f5f3ff',
        'unlocked' => $maxCompsInOneProjet >= 3,
        'progress' => min($maxCompsInOneProjet, 3),
        'total'    => 3,
    ],
    [
        'id'       => 'maitre',
        'icon'     => '🏆',
        'nom'      => 'Maître',
        'desc'     => 'Terminer 2 projets ou plus',
        'couleur'  => '#f97316',
        'bg'       => '#fff7ed',
        'unlocked' => $totalTermines >= 2,
        'progress' => min($totalTermines, 2),
        'total'    => 2,
    ],
    [
        'id'       => 'actif',
        'icon'     => '🔥',
        'nom'      => 'Très Actif',
        'desc'     => 'Avoir 2 projets en cours en même temps',
        'couleur'  => '#ef4444',
        'bg'       => '#fef2f2',
        'unlocked' => $totalEnCours >= 2,
        'progress' => min($totalEnCours, 2),
        'total'    => 2,
    ],
    [
        'id'       => 'rapide',
        'icon'     => '⚡',
        'nom'      => 'Rapide',
        'desc'     => 'Créer 2 projets cette semaine',
        'couleur'  => '#06b6d4',
        'bg'       => '#ecfeff',
        'unlocked' => $projetsThisWeek >= 2,
        'progress' => min($projetsThisWeek, 2),
        'total'    => 2,
    ],
    [
        'id'       => 'polyvalent',
        'icon'     => '🌟',
        'nom'      => 'Polyvalent',
        'desc'     => 'Avoir 5 compétences au total',
        'couleur'  => '#10b981',
        'bg'       => '#ecfdf5',
        'unlocked' => $totalComps >= 5,
        'progress' => min($totalComps, 5),
        'total'    => 5,
    ],
];

$unlockedCount = count(array_filter($allBadges, fn($b) => $b['unlocked']));
$totalBadges = count($allBadges);
?>

<div class="bg-white rounded-3xl p-8 shadow-sm mb-8">

  <!-- HEADER -->
  <div class="flex items-center justify-between mb-6">
    <h3 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
      <span class="w-8 h-8 bg-yellow-100 text-yellow-600 rounded-xl flex items-center justify-center text-sm">🏆</span>
      Mes Badges
    </h3>
    <div class="flex items-center gap-2">
      <span class="text-sm text-gray-500"><?= $unlockedCount ?>/<?= $totalBadges ?> débloqués</span>
      <div style="width:100px;height:8px;background:#f0f0f0;border-radius:4px;overflow:hidden;">
        <div style="width:<?= round(($unlockedCount/$totalBadges)*100) ?>%;height:100%;background:linear-gradient(90deg,#14b8a6,#3b82f6);border-radius:4px;transition:width 1s ease;"></div>
      </div>
    </div>
  </div>

  <!-- BADGES GRID -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <?php foreach ($allBadges as $badge): ?>
    <div style="
      background: <?= $badge['unlocked'] ? $badge['bg'] : '#f9fafb' ?>;
      border: 2px solid <?= $badge['unlocked'] ? $badge['couleur'].'40' : '#e5e7eb' ?>;
      border-radius: 20px;
      padding: 20px 16px;
      text-align: center;
      position: relative;
      transition: transform 0.2s, box-shadow 0.2s;
      <?= !$badge['unlocked'] ? 'filter: grayscale(1); opacity: 0.5;' : '' ?>
    "
    <?= $badge['unlocked'] ? 'onmouseover="this.style.transform=\'scale(1.05)\';this.style.boxShadow=\'0 8px 24px rgba(0,0,0,0.1)\'"
    onmouseout="this.style.transform=\'scale(1)\';this.style.boxShadow=\'none\'"' : '' ?>>

      <?php if($badge['unlocked']): ?>
        <!-- Unlocked checkmark -->
        <div style="position:absolute;top:10px;right:10px;width:18px;height:18px;
                    background:<?= $badge['couleur'] ?>;border-radius:50%;
                    display:flex;align-items:center;justify-content:center;font-size:10px;color:white;">
          ✓
        </div>
      <?php endif; ?>

      <!-- Icon -->
      <div style="font-size:36px;margin-bottom:8px;
                  filter: <?= $badge['unlocked'] ? 'drop-shadow(0 2px 4px rgba(0,0,0,0.15))' : 'none' ?>;">
        <?= $badge['icon'] ?>
      </div>

      <!-- Name -->
      <p style="font-size:13px;font-weight:600;color:<?= $badge['unlocked'] ? '#1f2937' : '#9ca3af' ?>;margin-bottom:4px;">
        <?= $badge['nom'] ?>
      </p>

      <!-- Description -->
      <p style="font-size:11px;color:#9ca3af;margin-bottom:10px;line-height:1.4;">
        <?= $badge['desc'] ?>
      </p>

      <!-- Progress bar -->
      <div style="width:100%;height:4px;background:#e5e7eb;border-radius:2px;overflow:hidden;">
        <div style="width:<?= round(($badge['progress']/$badge['total'])*100) ?>%;
                    height:100%;background:<?= $badge['couleur'] ?>;
                    border-radius:2px;transition:width 1s ease;"></div>
      </div>
      <p style="font-size:10px;color:#9ca3af;margin-top:4px;">
        <?= $badge['progress'] ?>/<?= $badge['total'] ?>
      </p>

    </div>
    <?php endforeach; ?>
  </div>

  <?php if($unlockedCount === $totalBadges): ?>
  <!-- All badges unlocked message -->
  <div style="margin-top:20px;text-align:center;padding:16px;background:linear-gradient(135deg,#e0f2f1,#eff6ff);border-radius:16px;">
    <p style="font-size:14px;font-weight:600;color:#14b8a6;">🎉 Félicitations! Tu as débloqué tous les badges!</p>
  </div>
  <?php endif; ?>

</div>