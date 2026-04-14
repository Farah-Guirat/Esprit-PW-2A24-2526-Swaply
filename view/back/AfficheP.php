<?php
session_start();
require_once __DIR__ . "/../../config/Database.php";
require_once __DIR__ . "/../../model/User.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['email'] !== 'klai.aziz@admin.tn') {
    header("Location: ../front/login.php");
    exit();
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header("Location: ProfilsB.php");
    exit();
}

$database = new Database();
$conn = $database->connect();
$userModel = new User($conn);
$profile = $userModel->getUserById($id);
if (!$profile || $profile['email'] === 'klai.aziz@admin.tn') {
    header("Location: ProfilsB.php");
    exit();
}

$initials = strtoupper(mb_substr($profile['nom'], 0, 1) . mb_substr($profile['prenom'], 0, 1));
$fullName = htmlspecialchars($profile['nom'] . ' ' . $profile['prenom'], ENT_QUOTES, 'UTF-8');
$profileEmail = htmlspecialchars($profile['email'], ENT_QUOTES, 'UTF-8');
$profilePhone = htmlspecialchars($profile['telephone'], ENT_QUOTES, 'UTF-8');
$profileGenre = htmlspecialchars($profile['genre'], ENT_QUOTES, 'UTF-8');
$profileBirthRaw = $profile['date_naissance'] ?? '';
$profileBirth = '';
$profileAge = '';

if (!empty($profileBirthRaw)) {
    $dob = DateTime::createFromFormat('Y-m-d', $profileBirthRaw);
    if ($dob === false) {
        $dob = new DateTime($profileBirthRaw);
    }

    if ($dob) {
        $months = [
            'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
            'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'
        ];
        $monthIndex = (int)$dob->format('m') - 1;
        $profileBirth = htmlspecialchars($dob->format('d') . ' ' . ($months[$monthIndex] ?? $dob->format('F')) . ' ' . $dob->format('Y'), ENT_QUOTES, 'UTF-8');
        $profileAge = $dob->diff(new DateTime('today'))->y . ' ans';
    } else {
        $profileBirth = htmlspecialchars($profileBirthRaw, ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Swaply – Détail Profil</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --teal:       #2baa8f;
    --teal-dark:  #1f8a73;
    --teal-light: #e8f7f4;
    --teal-mid:   #d0f0ea;
    --text:       #1a1a2e;
    --muted:      #6b7280;
    --border:     #e5e7eb;
    --white:      #ffffff;
    --bg:         #f9fafb;
    --success:    #10b981;
    --danger:     #ef4444;
  }

  body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
  }

  /* ── NAV ── */
  nav {
    background: var(--white);
    border-bottom: 1px solid var(--border);
    padding: 0 40px;
    height: 64px;
    display: flex; align-items: center; justify-content: space-between;
    position: sticky; top: 0; z-index: 100;
    box-shadow: 0 1px 6px rgba(0,0,0,.05);
  }
  .nav-logo {
    display: flex; align-items: center; gap: 10px;
    font-weight: 800; font-size: 1.25rem;
    color: var(--text); text-decoration: none;
  }
  .logo-icon {
    width: 36px; height: 36px; background: var(--teal);
    border-radius: 10px; display: flex; align-items: center; justify-content: center;
    color: white; font-weight: 800; font-size: 1rem;
  }
  .nav-links { display: flex; gap: 32px; list-style: none; }
  .nav-links a {
    text-decoration: none; color: var(--muted);
    font-size: .9rem; font-weight: 500; transition: color .2s;
  }
  .nav-links a:hover, .nav-links a.active { color: var(--teal); font-weight: 700; }
  .nav-avatar {
    width: 38px; height: 38px; border-radius: 50%;
    background: var(--teal); border: 2.5px solid var(--teal-mid);
    display: flex; align-items: center; justify-content: center;
    color: white; font-weight: 800; font-size: .85rem; cursor: pointer;
  }

  /* ── BREADCRUMB ── */
  .top-bar {
    padding: 20px 40px 0;
    display: flex; align-items: center; justify-content: space-between;
  }
  .breadcrumb {
    display: flex; align-items: center; gap: 8px;
    font-size: .8rem; color: var(--muted);
  }
  .breadcrumb a { color: var(--teal); font-weight: 600; text-decoration: none; }
  .breadcrumb a:hover { text-decoration: underline; }
  .bc-sep { color: var(--border); }
  .close-btn {
    display: flex; align-items: center; gap: 7px;
    padding: 9px 18px;
    background: white;
    border: 1.5px solid var(--border);
    border-radius: 50px;
    font-family: inherit; font-size: .82rem; font-weight: 700;
    color: var(--muted); cursor: pointer;
    transition: all .2s;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
  }
  .close-btn:hover { border-color: var(--danger); color: var(--danger); background: #fff5f5; }

  /* ── PAGE LAYOUT ── */
  .page-wrap {
    max-width: 1060px;
    margin: 0 auto;
    padding: 24px 40px 60px;
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 24px;
    align-items: start;
  }

  /* ── LEFT CARD ── */
  .left-col { display: flex; flex-direction: column; gap: 18px; }

  .profile-hero {
    background: var(--white);
    border-radius: 22px;
    border: 1.5px solid var(--border);
    overflow: hidden;
    animation: fadeUp .35s ease both;
  }

  .hero-banner {
    height: 52px;
    background: linear-gradient(135deg, #2baa8f 0%, #1a6e58 100%);
    position: relative;
  }
  .hero-banner::before {
    content: '';
    position: absolute; inset: 0;
    background: repeating-linear-gradient(
      45deg,
      transparent,
      transparent 18px,
      rgba(255,255,255,.05) 18px,
      rgba(255,255,255,.05) 36px
    );
  }

  .hero-body { padding: 0 24px 24px; }

  .avatar-row {
    display: flex; align-items: flex-end; justify-content: space-between;
    margin-top: 12px; margin-bottom: 14px;
  }
  .avatar-circle {
    width: 80px; height: 80px; border-radius: 50%;
    background: var(--teal);
    border: 4px solid white;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.8rem; font-weight: 800; color: white;
    box-shadow: 0 4px 16px rgba(43,170,143,.3);
  }
  .verified-pill {
    display: inline-flex; align-items: center; gap: 5px;
    background: var(--teal-light);
    color: var(--teal-dark);
    font-size: .7rem; font-weight: 700;
    padding: 5px 12px; border-radius: 50px;
    border: 1px solid var(--teal-mid);
  }

  .hero-name { font-size: 1.25rem; font-weight: 800; margin-bottom: 3px; }
  .hero-role { font-size: .82rem; color: var(--teal-dark); font-weight: 700; margin-bottom: 10px; }

  .hero-meta { display: flex; flex-direction: column; gap: 6px; }
  .meta-row {
    display: flex; align-items: center; gap: 8px;
    font-size: .78rem; color: var(--muted); font-weight: 500;
  }
  .meta-row svg { flex-shrink: 0; color: var(--teal); }

  .hero-divider { height: 1px; background: var(--border); margin: 16px 0; }

  .stats-row { display: grid; grid-template-columns: repeat(3,1fr); text-align: center; }
  .stat-item { padding: 10px 0; }
  .stat-item + .stat-item { border-left: 1px solid var(--border); }
  .stat-num { font-size: 1.1rem; font-weight: 800; color: var(--text); }
  .stat-label { font-size: .68rem; color: var(--muted); margin-top: 1px; font-weight: 500; }

  /* Action buttons */
  .action-btns { display: flex; flex-direction: column; gap: 10px; }
  .btn-primary {
    width: 100%; padding: 12px;
    background: var(--teal); color: white;
    border: none; border-radius: 14px;
    font-family: inherit; font-size: .875rem; font-weight: 700;
    cursor: pointer; transition: background .2s;
    display: flex; align-items: center; justify-content: center; gap: 8px;
  }
  .btn-primary:hover { background: var(--teal-dark); }
  .btn-outline {
    width: 100%; padding: 11px;
    background: white; color: var(--text);
    border: 1.5px solid var(--border); border-radius: 14px;
    font-family: inherit; font-size: .875rem; font-weight: 700;
    cursor: pointer; transition: all .2s;
    display: flex; align-items: center; justify-content: center; gap: 8px;
  }
  .btn-outline:hover { border-color: var(--teal); color: var(--teal); }
  .btn-danger {
    width: 100%; padding: 11px;
    background: var(--danger); color: white;
    border: 1.5px solid var(--danger); border-radius: 14px;
    font-family: inherit; font-size: .875rem; font-weight: 700;
    cursor: pointer; transition: all .2s;
    display: flex; align-items: center; justify-content: center; gap: 8px;
  }
  .btn-danger:hover { background: #c81e1e; border-color: #c81e1e; }

  /* Skills card */
  .skills-card {
    background: var(--white);
    border-radius: 18px;
    border: 1.5px solid var(--border);
    padding: 20px 22px;
    animation: fadeUp .4s ease both .05s;
  }
  .skills-title {
    font-size: .8rem; font-weight: 800; color: var(--muted);
    text-transform: uppercase; letter-spacing: .5px;
    margin-bottom: 12px;
  }
  .skills-wrap { display: flex; flex-wrap: wrap; gap: 7px; }
  .skill-tag {
    background: var(--teal-light); color: var(--teal-dark);
    font-size: .73rem; font-weight: 700;
    padding: 5px 12px; border-radius: 50px;
  }

  /* ── RIGHT COL ── */
  .right-col { display: flex; flex-direction: column; gap: 20px; }

  .info-card {
    background: var(--white);
    border-radius: 20px;
    border: 1.5px solid var(--border);
    padding: 26px 30px;
    animation: fadeUp .4s ease both;
  }
  .info-card:nth-child(2) { animation-delay: .06s; }
  .info-card:nth-child(3) { animation-delay: .12s; }

  .card-title {
    display: flex; align-items: center; gap: 10px;
    font-size: .95rem; font-weight: 800;
    margin-bottom: 22px;
  }
  .card-title-icon {
    width: 34px; height: 34px; border-radius: 10px;
    background: var(--teal-light);
    display: flex; align-items: center; justify-content: center;
    font-size: .95rem;
  }

  /* Info rows */
  .info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px 28px;
  }

  .info-item { display: flex; flex-direction: column; gap: 5px; }
  .info-item.full { grid-column: 1 / -1; }

  .info-label {
    font-size: .7rem; font-weight: 700;
    color: var(--muted); text-transform: uppercase; letter-spacing: .5px;
  }

  .info-value-wrap {
    display: flex; align-items: center; gap: 10px;
    background: var(--bg);
    border: 1.5px solid var(--border);
    border-radius: 12px;
    padding: 10px 14px;
  }
  .info-value-wrap svg { color: var(--teal); flex-shrink: 0; }
  .info-value {
    font-size: .875rem; font-weight: 600; color: var(--text);
    flex: 1;
  }

  /* Password field */
  .pwd-dots {
    letter-spacing: 4px; font-size: 1rem; color: var(--muted);
    margin-top: 1px;
  }
  .eye-btn {
    background: none; border: none; cursor: pointer;
    color: var(--muted); padding: 0; line-height: 0;
    transition: color .2s;
  }
  .eye-btn:hover { color: var(--teal); }

  /* Gender badge */
  .gender-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: #eff6ff; color: #3b82f6;
    font-size: .78rem; font-weight: 700;
    padding: 6px 14px; border-radius: 50px;
    border: 1px solid #bfdbfe;
  }

  /* Rating */
  .rating-row {
    display: flex; align-items: center; gap: 6px;
    margin-bottom: 16px;
  }
  .stars { display: flex; gap: 3px; }
  .star { color: #f59e0b; font-size: 1rem; }
  .star.empty { color: #d1d5db; }
  .rating-num { font-size: 1rem; font-weight: 800; }
  .rating-count { font-size: .78rem; color: var(--muted); }

  /* Review item */
  .review-item {
    padding: 14px 0;
    border-bottom: 1px solid var(--border);
  }
  .review-item:last-child { border-bottom: none; padding-bottom: 0; }
  .review-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 6px;
  }
  .reviewer { display: flex; align-items: center; gap: 9px; }
  .rev-avatar {
    width: 32px; height: 32px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .75rem; font-weight: 800; color: white;
  }
  .rev-name { font-size: .825rem; font-weight: 700; }
  .rev-date { font-size: .7rem; color: var(--muted); }
  .rev-stars { display: flex; gap: 2px; }
  .rev-star { color: #f59e0b; font-size: .75rem; }
  .review-text { font-size: .8rem; color: var(--muted); line-height: 1.5; }

  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* Password toggle */
  .pwd-hidden { display: inline; }
  .pwd-visible { display: none; }
</style>
</head>
<body>

<!-- NAV -->
<nav>
  <a class="nav-logo" href="#">
    <div class="logo-icon">S</div>
    Swaply
  </a>
  <ul class="nav-links">
    <li><a href="#">Accueil</a></li>
    <li><a href="#" class="active">Profils</a></li>
    <li><a href="#">Projets</a></li>
    <li><a href="#">Offres</a></li>
    <li><a href="#">Demandes</a></li>
    <li><a href="#">Publications</a></li>
    <li><a href="#">Messages</a></li>
    <li><a href="#">Réclamations</a></li>
  </ul>
  <div class="nav-avatar"><?= $initials ?></div>
</nav>

<!-- TOP BAR -->
<div class="top-bar">
  <div class="breadcrumb">
    <a href="swaplyB.php">Accueil</a>
    <span class="bc-sep">›</span>
    <a href="ProfilsB.php">Profils</a>
    <span class="bc-sep">›</span>
    <span><?= $fullName ?></span>
  </div>
  <button class="close-btn" onclick="window.location.href='ProfilsB.php'">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
      <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
    </svg>
    Fermer
  </button>
</div>

<!-- PAGE -->
<div class="page-wrap">

  <!-- LEFT -->
  <div class="left-col">
    <div class="profile-hero">
      <div class="hero-banner"></div>
      <div class="hero-body">
        <div class="avatar-row">
          <div class="avatar-circle"><?= $initials ?></div>
          <div class="verified-pill">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
            Vérifié
          </div>
        </div>
        <div class="hero-name"><?= $fullName ?></div>
        <div class="hero-role">Utilisateur</div>
        <div class="hero-meta">
          <div class="meta-row">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M21 10c0 6-9 13-9 13S3 16 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
            Tunis, Tunisie
          </div>
          <div class="meta-row">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Date de naissance : <?= $profileBirth ?: 'N/A' ?>
          </div>
          <div class="meta-row">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Disponible pour collaborer
          </div>
        </div>
        <div class="hero-divider"></div>
        <div class="stats-row">
          <div class="stat-item">
            <div class="stat-num">12</div>
            <div class="stat-label">Projets</div>
          </div>
          <div class="stat-item">
            <div class="stat-num">4.9</div>
            <div class="stat-label">Note</div>
          </div>
          <div class="stat-item">
            <div class="stat-num">32</div>
            <div class="stat-label">Avis</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="action-btns">
      <button class="btn-primary">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
        Envoyer un message
      </button>
      <form id="deleteProfileForm" method="POST" action="../../controller/deleteProfile.php">
        <input type="hidden" name="id_u" value="<?= $profile['id_u'] ?>">
        <button type="button" class="btn-danger" onclick="confirmDelete()">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
          Supprimer le compte
        </button>
      </form>
    </div>

    <!-- Skills -->
    <div class="skills-card">
      <div class="skills-title">Compétences</div>
      <div class="skills-wrap">
        <span class="skill-tag">React</span>
        <span class="skill-tag">Node.js</span>
        <span class="skill-tag">MongoDB</span>
        <span class="skill-tag">TypeScript</span>
        <span class="skill-tag">Docker</span>
        <span class="skill-tag">REST API</span>
        <span class="skill-tag">PostgreSQL</span>
        <span class="skill-tag">Git</span>
      </div>
    </div>
  </div>

  <!-- RIGHT -->
  <div class="right-col">

    <!-- Informations personnelles -->
    <div class="info-card">
      <div class="card-title">
        <div class="card-title-icon">👤</div>
        Informations personnelles
      </div>

      <div class="info-grid">

        <!-- Nom -->
        <div class="info-item">
          <div class="info-label">Nom</div>
          <div class="info-value-wrap">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <div class="info-value"><?= htmlspecialchars($profile['nom'], ENT_QUOTES, 'UTF-8') ?></div>
          </div>
        </div>

        <!-- Prénom -->
        <div class="info-item">
          <div class="info-label">Prénom</div>
          <div class="info-value-wrap">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <span class="info-value"><?= htmlspecialchars($profile['prenom'], ENT_QUOTES, 'UTF-8') ?></span>
          </div>
        </div>

        <!-- Genre (sous Prénom) -->
        <div class="info-item">
          <div class="info-label">Genre</div>
          <div class="info-value-wrap" style="background:transparent;border:none;padding:6px 0;">
            <span class="gender-badge">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="8" r="5"/><path d="M3 21v-1a9 9 0 0118 0v1"/></svg>
              Femme
            </span>
          </div>
        </div>

        <!-- Email -->
        <div class="info-item">
          <div class="info-label">Adresse e-mail</div>
          <div class="info-value-wrap">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <span class="info-value"><a href="mailto:<?= $profileEmail ?>"><?= $profileEmail ?></a></span>
          </div>
        </div>

        <!-- Téléphone (sous Email) -->
        <div class="info-item full">
          <div class="info-label">Téléphone</div>
          <div class="info-value-wrap">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.67A2 2 0 012.18 1h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 8.09a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 15.09"/></svg>
            <span class="info-value">+216 55 123 456</span>
          </div>
        </div>

        <!-- Mot de passe -->
        <div class="info-item">
          <div class="info-label">Mot de passe</div>
          <div class="info-value-wrap">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            <span class="info-value">
              <span class="pwd-hidden pwd-dots">••••••••••</span>
              <span class="pwd-visible" id="pwdText">Mot de passe protégé</span>
            </span>
            <button class="eye-btn" id="eyeBtn" onclick="togglePwd()" title="Afficher / masquer">
              <svg id="eyeIcon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Date de naissance -->
        <div class="info-item full">
          <div class="info-label">Date de naissance</div>
          <div class="info-value-wrap">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <span class="info-value"><?= $profileBirth ?: 'Non renseignée' ?><?php if ($profileAge): ?>&nbsp;·&nbsp;<span style="color:var(--muted);font-size:.82rem;"><?= htmlspecialchars($profileAge, ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?></span>
          </div>
        </div>

      </div>
    </div>

    <!-- Projets récents -->
    <div class="info-card">
      <div class="card-title">
        <div class="card-title-icon">💼</div>
        Projets récents
      </div>
      <div style="display:flex;flex-direction:column;gap:12px;">

        <div style="display:flex;align-items:center;gap:14px;padding:12px 14px;background:var(--bg);border-radius:14px;border:1.5px solid var(--border);">
          <div style="width:42px;height:42px;border-radius:12px;background:var(--teal-light);display:flex;align-items:center;justify-content:center;font-size:1.1rem;">🛒</div>
          <div style="flex:1;">
            <div style="font-size:.875rem;font-weight:700;">Plateforme e-commerce B2B</div>
            <div style="font-size:.74rem;color:var(--muted);margin-top:2px;">React · Node.js · MongoDB</div>
          </div>
          <span style="background:var(--teal-light);color:var(--teal-dark);font-size:.7rem;font-weight:700;padding:4px 10px;border-radius:50px;">Terminé</span>
        </div>

        <div style="display:flex;align-items:center;gap:14px;padding:12px 14px;background:var(--bg);border-radius:14px;border:1.5px solid var(--border);">
          <div style="width:42px;height:42px;border-radius:12px;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;">📊</div>
          <div style="flex:1;">
            <div style="font-size:.875rem;font-weight:700;">Dashboard analytics RH</div>
            <div style="font-size:.74rem;color:var(--muted);margin-top:2px;">TypeScript · PostgreSQL</div>
          </div>
          <span style="background:#fef3c7;color:#d97706;font-size:.7rem;font-weight:700;padding:4px 10px;border-radius:50px;">En cours</span>
        </div>

        <div style="display:flex;align-items:center;gap:14px;padding:12px 14px;background:var(--bg);border-radius:14px;border:1.5px solid var(--border);">
          <div style="width:42px;height:42px;border-radius:12px;background:#fce7f3;display:flex;align-items:center;justify-content:center;font-size:1.1rem;">📱</div>
          <div style="flex:1;">
            <div style="font-size:.875rem;font-weight:700;">App mobile livraison</div>
            <div style="font-size:.74rem;color:var(--muted);margin-top:2px;">React Native · Firebase</div>
          </div>
          <span style="background:var(--teal-light);color:var(--teal-dark);font-size:.7rem;font-weight:700;padding:4px 10px;border-radius:50px;">Terminé</span>
        </div>

      </div>
    </div>

    <!-- Avis -->
    <div class="info-card">
      <div class="card-title">
        <div class="card-title-icon">⭐</div>
        Avis & Évaluations
      </div>

      <div class="rating-row">
        <div class="stars">
          <span class="star">★</span><span class="star">★</span><span class="star">★</span>
          <span class="star">★</span><span class="star">★</span>
        </div>
        <span class="rating-num">4.9</span>
        <span class="rating-count">(32 avis)</span>
      </div>

      <div class="review-item">
        <div class="review-header">
          <div class="reviewer">
            <div class="rev-avatar" style="background:#6366f1;">KM</div>
            <div>
              <div class="rev-name">Karim Mansour</div>
              <div class="rev-date">il y a 2 semaines</div>
            </div>
          </div>
          <div class="rev-stars">
            <span class="rev-star">★</span><span class="rev-star">★</span>
            <span class="rev-star">★</span><span class="rev-star">★</span><span class="rev-star">★</span>
          </div>
        </div>
        <div class="review-text">Sara est une développeuse exceptionnelle. Très réactive et le code livré est de haute qualité. Je la recommande vivement.</div>
      </div>

      <div class="review-item">
        <div class="review-header">
          <div class="reviewer">
            <div class="rev-avatar" style="background:#f59e0b;">LH</div>
            <div>
              <div class="rev-name">Lina Haddad</div>
              <div class="rev-date">il y a 1 mois</div>
            </div>
          </div>
          <div class="rev-stars">
            <span class="rev-star">★</span><span class="rev-star">★</span>
            <span class="rev-star">★</span><span class="rev-star">★</span><span class="rev-star">★</span>
          </div>
        </div>
        <div class="review-text">Collaboration très fluide, Sara a su s'adapter à nos besoins et livrer dans les délais. Excellent travail sur le backend.</div>
      </div>

    </div>

  </div><!-- /right-col -->
</div><!-- /page-wrap -->

<script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script><script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script><script>
  let pwdShown = false;

  function confirmDelete() {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce compte ? Cette action est irréversible.')) {
      document.getElementById('deleteProfileForm').submit();
    }
  }

  function togglePwd() {
    pwdShown = !pwdShown;
    const dots = document.querySelector('.pwd-hidden');
    const text = document.getElementById('pwdText');
    const icon = document.getElementById('eyeIcon');

    if (pwdShown) {
      if (dots) dots.style.display = 'none';
      if (text) text.style.display = 'inline';
      if (icon) icon.setAttribute('stroke', 'var(--teal)');
    } else {
      if (dots) dots.style.display = 'inline';
      if (text) text.style.display = 'none';
      if (icon) icon.setAttribute('stroke', 'currentColor');
    }
  }
</script>
</body>
</html>
 