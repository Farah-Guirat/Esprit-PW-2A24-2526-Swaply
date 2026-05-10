<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Détails de l'offre: <?= htmlspecialchars($offre->getTitre()) ?> - Catégorie: <?= htmlspecialchars($offre->getCategorie()) ?>">
  <title>Swaply - Détails de l'offre</title>
  <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="../src/assets/css/style.css">

  <style>




    @keyframes fadeSlideIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    .animate-fade-in {
      animation: fadeSlideIn 0.4s ease-out;
    }
    .card-hover {
      transition: all 0.3s ease;
    }
    .card-hover:hover {
      transform: translateY(-2px);
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
    }
    .demande-card {
      transition: all 0.3s ease;
    }
    .demande-card:hover {
      transform: translateY(-4px);
      border-color: #14b8a6;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    
    /* Mobile optimizations */
    @media (max-width: 768px) {
      .card-hover:hover {
        transform: none;
      }
      .demande-card:hover {
        transform: none;
      }
      main {
        padding: 1rem;
      }
      header .nav {
        display: none;
      }
    }
  </style>
</head>

<body class="bg-gray-100 min-h-screen">

<!-- HEADER -->
<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$photo = $_SESSION['user']['photo'] ?? null;
?>


<!-- Header -->
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
        <a href="/swaply/view/front/swaplyf.php" class="nav-link ">Accueil</a>
        <a href="/swaply/view/front/Profil.php" class="nav-link">Profils</a>
        <a href="/swaply/view/front/projets.php" class="nav-link">Projets</a>
       <a href="/swaply/public/index.php?action=choice"  class="nav-link ">Demandes</a>
        <a href="/swaply/public/index.php?action=choicee" class="nav-link active">Offres</a>
        <a href="publications.html" class="nav-link">Publications</a>
        <a href="messages.html" class="nav-link">Messages</a>
        <a href="/swaply/view/front/reclamations.php" class="nav-link">Réclamations</a>
      </nav>

      <div onclick="window.location.href='/swaply/view/front/Profil.php'" class="w-10 h-10 bg-teal-100 rounded-2xl overflow-hidden border-2 border-white shadow cursor-pointer relative" onclick="togglePhotoMenu()">
        <?php if ($photo): ?>
          <img src="/swaply/uploads/profiles/<?= htmlspecialchars($photo) ?>">>" alt="Profil" class="w-full h-full object-cover" style="width: 100%; height: 100%; object-fit: cover; display: block;">
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


<!-- MAIN CONTENT -->
<main class="max-w-5xl mx-auto px-6 py-10 animate-fade-in">

  <!-- Breadcrumb -->
  <div class="mb-6">
    <nav class="flex items-center gap-2 text-sm">
      <a href="index.php?action=choicee" class="text-gray-500 hover:text-teal-600 transition">Offres</a>
      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M9 5l7 7-7 7"/>
      </svg>
      <span class="text-gray-800 font-medium">Détails de l'offre</span>
    </nav>
  </div>

  <!-- Carte principale -->
  <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100 card-hover mb-8">

    <!-- En-tête -->
    <div class="bg-gradient-to-r from-teal-500 to-teal-600 px-8 py-6">
      <div class="flex items-start justify-between">
        <div class="flex-1">
          <div class="inline-flex items-center gap-2 mb-3">
            <div class="w-8 h-8 bg-white/20 rounded-xl flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20 7h-4.18A3 3 0 0 0 16 5.18V4a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
              </svg>
            </div>
            <span class="text-white/80 text-sm font-medium">Offre de compétence</span>
          </div>
          <h1 class="text-3xl font-bold text-white leading-tight">
            <?= htmlspecialchars($offre->getTitre()) ?>
          </h1>
        </div>

        <?php
          $statut = strtolower($offre->getStatut());
          $badgeConfig = match($statut) {
            'active' => ['bg' => 'bg-emerald-500', 'text' => 'Active', 'icon' => '✓'],
            'pending' => ['bg' => 'bg-amber-500', 'text' => 'En attente', 'icon' => '⏳'],
            'inactive' => ['bg' => 'bg-gray-500', 'text' => 'Inactive', 'icon' => '○'],
            'bloque' => ['bg' => 'bg-red-500', 'text' => 'Bloquée', 'icon' => '✗'],
            default => ['bg' => 'bg-blue-500', 'text' => ucfirst($statut), 'icon' => 'ℹ']
          };
        ?>
        <div class="<?= $badgeConfig['bg'] ?> text-white px-4 py-2 rounded-xl font-semibold text-sm shadow-lg flex items-center gap-2">
          <span class="text-lg"><?= $badgeConfig['icon'] ?></span>
          <?= $badgeConfig['text'] ?>
        </div>
      </div>
    </div>

    <!-- Contenu -->
    <div class="p-8">

      <!-- Description -->
      <div class="mb-8">
        <div class="flex items-center gap-2 mb-3">
          <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
          <h2 class="text-lg font-semibold text-gray-800">Description détaillée</h2>
        </div>
        <div class="bg-gray-50 rounded-2xl p-5 border border-gray-200">
          <p class="text-gray-700 leading-relaxed">
            <?= nl2br(htmlspecialchars($offre->getDescription())) ?>
          </p>
        </div>
      </div>

      <!-- Grille d'informations -->
      <div class="grid md:grid-cols-2 gap-5 mb-8">
        
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-5 border border-blue-200">
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20 7h-4.18A3 3 0 0 0 16 5.18V4a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
              </svg>
            </div>
            <p class="text-xs text-blue-600 font-medium uppercase tracking-wide">Catégorie</p>
          </div>
          <p class="text-xl font-bold text-gray-800"><?= htmlspecialchars($offre->getCategorie()) ?></p>
        </div>

        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-5 border border-purple-200">
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-purple-500 rounded-xl flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
              </svg>
            </div>
            <p class="text-xs text-purple-600 font-medium uppercase tracking-wide">Niveau requis</p>
          </div>
          <p class="text-xl font-bold text-gray-800"><?= htmlspecialchars($offre->getNiveau()) ?></p>
        </div>

        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-2xl p-5 border border-orange-200">
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-orange-500 rounded-xl flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </div>
            <p class="text-xs text-orange-600 font-medium uppercase tracking-wide">Nombre de vues</p>
          </div>
          <p class="text-xl font-bold text-gray-800"><?= number_format($offre->getVues(), 0, ',', ' ') ?></p>
        </div>

        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-2xl p-5 border border-red-200">
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-red-500 rounded-xl flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 6v6l4 2"/>
              </svg>
            </div>
            <p class="text-xs text-red-600 font-medium uppercase tracking-wide">Date limite</p>
          </div>
          <p class="text-xl font-bold text-gray-800">
            <?= $offre->getDateLimite() ? $offre->getDateLimite()->format('d/m/Y') : 'Non spécifiée' ?>
          </p>
          <?php if ($offre->getDateLimite()): ?>
            <p class="text-xs text-gray-500 mt-1">à <?= $offre->getDateLimite()->format('H:i') ?></p>
          <?php endif; ?>
        </div>

        <!-- NOUVEAU BLOC CRÉATEUR - AJOUTÉ ICI -->
        <div class="bg-gradient-to-br from-teal-50 to-teal-100 rounded-2xl p-5 border border-teal-200 md:col-span-2">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 bg-teal-500 rounded-xl flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
              </svg>
            </div>
            <p class="text-xs text-teal-600 font-medium uppercase tracking-wide">Créé par</p>
          </div>
          
          <?php if (isset($createur) && $createur && !empty($createur['nom'])): ?>
            <div class="flex items-center gap-4">
              <!-- Avatar/Photo -->
              <div class="w-14 h-14 bg-teal-200 rounded-full overflow-hidden flex-shrink-0">
                <?php if (!empty($createur['photo'])): ?>
                  <img src="/swaply/uploads/profiles/<?= htmlspecialchars($createur['photo']) ?>" 
                       class="w-full h-full object-cover"
                       alt="Photo de <?= htmlspecialchars($createur['prenom']) ?>">
                <?php else: ?>
                  <div class="w-full h-full flex items-center justify-center text-teal-700 font-bold text-xl">
                    <?= strtoupper(substr($createur['prenom'] ?? '', 0, 1) . substr($createur['nom'] ?? '', 0, 1)) ?>
                  </div>
                <?php endif; ?>
              </div>
              
              <div class="flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                  <p class="text-lg font-bold text-gray-800">
                    <?= htmlspecialchars($createur['prenom'] ?? '') ?> <?= htmlspecialchars($createur['nom'] ?? '') ?>
                  </p>
                  <?php if (isset($_SESSION['user']['id_u']) && $createur['id_u'] == $_SESSION['user']['id_u']): ?>
                    <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full bg-teal-100 text-teal-700">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M5 13l4 4L19 7"/>
                      </svg>
                      Votre offre
                    </span>
                  <?php endif; ?>
                </div>
                <p class="text-sm text-gray-600">
                  📧 <?= htmlspecialchars($createur['email'] ?? '') ?>
                </p>
                <?php if (!empty($createur['telephone'])): ?>
                  <p class="text-xs text-gray-500 mt-1">
                    📞 <?= htmlspecialchars($createur['telephone']) ?>
                  </p>
                <?php endif; ?>
                <?php if (!empty($createur['date_naissance'])): ?>
                  <p class="text-xs text-gray-500">
                    🎂 Né(e) le: <?= date('d/m/Y', strtotime($createur['date_naissance'])) ?>
                  </p>
                <?php endif; ?>
              </div>
            </div>
          <?php else: ?>
            <div class="flex items-center gap-4">
              <div class="w-14 h-14 bg-gray-200 rounded-full flex items-center justify-center">
                <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
              </div>
              <div>
                <p class="text-lg font-bold text-gray-800">Utilisateur inconnu</p>
                <p class="text-xs text-gray-500">Compte supprimé ou inexistant (ID: <?= htmlspecialchars($offre->getIdU()) ?>)</p>
              </div>
            </div>
          <?php endif; ?>
        </div>

      </div>

      <!-- Actions -->
      <div class="flex gap-3 pt-6 border-t border-gray-200 flex-wrap">
        <a href="index.php?action=choicee" class="inline-flex items-center gap-2 bg-gray-800 text-white px-6 py-3 rounded-xl hover:bg-gray-700 transition font-medium shadow-md">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
          Retour
        </a>

        <!-- QR Code -->
        <button onclick="generateQR()" class="inline-flex items-center gap-2 bg-purple-500 text-white px-6 py-3 rounded-xl hover:bg-purple-600 transition font-medium shadow-md">
          📱 QR Code
        </button>

        <!-- Print -->
        <button onclick="window.print()" class="inline-flex items-center gap-2 bg-blue-500 text-white px-6 py-3 rounded-xl hover:bg-blue-600 transition font-medium shadow-md">
          🖨️ Imprimer
        </button>

        <?php if (isset($_SESSION['user']['id_u']) && $offre->getIdU() == $_SESSION['user']['id_u']): ?>
          <a href="index.php?action=edit&id=<?= $offre->getIdOffre() ?>" class="inline-flex items-center gap-2 bg-blue-500 text-white px-6 py-3 rounded-xl hover:bg-blue-600 transition font-medium shadow-md">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
              <path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/>
            </svg>
            Modifier
          </a>

          
        <?php endif; ?>
      </div>

    </div>

  </div>

  <!-- SECTION DES DEMANDES ASSOCIÉES -->
  <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
    
    <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-8 py-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-teal-500 rounded-xl flex items-center justify-center">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
          </svg>
        </div>
        <div>
          <h2 class="text-xl font-bold text-white">Demandes correspondantes</h2>
          <p class="text-gray-300 text-sm mt-1">Demandes qui correspondent à cette offre</p>
        </div>
      </div>
    </div>

    <div class="p-8">
      
      <?php if (!empty($demandes)): ?>
        
        <div class="grid md:grid-cols-2 gap-5">
          
          <?php foreach ($demandes as $d): ?>
           
            <div class="demande-card bg-gradient-to-br from-gray-50 to-white rounded-2xl border border-gray-200 p-5">
              <div class="mb-2">
                <span class="px-3 py-1 text-xs rounded-xl bg-teal-100 text-teal-700 font-bold">
                  Match: <?= $d['score'] ?>%
                </span>
              </div>

              <div class="flex items-start justify-between mb-3">
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-2">
                    <div class="w-6 h-6 bg-teal-100 rounded-lg flex items-center justify-center">
                      <svg class="w-3 h-3 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M4 4h16v16H4z M9 9h6v6H9z"/>
                      </svg>
                    </div>
                    <span class="text-xs text-teal-600 font-medium">Demande</span>
                  </div>
                  
                  <h3 class="font-semibold text-gray-800 text-lg leading-tight">
                    <?= htmlspecialchars($d['titre']) ?>
                  </h3>
                </div>
              </div>

              <p class="text-sm text-gray-600 leading-relaxed mb-4 line-clamp-2">
                <?= htmlspecialchars($d['description']) ?>
              </p>

              <div class="flex flex-wrap gap-2 mb-4">
                <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-lg bg-blue-50 text-blue-600 border border-blue-100">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M20 7h-4.18A3 3 0 0 0 16 5.18V4a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
                  </svg>
                  <?= htmlspecialchars($d['categorie']) ?>
                </span>
                <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-lg bg-purple-50 text-purple-600 border border-purple-100">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                  </svg>
                  <?= htmlspecialchars($d['niveau']) ?>
                </span>
              </div>

              <a href="index.php?action=showd&id=<?= $d['id_demande'] ?? $d['id'] ?? 0 ?>" class="inline-flex items-center gap-2 text-sm text-teal-600 font-medium hover:text-teal-700 transition group">
                Voir la demande
                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M9 5l7 7-7 7"/>
                </svg>
              </a>

            </div>
            
          <?php endforeach; ?>
          
        </div>
        
      <?php else: ?>
        
        <div class="text-center py-12">
          <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
          </div>
          <p class="text-gray-500 font-medium">Aucune demande correspondante</p>
          <p class="text-gray-400 text-sm mt-1">Aucune demande ne correspond à cette offre pour le moment</p>
        </div>
        
      <?php endif; ?>
      
    </div>

  </div>

</main>

<!-- Modal de suppression -->
<div id="deleteModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
  <div class="bg-white rounded-2xl w-full max-w-sm p-6 shadow-xl border border-gray-100" onclick="event.stopPropagation()">
    <div class="flex items-center justify-center w-12 h-12 bg-red-50 rounded-xl mx-auto mb-4">
      <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/>
      </svg>
    </div>
    <h2 class="text-lg font-semibold text-gray-800 text-center mb-2">Supprimer cette offre ?</h2>
    <p class="text-sm text-gray-400 text-center mb-6">Cette action est irréversible.</p>
    <div class="flex gap-3">
      <button onclick="this.closest('#deleteModal').classList.add('hidden')" class="flex-1 py-2.5 rounded-xl text-sm font-medium bg-gray-100 hover:bg-gray-200 text-gray-600 transition">
        Annuler
      </button>
      <a href="index.php?action=delete&id=<?= $offre->getIdOffre() ?>" class="flex-1 py-2.5 rounded-xl text-sm font-medium bg-red-500 hover:bg-red-600 text-white text-center transition">
        Supprimer
      </a>
    </div>
  </div>
</div>

<!-- Modal QR Code -->
<div id="qrModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
  <div class="bg-white p-6 rounded-2xl text-center shadow-xl max-w-sm mx-auto">
    <h2 class="text-lg font-bold mb-3">QR Code de l'offre</h2>
    <p class="text-sm text-gray-600 mb-4">Scannez ce code pour voir directement les détails de l'offre sur votre téléphone</p>
    <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-4">
      <p class="text-xs text-green-800">
        <strong>✅ Nouveau:</strong> Le QR code contient maintenant les détails complets de l'offre !
      </p>
    </div>
    <div id="qrBox" class="flex justify-center mb-4"></div>
    <div class="text-xs text-gray-500 mb-4">
      <p><strong>Titre:</strong> <?= htmlspecialchars($offre->getTitre()) ?></p>
      <p><strong>Catégorie:</strong> <?= htmlspecialchars($offre->getCategorie()) ?></p>
      <p><strong>Statut:</strong> <?= htmlspecialchars($offre->getStatut()) ?></p>
    </div>
    <div class="flex gap-2 mb-4">
      <button onclick="copyToClipboard()" class="flex-1 bg-teal-500 text-white px-3 py-2 rounded-lg text-sm hover:bg-teal-600 transition">
        📋 Copier les détails
      </button>
    </div>
    <button onclick="document.getElementById('qrModal').classList.add('hidden')" class="mt-4 bg-gray-800 text-white px-4 py-2 rounded-xl hover:bg-gray-700 transition">
      Fermer
    </button>
  </div>
</div>

<script>
  // Modal suppression
  const modal = document.getElementById('deleteModal');
  modal?.addEventListener('click', function(e) {
    if (e.target === this) this.classList.add('hidden');
  });
</script>

<!-- QR LIB -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
  // Données de l'offre pour le QR code
  const offerData = {
    id: "<?= $offre->getIdOffre() ?>",
    titre: "<?= addslashes(substr(htmlspecialchars($offre->getTitre()), 0, 30)) ?>",
    categorie: "<?= addslashes(htmlspecialchars($offre->getCategorie())) ?>",
    niveau: "<?= addslashes(htmlspecialchars($offre->getNiveau())) ?>",
    statut: "<?= addslashes(htmlspecialchars($offre->getStatut())) ?>",
    dateLimite: "<?= $offre->getDateLimite() ? $offre->getDateLimite()->format('d/m/Y') : 'N/A' ?>"
  };
 
  // QR CODE
  function generateQR() {
    try {
      console.log("Starting QR code generation...");
      console.log("Offer data:", offerData);
      
      const modal = document.getElementById("qrModal");
      const qrBox = document.getElementById("qrBox");
      
      modal.classList.remove("hidden");
      qrBox.innerHTML = "";
      
      const url = "http://localhost/swaply/public/index.php?action=show&id=" + offerData.id;
      const offerDetails = 
        url;
        
      new QRCode(qrBox, {
        text: offerDetails,
        width: 150,
        height: 150,
        correctLevel: QRCode.CorrectLevel.H
      });
      
      console.log("QR Code generated successfully");
    } catch (error) {
      console.error("Error generating QR code:", error);
      alert("Erreur lors de la génération du QR code: " + error.message);
    }
  }

  // Copy to clipboard
  function copyToClipboard() {
    const offerDetails = "SWAPLY OFFRE\n" +
      "Titre: " + offerData.titre + "\n" +
      "Catégorie: " + offerData.categorie + "\n" +
      "Niveau: " + offerData.niveau + "\n" +
      "Statut: " + offerData.statut + "\n" +
      "Échéance: " + offerData.dateLimite;
    
    navigator.clipboard.writeText(offerDetails).then(function() {
      alert("Détails de l'offre copiés dans le presse-papiers !");
    }).catch(function(err) {
      console.error("Erreur lors de la copie:", err);
      const textArea = document.createElement("textarea");
      textArea.value = offerDetails;
      document.body.appendChild(textArea);
      textArea.select();
      document.execCommand('copy');
      document.body.removeChild(textArea);
      alert("Détails de l'offre copiés dans le presse-papiers !");
    });
  }
</script>

<style>
  .line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
  
  @media print {
    header, button, .shadow-md, nav, #deleteModal, #qrModal {
      display: none !important;
    }
  }
</style>

</body>
</html>