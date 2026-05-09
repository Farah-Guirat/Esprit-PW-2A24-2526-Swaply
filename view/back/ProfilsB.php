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
$adminEmail = 'klai.aziz@admin.tn';
$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
$users = $userModel->getAllUsersExceptAdmin($adminEmail, $searchTerm);
$activeProfiles = $userModel->countUsersExceptAdmin($adminEmail, $searchTerm);
$genderStats = $userModel->getGenderStats($adminEmail, $searchTerm);

$genderData = [];
$total = 0;
foreach ($genderStats as $stat) {
    $total += $stat['count'];
}
$startAngle = 0;
foreach ($genderStats as $stat) {
    $percentage = $total > 0 ? ($stat['count'] / $total) * 100 : 0;
    $endAngle = $startAngle + ($percentage / 100) * 360;
    $genreLower = strtolower($stat['genre']);
    $color = $genreLower == 'homme' || $genreLower == 'male' ? '#3b82f6' : '#ec4899';
    $genderData[] = [
        'genre' => $stat['genre'],
        'percentage' => round($percentage, 1),
        'color' => $color,
        'start' => $startAngle,
        'end' => $endAngle
    ];
    $startAngle = $endAngle;
}

$genderDisplay = '';
foreach ($genderData as $data) {
    $genreLower = strtolower($data['genre']);
    $g = ($genreLower == 'homme' || $genreLower == 'male') ? 'H' : 'F';
    $genderDisplay .= $g . ': ' . $data['percentage'] . '%, ';
}
$genderDisplay = rtrim($genderDisplay, ', ');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Back Office - Profils</title>
<link rel="stylesheet" href="styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  .profile-page-header {
    padding: 36px 30px 24px;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
  }

  .profile-page-header h1 {
    font-size: 1.8rem;
    font-weight: 800;
    color: #0f766e;
  }

  .profile-page-header p {
    color: #64748b;
    font-size: 0.9rem;
    margin-top: 4px;
    max-width: 720px;
  }

  .profile-stats-row {
    display: flex;
    gap: 16px;
    padding: 0 30px 28px;
    flex-wrap: wrap;
  }

  .profile-filter-chip,
  .profile-sort-select,
  .profile-search-box {
    font-family: inherit;
  }

  .profile-search-box {
    flex: 1;
    min-width: 240px;
    max-width: 420px;
    position: relative;
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f1f5f9;
    border-radius: 9999px;
    padding: 10px 16px;
  }

  .profile-search-box svg {
    color: #64748b;
    min-width: 18px;
  }

  .profile-search-box input {
    width: 100%;
    border: none;
    outline: none;
    background: transparent;
    font-size: 0.95rem;
    color: #0f172a;
  }

  .profile-search-box button {
    border: none;
    background: #14b8a6;
    color: white;
    border-radius: 9999px;
    padding: 10px 18px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s;
  }

  .profile-search-box button:hover {
    background: #0f766e;
  }

  .profile-filters-bar {
    padding: 0 30px 24px;
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
  }

  .profile-filter-chip {
    padding: 8px 18px;
    border-radius: 50px;
    border: 1.5px solid #e5e7eb;
    background: white;
    color: #64748b;
    font-size: 0.825rem;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
  }

  .profile-filter-chip:hover {
    border-color: #14b8a6;
    color: #14b8a6;
  }

  .profile-filter-chip.active {
    background: #14b8a6;
    border-color: #14b8a6;
    color: white;
  }

  .profile-sort-select {
    margin-left: auto;
    padding: 9px 16px;
    border-radius: 50px;
    border: 1.5px solid #e5e7eb;
    background: white;
    color: #475569;
    cursor: pointer;
    font-size: 0.825rem;
    font-weight: 600;
  }

  .profiles-grid {
    padding: 0 30px 60px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
  }

  .profile-card {
    background: white;
    border-radius: 20px;
    border: 1.5px solid #e5e7eb;
    overflow: hidden;
    transition: transform .25s, box-shadow .25s;
    cursor: pointer;
  }

  .profile-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 36px rgba(43,170,143,.12);
    border-color: #d0f0ea;
  }

  .card-banner { height: 72px; }

  .card-body {
    padding: 0 20px 20px;
    position: relative;
  }

  .card-avatar {
    width: 64px; height: 64px;
    border-radius: 50%;
    border: 3px solid white;
    margin-top: -32px;
    margin-bottom: 10px;
    display: flex; align-items: center; justify-content: center;
    font-weight: 800;
    font-size: 1.4rem;
    color: white;
    box-shadow: 0 2px 8px rgba(0,0,0,.12);
  }

  .card-badge {
    position: absolute;
    top: -16px;
    right: 20px;
    background: #14b8a6;
    color: white;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 50px;
    letter-spacing: .5px;
  }

  .card-name {
    font-size: 1rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 2px;
  }

  .card-role {
    font-size: 0.8rem;
    color: #1f8a73;
    font-weight: 600;
    margin-bottom: 8px;
  }

  .card-location {
    font-size: 0.775rem;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 4px;
    margin-bottom: 12px;
  }

  .card-skills {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 14px;
  }

  .skill-tag {
    background: #d0f0ea;
    color: #1f8a73;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 50px;
  }

  .card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 14px;
    border-top: 1px solid #e5e7eb;
  }

  .card-rating {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.8rem;
    font-weight: 700;
    color: #0f172a;
  }

  .star { color: #f59e0b; }

  .card-btn {
    background: #14b8a6;
    color: white;
    border: none;
    padding: 7px 16px;
    border-radius: 50px;
    font-family: inherit;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    transition: background .2s;
  }

  .card-btn:hover { background: #0f766e; }

  .search-message {
    font-size: 0.95rem;
    color: #64748b;
    padding: 0 30px 18px;
  }

  @media (max-width: 900px) {
    .profile-page-header,
    .profile-filters-bar,
    .profile-stats-row {
      padding-left: 20px;
      padding-right: 20px;
    }

    .profile-search-box { max-width: 100%; }
    .profile-sort-select { margin-left: 0; width: 100%; }
    .profile-filter-chip { flex: 1 1 auto; }
  }
</style>
</head>
<body>
<div class="flex h-screen overflow-hidden">
  <div class="sidebar">
    <div class="logo">
      <span class="icon">📋</span>
      <h1>JobBoard Admin</h1>
    </div>
    <div class="menu">
      <a href="swaplyB.php" class="menu-item" id="menu-dashboard">
        <i class="fa-solid fa-house"></i> Dashboard
      </a>
      
      <a href="ProfilsB.php" class="menu-item active" id="menu-profiles">
        <i class="fa-solid fa-user"></i> Profils
      </a>
      <a href="/swaply/view/back/projets.php" onclick="showPage('projets')" class="menu-item" id="menu-projets">
        <i class="fa-solid fa-file"></i> Projets
      </a>
      <a href="/swaply/public/index.php?action=dashboard" class="menu-item" id="menu-dashboard">
    <i class="fa-solid fa-briefcase"></i> Offres & Demandes
</a>
      <a href="#" class="menu-item" id="menu-publications">
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
  <div class="main-content">
    <header class="header">
      <h2 id="page-title">Profils</h2>
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
          <img src="https://i.pravatar.cc/40?img=12" alt="Admin">
          <div>
            <p class="name">Admin</p>
            <p class="role">Super Admin</p>
          </div>
        </div>
        <a href="../../controller/logout.php" class="logout-btn" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?');">
          <i class="fa-solid fa-sign-out-alt"></i> Déconnexion
        </a>
      </div>
    </header>
    <div class="page-content">
      <div class="profile-page">
        <div class="profile-page-header">
          <div>
            <h1>Explorer les profils</h1>
            <p>Découvrez des talents et des experts prêts à collaborer.</p>
          </div>
        </div>
        <div class="profile-stats-row">
          <div class="stat-pill">
            <div class="stat-icon">👥</div>
            <div>
              <div class="stat-num"><?= number_format($activeProfiles, 0, '.', ' ') ?></div>
              <div class="stat-label">Profils actifs</div>
            </div>
          </div>
          <div class="stat-pill">
            <div class="stat-icon">🌍</div>
            <div>
              <div class="stat-num">38</div>
              <div class="stat-label">Pays représentés</div>
            </div>
          </div>
          <div class="stat-pill">
            <div class="stat-icon">⭐</div>
            <div>
              <div class="stat-num">4.8</div>
              <div class="stat-label">Note moyenne</div>
            </div>
          </div>
          <div class="stat-pill">
            <div class="stat-icon">👫</div>
            <div>
              <div class="stat-num" style="width: 100px; height: 20px; background: #e5e7eb; border-radius: 10px; overflow: hidden; margin: 0 auto;">
                <?php foreach ($genderData as $data): ?>
                  <span style="display: inline-block; height: 100%; width: <?=$data['percentage']?>%; background: <?=$data['color']?>;"></span>
                <?php endforeach; ?>
              </div>
              <div class="stat-label"><?=$genderDisplay?></div>
            </div>
          </div>
        </div>
        <div style="padding: 0 30px 20px;">
          <a href="export_pdf.php" class="card-btn" style="text-decoration: none;">Exporter PDF</a>
        </div>
        <div class="profile-filters-bar">
          <form method="get" action="ProfilsB.php" class="profile-search-box">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" xmlns="http://www.w3.org/2000/svg">
              <circle cx="11" cy="11" r="8"/>
              <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="q" placeholder="Rechercher un profil, compétence…" value="<?= htmlspecialchars($searchTerm) ?>">
            <button type="submit">Rechercher</button>
          </form>
          <button type="button" class="profile-filter-chip active">Tous</button>
          <button type="button" class="profile-filter-chip">Développeurs</button>
          <button type="button" class="profile-filter-chip">Designers</button>
          <button type="button" class="profile-filter-chip">Marketing</button>
          <button type="button" class="profile-filter-chip">Finance</button>
          <select class="profile-sort-select">
            <option>Trier : Plus récent</option>
            <option>Trier : Alphabétique</option>
          </select>
        </div>
        <div id="search-message" class="search-message" style="display:none;"></div>
        <?php if ($searchTerm !== '' && empty($users)): ?>
          <div style="padding: 0 30px 20px; color: #64748b;">Aucun profil trouvé pour « <?= htmlspecialchars($searchTerm) ?> ».</div>
        <?php endif; ?>
        <div class="profiles-grid">
          <?php if (empty($users)): ?>
            <div class="profile-card">
              <div class="card-body">
                <div class="card-name">Aucun profil disponible</div>
                <p style="color: #64748b;">Aucun utilisateur n'est enregistré pour le moment.</p>
              </div>
            </div>
          <?php else: ?>
            <?php foreach ($users as $profile): ?>
              <?php
                $initials = strtoupper(mb_substr($profile['nom'], 0, 1) . mb_substr($profile['prenom'], 0, 1));
                $displayName = htmlspecialchars($profile['nom'] . ' ' . $profile['prenom']);
                $searchText = htmlspecialchars(mb_strtolower($profile['nom'] . ' ' . $profile['prenom'] . ' ' . $profile['email']));
                $photo = $profile['photo'] ?? null;
                $isBanned = isset($profile['banned']) && $profile['banned'];
                $status = $isBanned ? 'Inactif' : 'Actif';
                $statusColor = $isBanned ? '#64748b' : '#14b8a6';
              ?>
              <div class="profile-card" data-search="<?= $searchText ?>" data-id="<?= $profile['id_u'] ?>" data-name="<?= htmlspecialchars($profile['nom'] . ' ' . $profile['prenom']) ?>">
                <div class="card-banner" style="background: linear-gradient(135deg,#2baa8f,#1f6f5c);"></div>
                <div class="card-body">
                  <div class="card-badge" style="background: <?= $statusColor ?>; color: white;"><?= $status ?></div>
                  <div class="card-avatar" style="background: #2baa8f;">
                    <?php if ($photo): ?>
                      <img src="../../uploads/profiles/<?= htmlspecialchars($photo) ?>" alt="Photo" style="width:100%; height:100%; border-radius:50%; object-fit:cover;">
                    <?php else: ?>
                      <?= $initials ?>
                    <?php endif; ?>
                  </div>
                  <div class="card-name"><?= $displayName ?></div>
                  <div class="card-role">Utilisateur inscrit</div>
                  <div class="card-location">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" xmlns="http://www.w3.org/2000/svg"><path d="M21 10c0 6-9 13-9 13S3 16 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    Tunisia
                  </div>
                  <div class="card-skills">
                    <span class="skill-tag">Profil enregistré</span>
                  </div>
                  <div class="card-footer">
                    <div class="card-rating"><span class="star">★</span> 4.8 <span style="color:#64748b;font-weight:400">(n/a)</span></div>
                    <a class="card-btn" href="AfficheP.php?id=<?= urlencode($profile['id_u']) ?>">Voir profil</a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  const winSearchInput = document.querySelector('.profile-search-box input[name="q"]');
  const profileCards = Array.from(document.querySelectorAll('.profile-card'));
  const searchMessage = document.getElementById('search-message');

  function filterProfiles() {
    const query = winSearchInput.value.trim().toLowerCase();
    let visibleCount = 0;

    profileCards.forEach(card => {
      const text = card.dataset.search || '';
      const match = query === '' || text.includes(query);
      card.style.display = match ? '' : 'none';
      if (match) visibleCount++;
    });

    if (query !== '' && visibleCount === 0) {
      searchMessage.textContent = `Aucun profil trouvé pour « ${winSearchInput.value} ».`;
      searchMessage.style.display = 'block';
    } else {
      searchMessage.style.display = 'none';
    }
  }

  if (winSearchInput) {
    winSearchInput.addEventListener('input', filterProfiles);
    if (winSearchInput.value.trim() !== '') {
      filterProfiles();
    }
  }

  document.querySelectorAll('.profile-filter-chip').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.profile-filter-chip').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
    });
  });

  const sortSelect = document.querySelector('.profile-sort-select');
  sortSelect.addEventListener('change', () => {
    const value = sortSelect.value;
    const grid = document.querySelector('.profiles-grid');
    const cards = Array.from(grid.querySelectorAll('.profile-card'));
    if (value === 'Trier : Plus récent') {
      cards.sort((a, b) => parseInt(b.dataset.id) - parseInt(a.dataset.id));
    } else if (value === 'Trier : Alphabétique') {
      cards.sort((a, b) => a.dataset.name.localeCompare(b.dataset.name));
    }
    cards.forEach(card => grid.appendChild(card));
  });
</script>
</body>
</html>
