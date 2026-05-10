<?php
session_start();

require_once __DIR__ . "/../../config/Database.php";
require_once __DIR__ . "/../../model/User.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$db = new Database();
$conn = $db->connect();

$userModel = new User($conn);

$id = $_SESSION['user']['id_u'];
$user = $userModel->getUserById($id);
?>










<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Swaply – Mon Profil</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --teal:       #2baa8f;
    --teal-dark:  #1f8a73;
    --teal-light: #e8f7f4;
    --teal-mid:   #c8ede6;
    --text:       #111827;
    --muted:      #6b7280;
    --border:     #e5e7eb;
    --white:      #ffffff;
    --bg:         #f4f6f8;
    --danger:     #ef4444;
    --success:    #10b981;
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
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 1px 6px rgba(0,0,0,.05);
  }
  .nav-logo {
    display: flex; align-items: center; gap: 10px;
    font-weight: 800; font-size: 1.2rem;
    color: var(--text); text-decoration: none;
  }
  .logo-icon {
    width: 36px; height: 36px;
    background: var(--teal); border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-weight: 800; font-size: 1rem;
  }
  .nav-links { display: flex; gap: 28px; list-style: none; }
  .nav-links a {
    text-decoration: none; color: var(--muted);
    font-size: .875rem; font-weight: 500; transition: color .2s;
  }
  .nav-links a:hover { color: var(--teal); }
  .nav-links a.active { color: var(--teal); font-weight: 700; }

  .nav-right { display: flex; align-items: center; gap: 14px; }
  .nav-notif {
    width: 36px; height: 36px; border-radius: 50%;
    background: var(--teal-light);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; position: relative;
  }
  .notif-dot {
    position: absolute; top: 6px; right: 6px;
    width: 8px; height: 8px;
    background: var(--danger); border-radius: 50%;
    border: 2px solid white;
  }
  .nav-avatar {
    width: 38px; height: 38px; border-radius: 50%;
    background: var(--teal); cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: white; font-weight: 800; font-size: .85rem;
    border: 2.5px solid var(--teal-mid);
  }

  /* ── LAYOUT ── */
  .page-wrap {
    max-width: 1060px;
    margin: 0 auto;
    padding: 36px 24px 60px;
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 24px;
  }

  /* ── LEFT SIDEBAR ── */
  .sidebar { display: flex; flex-direction: column; gap: 16px; }

  .profile-card {
    background: var(--white);
    border-radius: 22px;
    border: 1.5px solid var(--border);
    overflow: hidden;
    animation: fadeUp .4s ease both;
  }

  .profile-banner {
    height: 80px;
    background: linear-gradient(135deg, var(--teal) 0%, #1a7a63 100%);
    position: relative;
  }
  .profile-banner::after {
    content: '';
    position: absolute; inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.06'%3E%3Ccircle cx='30' cy='30' r='28'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
  }

  .avatar-wrap {
    position: relative;
    display: flex; justify-content: center;
    margin-top: -36px;
    margin-bottom: 12px;
  }
  .avatar-circle {
    width: 72px; height: 72px; border-radius: 50%;
    background: var(--teal);
    border: 4px solid var(--white);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem; font-weight: 800; color: white;
    box-shadow: 0 4px 14px rgba(43,170,143,.3);
    cursor: pointer; position: relative;
  }
  .avatar-edit {
    position: absolute; bottom: 0; right: 0;
    width: 22px; height: 22px; border-radius: 50%;
    background: var(--teal); border: 2px solid white;
    display: flex; align-items: center; justify-content: center;
  }

  .sidebar-info { padding: 0 20px 20px; text-align: center; }
  .sidebar-name { font-size: 1.05rem; font-weight: 800; margin-bottom: 3px; }
  .sidebar-role { font-size: .8rem; color: var(--teal-dark); font-weight: 600; margin-bottom: 6px; }
  .sidebar-loc  {
    font-size: .75rem; color: var(--muted);
    display: flex; align-items: center; justify-content: center; gap: 4px;
  }

  .verified-badge {
    display: inline-flex; align-items: center; gap: 5px;
    margin: 12px auto 0;
    background: var(--teal-light);
    color: var(--teal-dark);
    font-size: .72rem; font-weight: 700;
    padding: 4px 12px; border-radius: 50px;
  }

  .sidebar-stats {
    display: grid; grid-template-columns: 1fr 1fr;
    border-top: 1px solid var(--border);
    margin-top: 16px;
  }
  .s-stat {
    padding: 14px 0; text-align: center;
  }
  .s-stat:first-child { border-right: 1px solid var(--border); }
  .s-stat-num { font-size: 1.15rem; font-weight: 800; color: var(--text); }
  .s-stat-label { font-size: .7rem; color: var(--muted); margin-top: 2px; }

  /* Sidebar menu */
  .sidebar-nav {
    background: var(--white);
    border-radius: 18px;
    border: 1.5px solid var(--border);
    overflow: hidden;
    animation: fadeUp .45s ease both;
  }
  .snav-item {
    display: flex; align-items: center; gap: 12px;
    padding: 14px 20px;
    cursor: pointer;
    font-size: .875rem; font-weight: 600;
    color: var(--muted);
    border-bottom: 1px solid var(--border);
    transition: all .18s;
  }
  .snav-item:last-child { border-bottom: none; }
  .snav-item:hover { background: var(--teal-light); color: var(--teal); }
  .snav-item.active { background: var(--teal-light); color: var(--teal); }
  .snav-icon {
    width: 32px; height: 32px; border-radius: 9px;
    background: var(--bg);
    display: flex; align-items: center; justify-content: center;
    font-size: .95rem;
  }
  .snav-item.active .snav-icon { background: var(--teal-mid); }

  /* ── MAIN CONTENT ── */
  .main-content { display: flex; flex-direction: column; gap: 20px; }

  .section-card {
    background: var(--white);
    border-radius: 20px;
    border: 1.5px solid var(--border);
    padding: 28px 32px;
    animation: fadeUp .5s ease both;
  }

  .section-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 24px;
  }
  .section-title {
    font-size: 1rem; font-weight: 800;
    display: flex; align-items: center; gap: 10px;
  }
  .section-title-icon {
    width: 34px; height: 34px; border-radius: 10px;
    background: var(--teal-light);
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
  }
  .edit-btn {
    display: flex; align-items: center; gap: 6px;
    padding: 7px 16px;
    border-radius: 50px;
    border: 1.5px solid var(--border);
    background: var(--white);
    font-family: inherit;
    font-size: .78rem; font-weight: 700;
    color: var(--muted);
    cursor: pointer;
    transition: all .2s;
  }
  .edit-btn:hover { border-color: var(--teal); color: var(--teal); }
  .edit-btn.save {
    background: var(--teal); color: white; border-color: var(--teal);
  }
  .edit-btn.save:hover { background: var(--teal-dark); }

  /* FORM GRID */
  .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px 24px;
  }
  .form-group { display: flex; flex-direction: column; gap: 6px; }
  .form-group.full { grid-column: 1 / -1; }

  label {
    font-size: .75rem;
    font-weight: 700;
    color: var(--muted);
    letter-spacing: .4px;
    text-transform: uppercase;
  }

  .field-wrap { position: relative; }

  .field-icon {
    position: absolute; left: 14px; top: 50%;
    transform: translateY(-50%);
    color: var(--muted);
    pointer-events: none;
  }

  input,
  select,
  textarea {
    width: 100%;
    padding: 11px 14px 11px 40px;
    border: 1.5px solid var(--border);
    border-radius: 12px;
    font-family: inherit;
    font-size: .875rem;
    font-weight: 500;
    color: var(--text);
    background: var(--bg);
    outline: none;
    transition: border .2s, background .2s;
  }

  input:disabled, select:disabled, textarea:disabled {
    background: #f9fafb;
    color: var(--text);
    cursor: default;
    border-color: transparent;
  }

  input:not(:disabled):focus,
  select:not(:disabled):focus,
  textarea:not(:disabled):focus {
    border-color: var(--teal);
    background: white;
    box-shadow: 0 0 0 3px rgba(43,170,143,.1);
  }

  /* Status field */
  .status-badge {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 8px 14px;
    border-radius: 10px;
    font-size: .8rem; font-weight: 700;
    background: var(--teal-light);
    color: var(--teal-dark);
    border: 1.5px solid var(--teal-mid);
  }
  .status-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--success); }

  /* Progress bar */
  .profile-completion {
    margin-bottom: 0;
  }
  .completion-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 8px;
  }
  .completion-label { font-size: .8rem; font-weight: 700; color: var(--text); }
  .completion-pct { font-size: .8rem; font-weight: 800; color: var(--teal); }
  .progress-bar {
    height: 8px;
    background: var(--teal-light);
    border-radius: 50px;
    overflow: hidden;
  }
  .progress-fill {
    height: 100%;
    width: 75%;
    background: linear-gradient(90deg, var(--teal), #3dd6b5);
    border-radius: 50px;
    animation: fillBar .8s ease both .3s;
  }
  @keyframes fillBar {
    from { width: 0; }
    to   { width: 75%; }
  }
  .completion-hint {
    margin-top: 8px;
    font-size: .72rem; color: var(--muted);
  }

  /* Security section */
  .security-item {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 0;
    border-bottom: 1px solid var(--border);
  }
  .security-item:last-child { border-bottom: none; }
  .security-left { display: flex; align-items: center; gap: 12px; }
  .security-icon {
    width: 38px; height: 38px; border-radius: 11px;
    background: var(--teal-light);
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
  }
  .security-name { font-size: .875rem; font-weight: 700; }
  .security-desc { font-size: .75rem; color: var(--muted); margin-top: 1px; }
  .security-action {
    padding: 7px 14px; border-radius: 50px;
    border: 1.5px solid var(--border);
    background: white;
    font-family: inherit; font-size: .75rem; font-weight: 700;
    color: var(--muted); cursor: pointer; transition: all .2s;
  }
  .security-action:hover { border-color: var(--teal); color: var(--teal); }

  /* Delete zone */
  .danger-zone {
    border-color: #fee2e2 !important;
    background: #fff8f8;
  }
  .danger-zone .section-title-icon { background: #fee2e2; }
  .delete-btn {
    margin-top: 16px;
    padding: 10px 22px;
    background: transparent;
    border: 1.5px solid var(--danger);
    color: var(--danger);
    border-radius: 50px;
    font-family: inherit; font-size: .8rem; font-weight: 700;
    cursor: pointer; transition: all .2s;
  }
  .delete-btn:hover { background: var(--danger); color: white; }

  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* Toast */
  .toast {
    position: fixed; bottom: 32px; right: 32px;
    background: var(--text); color: white;
    padding: 12px 22px; border-radius: 14px;
    font-size: .875rem; font-weight: 600;
    display: flex; align-items: center; gap: 10px;
    box-shadow: 0 8px 24px rgba(0,0,0,.2);
    transform: translateY(80px); opacity: 0;
    transition: all .35s cubic-bezier(.34,1.56,.64,1);
    z-index: 999;
  }
  .toast.show { transform: translateY(0); opacity: 1; }
  .toast-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--success); }

  /* Logout button */
  .logout-btn {
    display: flex; align-items: center; gap: 7px;
    padding: 8px 16px;
    border-radius: 50px;
    border: 1.5px solid #fca5a5;
    background: #fff8f8;
    font-family: inherit;
    font-size: .78rem; font-weight: 700;
    color: var(--danger);
    cursor: pointer;
    transition: all .2s;
  }
  .logout-btn:hover { background: var(--danger); color: white; border-color: var(--danger); }

  /* ── PHOTO MENU ── */
  .hidden { display: none !important; }
  .cursor-pointer { cursor: pointer; }
  .relative { position: relative; }
  .absolute { position: absolute; }
  .top-full { top: 100%; }
  .left-0 { left: 0; }
  .z-50 { z-index: 50; }
  
  #photo-menu {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,.1);
    padding: 8px;
    margin-top: 8px;
    min-width: 180px;
  }
  
  #photo-menu button {
    display: block;
    width: 100%;
    text-align: left;
    padding: 8px 16px;
    font-size: .85rem;
    color: var(--text);
    background: transparent;
    border: none;
    cursor: pointer;
    border-radius: 6px;
    transition: background .2s;
  }
  
  #photo-menu button:hover {
    background: var(--teal-light);
  }
  
  #photo-menu button.delete:hover {
    background: #fee2e2;
    color: var(--danger);
  }
  
  .avatar-circle img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    display: block;
  }

  /* Styles pour la caméra */
  #camera-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.9);
    display: none;
    z-index: 1000;
    align-items: center;
    justify-content: center;
    flex-direction: column;
  }

  #camera-modal.active {
    display: flex;
  }

  #camera-modal video {
    width: 100%;
    max-width: 500px;
    height: auto;
    border-radius: 12px;
    margin-bottom: 20px;
    display: block;
  }

  #camera-modal canvas {
    display: none;
  }

  #camera-modal #preview-image {
    max-width: 500px;
    width: 100%;
    height: auto;
    border-radius: 12px;
    margin-bottom: 20px;
    display: none;
  }

  .camera-controls {
    display: flex;
    gap: 12px;
    justify-content: center;
    margin-top: 20px;
    flex-wrap: wrap;
  }

  .camera-btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    min-width: 120px;
  }

  .camera-btn.primary {
    background: var(--teal);
    color: white;
  }

  .camera-btn.primary:hover {
    background: var(--teal-dark);
  }

  .camera-btn.secondary {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.5);
  }

  .camera-btn.secondary:hover {
    background: rgba(255, 255, 255, 0.3);
  }

  .camera-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  .camera-title {
    color: white;
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 20px;
    text-align: center;
  }
</style>
</head>
<body>

<!-- NAV -->
<nav>
  <a class="nav-logo" href="swaplyf.php">
    <div class="logo-icon">S</div>
    Swaply
  </a>
  <ul class="nav-links">
    <li><a href="swaplyf.php">Accueil</a></li>
    <li><a href="#" class="active">Profils</a></li>
    <li><a href="#">Projets</a></li>
<a href="/swaply/public/index.php?action=choicee">Offres</a>
<a href="/swaply/public/index.php?action=choice">Demandes</a>
    <li><a href="#">Publications</a></li>
    <li><a href="#">Messages</a></li>
    <li><a href="reclamations.php">Réclamations</a></li>
  </ul>
  <div class="nav-right">
    <div class="nav-notif">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2.2">
        <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/>
      </svg>
      <div class="notif-dot"></div>
    </div>
    <?php if (!empty($user['photo'])): ?>
      <div class="nav-avatar" style="background: transparent; overflow: hidden; padding: 0;">
        <img src="../../uploads/profiles/<?= htmlspecialchars($user['photo']) ?>" alt="Profil" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
      </div>
    <?php else: ?>
      <div class="nav-avatar"><?= htmlspecialchars(mb_strtoupper(mb_substr($user['nom'], 0, 1) . mb_substr($user['prenom'], 0, 1))); ?></div>
    <?php endif; ?>
    <button class="logout-btn" onclick="return confirmLogout()">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
        <polyline points="16 17 21 12 16 7"/>
        <line x1="21" y1="12" x2="9" y2="12"/>
      </svg>
      Déconnexion
    </button>
  </div>
</nav>

<!-- PAGE -->
<div class="page-wrap">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="profile-card">
      <div class="profile-banner"></div>
      <div class="avatar-wrap">
        <div class="avatar-circle cursor-pointer relative" onclick="togglePhotoMenu(event)">
          <?php if (!empty($user['photo'])): ?>
            <img src="../../uploads/profiles/<?= htmlspecialchars($user['photo']) ?>" alt="Profil" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%; display: block;">
          <?php else: ?>
            <?= htmlspecialchars(mb_strtoupper(mb_substr($user['nom'], 0, 1) . mb_substr($user['prenom'], 0, 1))); ?>
          <?php endif; ?>
          <div class="avatar-edit">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
              <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
              <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
            </svg>
          </div>
          <div id="photo-menu" class="absolute top-full left-0 hidden z-50">
            <button onclick="uploadFile(event)" style="color: var(--text);">Télécharger un fichier</button>
            <button onclick="takePhoto(event)" style="color: var(--text);">Prendre une photo</button>
            <?php if (!empty($user['photo'])): ?>
              <button onclick="deletePhoto(event)" class="delete" style="color: var(--danger);">Supprimer la photo</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="sidebar-info">
        <div class="sidebar-name"><?= $user['prenom'] . " " . $user['nom']; ?></div>
        <div class="sidebar-role">Développeuse Full-Stack</div>
        <div class="sidebar-loc">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M21 10c0 6-9 13-9 13S3 16 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>
          </svg>
          Tunis, Tunisie
        </div>
        <div class="verified-badge">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          Profil vérifié
        </div>
        <div class="sidebar-stats">
          <div class="s-stat">
            <div class="s-stat-num">12</div>
            <div class="s-stat-label">Projets</div>
          </div>
          <div class="s-stat">
            <div class="s-stat-num">4.9 ★</div>
            <div class="s-stat-label">Note</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Completion -->
    <div class="section-card" style="padding:20px 22px;">
      <div class="profile-completion">
        <div class="completion-header">
          <span class="completion-label">Complétion du profil</span>
          <span class="completion-pct">75%</span>
        </div>
        <div class="progress-bar"><div class="progress-fill"></div></div>
        <div class="completion-hint">Ajoutez une bio pour atteindre 100%</div>
      </div>
    </div>

    <!-- Side nav -->
    <div class="sidebar-nav">
      <div class="snav-item active">
        <div class="snav-icon">👤</div>
        Informations personnelles
      </div>
      <div class="snav-item">
        <div class="snav-icon">🔒</div>
        Sécurité
      </div>
      <div class="snav-item">
        <div class="snav-icon">🔔</div>
        Notifications
      </div>
      <div class="snav-item">
        <div class="snav-icon">💼</div>
        Compétences
      </div>
      <div class="snav-item">
        <div class="snav-icon">⭐</div>
        Avis reçus
      </div>
    </div>
  </aside>

  <!-- MAIN -->
  <div class="main-content">

    <!-- Personal Info -->
    <div class="section-card">
      <div class="section-header">
        <div class="section-title">
          <div class="section-title-icon">👤</div>
          Informations personnelles
        </div>
        <button type="button" class="edit-btn" id="editBtn" onclick="toggleEdit()">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
          </svg>
          Modifier
        </button>
      </div>


      <form id="profileForm">
       

        <input type="hidden" name="update_profile" value="1">

        <input type="hidden" name="id_u" value="<?php echo $_SESSION['user']['id_u']; ?>">

      <div class="form-grid">

        <!-- Last name -->
        <div class="form-group">
          <label>Nom</label>
          <div class="field-wrap">
            <svg class="field-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            <input type="text" id="lastName" name="nom" value="<?= $user['nom'] ?>" disabled>




          </div>
        </div>

        <!-- First name -->
        <div class="form-group">
          <label>Prénom</label>
          <div class="field-wrap">
            <svg class="field-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            <input type="text" id="firstName" name="prenom" value="<?= $user['prenom'] ?>" disabled>


          </div>
        </div>

        <!-- Email -->
        <div class="form-group">
          <label>Adresse e-mail</label>
          <div class="field-wrap">
            <svg class="field-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
              <polyline points="22,6 12,13 2,6"/>
            </svg>
            <input type="text" id="email" name="email" value="<?= $user['email'] ?>" disabled>

          </div>
        </div>

        <!-- Phone -->
        <div class="form-group">
          <label>Numéro de téléphone</label>
          <div class="field-wrap">
            <svg class="field-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.67A2 2 0 012.18 1h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 8.09a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 15.09"/>
            </svg>
            <input type="text" id="phone" name="telephone" value="<?= $user['telephone'] ?>" disabled>



          </div>
        </div>

        <!-- Date of Birth -->
        <div class="form-group">
          <label>Date de naissance</label>
          <div class="field-wrap">
            <svg class="field-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
              <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
              <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <input type="text" id="dob" name="date_naissance" value="<?= $user['date_naissance'] ?>" disabled>


          </div>
        </div>

        <!-- Status -->
        <div class="form-group">
          <label>Statut</label>
          <div style="padding-top:4px;">
            <span class="status-badge">
              <span class="status-dot"></span>
              Disponible pour collaborer
            </span>
          </div>
        </div>

        <!-- Location -->
        <div class="form-group full">
          <label>Localisation</label>
          <div class="field-wrap">
            <svg class="field-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <path d="M21 10c0 6-9 13-9 13S3 16 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>
            </svg>
            <input type="text" id="location" value="Tunis, Tunisie" disabled>
          </div>
        </div>

      </div><!-- /form-grid -->
    </div><!-- /section-card -->

    <!-- Security -->
    <div class="section-card">
      <div class="section-header">
        <div class="section-title">
          <div class="section-title-icon">🔒</div>
          Sécurité & Connexion
        </div>
      </div>

      <div class="security-item">
        <div class="security-left">
          <div class="security-icon">🔑</div>
          <div>
            <div class="security-name">Mot de passe</div>
            <div class="security-desc">Dernière modification il y a 3 mois</div>
          </div>
        </div>
        <a href="Password.php" class="security-action">Changer</a>
      </div>

      <div class="security-item">
        <div class="security-left">
          <div class="security-icon">🌐</div>
          <div>
            <div class="security-name">Sessions actives</div>
            <div class="security-desc">1 appareil connecté actuellement</div>
          </div>
        </div>
        <button class="security-action">Gérer</button>
      </div>
    </div>

    <!-- Danger zone -->
    <div class="section-card danger-zone">
      <div class="section-header" style="margin-bottom:8px;">
        <div class="section-title" style="color:#ef4444;">
          <div class="section-title-icon">⚠️</div>
          Zone de danger
        </div>
      </div>
        <p style="font-size:.825rem;color:var(--muted);">
      La suppression de votre compte est permanente et irréversible. Toutes vos données seront effacées.
      </p>

    <form method="POST" action="../../controller/deleteAccount.php">
        <button class="delete-btn"
                type="button"
                onclick="confirmDeleteAccount()">
            Supprimer mon compte
        </button>
    </form>
    </div>

  </div><!-- /main-content -->
</div><!-- /page-wrap -->

<!-- Toast -->
<div class="toast" id="toast">
  <span class="toast-dot"></span>
  Modifications enregistrées avec succès
</div>

<input type="file" id="file-input" style="display:none;" accept="image/*">

<!-- Modal Caméra -->
<div id="camera-modal">
  <div class="camera-title">📷 Prendre une photo</div>
  <video id="camera-video" autoplay playsinline></video>
  <canvas id="camera-canvas" style="display:none;"></canvas>
  <img id="preview-image" src="" alt="Aperçu">
  <div class="camera-controls">
    <button class="camera-btn primary" id="capture-btn" onclick="capturePhoto()">Capturer</button>
    <button class="camera-btn secondary" id="retake-btn" onclick="retakePhoto()" style="display:none;">Reprendre</button>
    <button class="camera-btn secondary" id="send-btn" onclick="sendCapturedPhoto()" style="display:none;">Envoyer</button>
    <button class="camera-btn secondary" onclick="closeCamera()">Annuler</button>
  </div>
</div>

<script>
  // Photo menu functions
  function togglePhotoMenu(event) {
    event.stopPropagation();
    const menu = document.getElementById('photo-menu');
    menu.classList.toggle('hidden');
  }

  function uploadFile(event) {
    event.stopPropagation();
    document.getElementById('file-input').click();
  }

  function takePhoto(event) {
    event.stopPropagation();
    openCamera();
  }

  // ────── Fonctions Caméra ──────
  let cameraStream = null;
  let capturedImageData = null;

  function openCamera() {
    const modal = document.getElementById('camera-modal');
    const video = document.getElementById('camera-video');
    
    modal.classList.add('active');
    
    // Demander l'accès à la caméra
    navigator.mediaDevices.getUserMedia({ 
      video: { facingMode: 'user' },
      audio: false 
    })
    .then(stream => {
      cameraStream = stream;
      video.srcObject = stream;
    })
    .catch(error => {
      alert('Erreur d\'accès à la caméra: ' + error.message);
      closeCamera();
    });
  }

  function capturePhoto() {
    const video = document.getElementById('camera-video');
    const canvas = document.getElementById('camera-canvas');
    const context = canvas.getContext('2d');
    
    // Définir les dimensions du canvas
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    
    // Dessiner l'image de la vidéo
    context.drawImage(video, 0, 0);
    
    // Convertir en image
    capturedImageData = canvas.toDataURL('image/jpeg');
    
    // Afficher l'aperçu
    const previewImg = document.getElementById('preview-image');
    previewImg.src = capturedImageData;
    previewImg.style.display = 'block';
    
    // Masquer la vidéo
    video.style.display = 'none';
    
    // Modifier les boutons
    document.getElementById('capture-btn').style.display = 'none';
    document.getElementById('retake-btn').style.display = 'inline-block';
    document.getElementById('send-btn').style.display = 'inline-block';
  }

  function retakePhoto() {
    const video = document.getElementById('camera-video');
    const previewImg = document.getElementById('preview-image');
    
    video.style.display = 'block';
    previewImg.style.display = 'none';
    
    document.getElementById('capture-btn').style.display = 'inline-block';
    document.getElementById('retake-btn').style.display = 'none';
    document.getElementById('send-btn').style.display = 'none';
  }

  function sendCapturedPhoto() {
    if (!capturedImageData) {
      alert('Veuillez d\'abord capturer une photo');
      return;
    }
    
    // Convertir data URL en Blob
    const parts = capturedImageData.split(',');
    const mimeMatch = parts[0].match(/:(.*?);/);
    const mimeType = mimeMatch ? mimeMatch[1] : 'image/jpeg';
    const bstr = atob(parts[1]);
    const n = bstr.length;
    const u8arr = new Uint8Array(n);
    for (let i = 0; i < n; i++) {
      u8arr[i] = bstr.charCodeAt(i);
    }
    const blob = new Blob([u8arr], { type: mimeType });
    
    const formData = new FormData();
    formData.append('photo', blob, 'camera-photo.jpg');
    formData.append('id', <?= $user['id_u'] ?>);
    
    fetch('../../controller/uploadPhoto.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.text())
    .then(data => {
      alert(data);
      closeCamera();
      location.reload();
    })
    .catch(error => {
      alert('Erreur lors du téléchargement: ' + error.message);
    });
  }

  function closeCamera() {
    const modal = document.getElementById('camera-modal');
    const video = document.getElementById('camera-video');
    const previewImg = document.getElementById('preview-image');
    
    // Arrêter la caméra
    if (cameraStream) {
      cameraStream.getTracks().forEach(track => track.stop());
      cameraStream = null;
    }
    
    // Réinitialiser
    modal.classList.remove('active');
    video.style.display = 'block';
    previewImg.style.display = 'none';
    capturedImageData = null;
    
    document.getElementById('capture-btn').style.display = 'inline-block';
    document.getElementById('retake-btn').style.display = 'none';
    document.getElementById('send-btn').style.display = 'none';
  }

  function deletePhoto(event) {
    event.stopPropagation();
    if (confirm('Supprimer la photo ?')) {
      window.location.href = '../../controller/deletePhoto.php?id=<?= $user['id_u'] ?>';
    }
  }

  document.getElementById('file-input').addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
      const formData = new FormData();
      formData.append('photo', file);
      formData.append('id', <?= $user['id_u'] ?>);
      fetch('../../controller/uploadPhoto.php', {
        method: 'POST',
        body: formData
      }).then(response => response.text()).then(data => {
        alert(data);
        location.reload();
      }).catch(error => {
        alert('Erreur lors du téléchargement');
      });
    }
  });

  // Fermer le menu en cliquant ailleurs
  document.addEventListener('click', function(e) {
    const menu = document.getElementById('photo-menu');
    const avatarCircle = document.querySelector('.avatar-circle');
    if (menu && !menu.classList.contains('hidden') && !avatarCircle.contains(e.target)) {
      menu.classList.add('hidden');
    }
  });

  function handleLogout() {
    if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
      alert('Déconnexion réussie. À bientôt !');
    }
  }

let editing = false;
const fields = ['lastName','firstName','email','phone','dob','location'];

function toggleEdit() {

  const btn = document.getElementById('editBtn');

  if (!editing) {

    editing = true;

    fields.forEach(id => {
      const el = document.getElementById(id);
      el.disabled = false;
      el.style.background = 'white';
    });

    btn.innerHTML = "Enregistrer";
    btn.classList.add('save');

  } else {

    const form = document.getElementById("profileForm");
    const formData = new FormData(form);

    fetch("../../controller/UserC.php", {
      method: "POST",
      body: formData
    })
    .then(response => response.text())
    .then(data => {

      // après succès
      editing = false;

      fields.forEach(id => {
        const el = document.getElementById(id);
        el.disabled = true;
        el.style.background = '#f5f5f5';
      });

      btn.innerHTML = "Modifier";
      btn.classList.remove('save');

      alert("Profil mis à jour ✅");

    });

  }
}

  function showToast() {
    const t = document.getElementById('toast');
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3000);
  }

  function confirmLogout() {
  if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
    window.location.href = '../../controller/logout.php';
  }
  return false;
}


// ✅ Nouveau — POST avec le même chemin relatif que logout
function confirmDeleteAccount() {
  if (confirm("⚠️ Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.")) {
    alert("Votre compte a été supprimé avec succès");
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/swaply/controller/deleteAccount.php';
    document.body.appendChild(form);
    form.submit();
  }
}



  // Sidebar nav click
  document.querySelectorAll('.snav-item').forEach(item => {
    item.addEventListener('click', () => {
      document.querySelectorAll('.snav-item').forEach(i => i.classList.remove('active'));
      item.classList.add('active');
    });
  });
</script>
</body>
</html>


