# Guide de Démarrage Rapide - Système d'Appel Vidéo Swaply

## 🚀 Démarrage en 5 minutes

### Étape 1: Installation automatique

**Windows:**
```batch
cd c:\xampp\htdocs\swaply
install_video.bat
```

**macOS/Linux:**
```bash
cd /path/to/swaply
chmod +x install_video.sh
./install_video.sh
```

### Étape 2: Lancer le serveur de signalisation

Dans un terminal séparé:

```bash
cd c:\xampp\htdocs\swaply\video_server
npm start
```

Vous devriez voir:
```
🎥 Serveur WebRTC Swaply en écoute sur le port 3000
📡 Socket.io prêt pour la signalisation
```

### Étape 3: Intégrer dans votre messagerie

Ouvrez `view/Front/Messages.php` et ajoutez:

```html
<!-- Avant la fermeture </head> -->
<link rel="stylesheet" href="/asset/css/videocall.css">

<!-- Avant la fermeture </body> -->
<script src="http://localhost:3000/socket.io/socket.io.js"></script>
<script src="/asset/js/VideoCallManager.js"></script>
<script src="/asset/js/VideoCallUI.js"></script>

<script>
    window.currentUserId = <?php echo (int)($_SESSION['id_user'] ?? 0); ?>;
</script>
```

Ajoutez aussi un bouton pour lancer l'appel:

```html
<button onclick="window.videoCallUI?.initiateCall(<?php echo $id_conversation; ?>)" class="btn-video">
    📞 Appel vidéo
</button>
```

### Étape 4: Tester

1. Ouvrez la messagerie dans deux onglets/navigateurs différents
2. Connectez-vous avec deux utilisateurs différents
3. Ouvrez une conversation entre les deux
4. Cliquez sur "Appel vidéo"
5. L'autre utilisateur reçoit la notification
6. Cliquez "Accepter" et l'appel démarre!

## 📞 Scénarios de test

### Test 1: Appel simple 1-to-1
- 2 utilisateurs
- 1 appel bidirectionnel
- Vérifier audio/vidéo

### Test 2: Rejet d'appel
- User A appelle User B
- User B rejette
- Vérifier que l'interface se ferme

### Test 3: Contrôles audio/vidéo
- Actif un appel
- Cliquer sur le bouton microphone (mute/unmute)
- Cliquer sur le bouton caméra (on/off)
- Vérifier les changements

### Test 4: Terminer l'appel
- Actif un appel
- Cliquer sur le bouton rouge "Terminer"
- Vérifier que l'interface se ferme

## 🔍 Dépannage rapide

### Le serveur Node.js ne démarre pas
```
Erreur: npm install manque les dépendances
Solution: cd video_server && npm install
```

### Pas de vidéo/audio
```
Erreur: Permission d'accès caméra/microphone
Solution: 
1. Autoriser l'accès dans les paramètres du navigateur
2. Redémarrer le navigateur
3. Vérifier que les appareils fonctionnent
```

### Erreur "Socket not connected"
```
Erreur: Le serveur Node.js n'est pas accessible
Solution:
1. Vérifier que npm start s'exécute
2. Vérifier que le port 3000 est libre
3. Vérifier les logs du serveur
```

### Pas de notification d'appel entrant
```
Erreur: Les utilisateurs ne sont pas enregistrés
Solution:
1. Vérifier la console du navigateur
2. Vérifier que window.currentUserId est défini
3. Vérifier les logs du serveur Socket.io
```

## 📊 Vérifier l'installation

### Test de santé du serveur

```bash
curl http://localhost:3000/health

# Réponse attendue:
# {"status":"OK","timestamp":"2026-05-04T..."}
```

### Vérifier les appels actifs

```bash
curl http://localhost:3000/api/calls

# Réponse: Liste des appels en cours
```

## 🛠️ Configuration

### Changer le port du serveur

Modifier `.env` dans `video_server/`:

```env
PORT=4000
```

Puis relancer le serveur.

### Autoriser plus d'origines CORS

Modifier `.env`:

```env
ALLOWED_ORIGINS=http://localhost:8080,http://localhost:3000,http://192.168.1.100
```

### Configurer un TURN server

Pour fonctionner derrière un NAT/firewall:

```env
TURN_SERVER=turn:turnserver.com:3478
TURN_USERNAME=user
TURN_PASSWORD=pass
```

## 📝 Commandes utiles

### Vérifier les dépendances
```bash
node --version
npm --version
php --version
```

### Tester la connexion MySQL
```php
php -r "
require_once 'config/database.php';
\$db = Database::getInstance();
echo 'Connexion BD OK';
"
```

### Nettoyer les fichiers temporaires
```bash
rm -rf video_server/node_modules
npm install
```

### Vérifier les ports utilisés
```bash
# Linux/macOS
lsof -i :3000

# Windows
netstat -ano | findstr :3000
```

## 📞 Support

### Logs du navigateur (F12)
```
Vérifier la console pour les messages [VideoCall]
```

### Logs du serveur
```
Exécuté dans le terminal npm start
Voir [CONNECT], [REGISTER], [CALL_INIT]
```

### Logs PHP
```
Vérifier les erreurs dans logs/php_errors.log
```

## 🎯 Checklist avant production

- [ ] Base de données migrée
- [ ] Serveur Node.js fonctionne
- [ ] HTTPS/WSS configurés
- [ ] TURN server configuré
- [ ] Permissions caméra/microphone accordées
- [ ] Tests d'appels réussis
- [ ] Gestion d'erreurs testée
- [ ] Performances acceptables

## 📚 Documentation complète

Consultez `VIDEO_CALL_README.md` pour:
- Architecture détaillée
- API endpoints
- Événements Socket.io
- Structure BDD
- Sécurité
- Performance

---

**Besoin d'aide?** Vérifiez d'abord les logs, puis consultez la documentation complète.
