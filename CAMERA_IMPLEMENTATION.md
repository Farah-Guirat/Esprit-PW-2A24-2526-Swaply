# Implémentation de la Fonction Caméra

## 📋 Résumé des modifications

La fonction caméra a été implémentée dans deux fichiers :
- **[view/front/Profil.php](view/front/Profil.php)** - Page de profil utilisateur
- **[view/front/swaplyf.php](view/front/swaplyf.php)** - Page d'accueil

## ✨ Fonctionnalités

### 1. **Accès à la caméra**
   - Utilisation de l'API `getUserMedia()` pour accéder à la caméra de l'utilisateur
   - Demande de permission de l'utilisateur via le navigateur
   - Gestion des erreurs d'accès

### 2. **Aperçu en direct**
   - Affichage vidéo en temps réel de la caméra
   - Modal plein écran avec interface intuitive
   - Titre indicatif "📷 Prendre une photo"

### 3. **Capture de photo**
   - Bouton "Capturer" pour prendre une photo
   - Utilisation de `canvas` pour capturer l'image vidéo
   - Conversion en format JPEG

### 4. **Aperçu et retouche**
   - Affichage de l'image capturée
   - Bouton "Reprendre" pour refaire une photo
   - Possibilité de naviguer entre la vidéo et la preview

### 5. **Envoi de la photo**
   - Conversion de l'image en Blob
   - Envoi au serveur via FormData
   - Utilisation du même endpoint que le téléchargement (`uploadPhoto.php`)
   - Rechargement de la page après succès

## 🎯 Composants ajoutés

### HTML
```html
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
```

### CSS (Profil.php)
Styles personnalisés pour le modal avec :
- Arrière-plan noir semi-transparent
- Vidéo et aperçu image avec bordures arrondies
- Boutons stylisés (primaire et secondaire)
- Gestion de l'affichage/masquage des éléments

### CSS (swaplyf.php)
Mêmes styles adaptés pour la cohérence avec Tailwind CSS

### JavaScript - Nouvelles fonctions
1. **`openCamera()`** - Ouvre le modal et accède à la caméra
2. **`capturePhoto()`** - Capture l'image de la vidéo
3. **`retakePhoto()`** - Revient à l'aperçu vidéo
4. **`sendCapturedPhoto()`** - Envoie la photo au serveur
5. **`closeCamera()`** - Ferme le modal et arrête le flux vidéo

## 🔧 Configuration requise

### Navigateur
- Support de `getUserMedia()` (Chrome, Firefox, Edge, Safari)
- Support de `Canvas API`
- HTTPS requis sur site de production (getUserMedia ne fonctionne qu'en HTTPS)

### Permissions
- L'utilisateur doit autoriser l'accès à la caméra via la notification du navigateur

### Serveur
- L'endpoint `uploadPhoto.php` doit accepter les fichiers `multipart/form-data`
- Le répertoire `uploads/profiles/` doit exister et être accessible en écriture

## 🚀 Utilisation

1. **Cliquer sur le cercle avatar** pour ouvrir le menu
2. **Sélectionner "Prendre une photo"** pour ouvrir le modal caméra
3. **Voir l'aperçu en direct** de votre caméra
4. **Cliquer "Capturer"** pour prendre une photo
5. **Voir la preview** de la photo capturée
6. **Cliquer "Envoyer"** pour envoyer la photo
   - OU **Cliquer "Reprendre"** pour refaire une photo
   - OU **Cliquer "Annuler"** pour fermer sans envoyer

## ⚠️ Notes importantes

- **HTTPS requis** : sur un site de production, l'API `getUserMedia()` ne fonctionne que sur HTTPS
- **Permissions**: L'utilisateur doit autoriser l'accès à la caméra
- **Format** : Les photos sont capturées en JPEG
- **Compatible mobile** : L'attribut `playsinline` permet l'affichage vidéo sur mobile

## 🐛 Gestion des erreurs

La fonction gère les erreurs suivantes :
- Refus d'accès à la caméra par l'utilisateur
- Caméra indisponible
- Erreur lors du téléchargement

Les messages d'erreur s'affichent via `alert()` pour informer l'utilisateur.

## 📁 Fichiers modifiés

- `view/front/Profil.php` - Ajout du modal, CSS et fonctions JavaScript
- `view/front/swaplyf.php` - Ajout du modal, CSS et fonctions JavaScript

## ✅ Vérification

Pour vérifier le bon fonctionnement :
1. Ouvrir l'une des pages (Profil.php ou swaplyf.php)
2. Cliquer sur l'avatar
3. Sélectionner "Prendre une photo"
4. Autoriser l'accès à la caméra si demandé
5. Vous devez voir l'aperçu vidéo en direct
