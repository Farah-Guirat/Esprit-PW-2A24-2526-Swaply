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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Swaply – Profils</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --teal: #2baa8f;
    --teal-dark: #1f8a73;
    --teal-light: #e8f7f4;
    --teal-mid: #d0f0ea;
    --text: #1a1a2e;
    --muted: #6b7280;
    --border: #e5e7eb;
    --white: #ffffff;
    --bg: #f9fafb;
  }

  body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
  }

  /* NAV */
  nav {
    background: var(--white);
    border-bottom: 1px solid var(--border);
    padding: 0 40px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 1px 6px rgba(0,0,0,0.05);
  }

  .nav-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 800;
    font-size: 1.25rem;
    color: var(--text);
    text-decoration: none;
  }

  .logo-icon {
    width: 36px; height: 36px;
    background: var(--teal);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: white;
    font-weight: 800;
    font-size: 1rem;
  }

  .nav-links {
    display: flex;
    gap: 32px;
    list-style: none;
  }

  .nav-links a {
    text-decoration: none;
    color: var(--muted);
    font-size: 0.9rem;
    font-weight: 500;
    transition: color .2s;
  }

  .nav-links a:hover, .nav-links a.active {
    color: var(--teal);
    font-weight: 700;
  }

  .nav-links a.active {
    position: relative;
  }

  .nav-links a.active::after {
    content: '';
    position: absolute;
    bottom: -22px;
    left: 0; right: 0;
    height: 2px;
    background: var(--teal);
    border-radius: 2px;
  }

  .nav-avatar {
    width: 38px; height: 38px;
    border-radius: 50%;
    background: #ccc;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid var(--teal-mid);
  }

  .nav-avatar img { width: 100%; height: 100%; object-fit: cover; }

  /* PAGE HEADER */
  .page-header {
    padding: 36px 40px 24px;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
  }

  .page-header h1 {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--text);
  }

  .page-header p {
    color: var(--muted);
    font-size: 0.9rem;
    margin-top: 4px;
  }

  /* FILTERS BAR */
  .filters-bar {
    padding: 0 40px 24px;
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
  }

  .search-box {
    flex: 1;
    min-width: 240px;
    max-width: 360px;
    position: relative;
  }

  .search-box {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .search-box input {
    width: 100%;
    padding: 10px 16px 10px 42px;
    border: 1.5px solid var(--border);
    border-radius: 50px;
    font-family: inherit;
    font-size: 0.875rem;
    background: white;
    outline: none;
    transition: border .2s;
    color: var(--text);
  }

  .search-box input:focus { border-color: var(--teal); }

  .search-box svg {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--muted);
  }

  .search-btn {
    padding: 10px 18px;
    border-radius: 50px;
    border: 1.5px solid var(--teal);
    background: var(--teal);
    color: white;
    font-family: inherit;
    font-size: 0.85rem;
    cursor: pointer;
    transition: background .2s, border .2s;
  }

  .search-btn:hover {
    background: #1f8a73;
    border-color: #1f8a73;
  }

  .search-message {
    font-size: 0.95rem;
    color: var(--muted);
    padding: 0 40px 18px;
  }

  .filter-chip {
    padding: 8px 18px;
    border-radius: 50px;
    border: 1.5px solid var(--border);
    background: white;
    font-family: inherit;
    font-size: 0.825rem;
    font-weight: 600;
    color: var(--muted);
    cursor: pointer;
    transition: all .2s;
  }

  .filter-chip:hover { border-color: var(--teal); color: var(--teal); }
  .filter-chip.active { background: var(--teal); border-color: var(--teal); color: white; }

  .sort-select {
    padding: 9px 16px;
    border-radius: 50px;
    border: 1.5px solid var(--border);
    background: white;
    font-family: inherit;
    font-size: 0.825rem;
    font-weight: 600;
    color: var(--muted);
    cursor: pointer;
    outline: none;
    margin-left: auto;
  }

  /* GRID */
  .profiles-grid {
    padding: 0 40px 60px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
  }

  /* CARD */
  .profile-card {
    background: white;
    border-radius: 20px;
    border: 1.5px solid var(--border);
    overflow: hidden;
    transition: transform .25s, box-shadow .25s;
    cursor: pointer;
    animation: fadeUp .4s ease both;
  }

  .profile-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 36px rgba(43,170,143,.12);
    border-color: var(--teal-mid);
  }

  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  .profile-card:nth-child(1) { animation-delay: .05s; }
  .profile-card:nth-child(2) { animation-delay: .10s; }
  .profile-card:nth-child(3) { animation-delay: .15s; }
  .profile-card:nth-child(4) { animation-delay: .20s; }
  .profile-card:nth-child(5) { animation-delay: .25s; }
  .profile-card:nth-child(6) { animation-delay: .30s; }
  .profile-card:nth-child(7) { animation-delay: .35s; }
  .profile-card:nth-child(8) { animation-delay: .40s; }

  .card-banner {
    height: 72px;
  }

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
    background: var(--teal);
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
    color: var(--text);
    margin-bottom: 2px;
  }

  .card-role {
    font-size: 0.8rem;
    color: var(--teal-dark);
    font-weight: 600;
    margin-bottom: 8px;
  }

  .card-location {
    font-size: 0.775rem;
    color: var(--muted);
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
    background: var(--teal-light);
    color: var(--teal-dark);
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
    border-top: 1px solid var(--border);
  }

  .card-rating {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--text);
  }

  .star { color: #f59e0b; }

  .card-btn {
    background: var(--teal);
    color: white;
    border: none;
    padding: 7px 16px;
    border-radius: 50px;
    font-family: inherit;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    transition: background .2s;
  }

  .card-btn:hover { background: var(--teal-dark); }

  /* Stats row */
  .stats-row {
    display: flex;
    gap: 16px;
    padding: 0 40px 28px;
  }

  .stat-pill {
    background: white;
    border: 1.5px solid var(--border);
    border-radius: 16px;
    padding: 14px 22px;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .stat-icon {
    width: 40px; height: 40px;
    border-radius: 12px;
    background: var(--teal-light);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
  }

  .stat-num {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--text);
  }

  .stat-label {
    font-size: 0.75rem;
    color: var(--muted);
    font-weight: 500;
  }
</style>
</head>
<body>

<!-- NAV -->
<nav>
  <a class="nav-logo" href="swaplyB.php">
    <div class="logo-icon">S</div>
    Swaply
  </a>
  <ul class="nav-links">
      <li><a href="swaplyB.php">Accueil</a></li>
    <li><a href="#" class="active">Profils</a></li>
    <li><a href="#">Projets</a></li>
    <li><a href="#">Offres</a></li>
    <li><a href="#">Demandes</a></li>
    <li><a href="#">Publications</a></li>
    <li><a href="#">Messages</a></li>
    <li><a href="#">Réclamations</a></li>
  </ul>
  <div class="nav-avatar">
    <svg viewBox="0 0 36 36" fill="#888" xmlns="http://www.w3.org/2000/svg">
      <circle cx="18" cy="13" r="7" fill="#aaa"/>
      <path d="M4 30c0-7.7 6.3-14 14-14s14 6.3 14 14" fill="#ccc"/>
    </svg>
  </div>
</nav>

<!-- PAGE HEADER -->
<div class="page-header">
  <div>
    <h1>Explorer les profils</h1>
    <p>Découvrez des talents et des experts prêts à collaborer</p>
  </div>
</div>

<!-- STATS -->
<div class="stats-row">
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
</div>

<!-- FILTERS -->
<div class="filters-bar">
  <form method="get" action="ProfilsB.php" class="search-box">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
      <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
    </svg>
    <input type="text" name="q" placeholder="Rechercher un profil, compétence…" value="<?= htmlspecialchars($searchTerm) ?>">
    <button type="submit" class="search-btn">Rechercher</button>
  </form>
  <button class="filter-chip active">Tous</button>
  <button class="filter-chip">Développeurs</button>
  <button class="filter-chip">Designers</button>
  <button class="filter-chip">Marketing</button>
  <button class="filter-chip">Finance</button>
  <select class="sort-select">
    <option>Trier : Plus récent</option>
    <option>Trier : Mieux notés</option>
    <option>Trier : Alphabétique</option>
  </select>
</div>

<div id="search-message" class="search-message" style="display:none;"></div>

<?php if ($searchTerm !== '' && empty($users)): ?>
  <div style="padding: 0 40px 20px; color: var(--muted);">Aucun profil trouvé pour « <?= htmlspecialchars($searchTerm) ?> ».</div>
<?php endif; ?>

<!-- GRID -->
<div class="profiles-grid">
  <?php if (empty($users)): ?>
    <div class="profile-card">
      <div class="card-body">
        <div class="card-name">Aucun profil disponible</div>
        <p style="color: var(--muted);">Aucun utilisateur n'est enregistré pour le moment.</p>
      </div>
    </div>
  <?php else: ?>
    <?php foreach ($users as $profile): ?>
      <?php
        $initials = strtoupper(mb_substr($profile['nom'], 0, 1) . mb_substr($profile['prenom'], 0, 1));
        $displayName = htmlspecialchars($profile['nom'] . ' ' . $profile['prenom']);
        $searchText = htmlspecialchars(mb_strtolower($profile['nom'] . ' ' . $profile['prenom'] . ' ' . $profile['email']));
      ?>
      <div class="profile-card" data-search="<?= $searchText ?>">
        <div class="card-banner" style="background: linear-gradient(135deg,#2baa8f,#1f6f5c);"></div>
        <div class="card-body">
          <div class="card-badge">✔ Vérifié</div>
          <div class="card-avatar" style="background: #2baa8f;"><?= $initials ?></div>
          <div class="card-name"><?= $displayName ?></div>
          <div class="card-role">Utilisateur inscrit</div>
          <div class="card-location">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 6-9 13-9 13S3 16 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
            Tunisia
          </div>
          <div class="card-skills">
            <span class="skill-tag">Profil enregistré</span>
          </div>
          <div class="card-footer">
            <div class="card-rating"><span class="star">★</span> 4.8 <span style="color:var(--muted);font-weight:400">(n/a)</span></div>
            <a class="card-btn" href="AfficheP.php?id=<?= urlencode($profile['id_u']) ?>">Voir profil</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<script>
  const searchInput = document.querySelector('.search-box input[name="q"]');
  const profileCards = Array.from(document.querySelectorAll('.profile-card'));
  const searchMessage = document.getElementById('search-message');

  function filterProfiles() {
    const query = searchInput.value.trim().toLowerCase();
    let visibleCount = 0;

    profileCards.forEach(card => {
      const text = card.dataset.search || '';
      const match = query === '' || text.includes(query);
      card.style.display = match ? '' : 'none';
      if (match) visibleCount++;
    });

    if (query !== '' && visibleCount === 0) {
      searchMessage.textContent = `Aucun profil trouvé pour « ${searchInput.value} ».`;
      searchMessage.style.display = 'block';
    } else {
      searchMessage.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterProfiles);
    if (searchInput.value.trim() !== '') {
      filterProfiles();
    }
  }

  document.querySelectorAll('.filter-chip').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.filter-chip').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
    });
  });
</script>
</body>
</html>
