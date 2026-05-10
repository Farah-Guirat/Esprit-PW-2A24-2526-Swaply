<?php
require_once '../../controller/ReclamationController.php';
require_once '../../controller/ReponseController.php';

$controller    = new ReclamationController();
$repController = new ReponseController();
$recs          = $controller->afficher()->fetchAll(PDO::FETCH_ASSOC);

// ── Métier 6 : Notifications ──────────────────────────────────────────────
// On compte les réclamations qui ont au moins une réponse non-vide (= admin a répondu)
// et dont l'utilisateur n'a pas encore "vu" la réponse (on stocke les IDs vus en session)
session_start();
$photo = $_SESSION['user']['photo'] ?? null; // Ajout de cette ligne
if (!isset($_SESSION['seen_reponses'])) {
    $_SESSION['seen_reponses'] = [];
}

// Marquer comme "vu" si l'action est demandée
if (isset($_GET['mark_seen']) && $_GET['mark_seen'] === 'all') {
    foreach ($recs as $rec) {
        $_SESSION['seen_reponses'][] = (int)$rec['id_reclamation'];
    }
    $_SESSION['seen_reponses'] = array_unique($_SESSION['seen_reponses']);
    header("Location: reclamations.php");
    exit;
}

// Construire la liste des notifications (réponses non vues)
$notifications   = [];
foreach ($recs as $rec) {
    $id = (int)$rec['id_reclamation'];
    if (in_array($id, $_SESSION['seen_reponses'])) continue;

    $repStmt = $repController->afficher($id);
    $repAll  = $repStmt->fetchAll(PDO::FETCH_ASSOC);
    $hasReponse = array_filter($repAll, fn($r) => trim($r['contenu'] ?? '') !== '');
    if (!empty($hasReponse)) {
        $notifications[] = [
            'id'          => $id,
            'description' => $rec['description'],
        ];
    }
}
$notifCount = count($notifications);

// ── Métier 5 : Filtrage ───────────────────────────────────────────────────
$filterStatut = $_GET['statut'] ?? '';
$filterType   = $_GET['type']   ?? '';
$searchQuery  = trim($_GET['q'] ?? '');

function normalizeStatut(string $s): string {
    $s = strtolower(trim($s));
    return str_replace(
        ['é','è','ê','ë','à','â','î','ï','ô','û','ù','ç'],
        ['e','e','e','e','a','a','i','i','o','u','u','c'],
        $s
    );
}

// Appliquer les filtres côté PHP
$filtered = array_filter($recs, function($rec) use ($filterStatut, $filterType, $searchQuery) {
    $sNorm = normalizeStatut($rec['statut'] ?? '');

    if ($filterStatut !== '') {
        $fNorm = normalizeStatut($filterStatut);
        if (!str_contains($sNorm, $fNorm)) return false;
    }
    if ($filterType !== '' && strtolower($rec['type'] ?? '') !== strtolower($filterType)) {
        return false;
    }
    if ($searchQuery !== '') {
        $desc = strtolower($rec['description'] ?? '');
        $user = strtolower($rec['username_cible'] ?? '');
        if (!str_contains($desc, strtolower($searchQuery)) && !str_contains($user, strtolower($searchQuery))) {
            return false;
        }
    }
    return true;
});
$filtered = array_values($filtered);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Swaply - Mes Réclamations</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="../../assets/css/style.css">
<style>
/* Notification dropdown */
.notif-dropdown {
    display: none;
    position: absolute;
    top: calc(100% + 12px);
    right: 0;
    width: 340px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    z-index: 999;
    overflow: hidden;
    animation: dropDown .2s ease;
}
.notif-dropdown.open { display: block; }
@keyframes dropDown {
    from { opacity:0; transform:translateY(-8px); }
    to   { opacity:1; transform:translateY(0); }
}

/* Filter bar */
.filter-bar {
    background: white;
    border-radius: 20px;
    padding: 16px 20px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
    margin-bottom: 28px;
}
.filter-input {
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    padding: 8px 14px;
    font-size: 14px;
    outline: none;
    transition: border-color .2s;
    background: #f8fafc;
}
.filter-input:focus { border-color: #14b8a6; background: white; }

/* Cards */
@keyframes fadeUp {
    from { opacity:0; transform:translateY(12px); }
    to   { opacity:1; transform:translateY(0); }
}
.rec-card {
    animation: fadeUp .35s ease both;
}

/* Empty state */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 60px 20px;
    color: #94a3b8;
    gap: 12px;
}

/* Notif bell shake */
@keyframes bellShake {
    0%,100% { transform: rotate(0); }
    15%      { transform: rotate(15deg); }
    30%      { transform: rotate(-12deg); }
    45%      { transform: rotate(8deg); }
    60%      { transform: rotate(-5deg); }
}
.bell-shake { animation: bellShake .6s ease; }
</style>
</head>
<body class="bg-gray-50">

<!-- ── NAVBAR ── -->

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
        <a href="swaplyf.php" class="nav-link">Accueil</a>
        <a href="Profil.php" class="nav-link">Profils</a>
        <a href="projets.php" class="nav-link">Projets</a>
        <a href="/swaply/public/index.php?action=choice" class="nav-link">Demandes</a>
        <a href="/swaply/public/index.php?action=choicee" class="nav-link">Offres</a>
        <a href="listepublication.php" class="nav-link">Publications</a>
        <a href="Messages.php" class="nav-link">Messages</a>
        <a href="reclamations.php" class="nav-link">Réclamations</a>
      </nav>
      <div class="flex items-center gap-4">
      <div class="relative" id="notifWrapper">
        <button id="notifBtn"
                onclick="toggleNotif()"
                class="relative w-10 h-10 flex items-center justify-center rounded-2xl hover:bg-gray-100 transition text-xl">
          🔔
          <?php if ($notifCount > 0): ?>
          <span id="notifBadge"
                class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold
                       w-5 h-5 rounded-full flex items-center justify-center">
            <?= $notifCount ?>
          </span>
          <?php endif; ?>
        </button>
        <div class="notif-dropdown" id="notifDropdown">
          <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <span class="font-semibold text-gray-800">🔔 Notifications</span>
            <?php if ($notifCount > 0): ?>
            <a href="?mark_seen=all" class="text-xs text-teal-500 hover:underline">Tout marquer comme lu</a>
            <?php endif; ?>
          </div>

          <?php if (empty($notifications)): ?>
          <div class="px-5 py-8 text-center text-gray-400 text-sm">
            <div class="text-4xl mb-3">✅</div>
            Aucune nouvelle notification
          </div>
          <?php else: ?>
          <div class="divide-y divide-gray-50 max-h-72 overflow-y-auto">
            <?php foreach ($notifications as $n): ?>
            <a href="detail_reclamation.php?id=<?= $n['id'] ?>"
               class="flex items-start gap-3 px-5 py-4 hover:bg-teal-50 transition">
              <span class="text-teal-500 text-lg mt-0.5">💬</span>
              <div>
                <p class="text-sm font-medium text-gray-800 line-clamp-1">
                  <?= htmlspecialchars(mb_substr($n['description'], 0, 50)) ?>…
                </p>
                <p class="text-xs text-teal-600 mt-0.5">L'admin a répondu à votre réclamation</p>
              </div>
            </a>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
</div>
      <div onclick="window.location.href='/swaply/view/front/Profil.php'" class="w-10 h-10 bg-teal-100 rounded-2xl overflow-hidden border-2 border-white shadow cursor-pointer relative" onclick="togglePhotoMenu()">
        <?php if ($photo): ?>
          <img src="/swaply/uploads/profiles/<?= htmlspecialchars($photo) ?>" alt="Profil" class="w-full h-full object-cover" style="width: 100%; height: 100%; object-fit: cover; display: block;">
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





<!-- ── MAIN ── -->
<main class="max-w-5xl mx-auto px-8 py-10">

  <div class="flex justify-between items-center mb-8">
    <div>
      <h2 class="text-2xl font-semibold text-gray-800">Mes Réclamations</h2>
      <p class="text-sm text-gray-400 mt-1"><?= count($recs) ?> réclamation<?= count($recs) > 1 ? 's' : '' ?> au total</p>
    </div>
    <div class="flex gap-3">
      <a href="stats_reclamations.php"
         class="border border-teal-500 text-teal-600 px-4 py-2 rounded-xl hover:bg-teal-50 transition text-sm font-medium">
         📊 Statistiques
      </a>
      <a href="add_reclamation.php"
         class="bg-teal-500 text-white px-5 py-2 rounded-xl hover:bg-teal-600 transition shadow text-sm font-medium">
         + Ajouter Réclamation
      </a>
    </div>
  </div>

  <!-- ── Métier 5 : Barre de Recherche + Filtres ── -->
  <form method="GET" class="filter-bar">
    <!-- Recherche texte -->
    <div class="relative flex-1 min-w-[180px]">
      <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">🔍</span>
      <input
        type="text"
        name="q"
        value="<?= htmlspecialchars($searchQuery) ?>"
        placeholder="Rechercher par description ou @username…"
        class="filter-input w-full pl-8"
      >
    </div>

    <!-- Filtre Statut -->
    <select name="statut" class="filter-input">
      <option value="">Tous les statuts</option>
      <option value="en attente" <?= $filterStatut === 'en attente' ? 'selected' : '' ?>>⏳ En attente</option>
      <option value="en cours"   <?= $filterStatut === 'en cours'   ? 'selected' : '' ?>>🔄 En cours</option>
      <option value="traité"     <?= $filterStatut === 'traité'     ? 'selected' : '' ?>>✅ Traité</option>
    </select>

    <!-- Filtre Type -->
    <select name="type" class="filter-input">
      <option value="">Tous les types</option>
      <option value="person"  <?= $filterType === 'person'  ? 'selected' : '' ?>>👤 Personne</option>
      <option value="company" <?= $filterType === 'company' ? 'selected' : '' ?>>🏢 Entreprise</option>
    </select>

    <!-- Boutons -->
    <button type="submit"
            class="bg-teal-500 text-white px-5 py-2 rounded-xl hover:bg-teal-600 transition text-sm font-medium">
      Filtrer
    </button>
    <?php if ($filterStatut || $filterType || $searchQuery): ?>
    <a href="reclamations.php"
       class="text-gray-500 hover:text-red-400 text-sm px-3 py-2 rounded-xl hover:bg-red-50 transition">
      ✕ Reset
    </a>
    <?php endif; ?>
  </form>

  <!-- Résultat filtrage -->
  <?php if ($filterStatut || $filterType || $searchQuery): ?>
  <p class="text-sm text-gray-400 mb-4">
    <?= count($filtered) ?> résultat<?= count($filtered) > 1 ? 's' : '' ?> trouvé<?= count($filtered) > 1 ? 's' : '' ?>
  </p>
  <?php endif; ?>

  <!-- ── Liste des réclamations ── -->
  <div class="grid gap-5">

    <?php if (empty($filtered)): ?>
    <div class="empty-state bg-white rounded-3xl shadow-sm">
      <div class="text-5xl">🔍</div>
      <p class="text-lg font-medium text-gray-500">Aucune réclamation trouvée</p>
      <p class="text-sm">Essayez de modifier vos filtres</p>
      <a href="reclamations.php" class="mt-2 text-teal-500 hover:underline text-sm">Réinitialiser les filtres</a>
    </div>

    <?php else: ?>
    <?php foreach ($filtered as $i => $rec):
      $sNorm = normalizeStatut($rec['statut'] ?? 'en attente');

      if (str_contains($sNorm, 'traite')) {
          $badgeClass = 'bg-green-100 text-green-600';
          $dot        = 'bg-green-500';
          $borderAcc  = 'border-l-green-400';
      } elseif (str_contains($sNorm, 'cours')) {
          $badgeClass = 'bg-orange-100 text-orange-500';
          $dot        = 'bg-orange-400';
          $borderAcc  = 'border-l-orange-400';
      } else {
          $badgeClass = 'bg-red-100 text-red-500';
          $dot        = 'bg-red-400';
          $borderAcc  = 'border-l-red-300';
      }

      // Vérifier si cette réclamation a une notif non vue
      $hasNotif = in_array((int)$rec['id_reclamation'], array_column($notifications, 'id'));

      $stars = str_repeat('⭐', max(0, min(5, (int)($rec['rating'] ?? 0))));
    ?>

    <div class="rec-card bg-white p-6 rounded-3xl shadow-sm border-l-4 <?= $borderAcc ?> relative"
         style="animation-delay: <?= $i * 0.05 ?>s">

      <?php if ($hasNotif): ?>
      <div class="absolute top-4 right-4">
        <span class="bg-teal-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full animate-pulse">
          💬 Nouvelle réponse
        </span>
      </div>
      <?php endif; ?>

      <div class="flex items-start justify-between gap-4 <?= $hasNotif ? 'pr-32' : '' ?>">
        <div class="flex-1">
          <h3 class="text-base font-semibold text-gray-800 line-clamp-2">
            <?= htmlspecialchars($rec['description']) ?>
          </h3>
          <div class="flex flex-wrap items-center gap-3 mt-2 text-xs text-gray-400">
            <span><?= htmlspecialchars($rec['date_creation']) ?></span>
            <?php if (!empty($rec['username_cible'])): ?>
            <span class="bg-teal-50 text-teal-600 px-2 py-0.5 rounded-full">
              @<?= htmlspecialchars($rec['username_cible']) ?>
            </span>
            <?php endif; ?>
            <?php if (!empty($rec['type'])): ?>
            <span class="bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">
              <?= $rec['type'] === 'company' ? '🏢 Entreprise' : '👤 Personne' ?>
            </span>
            <?php endif; ?>
            <?php if ($stars): ?>
            <span><?= $stars ?></span>
            <?php endif; ?>
          </div>
        </div>

        <span class="<?= $badgeClass ?> text-xs font-semibold px-3 py-1 rounded-full whitespace-nowrap flex items-center gap-1.5 self-start shrink-0">
          <span class="w-1.5 h-1.5 rounded-full <?= $dot ?>"></span>
          <?= htmlspecialchars($rec['statut'] ?? 'en attente') ?>
        </span>
      </div>

      <div class="mt-4 pt-4 border-t border-gray-50 flex justify-end">
        <a href="detail_reclamation.php?id=<?= $rec['id_reclamation'] ?>"
           class="text-teal-500 hover:text-teal-700 text-sm font-medium hover:underline transition">
           Voir détails →
        </a>
      </div>

    </div>

    <?php endforeach; ?>
    <?php endif; ?>

  </div>

</main>

<script src="../../assets/js/main.js"></script>
<script>
// ── Toggle notification dropdown ──
function toggleNotif() {
    const dd  = document.getElementById('notifDropdown');
    const btn = document.getElementById('notifBtn');
    dd.classList.toggle('open');
    if (dd.classList.contains('open')) {
        btn.classList.add('bell-shake');
        setTimeout(() => btn.classList.remove('bell-shake'), 700);
    }
}

// Fermer en cliquant dehors
document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('notifWrapper');
    if (wrapper && !wrapper.contains(e.target)) {
        document.getElementById('notifDropdown').classList.remove('open');
    }
});

// ── Métier 5 : Recherche live (optionnel, en plus du submit) ──
const searchInput = document.querySelector('input[name="q"]');
if (searchInput) {
    let debounceTimer;
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            this.closest('form').submit();
        }, 500);
    });
}
</script>

</body>
</html>