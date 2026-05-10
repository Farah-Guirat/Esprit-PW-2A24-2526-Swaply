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

$photo = $profile['photo'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Back Office - Profil</title>
<link rel="stylesheet" href="styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  .profile-view {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 24px;
  }

  .profile-panel {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .profile-hero,
  .info-card,
  .skills-card {
    background: white;
    border-radius: 18px;
    border: 1.5px solid #e5e7eb;
    overflow: hidden;
  }

  .hero-banner {
    height: 64px;
    background: linear-gradient(135deg,#2baa8f,#1f6f5c);
    position: relative;
  }

  .hero-body {
    padding: 24px;
  }

  .avatar-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 14px;
    margin-bottom: 16px;
  }

  .avatar-circle {
    width: 86px;
    height: 86px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #2baa8f;
    color: white;
    font-size: 1.8rem;
    font-weight: 800;
    border: 4px solid white;
    box-shadow: 0 8px 24px rgba(43,170,143,.2);
    cursor: pointer;
    position: relative;
  }

  .avatar-circle .edit-icon {
    position: absolute;
    top: 5px;
    right: 5px;
    color: white;
    font-size: 12px;
    opacity: 0.8;
  }

  .verified-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: .75rem;
    font-weight: 700;
    color: #1f8a73;
    background: #e8f7f4;
    border: 1px solid #d0f0ea;
    padding: 8px 14px;
    border-radius: 999px;
  }

  .hero-name {
    font-size: 1.35rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 4px;
  }

  .hero-role {
    font-size: .9rem;
    font-weight: 700;
    color: #1f8a73;
    margin-bottom: 16px;
  }

  .hero-meta {
    display: grid;
    gap: 10px;
  }

  .meta-row {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #475569;
    font-size: .88rem;
    font-weight: 500;
  }

  .hero-divider {
    height: 1px;
    background: #e5e7eb;
    margin: 18px 0;
  }

  .stats-row {
    display: grid;
    grid-template-columns: repeat(3,1fr);
    gap: 1px;
    background: #e5e7eb;
  }

  .stat-item {
    padding: 16px 14px;
    background: white;
    text-align: center;
  }

  .stat-num {
    font-size: 1.1rem;
    font-weight: 800;
    color: #0f172a;
  }

  .stat-label {
    margin-top: 6px;
    font-size: .72rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: .5px;
  }

  .action-btns {
    display: grid;
    gap: 12px;
  }

  .btn-primary,
  .btn-danger,
  .btn-outline {
    width: 100%;
    border-radius: 16px;
    font-family: inherit;
    font-size: .92rem;
    font-weight: 700;
    padding: 14px 16px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all .2s;
    text-decoration: none;
  }

  .btn-primary {
    background: #14b8a6;
    color: white;
    border: none;
  }

  .btn-primary:hover {
    background: #0f766e;
  }

  .btn-outline {
    background: white;
    color: #0f172a;
    border: 1.5px solid #e5e7eb;
  }

  .btn-outline:hover {
    border-color: #14b8a6;
    color: #14b8a6;
  }

  .btn-danger {
    background: #ef4444;
    color: white;
    border: 1.5px solid #ef4444;
  }

  .btn-danger:hover {
    background: #dc2626;
    border-color: #dc2626;
  }

  .btn-ban {
    background: lightgray;
    color: black;
    border: 1.5px solid lightgray;
  }

  .btn-ban:hover {
    background: darkgray;
    border-color: darkgray;
  }

  form[action*="toggleBan"] .btn-primary {
    background: darkgray;
    color: white;
    border: 1.5px solid darkgray;
  }

  form[action*="toggleBan"] .btn-primary:hover {
    background: darkgray;
    border-color: darkgray;
  }

  .skills-card {
    padding: 20px;
  }

  .skills-title {
    font-size: .8rem;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #64748b;
    font-weight: 800;
    margin-bottom: 14px;
  }

  .skills-wrap {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
  }

  .skill-tag {
    padding: 8px 14px;
    border-radius: 999px;
    font-size: .8rem;
    font-weight: 700;
    color: #1f8a73;
    background: #d0f0ea;
  }

  .info-card {
    padding: 26px 28px;
  }

  .card-title {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 1rem;
    font-weight: 800;
    margin-bottom: 18px;
  }

  .card-title-icon {
    width: 36px;
    height: 36px;
    border-radius: 12px;
    background: #d0f0ea;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
  }

  .info-grid {
    display: grid;
    gap: 18px 24px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .info-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .info-item.full { grid-column: 1 / -1; }

  .info-label {
    font-size: .72rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: .5px;
  }

  .info-value-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    border-radius: 14px;
    background: #f8fafc;
    border: 1.5px solid #e5e7eb;
  }

  .info-value {
    font-size: .92rem;
    color: #0f172a;
    font-weight: 600;
    flex: 1;
  }

  .gender-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 999px;
    font-size: .8rem;
    font-weight: 700;
    color: #1d4ed8;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
  }

  .rating-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 18px;
  }

  .stars { display: flex; gap: 4px; }
  .star { color: #f59e0b; font-size: 1rem; }
  .rating-num { font-weight: 800; }
  .rating-count { color: #64748b; font-size: .84rem; }

  .review-item { border-top: 1px solid #e5e7eb; padding-top: 18px; }
  .review-item:first-child { border-top: none; padding-top: 0; }

  .review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
  }

  .reviewer { display: flex; align-items: center; gap: 12px; }
  .rev-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: .8rem;
    font-weight: 800;
  }

  .rev-name { font-size: .9rem; font-weight: 700; }
  .rev-date { font-size: .78rem; color: #64748b; }
  .rev-stars { display: flex; gap: 4px; }
  .review-text { color: #475569; line-height: 1.6; font-size: .9rem; }

  @media (max-width: 1100px) {
    .profile-view { grid-template-columns: 1fr; }
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
      <a href="#" class="menu-item" id="menu-users">
        <i class="fa-solid fa-users"></i> Utilisateurs
      </a>
      <a href="ProfilsB.php" class="menu-item active" id="menu-profiles">
        <i class="fa-solid fa-user"></i> Profils
      </a>
      <a href="/swaply/public/index.php?action=dashboard" class="menu-item" id="menu-offres">
        <i class="fa-solid fa-briefcase"></i> Offres & Demandes
      </a>
      <a href="/swaply/view/back/publication_back.php" class="menu-item" id="menu-publications">
        <i class="fa-solid fa-newspaper"></i> Publications
      </a>
      <a href="/swaply/view/back/conversations.php" class="menu-item" id="menu-conversations">
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
      <h2 id="page-title">Profil</h2>
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
      <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:24px;flex-wrap:wrap;">
        <div>
          <div style="font-size:.95rem;color:#64748b;margin-bottom:6px;">Profils > Détail</div>
          <h1 style="font-size:1.7rem;font-weight:800;color:#0f766e;">Détails du profil</h1>
        </div>
        <a href="ProfilsB.php" class="btn-outline" style="max-width:220px;">Retour aux profils</a>
      </div>

      <div class="profile-view">
        <div class="profile-panel">
          <div class="profile-hero">
            <div class="hero-banner"></div>
            <div class="hero-body">
              <div class="avatar-row">
                <div class="avatar-circle">
                    <?php if ($photo): ?>
                        <img src="../../uploads/profiles/<?= htmlspecialchars($photo) ?>" alt="Photo" style="width:100%; height:100%; border-radius:50%; object-fit:cover;">
                    <?php else: ?>
                        <?= $initials ?>
                    <?php endif; ?>
                </div>
                <div class="verified-pill">
                  <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                  Vérifié
                </div>
              </div>

              <div class="hero-name"><?= $fullName ?></div>
              <div class="hero-role">Utilisateur</div>
              <div class="hero-meta">
                <div class="meta-row">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M21 10c0 6-9 13-9 13S3 16 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                  Tunis, Tunisie
                </div>
                <div class="meta-row">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                  Date de naissance : <?= $profileBirth ?: 'N/A' ?>
                </div>
                <div class="meta-row">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
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

          <div class="action-btns">
            <button type="button" class="btn-primary">
              <i class="fa-solid fa-envelope"></i>
              Envoyer un message
            </button>
            <form method="POST" action="../../controller/toggleBan.php" style="width:100%;margin:0;">
              <input type="hidden" name="id_u" value="<?= htmlspecialchars($profile['id_u'], ENT_QUOTES, 'UTF-8') ?>">
              <button type="submit" class="btn-primary">
                <i class="fa-solid fa-ban"></i>
                <?= isset($profile['banned']) && $profile['banned'] ? 'Unban' : 'Ban' ?>
              </button>
            </form>
            <form id="deleteProfileForm" method="POST" action="../../controller/deleteProfile.php" style="width:100%;margin:0;">
              <input type="hidden" name="id_u" value="<?= htmlspecialchars($profile['id_u'], ENT_QUOTES, 'UTF-8') ?>">
              <button type="button" class="btn-danger" onclick="confirmDelete()">
                <i class="fa-solid fa-trash"></i>
                Supprimer le compte
              </button>
            </form>
          </div>

          <div class="skills-card">
            <div class="skills-title">Compétences</div>
            <div class="skills-wrap">
              <span class="skill-tag">React</span>
              <span class="skill-tag">Node.js</span>
              <span class="skill-tag">MongoDB</span>
              <span class="skill-tag">TypeScript</span>
              <span class="skill-tag">Docker</span>
              <span class="skill-tag">REST API</span>
            </div>
          </div>
        </div>

        <div class="profile-panel">
          <div class="info-card">
            <div class="card-title">
              <div class="card-title-icon">👤</div>
              Informations personnelles
            </div>
            <div class="info-grid">
              <div class="info-item">
                <div class="info-label">Nom</div>
                <div class="info-value-wrap">
                  <i class="fa-solid fa-user"></i>
                  <div class="info-value"><?= htmlspecialchars($profile['nom'], ENT_QUOTES, 'UTF-8') ?></div>
                </div>
              </div>
              <div class="info-item">
                <div class="info-label">Prénom</div>
                <div class="info-value-wrap">
                  <i class="fa-solid fa-user"></i>
                  <div class="info-value"><?= htmlspecialchars($profile['prenom'], ENT_QUOTES, 'UTF-8') ?></div>
                </div>
              </div>
              <div class="info-item">
                <div class="info-label">Genre</div>
                <div class="info-value-wrap" style="background:transparent;border:none;padding:0;">
                  <span class="gender-badge">
                    <i class="fa-solid fa-venus-mars"></i>
                    <?= $profileGenre ?: 'N/A' ?>
                  </span>
                </div>
              </div>
              <div class="info-item">
                <div class="info-label">Adresse e-mail</div>
                <div class="info-value-wrap">
                  <i class="fa-solid fa-envelope"></i>
                  <div class="info-value"><a href="mailto:<?= $profileEmail ?>" style="color:#0f172a;text-decoration:none;"><?= $profileEmail ?></a></div>
                </div>
              </div>
              <div class="info-item full">
                <div class="info-label">Téléphone</div>
                <div class="info-value-wrap">
                  <i class="fa-solid fa-phone"></i>
                  <div class="info-value"><?= $profilePhone ?: 'Non renseigné' ?></div>
                </div>
              </div>
              <div class="info-item">
                <div class="info-label">Mot de passe</div>
                <div class="info-value-wrap">
                  <i class="fa-solid fa-lock"></i>
                  <span class="info-value">••••••••••</span>
                </div>
              </div>
              <div class="info-item full">
                <div class="info-label">Date de naissance</div>
                <div class="info-value-wrap">
                  <i class="fa-solid fa-calendar-days"></i>
                  <div class="info-value"><?= $profileBirth ?: 'Non renseignée' ?><?php if ($profileAge): ?>&nbsp;·&nbsp;<span style="color:#64748b;font-size:.86rem;font-weight:600;"><?= htmlspecialchars($profileAge, ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?></div>
                </div>
              </div>
            </div>
          </div>

          <div class="info-card">
            <div class="card-title">
              <div class="card-title-icon">💼</div>
              Projets récents
            </div>
            <div style="display:flex;flex-direction:column;gap:14px;">
              <div style="display:flex;align-items:center;gap:14px;padding:14px 16px;background:#f8fafc;border:1.5px solid #e5e7eb;border-radius:16px;">
                <div style="width:42px;height:42px;border-radius:14px;background:#daf5ed;display:flex;align-items:center;justify-content:center;font-size:1.1rem;">🛒</div>
                <div style="flex:1;">
                  <div style="font-size:.93rem;font-weight:700;color:#0f172a;">Plateforme e-commerce</div>
                  <div style="font-size:.78rem;color:#64748b;margin-top:2px;">React · Node.js · MongoDB</div>
                </div>
                <span style="background:#e8f7f4;color:#1f8a73;padding:6px 10px;border-radius:999px;font-size:.72rem;font-weight:700;">Terminé</span>
              </div>
              <div style="display:flex;align-items:center;gap:14px;padding:14px 16px;background:#f8fafc;border:1.5px solid #e5e7eb;border-radius:16px;">
                <div style="width:42px;height:42px;border-radius:14px;background:#e0f2fe;display:flex;align-items:center;justify-content:center;font-size:1.1rem;">📊</div>
                <div style="flex:1;">
                  <div style="font-size:.93rem;font-weight:700;color:#0f172a;">Dashboard Analytics RH</div>
                  <div style="font-size:.78rem;color:#64748b;margin-top:2px;">TypeScript · PostgreSQL</div>
                </div>
                <span style="background:#fef3c7;color:#d97706;padding:6px 10px;border-radius:999px;font-size:.72rem;font-weight:700;">En cours</span>
              </div>
            </div>
          </div>

          <div class="info-card">
            <div class="card-title">
              <div class="card-title-icon">⭐</div>
              Avis & Évaluations
            </div>
            <div class="rating-row">
              <div class="stars">
                <span class="star">★</span><span class="star">★</span><span class="star">★</span><span class="star">★</span><span class="star">★</span>
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
                  <span class="rev-star">★</span><span class="rev-star">★</span><span class="rev-star">★</span><span class="rev-star">★</span><span class="rev-star">★</span>
                </div>
              </div>
              <div class="review-text">Sara est une développeuse exceptionnelle. Très réactive et le code livré est de haute qualité.</div>
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
                  <span class="rev-star">★</span><span class="rev-star">★</span><span class="rev-star">★</span><span class="rev-star">★</span><span class="rev-star">★</span>
                </div>
              </div>
              <div class="review-text">Collaboration très fluide, Sara a su s'adapter à nos besoins et livrer dans les délais.</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  function confirmDelete() {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce compte ? Cette action est irréversible.')) {
      const form = document.getElementById('deleteProfileForm');
      if (form) {
        form.submit();
      }
    }
  }
</script>
</body>
</html>
