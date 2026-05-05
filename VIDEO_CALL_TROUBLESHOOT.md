# 🔧 Guide de Dépannage Appel Vidéo

## Problème 1: "Erreur HTTP 409: Conflict"

**Cause:** Un appel précédent est encore marqué comme "en attente" ou "en cours" dans la base de données et n'a pas été terminé correctement.

**Solutions:**

### Option 1: Nettoyer la base de données (rapide)
```bash
# Via phpMyAdmin ou MySQL CLI
UPDATE video_calls SET statut = 'termine', date_fin = NOW() 
WHERE statut IN ('en_attente', 'en_cours') AND date_debut < DATE_SUB(NOW(), INTERVAL 1 HOUR);
```

### Option 2: Redémarrer complètement
1. Fermer tous les navigateurs
2. Redémarrer Apache dans XAMPP
3. Redémarrer le serveur Node.js: `npm start` dans `video_server/`
4. Attendre 5 secondes
5. Ouvrir deux onglets navigateur avec deux utilisateurs différents

### Option 3: Automatic cleanup (déjà implémenté)
Le serveur PHP nettoie maintenant automatiquement les appels de plus de 5 minutes non terminés.

---

## Problème 2: "L'utilisateur 2 ne reçoit pas la notification d'appel entrant"

**Cause:** L'utilisateur 2 n'est pas enregistré auprès du serveur Socket.io.

### Diagnostic:
1. Aller à: `http://localhost/swaply/debug_video_call.html`
2. Vérifier la section "📱 Votre Session"
3. Vérifier que:
   - ✓ ID Utilisateur s'affiche
   - ✓ VideoCallUI Chargé = "Oui"
   - ✓ Socket.io Chargé = "Oui"
   - ✓ Socket Connecté = "Connecté"

### Solutions:

**1. Vérifier que le serveur Node.js est démarré**
```bash
# Vérifier dans un terminal
curl http://localhost:3000/health

# Vous devriez voir: {"status":"OK","users":{...},"calls":0}
```

**2. Vérifier que tous les utilisateurs sont sur la page Messages.php**
- L'utilisateur 2 DOIT être sur: `http://localhost/swaply/view/Front/Messages.php?id=CONVERSATION_ID`
- Pas ailleurs! VideoCallUI ne s'initialise que si on est sur Messages.php

**3. Vérifier les logs du navigateur**
- Ouvrir DevTools (F12)
- Aller à "Console"
- Vérifier les logs `[VideoCall]`:
  - ✓ `[VideoCall] Connecté au serveur de signalisation`
  - ✓ `[REGISTER] User XXX -> socket YYY`
  - ✓ `[NOTIFY] OK - appel notifié à ZZZ`

**4. Si socket ne se connecte pas**
- Vérifier que le serveur Node.js tourne: `npm start` dans `video_server/`
- Vérifier l'URL: doit être `http://localhost:3000` (pas `https://`)
- Vérifier les logs du serveur Node.js pour les erreurs CORS

---

## Problème 3: "Erreur lors de l'accès à la caméra/microphone"

**Solutions:**
1. Accepter l'accès aux permissions
2. Si Firefox/Chrome: aller aux paramètres et autoriser l'accès camera/micro pour localhost
3. Utiliser HTTPS pour la prod (pas HTTP localhost)
4. Vérifier que HTTPS est activé si c'est en ligne

---

## Checklist de Démarrage Correct ✓

Avant d'appeler:
- [ ] Serveur Node.js est démarré: `npm start` depuis `c:\xampp\htdocs\swaply\video_server\`
- [ ] Apache est démarré (XAMPP)
- [ ] MySQL est démarré (XAMPP)
- [ ] Les tables vidéo existent: Aller à `http://localhost/swaply/install_video_tables.php`
- [ ] Les deux utilisateurs sont sur: `http://localhost/swaply/view/Front/Messages.php?id=CONV_ID`
- [ ] Les deux utilisateurs ont `window.currentUserId` défini (Console > taper `window.currentUserId`)

---

## Tester Manuellement

1. Ouvrir la page de debug: `http://localhost/swaply/debug_video_call.html`
2. Cliquer "Vérifier Serveur Socket.io"
3. Cliquer "Vérifier Utilisateur"
4. Vérifier que tout est ✓

Si le serveur ne répond pas:
```bash
cd c:\xampp\htdocs\swaply\video_server
npm start
```

---

## Logs Importants

### Dans le navigateur (DevTools > Console):
```
[VideoCall] ✓ Connecté au serveur de signalisation
[VideoCall] Enregistrement de l'utilisateur 123
[VideoCallUI] Appel en attente...
[VideoCallUI] Appel accepté
```

### Dans le serveur Node.js:
```
[CONNECT] Socket abc123
[REGISTER] User 123 -> socket abc123
[CALL_INIT] Appel 456 par user 1 vers user 2
[NOTIFY] OK - appel notifié à 2
```

---

## Derniers recours

```bash
# 1. Redémarrer tout
# Terminal 1:
cd c:\xampp\htdocs\swaply\video_server
npm start

# 2. Redémarrer XAMPP
# Cliquer "Stop All" puis "Start All"

# 3. Vider la cache navigateur
# F12 > Application > Cache > Vider tout

# 4. Ouvrir deux onglets incognito

# 5. Aller à: http://localhost/swaply/install_video_tables.php
# pour vérifier les tables
```

---

**📞 Besoin d'aide?** Vérifier les fichiers:
- `c:\xampp\htdocs\swaply\video_server\server.js` - Serveur WebSocket
- `c:\xampp\htdocs\swaply\asset\js\VideoCallManager.js` - Client WebSocket
- `c:\xampp\htdocs\swaply\asset\js\VideoCallUI.js` - Interface utilisateur
- `c:\xampp\htdocs\swaply\controller\VideoCallController.php` - API PHP
