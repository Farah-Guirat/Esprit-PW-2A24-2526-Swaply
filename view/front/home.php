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
  <title>Swaply - Accueil</title>
  
  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  
  <!-- Custom CSS -->
      <link rel="stylesheet" href="../src/assets/css/style.css">

  
  <style>
    .hero-bg {
      background: linear-gradient(135deg, #14b8a6, #0f766e);
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
      background: #14b8a6;
      color: white;
    }

    .camera-btn.primary:hover {
      background: #0f766e;
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
        <a href="swaplyf.php" class="nav-link">Accueil</a>
        <a href="Profil.php" class="nav-link">Profils</a>
        <a href="projets.php" class="nav-link">Projets</a>
        <a href="/swaply/public/index.php?action=choice" class="nav-link">Demandes</a>
        <a href="/swaply/public/index.php?action=choicee" class="nav-link">Offres</a>
        <a href="listepublication.php" class="nav-link">Publications</a>
        <a href="Messages.php" class="nav-link">Messages</a>
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

  <?php if (isset($_GET['account_created'])): ?>
    <div class="max-w-7xl mx-auto px-8 py-4">
      <div id="success-banner" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
        <strong>Succès !</strong> Votre compte a été bien créé.
      </div>
    </div>
  <?php endif; ?>

  <main class="max-w-7xl mx-auto px-8 py-10">

    <!-- Hero Section -->
    <div class="hero-bg rounded-3xl p-12 text-white mb-12">
      <div class="max-w-2xl">
        <h2 class="text-5xl font-bold leading-tight">Échangez, collaborez et développez<br>vos projets ensemble</h2>
        <p class="mt-4 text-teal-100 text-lg">La plateforme pour connecter talents, projets et opportunités.</p>
        <div class="mt-8 flex gap-4">
          <a href="profils.html" class="bg-white text-teal-700 px-8 py-4 rounded-3xl font-semibold text-lg shadow-lg hover:shadow-xl transition">
            Explorer les profils
          </a>
          <a href="projets.html" class="border border-white/70 px-8 py-4 rounded-3xl font-semibold text-lg hover:bg-white/10 transition">
            Voir les projets
          </a>
        </div>
      </div>
    </div>

    <!-- Nos sections -->
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Nos sections</h2>
    
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">

      <a href="Profil.php" class="card-hover bg-white rounded-3xl p-6 border border-transparent hover:border-teal-200">
        <div class="w-14 h-14 bg-teal-100 text-teal-600 rounded-2xl flex items-center justify-center text-3xl mb-5">👥</div>
        <h3 class="text-xl font-semibold">Profils</h3>
        <p class="text-gray-500 mt-2">Découvrez les membres de la communauté</p>
      </a>

      <a href="projets.html" class="card-hover bg-white rounded-3xl p-6 border border-transparent hover:border-teal-200">
        <div class="w-14 h-14 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center text-3xl mb-5">📁</div>
        <h3 class="text-xl font-semibold">Projets</h3>
        <p class="text-gray-500 mt-2">Explorez les projets et réalisations</p>
      </a>

      <a href="offres.html" class="card-hover bg-white rounded-3xl p-6 border border-transparent hover:border-teal-200">
        <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-3xl mb-5">💼</div>
        <h3 class="text-xl font-semibold">Offres</h3>
        <p class="text-gray-500 mt-2">Consultez les offres de collaboration</p>
      </a>

      <a href="demandes.html" class="card-hover bg-white rounded-3xl p-6 border border-transparent hover:border-teal-200">
        <div class="w-14 h-14 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center text-3xl mb-5">📢</div>
        <h3 class="text-xl font-semibold">Demandes</h3>
        <p class="text-gray-500 mt-2">Trouvez les demandes de services</p>
      </a>

      <a href="publications.html" class="card-hover bg-white rounded-3xl p-6 border border-transparent hover:border-teal-200">
        <div class="w-14 h-14 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center text-3xl mb-5">📰</div>
        <h3 class="text-xl font-semibold">Publications</h3>
        <p class="text-gray-500 mt-2">Actualités et partages de la communauté</p>
      </a>

      <a href="messages.html" class="card-hover bg-white rounded-3xl p-6 border border-transparent hover:border-teal-200">
        <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center text-3xl mb-5">💬</div>
        <h3 class="text-xl font-semibold">Messages</h3>
        <p class="text-gray-500 mt-2">Vos conversations privées</p>
      </a>

      <a href="reclamations.html" class="card-hover bg-white rounded-3xl p-6 border border-transparent hover:border-teal-200">
        <div class="w-14 h-14 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center text-3xl mb-5">⚠️</div>
        <h3 class="text-xl font-semibold">Réclamations</h3>
        <p class="text-gray-500 mt-2">Signalez un problème ou une réclamation</p>
      </a>

    </div>

  </main>

  <!-- Custom JS -->
  <script src="../../../swaply/src/assets/main.js"></script>
  
  <script>
    // Masquer la bannière de succès après 5 secondes
    const successBanner = document.getElementById('success-banner');
    if (successBanner) {
      setTimeout(() => {
        successBanner.style.display = 'none';
      }, 5000); // 5 secondes
    }

    // Photo menu functions
    function togglePhotoMenu() {
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
      formData.append('id', <?= $_SESSION['user']['id_u'] ?>);
      
      fetch('../controller/uploadPhoto.php', {
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
        fetch('../controller/deletePhoto.php?id=<?= $_SESSION['user']['id_u'] ?>', {
          method: 'GET'
        }).then(response => {
          alert('Photo supprimée avec succès');
          location.reload();
        }).catch(error => {
          alert('Erreur lors de la suppression');
        });
      }
    }

    document.getElementById('file-input').addEventListener('change', function() {
      const file = this.files[0];
      if (file) {
        const formData = new FormData();
        formData.append('photo', file);
        formData.append('id', <?= $_SESSION['user']['id_u'] ?>);
        fetch('../controller/uploadPhoto.php', {
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
  </script>
</body>
</html>