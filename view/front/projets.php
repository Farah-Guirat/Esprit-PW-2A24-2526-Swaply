<?php
require_once "../../model/projet.php";
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$photo = $_SESSION['user']['photo'] ?? null;
$p = new Projet();
$data = $p->getAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Swaply - Projets</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
      <link rel="stylesheet" href="../src/assets/css/style.css">


  <style>
    .choice-card {
      transition: all 0.3s ease;
    }

    .choice-card:hover {
      transform: translateY(-8px);
    }
  </style>
</head>

<body class="bg-gray-50 min-h-screen">

<!-- Header -->
<header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-8 py-5 flex items-center justify-between">
      <div class="flex items-center gap-3">
          <span class="text-gray-700 font-medium">
              <?= htmlspecialchars($_SESSION['user']['nom'] ?? '') ?>
              <?= htmlspecialchars($_SESSION['user']['prenom'] ?? '') ?>
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

      <div onclick="window.location.href='Profil.php'" class="w-10 h-10 bg-teal-100 rounded-2xl overflow-hidden border-2 border-white shadow cursor-pointer relative">
        <?php if ($photo): ?>
          <img src="/swaply/uploads/profiles/<?= htmlspecialchars($photo) ?>" alt="Profil" class="w-full h-full object-cover" style="width: 100%; height: 100%; object-fit: cover; display: block;">
        <?php else: ?>
          <div class="w-full h-full flex items-center justify-center text-teal-600 font-bold text-lg">
            <?= strtoupper(substr($_SESSION['user']['nom'] ?? '', 0, 1) . substr($_SESSION['user']['prenom'] ?? '', 0, 1)) ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </header>


  <main class="max-w-7xl mx-auto px-8 py-6">

    <!-- HERO -->
    <div class="hero-bg rounded-3xl px-12 py-8 text-white mb-6">
      <div class="max-w-2xl">
        <h2 class="text-4xl font-bold leading-tight">Gérez vos projets et compétences</h2>
        <p class="mt-2 text-gray-900 font-bold text-3xl">Ajoutez, modifiez et suivez tous vos projets collaboratifs!</p>
      </div>
    </div>

    <!-- FORMULAIRE AJOUT -->
    <div class="bg-white rounded-3xl p-8 shadow-sm mb-8">
      <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
        <span class="w-8 h-8 bg-teal-100 text-teal-600 rounded-xl flex items-center justify-center text-sm">➕</span>
        Ajouter un projet
      </h3>

      <form method="POST" action="../../controller/ProjetController.php" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Nom du projet</label>
          <input type="text" name="nom" placeholder="Ex: Application mobile..."
            class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:outline-none focus:border-teal-400 text-sm">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Description</label>
          <input type="text" name="desc" placeholder="Décrivez le projet..."
            class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:outline-none focus:border-teal-400 text-sm">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Statut</label>
          <select name="statut"
            class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:outline-none focus:border-teal-400 text-sm bg-white">
            <option value="En cours">En cours</option>
            <option value="Terminé">Terminé</option>
          </select>
        </div>
        <div class="md:col-span-3 flex justify-end">
          <button name="add"
            class="bg-teal-500 hover:bg-teal-600 text-white px-8 py-3 rounded-2xl font-semibold text-sm transition">
            Ajouter le projet
          </button>
        </div>
      </form>
    </div>
    
    <?php include 'badges.php'; ?>
    <?php include 'matching.php'; ?>

    <!-- LISTE DES PROJETS -->
      <!-- RECHERCHE -->
<div class="mb-6">
  <input 
    type="text" 
    id="searchInput"
    placeholder="🔍 Rechercher un projet (nom, description, statut)..."
    class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:outline-none focus:border-teal-400 text-sm"
  >
</div>

    <!-- ONGLETS -->
    <div class="flex gap-2 mb-6">
      <button class="tab-btn active px-6 py-2 rounded-2xl font-semibold text-sm transition" data-tab="actifs">
        📁 Projets actifs
      </button>
      <button class="tab-btn px-6 py-2 rounded-2xl font-semibold text-sm transition" data-tab="masques">
        🙈 Projets masqués
      </button>
      <button class="tab-btn px-6 py-2 rounded-2xl font-semibold text-sm transition" data-tab="archives">
        📦 Projets archivés
      </button>
      <button class="tab-btn px-6 py-2 rounded-2xl font-semibold text-sm transition" data-tab="favoris">
        ⭐ Mes favoris
      </button>
    </div>

    <h3 class="text-xl font-semibold text-gray-800 mb-6 tab-title" id="tab-actifs">Tous les projets</h3>
    <h3 class="text-xl font-semibold text-gray-800 mb-6 tab-title hidden" id="tab-masques">Projets masqués</h3>
    <h3 class="text-xl font-semibold text-gray-800 mb-6 tab-title hidden" id="tab-archives">Projets archivés</h3>
    <h3 class="text-xl font-semibold text-gray-800 mb-6 tab-title hidden" id="tab-favoris">Mes projets favoris</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

      <?php foreach($data as $row) { ?>
      <?php 
        $showInActifs = ($row['is_hidden'] == 0 && $row['is_archived'] == 0);
        $showInMasques = ($row['is_hidden'] == 1);
        $showInArchives = ($row['is_archived'] == 1);
        $showInFavoris = ($row['is_favorite'] == 1 && $row['is_hidden'] == 0 && $row['is_archived'] == 0);
      ?>

      <div class="card-hover project-card bg-white rounded-3xl p-6 border border-transparent hover:border-teal-200 shadow-sm"
        data-nom="<?= htmlspecialchars(strtolower($row['nom_projet']), ENT_QUOTES) ?>"
        data-desc="<?= htmlspecialchars(strtolower($row['description']), ENT_QUOTES) ?>"
        data-statut="<?= htmlspecialchars(strtolower($row['statut']), ENT_QUOTES) ?>"
        data-actif="<?= $showInActifs ? '1' : '0' ?>"
        data-masque="<?= $showInMasques ? '1' : '0' ?>"
        data-archive="<?= $showInArchives ? '1' : '0' ?>"
        data-favori="<?= $showInFavoris ? '1' : '0' ?>"
        style="display: <?= $showInActifs ? 'block' : 'none' ?>;">

        <!-- EN-TÊTE CARTE -->
        <div class="flex items-start justify-between mb-4">
          <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center text-2xl relative">
            📁
            <?php if($row['is_favorite'] == 1): ?>
              <span class="absolute -top-1 -right-1 text-lg">⭐</span>
            <?php endif; ?>
          </div>
          <span class="text-xs font-medium px-3 py-1 rounded-full
            <?= $row['statut'] === 'Terminé' ? 'bg-emerald-100 text-emerald-600' :
               ($row['statut'] === 'En cours' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-500') ?>">
            <?= $row['statut'] ?>
          </span>
        </div>

        <!-- NOM + DESCRIPTION -->
        <h4 class="text-lg font-semibold text-gray-800 mb-1"><?= $row['nom_projet'] ?></h4>
        <p class="text-sm text-gray-500 mb-4"><?= $row['description'] ?></p>

        <!-- COMPETENCES -->
        <div class="flex flex-wrap gap-2 mb-5">
          <?php
            $comps = $p->getCompetences($row['id_projet']);
            foreach($comps as $c) { ?>
              <span class="bg-teal-50 text-teal-600 text-xs px-3 py-1 rounded-full font-medium">
                <?= $c['nom_competence'] ?>
              </span>
          <?php } ?>
        </div>

        <!-- ACTIONS -->
        <div class="flex gap-2 flex-wrap">
          <a href="competences.php?id_projet=<?= $row['id_projet'] ?>"
            class="flex-1 text-center bg-teal-50 hover:bg-teal-100 text-teal-600 text-xs font-semibold px-3 py-2 rounded-xl transition">
            ➕ Compétences
          </a>
          <button onclick="toggleForm(<?= $row['id_projet'] ?>)"
            class="flex-1 bg-amber-50 hover:bg-amber-100 text-amber-600 text-xs font-semibold px-3 py-2 rounded-xl transition border-0 cursor-pointer">
            ✏️ Modifier
          </button>
          <a href="../../controller/ProjetController.php?delete=<?= $row['id_projet'] ?>"
            onclick="return confirm('Supprimer ce projet ?')"
            class="flex-1 text-center bg-red-50 hover:bg-red-100 text-red-500 text-xs font-semibold px-3 py-2 rounded-xl transition">
            🗑 Supprimer
          </a>
          <a href="../../controller/ProjetController.php?favorite=<?= $row['id_projet'] ?>"
            class="flex-1 text-center bg-yellow-50 hover:bg-yellow-100 <?= $row['is_favorite'] == 1 ? 'text-yellow-500' : 'text-gray-400' ?> text-xs font-semibold px-3 py-2 rounded-xl transition">
            ⭐ Favori
          </a>
          <a href="../../controller/ProjetController.php?hide=<?= $row['id_projet'] ?>"
            class="flex-1 text-center bg-gray-50 hover:bg-gray-100 text-gray-500 text-xs font-semibold px-3 py-2 rounded-xl transition">
            🙈 Masquer
          </a>
          <a href="../../controller/ProjetController.php?archive=<?= $row['id_projet'] ?>"
            class="flex-1 text-center bg-blue-50 hover:bg-blue-100 text-blue-500 text-xs font-semibold px-3 py-2 rounded-xl transition">
            📦 Archiver
          </a>
        </div>

        <?php if($showInMasques): ?>
        <div class="mt-4 pt-4 border-t border-gray-100">
          <a href="../../controller/ProjetController.php?unhide=<?= $row['id_projet'] ?>"
            class="block text-center w-full bg-teal-50 hover:bg-teal-100 text-teal-600 text-xs font-semibold px-3 py-2 rounded-xl transition">
            👁️ Afficher à nouveau
          </a>
        </div>
        <?php endif; ?>

        <?php if($showInArchives): ?>
        <div class="mt-4 pt-4 border-t border-gray-100">
          <a href="../../controller/ProjetController.php?unarchive=<?= $row['id_projet'] ?>"
            class="block text-center w-full bg-teal-50 hover:bg-teal-100 text-teal-600 text-xs font-semibold px-3 py-2 rounded-xl transition">
            👁️ Restaurer
          </a>
        </div>
        <?php endif; ?>

        <!-- FORM MODIFIER CACHÉ -->
        <form id="form-<?= $row['id_projet'] ?>" class="hidden mt-4 pt-4 border-t border-gray-100"
          method="POST" action="../../controller/ProjetController.php">
          <input type="hidden" name="id" value="<?= $row['id_projet'] ?>">
          <input type="text" name="nom" value="<?= $row['nom_projet'] ?>"
            class="w-full px-3 py-2 mb-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-teal-400">
          <input type="text" name="desc" value="<?= $row['description'] ?>"
            class="w-full px-3 py-2 mb-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-teal-400">
          <select name="statut"
            class="w-full px-3 py-2 mb-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-teal-400 bg-white">
            <option value="En cours" <?= $row['statut'] === 'En cours' ? 'selected' : '' ?>>En cours</option>
            <option value="Terminé" <?= $row['statut'] === 'Terminé' ? 'selected' : '' ?>>Terminé</option>
          </select>
          <button name="update"
            class="w-full bg-teal-500 hover:bg-teal-600 text-white text-sm font-semibold py-2 rounded-xl transition border-0 cursor-pointer">
            ✔ Enregistrer
          </button>
        </form>

      </div>

      <?php } ?>

    </div>
    

  </main>

  <script>
// GESTION DES ONGLETS
document.querySelectorAll(".tab-btn").forEach(btn => {
  btn.addEventListener("click", function() {
    let tabName = this.getAttribute("data-tab");
    
    // Update buttons
    document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active", "bg-teal-100", "text-teal-600"));
    document.querySelectorAll(".tab-btn").forEach(b => b.classList.add("bg-gray-100", "text-gray-600"));
    this.classList.remove("bg-gray-100", "text-gray-600");
    this.classList.add("active", "bg-teal-100", "text-teal-600");
    
    // Update titles
    document.querySelectorAll(".tab-title").forEach(t => t.classList.add("hidden"));
    document.getElementById("tab-" + tabName).classList.remove("hidden");
    
    // Show/hide cards based on tab
    document.querySelectorAll(".project-card").forEach(card => {
      if (tabName === "actifs" && card.getAttribute("data-actif") === "1") {
        card.style.display = "block";
      } else if (tabName === "masques" && card.getAttribute("data-masque") === "1") {
        card.style.display = "block";
      } else if (tabName === "archives" && card.getAttribute("data-archive") === "1") {
        card.style.display = "block";
      } else if (tabName === "favoris" && card.getAttribute("data-favori") === "1") {
        card.style.display = "block";
      } else {
        card.style.display = "none";
      }
    });
    
    // Reset search
    document.getElementById("searchInput").value = "";
  });
});

function toggleForm(id) {
  let form = document.getElementById("form-" + id);
  form.classList.toggle("hidden");
}

function showError(input, message) {
  let existing = input.parentNode.querySelector(".error-msg");
  if (existing) existing.remove();
  input.classList.add("border-red-400");
  let msg = document.createElement("p");
  msg.className = "error-msg text-red-500 text-xs mt-1";
  msg.textContent = message;
  input.parentNode.appendChild(msg);
}

function clearError(input) {
  input.classList.remove("border-red-400");
  let existing = input.parentNode.querySelector(".error-msg");
  if (existing) existing.remove();
}

document.addEventListener("DOMContentLoaded", function () {
  let forms = document.querySelectorAll("form");
  forms.forEach(form => {
    form.addEventListener("submit", function(e) {
      let nom = form.querySelector("[name='nom']");
      let desc = form.querySelector("[name='desc']");
      if (!nom || !desc) return;

      let valid = true;

      clearError(nom);
      clearError(desc);

      if (nom.value.trim().length < 3) {
        showError(nom, "Le nom doit contenir au moins 3 caractères.");
        valid = false;
      }

      if (desc.value.trim().length < 10) {
        showError(desc, "La description doit contenir au moins 10 caractères.");
        valid = false;
      }

      if (!valid) e.preventDefault();
    });
  });

  let searchInput = document.getElementById("searchInput");

  if (searchInput) {
    searchInput.addEventListener("keyup", function () {
      let value = this.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

      let cards = document.querySelectorAll(".project-card");

      cards.forEach(card => {
        if (card.style.display === "none") return; // Skip hidden tabs
        
        let nom    = (card.getAttribute("data-nom")    || "").normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        let desc   = (card.getAttribute("data-desc")   || "").normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        let statut = (card.getAttribute("data-statut") || "").normalize("NFD").replace(/[\u0300-\u036f]/g, "");

        if (nom.includes(value) || desc.includes(value) || statut.includes(value)) {
          card.style.display = "block";
        } else {
          card.style.display = "none";
        }
      });
    });
  }
});
</script>
<!-- CHATBOT BUTTON -->
<button id="chatBtn" onclick="toggleChat()"
  style="position:fixed;bottom:24px;right:24px;z-index:1000;
         width:56px;height:56px;border-radius:50%;background:#14b8a6;
         color:white;border:none;cursor:pointer;font-size:22px;
         box-shadow:0 4px 20px rgba(20,184,166,0.4);
         transition:transform 0.2s;"
  onmouseover="this.style.transform='scale(1.1)'"
  onmouseout="this.style.transform='scale(1)'">
  💬
</button>

<!-- CHATBOT WINDOW -->
<div id="chatWindow" style="display:none;position:fixed;bottom:90px;right:24px;z-index:1000;
     width:360px;height:500px;background:white;border-radius:24px;
     box-shadow:0 8px 40px rgba(0,0,0,0.15);display:none;flex-direction:column;overflow:hidden;">

  <!-- HEADER -->
  <div style="background:#14b8a6;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;">
    <div style="display:flex;align-items:center;gap:10px;">
      <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:50%;
                  display:flex;align-items:center;justify-content:center;font-size:18px;">🤖</div>
      <div>
        <p style="color:white;font-weight:600;font-size:14px;margin:0;">Assistant Swaply</p>
        <p style="color:rgba(255,255,255,0.8);font-size:11px;margin:0;">Propulsé par IA</p>
      </div>
    </div>
    <button onclick="toggleChat()" style="background:none;border:none;color:white;font-size:20px;cursor:pointer;">✕</button>
  </div>

  <!-- MESSAGES -->
  <div id="chatMessages" style="flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:10px;background:#f9fafb;">
    <!-- Welcome message -->
    <div style="display:flex;gap:8px;align-items:flex-start;">
      <div style="width:28px;height:28px;background:#14b8a6;border-radius:50%;
                  display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;">🤖</div>
      <div style="background:white;padding:10px 14px;border-radius:16px 16px 16px 4px;
                  font-size:13px;color:#374151;box-shadow:0 1px 4px rgba(0,0,0,0.06);max-width:260px;">
        Bonjour! 👋 Je suis ton assistant Swaply. Pose moi des questions sur tes projets et compétences!
      </div>
    </div>
  </div>

  <!-- SUGGESTIONS -->
  <div id="suggestions" style="padding:8px 16px;display:flex;gap:6px;flex-wrap:wrap;background:#f9fafb;border-top:1px solid #f0f0f0;">
    <button onclick="sendSuggestion('Quels projets sont en cours?')"
      style="background:#e0f2f1;color:#14b8a6;border:none;padding:5px 10px;border-radius:20px;font-size:11px;cursor:pointer;">
      📋 Projets en cours
    </button>
    <button onclick="sendSuggestion('Quelles compétences sont utilisées?')"
      style="background:#e0f2f1;color:#14b8a6;border:none;padding:5px 10px;border-radius:20px;font-size:11px;cursor:pointer;">
      🎯 Compétences
    </button>
    <button onclick="sendSuggestion('Combien de projets sont terminés?')"
      style="background:#e0f2f1;color:#14b8a6;border:none;padding:5px 10px;border-radius:20px;font-size:11px;cursor:pointer;">
      ✅ Terminés
    </button>
  </div>

  <!-- INPUT -->
  <div style="padding:12px 16px;background:white;border-top:1px solid #f0f0f0;display:flex;gap:8px;">
    <input type="text" id="chatInput" placeholder="Pose une question..."
      style="flex:1;padding:10px 14px;border-radius:20px;border:1px solid #e5e7eb;
             font-size:13px;outline:none;"
      onfocus="this.style.borderColor='#14b8a6'"
      onblur="this.style.borderColor='#e5e7eb'"
      onkeydown="if(event.key==='Enter') sendMessage()">
    <button onclick="sendMessage()"
      style="width:38px;height:38px;background:#14b8a6;border:none;border-radius:50%;
             color:white;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;">
      ➤
    </button>
  </div>

</div>

<script>
  let chatOpen = false;
  let chatHistory = [];

  function toggleChat() {
    chatOpen = !chatOpen;
    let win = document.getElementById('chatWindow');
    win.style.display = chatOpen ? 'flex' : 'none';
    if (chatOpen) document.getElementById('chatInput').focus();
  }

  function sendSuggestion(text) {
    document.getElementById('chatInput').value = text;
    sendMessage();
  }

  function addMessage(text, isUser) {
    let messages = document.getElementById('chatMessages');

    let wrapper = document.createElement('div');
    wrapper.style.cssText = 'display:flex;gap:8px;align-items:flex-start;' + (isUser ? 'flex-direction:row-reverse;' : '');

    let avatar = document.createElement('div');
    avatar.style.cssText = 'width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;' +
      (isUser ? 'background:#e0f2f1;' : 'background:#14b8a6;');
    avatar.textContent = isUser ? '👤' : '🤖';

    let bubble = document.createElement('div');
    bubble.style.cssText = 'padding:10px 14px;border-radius:' +
      (isUser ? '16px 16px 4px 16px;background:#14b8a6;color:white;' : '16px 16px 16px 4px;background:white;color:#374151;') +
      'font-size:13px;box-shadow:0 1px 4px rgba(0,0,0,0.06);max-width:260px;line-height:1.5;';
    bubble.textContent = text;

    wrapper.appendChild(avatar);
    wrapper.appendChild(bubble);
    messages.appendChild(wrapper);
    messages.scrollTop = messages.scrollHeight;
  }

  function addTyping() {
    let messages = document.getElementById('chatMessages');
    let div = document.createElement('div');
    div.id = 'typing';
    div.style.cssText = 'display:flex;gap:8px;align-items:center;';
    div.innerHTML = `
      <div style="width:28px;height:28px;background:#14b8a6;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;">🤖</div>
      <div style="background:white;padding:10px 14px;border-radius:16px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
        <span style="display:flex;gap:4px;">
          <span style="width:6px;height:6px;background:#14b8a6;border-radius:50%;animation:bounce 0.8s infinite;"></span>
          <span style="width:6px;height:6px;background:#14b8a6;border-radius:50%;animation:bounce 0.8s 0.2s infinite;"></span>
          <span style="width:6px;height:6px;background:#14b8a6;border-radius:50%;animation:bounce 0.8s 0.4s infinite;"></span>
        </span>
      </div>`;
    messages.appendChild(div);
    messages.scrollTop = messages.scrollHeight;
  }

  function removeTyping() {
    let t = document.getElementById('typing');
    if (t) t.remove();
  }

  async function sendMessage() {
    let input = document.getElementById('chatInput');
    let message = input.value.trim();
    if (!message) return;

    // Hide suggestions after first message
    document.getElementById('suggestions').style.display = 'none';

    input.value = '';
    addMessage(message, true);
    addTyping();

    // Add to history
    chatHistory.push({ role: 'user', text: message });

    try {
      let response = await fetch('../../controller/chatbot.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: message, history: chatHistory })
      });

      let data = await response.json();
      removeTyping();
      addMessage(data.reply, false);

      // Add bot reply to history
      chatHistory.push({ role: 'model', text: data.reply });

    } catch (err) {
      removeTyping();
      addMessage("Désolé, une erreur est survenue. Réessaie!", false);
    }
  }
</script>

<style>
  @keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
  }
</style>
</body>
</html>