# Système d'Appel Vidéo Swaply

## 📋 Vue d'ensemble

Système complet d'appel vidéo WebRTC pour l'application Swaply, supportant:
- Appels 1-to-1 et groupe
- Signalisation en temps réel avec Socket.io
- Gestion des participants
- Contrôles audio/vidéo
- Historique d'appels
- Statistiques utilisateur

## 🏗️ Architecture

### Backend (PHP)
- **VideoCall.php** : Modèle de gestion des appels en base de données
- **VideoCallController.php** : API REST pour les appels vidéo
- **Base de données** : Tables `video_calls` et `video_call_participants`

### Frontend (JavaScript)
- **VideoCallManager.js** : Gestion WebRTC et Socket.io
- **VideoCallUI.js** : Interface utilisateur
- **videocall.css** : Styles de l'interface

### Serveur de signalisation
- **Node.js + Socket.io** : Signalisation WebRTC en temps réel

## 📦 Installation

### 1. Prérequis
- Node.js >= 14
- PHP >= 7.4
- MySQL >= 5.7

### 2. Configuration du serveur de signalisation

```bash
cd c:\xampp\htdocs\swaply\video_server

# Installer les dépendances
npm install

# Créer le fichier .env
copy .env.example .env

# Démarrer le serveur
npm start
```

Le serveur écoute par défaut sur `http://localhost:3000`

### 3. Migration de la base de données

Exécuter le script SQL pour créer les tables:

```sql
-- Dans phpMyAdmin ou via mysql CLI
source c:\xampp\htdocs\swaply\migrations\003_create_video_calls.sql
```

Ou via PHP:
```php
<?php
require_once __DIR__ . '/config/database.php';
$pdo = Database::getInstance()->getConnection();
$sql = file_get_contents(__DIR__ . '/migrations/003_create_video_calls.sql');
$pdo->exec($sql);
?>
```

### 4. Intégration dans la messagerie

#### A. Charger les scripts et styles

Dans `view/Front/Messages.php`, ajouter:

```html
<!-- Scripts Socket.io et WebRTC -->
<script src="http://localhost:3000/socket.io/socket.io.js"></script>
<script src="/asset/js/VideoCallManager.js"></script>
<script src="/asset/js/VideoCallUI.js"></script>

<!-- CSS -->
<link rel="stylesheet" href="/asset/css/videocall.css">
```

#### B. Passer l'ID utilisateur à JavaScript

```html
<script>
    window.currentUserId = <?php echo (int)$_SESSION['id_user']; ?>;
</script>
```

#### C. Ajouter le bouton d'appel vidéo

Dans le template de la conversation:

```html
<!-- Bouton pour lancer un appel vidéo -->
<div class="message-controls">
    <button onclick="window.videoCallUI?.initiateCall(<?php echo $id_conversation; ?>)" 
            class="video-call-btn">
        📞 Appel vidéo
    </button>
</div>
```

## 🎮 Utilisation

### Pour l'utilisateur initiatrice

```javascript
// Initialiser le gestionnaire
const videoUI = new VideoCallUI(userId, 'http://localhost:3000');

// Lancer un appel
videoUI.initiateCall(conversationId);

// Contrôler l'appel
videoUI.toggleAudio();  // Mute/Unmute
videoUI.toggleVideo();  // On/Off vidéo
videoUI.endCall();      // Terminer
```

### Pour l'utilisateur destinataire

1. Notification d'appel entrant
2. Accepter ou rejeter
3. L'interface vidéo s'affiche automatiquement
4. Contrôler le flux avec les boutons

## 🔌 API Endpoints

### Initier un appel
```
POST /controller/VideoCallController.php?action=initiate
Données: id_conversation, type_appel (1to1|groupe)
Réponse: { success, id_video_call }
```

### Accepter un appel
```
POST /controller/VideoCallController.php?action=accept
Données: id_video_call
Réponse: { success }
```

### Rejeter un appel
```
POST /controller/VideoCallController.php?action=reject
Données: id_video_call
Réponse: { success }
```

### Terminer un appel
```
POST /controller/VideoCallController.php?action=end
Données: id_video_call
Réponse: { success }
```

### Obtenir les participants
```
GET /controller/VideoCallController.php?action=getParticipants&id_video_call=1
Réponse: { success, participants: [...] }
```

### Obtenir l'historique
```
GET /controller/VideoCallController.php?action=getHistory&id_conversation=1&limit=50&offset=0
Réponse: { success, history: [...] }
```

### Appels manqués
```
GET /controller/VideoCallController.php?action=getMissedCalls
Réponse: { success, missed_calls: [...] }
```

## 🎨 Événements Socket.io

### Client → Serveur

- `register_user` : Enregistrer l'utilisateur
- `initiate_call` : Lancer un appel
- `accept_call` : Accepter un appel
- `reject_call` : Rejeter un appel
- `join_call` : Rejoindre un appel
- `leave_call` : Quitter un appel
- `webrtc_offer` : Envoyer une offer WebRTC
- `webrtc_answer` : Envoyer une answer WebRTC
- `ice_candidate` : Envoyer un candidat ICE
- `toggle_audio` : Activer/désactiver le microphone
- `toggle_video` : Activer/désactiver la caméra

### Serveur → Client

- `user_registered` : Utilisateur enregistré
- `incoming_call` : Appel entrant
- `call_accepted` : Appel accepté
- `call_rejected` : Appel rejeté
- `call_started` : Appel démarré
- `user_joined` : Utilisateur rejoint
- `user_left` : Utilisateur parti
- `call_ended` : Appel terminé
- `webrtc_offer` : Offer WebRTC reçue
- `webrtc_answer` : Answer WebRTC reçue
- `ice_candidate` : Candidat ICE reçu

## 📊 Structure de la base de données

### Table: video_calls

```sql
- id_video_call (PK)
- id_conversation (FK)
- id_initiateur (FK)
- type_appel (1to1 | groupe)
- statut (en_attente | en_cours | termine | rejete | manque)
- date_debut
- date_fin
- duree_secondes
- created_at
- updated_at
```

### Table: video_call_participants

```sql
- id_participant (PK)
- id_video_call (FK)
- id_user (FK)
- statut_participant (en_attente | accepte | rejete | deconnecte)
- date_acceptation
- date_depart
- duree_participation_secondes
- joined_at
```

## 🔒 Sécurité

### Authentification
- Vérification de session PHP obligatoire
- Contrôle d'accès sur les conversations
- Validation des IDs utilisateur

### WebRTC
- STUN servers : Google publics
- Configuration CORS stricte
- Validation des messages Socket.io

### Données
- Préparation des requêtes SQL (PDO)
- Validation des entrées
- Sanitisation JSON

## 🚀 Performance

### Optimisations
- Lazy loading des scripts vidéo
- Compression des candidats ICE
- Cache des streams
- Pooling des connexions BDD

### Recommandations
- Utiliser HTTPS/WSS en production
- Configurer un TURN server pour NAT traversal
- Monitorer la latence réseau
- Utiliser CDN pour les assets statiques

## 🐛 Dépannage

### Pas de vidéo/audio
1. Vérifier les permissions d'accès caméra/micro
2. Vérifier la connexion Socket.io
3. Regarder la console navigateur

### Connexion lente
1. Vérifier la qualité du réseau
2. Configurer un TURN server
3. Réduire la résolution vidéo

### Appel qui chute
1. Vérifier la stabilité du serveur Node.js
2. Vérifier les logs Socket.io
3. Vérifier la configuration CORS

## 📝 Logs

### Server Node.js
```
[CONNECT] Socket connecté
[REGISTER] User enregistré
[CALL_INIT] Appel initié
[NOTIFY] Appel notifié
[OFFER] Offer envoyée
```

### Console navigateur
```
[VideoCall] Logs avec prefix
[VideoCallUI] Logs interface
[VideoCallManager] Logs manager
```

## 🔄 Flux d'un appel

1. Utilisateur A lance un appel
2. Server Socket.io notifie l'initiateur
3. VideoManager crée les connexions P2P
4. Utilisateur B reçoit la notification
5. Utilisateur B accepte l'appel
6. Signalisation WebRTC (Offer/Answer/ICE)
7. Flux audio/vidéo établis
8. Appel actif avec contrôles
9. L'un des utilisateurs termine
10. Nettoyage des ressources

## 📞 Support

Pour les problèmes ou améliorations:
1. Vérifier les logs
2. Consulter la documentation
3. Vérifier les prérequis
4. Tester avec les outils de débogage

## 🎯 Évolutions futures

- [ ] Enregistrement des appels
- [ ] Partage d'écran
- [ ] Filtre audio (réduction du bruit)
- [ ] Effets vidéo
- [ ] Appels de groupe améliorés
- [ ] Sous-titrage en temps réel
- [ ] Intégration avec calendrier

---

**Version:** 1.0.0
**Date:** 2026-05-04
**Licence:** MIT
