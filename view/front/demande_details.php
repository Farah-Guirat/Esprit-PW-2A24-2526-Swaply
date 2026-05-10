<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$photo = $_SESSION['user']['photo'] ?? null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Détails de la demande: <?= htmlspecialchars($demande->getTitre() ?? 'Demande') ?> - Catégorie: <?= htmlspecialchars($demande->getCategorie() ?? 'Non spécifiée') ?>">
  <title>Swaply - Détails de la demande</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    .offre-card {
      transition: all 0.3s ease;
    }
    .offre-card:hover {
      transform: translateY(-4px);
      border-color: #14b8a6;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    .status-badge {
      transition: all 0.3s ease;
    }
    .status-badge:hover {
      transform: scale(1.05);
    }
    
    /* Mobile optimizations */
    @media (max-width: 768px) {
      .card-hover:hover {
        transform: none;
      }
      .offre-card:hover {
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

<body class="bg-gradient-to-br from-gray-100 to-gray-200 min-h-screen">

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
        <a href="swaplyf.php" class="nav-link ">Accueil</a>
        <a href="profils.html" class="nav-link">Profils</a>
        <a href="projets.html" class="nav-link">Projets</a>
       <a href="/swaply/public/index.php?action=choice"  class="nav-link active">Demandes</a>
        <a href="/swaply/public/index.php?action=choicee">Offres</a>
        <a href="publications.html" class="nav-link">Publications</a>
        <a href="messages.html" class="nav-link">Messages</a>
        <a href="reclamations.php" class="nav-link">Réclamations</a>
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
      <a href="index.php?action=choice" class="text-gray-500 hover:text-teal-600 transition">Demandes</a>
      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M9 5l7 7-7 7"/>
      </svg>
      <span class="text-gray-800 font-medium">Détails de la demande</span>
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
              <i class="fas fa-file-alt text-white text-lg"></i>
            </div>
            <span class="text-white/80 text-sm font-medium">Demande de compétence</span>
          </div>
          <h1 class="text-3xl font-bold text-white leading-tight">
            <?= htmlspecialchars($demande->getTitre() ?? 'Demande sans titre') ?>
          </h1>
        </div>

        <?php
          $statut = strtolower($demande->getStatut() ?? '');
          $badgeConfig = match($statut) {
            'active' => ['bg' => 'bg-emerald-500', 'text' => 'Active', 'icon' => '✓'],
            'pending' => ['bg' => 'bg-amber-500', 'text' => 'En attente', 'icon' => '⏳'],
            'inactive' => ['bg' => 'bg-gray-500', 'text' => 'Inactive', 'icon' => '○'],
            'bloque' => ['bg' => 'bg-red-500', 'text' => 'Bloquée', 'icon' => '✗'],
            default => ['bg' => 'bg-blue-500', 'text' => ucfirst($statut), 'icon' => 'ℹ']
          };
        ?>
        <div class="<?= $badgeConfig['bg'] ?> text-white px-4 py-2 rounded-xl font-semibold text-sm shadow-lg flex items-center gap-2 status-badge">
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
          <i class="fas fa-align-left text-teal-500"></i>
          <h2 class="text-lg font-semibold text-gray-800">Description détaillée</h2>
        </div>
        <div class="bg-gray-50 rounded-2xl p-5 border border-gray-200">
          <p class="text-gray-700 leading-relaxed">
            <?php 
              $description = $demande->getDescription() ?? '';
              if(empty($description)) {
                echo '<span class="text-gray-400 italic">Aucune description fournie</span>';
              } else {
                echo nl2br(htmlspecialchars($description));
              }
            ?>
          </p>
        </div>
      </div>

      <!-- Grille d'informations -->
      <div class="grid md:grid-cols-2 gap-5 mb-8">
        
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-5 border border-blue-200">
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center">
              <i class="fas fa-tag text-white"></i>
            </div>
            <p class="text-xs text-blue-600 font-medium uppercase tracking-wide">Catégorie</p>
          </div>
          <p class="text-xl font-bold text-gray-800"><?= htmlspecialchars($demande->getCategorie() ?? 'Non spécifiée') ?></p>
        </div>

        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-5 border border-purple-200">
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-purple-500 rounded-xl flex items-center justify-center">
              <i class="fas fa-chart-line text-white"></i>
            </div>
            <p class="text-xs text-purple-600 font-medium uppercase tracking-wide">Niveau requis</p>
          </div>
          <p class="text-xl font-bold text-gray-800"><?= htmlspecialchars($demande->getNiveau() ?? 'Non spécifié') ?></p>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-5 border border-green-200">
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-green-500 rounded-xl flex items-center justify-center">
              <i class="fas fa-calendar-alt text-white"></i>
            </div>
            <p class="text-xs text-green-600 font-medium uppercase tracking-wide">Date de création</p>
          </div>
          <p class="text-xl font-bold text-gray-800">
            <?php 
              try {
                $dateCreation = $demande->getDateCreation();
                if($dateCreation instanceof DateTime) {
                  echo $dateCreation->format('d/m/Y');
                } elseif(is_string($dateCreation)) {
                  echo date('d/m/Y', strtotime($dateCreation));
                } else {
                  echo 'Date non disponible';
                }
              } catch (Exception $e) {
                echo 'Date non disponible';
              }
            ?>
          </p>
          <?php 
            try {
              $dateCreation = $demande->getDateCreation();
              if($dateCreation instanceof DateTime) {
                echo '<p class="text-xs text-gray-500 mt-1">à ' . $dateCreation->format('H:i') . '</p>';
              } elseif(is_string($dateCreation)) {
                echo '<p class="text-xs text-gray-500 mt-1">à ' . date('H:i', strtotime($dateCreation)) . '</p>';
              }
            } catch (Exception $e) {
              // Ne rien afficher
            }
          ?>
        </div>

        <!-- NOUVEAU BLOC CRÉATEUR - AJOUTÉ ICI -->
        <div class="bg-gradient-to-br from-teal-50 to-teal-100 rounded-2xl p-5 border border-teal-200">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 bg-teal-500 rounded-xl flex items-center justify-center">
              <i class="fas fa-user-circle text-white text-xl"></i>
            </div>
            <p class="text-xs text-teal-600 font-medium uppercase tracking-wide">Créé par</p>
          </div>
          
          <?php if (isset($createur) && $createur && !empty($createur['nom'])): ?>
            <div class="flex items-center gap-3">
              <!-- Avatar/Photo -->
              <div class="w-12 h-12 bg-teal-200 rounded-full overflow-hidden flex-shrink-0">
                <?php if (!empty($createur['photo'])): ?>
                  <img src="/swaply/uploads/profiles/<?= htmlspecialchars($createur['photo']) ?>" 
                       class="w-full h-full object-cover"
                       alt="Photo de <?= htmlspecialchars($createur['prenom']) ?>">
                <?php else: ?>
                  <div class="w-full h-full flex items-center justify-center text-teal-700 font-bold text-lg">
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
                      <i class="fas fa-check-circle text-xs"></i>
                      Votre demande
                    </span>
                  <?php endif; ?>
                </div>
                <p class="text-sm text-gray-600">
                  <i class="fas fa-envelope mr-1"></i> <?= htmlspecialchars($createur['email'] ?? '') ?>
                </p>
                <?php if (!empty($createur['telephone'])): ?>
                  <p class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-phone mr-1"></i> <?= htmlspecialchars($createur['telephone']) ?>
                  </p>
                <?php endif; ?>
              </div>
            </div>
          <?php else: ?>
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center">
                <i class="fas fa-user-slash text-gray-400 text-xl"></i>
              </div>
              <div>
                <p class="text-lg font-bold text-gray-800">Utilisateur inconnu</p>
                <p class="text-xs text-gray-500">Compte supprimé ou inexistant (ID: <?= htmlspecialchars($demande->getIdU() ?? 'N/A') ?>)</p>
              </div>
            </div>
          <?php endif; ?>
        </div>

      </div>

      <!-- Informations supplémentaires -->
      <?php if(method_exists($demande, 'getBudget') && $demande->getBudget() && $demande->getBudget() > 0): ?>
      <div class="mb-6">
        <div class="bg-gradient-to-r from-yellow-50 to-orange-50 p-5 rounded-xl border border-yellow-200">
          <div class="flex items-center gap-2 mb-2">
            <i class="fas fa-euro-sign text-yellow-600"></i>
            <p class="text-xs text-yellow-600 font-semibold uppercase tracking-wide">Budget proposé</p>
          </div>
          <p class="text-2xl font-bold text-gray-800"><?= number_format($demande->getBudget(), 2) ?> €</p>
        </div>
      </div>
      <?php endif; ?>

      <?php if(method_exists($demande, 'getDuree') && $demande->getDuree()): ?>
      <div class="mb-6">
        <div class="flex items-center gap-2 p-4 bg-gray-50 rounded-xl border border-gray-200">
          <i class="fas fa-hourglass-half text-teal-500"></i>
          <p class="text-gray-700"><strong class="font-semibold">Durée estimée :</strong> <?= htmlspecialchars($demande->getDuree()) ?></p>
        </div>
      </div>
      <?php endif; ?>

      <!-- Actions -->
      <div class="flex gap-3 pt-6 border-t border-gray-200 flex-wrap">
        <a href="index.php?action=choice" class="inline-flex items-center gap-2 bg-gray-800 text-white px-6 py-3 rounded-xl hover:bg-gray-700 transition font-medium shadow-md">
          <i class="fas fa-arrow-left"></i>
          Retour
        </a>

        

        <!-- Print -->
        <button onclick="window.print()" class="inline-flex items-center gap-2 bg-blue-500 text-white px-6 py-3 rounded-xl hover:bg-blue-600 transition font-medium shadow-md">
          <i class="fas fa-print"></i>
          Imprimer
        </button>

        <?php if (isset($_SESSION['user']['id_u']) && $demande->getIdU() == $_SESSION['user']['id_u']): ?>
          <a href="index.php?action=editd&id=<?= $demande->getIdDemande() ?>" class="inline-flex items-center gap-2 bg-blue-500 text-white px-6 py-3 rounded-xl hover:bg-blue-600 transition font-medium shadow-md">
            <i class="fas fa-edit"></i>
            Modifier
          </a>

         
        <?php endif; ?>
      </div>

    </div>

  </div>

 

  </div>

</main>

<!-- Modal de suppression -->
<div id="deleteModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
  <div class="bg-white rounded-2xl w-full max-w-sm p-6 shadow-xl border border-gray-100" onclick="event.stopPropagation()">
    <div class="flex items-center justify-center w-12 h-12 bg-red-50 rounded-xl mx-auto mb-4">
      <i class="fas fa-trash-alt text-red-500 text-xl"></i>
    </div>
    <h2 class="text-lg font-semibold text-gray-800 text-center mb-2">Supprimer cette demande ?</h2>
    <p class="text-sm text-gray-400 text-center mb-6">Cette action est irréversible.</p>
    <div class="flex gap-3">
      <button onclick="this.closest('#deleteModal').classList.add('hidden')" class="flex-1 py-2.5 rounded-xl text-sm font-medium bg-gray-100 hover:bg-gray-200 text-gray-600 transition">
        Annuler
      </button>
      <a href="index.php?action=deleted&id=<?= $demande->getIdDemande() ?>" class="flex-1 py-2.5 rounded-xl text-sm font-medium bg-red-500 hover:bg-red-600 text-white text-center transition">
        Supprimer
      </a>
    </div>
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
  // Données de la demande pour le QR code
  const demandeData = {
    id: "<?= $demande->getIdDemande() ?>",
    titre: "<?= addslashes(substr(htmlspecialchars($demande->getTitre() ?? ''), 0, 30)) ?>",
    categorie: "<?= addslashes(htmlspecialchars($demande->getCategorie() ?? '')) ?>",
    niveau: "<?= addslashes(htmlspecialchars($demande->getNiveau() ?? '')) ?>",
    statut: "<?= addslashes(htmlspecialchars($demande->getStatut() ?? '')) ?>",
    dateCreation: "<?php 
      try {
        $dateCreation = $demande->getDateCreation();
        if($dateCreation instanceof DateTime) {
          echo $dateCreation->format('d/m/Y');
        } elseif(is_string($dateCreation)) {
          echo date('d/m/Y', strtotime($dateCreation));
        } else {
          echo 'N/A';
        }
      } catch (Exception $e) {
        echo 'N/A';
      }
    ?>"
  };
 
  // QR CODE
  function generateQR() {
    try {
      console.log("Starting QR code generation...");
      console.log("Demande data:", demandeData);
      
      const modal = document.getElementById("qrModal");
      const qrBox = document.getElementById("qrBox");
      
      modal.classList.remove("hidden");
      qrBox.innerHTML = "";
      
      const url = "http://localhost/swaply/public/index.php?action=showd&id=" + demandeData.id;
      const demandeDetails = "SWAPLY\n" +
        "Type: DEMANDE\n" +
        "Titre: " + demandeData.titre + "\n" +
        "Statut: " + demandeData.statut + "\n" +
        "Date création: " + demandeData.dateCreation + "\n\n" +
        url;
        
      new QRCode(qrBox, {
        text: demandeDetails,
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
    const demandeDetails = "SWAPLY DEMANDE\n" +
      "Titre: " + demandeData.titre + "\n" +
      "Catégorie: " + demandeData.categorie + "\n" +
      "Niveau: " + demandeData.niveau + "\n" +
      "Statut: " + demandeData.statut + "\n" +
      "Date création: " + demandeData.dateCreation;
    
    navigator.clipboard.writeText(demandeDetails).then(function() {
      alert("Détails de la demande copiés dans le presse-papiers !");
    }).catch(function(err) {
      console.error("Erreur lors de la copie:", err);
      const textArea = document.createElement("textarea");
      textArea.value = demandeDetails;
      document.body.appendChild(textArea);
      textArea.select();
      document.execCommand('copy');
      document.body.removeChild(textArea);
      alert("Détails de la demande copiés dans le presse-papiers !");
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