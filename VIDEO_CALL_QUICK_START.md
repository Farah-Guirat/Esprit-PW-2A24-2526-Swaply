# 📞 Guide Complet Appel Vidéo Swaply

## ⚡ Démarrage Rapide

### Étape 1: Vérifier le diagnostic
```
1. Aller à: http://localhost/swaply/diagnostic_video_call.php
2. Vérifier que tous les ✓ sont au vert
3. Vérifier que le serveur Socket.io est actif
```

### Étape 2: Démarrer le serveur Socket.io
```bash
cd c:\xampp\htdocs\swaply\video_server
npm start
```

Vous devez voir:
```
🎥 Swaply WebRTC sur port 3000
```

### Étape 3: Tester l'appel vidéo
1. Ouvrir deux onglets navigateur
2. **Onglet 1:** Connexion utilisateur 1
3. **Onglet 2:** Connexion utilisateur 2
4. **Onglet 1:** Aller à Messages, sélectionner une conversation avec utilisateur 2
5. **Onglet 1:** Cliquer sur "📞 Appel vidéo"
6. **Onglet 2:** Vous devriez recevoir une notification "Appel entrant"
7. **Onglet 2:** Cliquer "Accepter"
8. 🎉 **Appel vidéo connecté!**

---

## 🔧 Si ça ne fonctionne pas

### Erreur: "HTTP 409: Conflict"
**Solution:** Un appel précédent n'a pas été terminé correctement. 
```bash
# Nettoyer la base de données
# Via phpMyAdmin:
UPDATE video_calls SET statut = 'termine', date_fin = NOW() 
WHERE statut IN ('en_attente', 'en_cours');
```

### Erreur: "L'utilisateur 2 ne reçoit pas la notification"
**Causes possibles:**

1. **Serveur Node.js n'est pas démarré**
   - Vérifier: `http://localhost:3000/health`
   - Si erreur: lancer `npm start` dans `video_server/`

2. **Utilisateur 2 n'est pas sur la page Messages**
   - DOIT être à: `http://localhost/swaply/view/Front/Messages.php?id=...`
   - Pas sur une autre page!

3. **Vérifier les logs du navigateur**
   - Ouvrir F12 > Console
   - Chercher `[VideoCall]` logs
   - Vérifier que vous voyez: `✓ Connecté au serveur de signalisation`

### Vérifier l'enregistrement de l'utilisateur
```javascript
// Dans la console du navigateur (F12 > Console)
console.log(window.currentUserId);        // Doit afficher le numéro
console.log(window.videoCallUI);          // Doit afficher l'objet
window.videoCallUI.videoManager.socket    // Doit avoir .connected = true
```

---

## 📊 Outils de Debug

### Page de Diagnostic
```
http://localhost/swaply/diagnostic_video_call.php
```
- Vérifie les tables BD
- Vérifie le serveur Node.js
- Vérifie les fichiers
- Affiche les stats des appels

### Page de Debug Avancé
```
http://localhost/swaply/debug_video_call.html
```
- Console en temps réel
- État de la connexion Socket.io
- Boutons de vérification rapide
- Logs détaillés

### Vérifier le serveur Node.js
```bash
# Terminal
curl http://localhost:3000/health

# Devrait afficher:
# {"status":"OK","users":{"1":"abc123"},"calls":0}
```

---

## 🏗️ Architecture du système

```
┌─────────────────────────────────────────────┐
│          Browser User 1 (PHP)               │
│  ┌────────────────────────────────────────┐ │
│  │ Messages.php                           │ │
│  │  ├─ VideoCallUI.js (init auto)         │ │
│  │  ├─ VideoCallManager.js (WebSocket)    │ │
│  │  └─ Socket.io client (io.js)           │ │
│  └────────────────────────────────────────┘ │
└────────────┬────────────────────────────────┘
             │
      Socket.io (WebSocket)
             │
      ┌──────┴──────┐
      │             │
┌─────▼──────┐  ┌──▼──────────┐
│ Node.js    │  │ PHP Backend  │
│ server.js  │  │ (MySQL)      │
│ Port 3000  │  │              │
└─────┬──────┘  └──────────────┘
      │
      │
┌─────▼──────┐
│Browser User 2
│ (notification
│  + WebRTC)
└────────────┘
```

---

## 📝 Fichiers Clés

| Fichier | Rôle |
|---------|------|
| `video_server/server.js` | Serveur Node.js - Gère Socket.io |
| `asset/js/VideoCallManager.js` | Client WebRTC - Gère P2P |
| `asset/js/VideoCallUI.js` | Interface - UI et notifications |
| `controller/VideoCallController.php` | API PHP - CRUD appels |
| `model/VideoCall.php` | Modèle BD - Requêtes SQL |
| `view/Front/VideoCallUI.html` | HTML du widget appel |
| `migrations/003_create_video_calls.sql` | Schéma BD |

---

## 🚀 Checklist avant de tester

- [ ] **Node.js démarré**: `npm start` dans `video_server/`
- [ ] **Apache démarré** (XAMPP)
- [ ] **MySQL démarré** (XAMPP)
- [ ] **Tables BD créées**: Aller à `diagnostic_video_call.php` puis "Créer les tables"
- [ ] **Utilisateur 1 connecté** sur Messages.php
- [ ] **Utilisateur 2 connecté** sur Messages.php (même conversation)
- [ ] **Browser DevTools ouverts** (F12) pour vérifier les logs
- [ ] **Deux onglets incognito** (pour pas partager le cache)

---

## 📞 Processus Appel Complet

### Timeline Utilisateur 1 (Initiateur)
```
0s  Clique sur "📞 Appel vidéo"
    └─> Affiche "Demande d'appel envoyée..."
    └─> Envoie HTTP POST /controller/VideoCallController.php?action=initiate
    └─> Reçoit id_video_call (ex: 42)
    └─> Affiche le widget vidéo
    └─> Demande accès caméra/micro
    └─> Connecté à Socket.io, envoie 'initiate_call'

30s ├─ Timeout si pas de réponse
    └─> Raccroche automatiquement

Xs  Reçoit Socket.io 'call_accepted'
    ├─> Crée WebRTC offer
    ├─> Envoie 'webrtc_offer' vers Utilisateur 2
    ├─> Échange ICE candidates
    └─> Vidéo connectée ✓
```

### Timeline Utilisateur 2 (Destinataire)
```
0s  Rien ne se passe - Utilisateur 1 envoie l'appel

Xs  Reçoit Socket.io 'incoming_call'
    ├─> Widget d'appel entrant s'affiche
    ├─> Son de sonnerie (Web Audio API)
    └─> Affiche nom de l'appelant

Ys  Clique "Accepter"
    ├─> Envoie HTTP POST /controller/VideoCallController.php?action=accept
    ├─> Envoie Socket.io 'accept_call'
    ├─> Demande accès caméra/micro
    └─> Reçoit WebRTC offer

Ys+1s Envoie WebRTC answer
    ├─> Échange ICE candidates
    └─> Vidéo connectée ✓
```

---

## 🐛 Logs à Chercher

### Bon fonctionnement
```
[VideoCall] ✓ Connecté au serveur de signalisation
[VideoCall] Enregistrement de l'utilisateur 123
[VideoCall_INIT] Appel 42 par user 1 vers user 2
[NOTIFY] OK - appel notifié à 2
[ACCEPT] User 2 a accepté
[VideoCall] ✓ Offer envoyée
[VideoCall] ✓ Answer traitée
```

### Problèmes
```
[VideoCall] Erreur de connexion socket: Cannot get io        // Socket.io pas chargé
[VideoCall] Erreur initiateCall: HTTP 409: Conflict           // Appel zombie
[VideoCall] Erreur initiateCall: HTTP 403: Forbidden          // Pas accès conversation
[VideoCall] Pas de réponse après 30 secondes                  // Utilisateur ne répond pas
```

---

## 🆘 Support Rapide

| Symptôme | Diagnostic | Solution |
|----------|-----------|----------|
| Aucune notification | Utilisateur 2 pas enregistré | Vérifier qu'il est sur Messages.php |
| HTTP 409 Conflict | Appel zombie en BD | Nettoyer: `UPDATE video_calls SET statut='termine'...` |
| Vidéo noire | Permissions microphone | Accepter permissions, relancer |
| Pas de son | Audio désactivé | Cliquer sur 🔊 microphone |
| Déconnexion rapide | Appel n'a pas démarré | Vérifier logs, redémarrer |

---

**Questions? Consultez:**
- `VIDEO_CALL_TROUBLESHOOT.md` - Troubleshooting détaillé
- `debug_video_call.html` - Outils debug
- `diagnostic_video_call.php` - Diagnostic système
