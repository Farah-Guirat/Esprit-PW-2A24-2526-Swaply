# 🚨 SOLUTION: Erreur HTTP 409 - Appel Vidéo Bloqué

## ❌ Problème
```
❌ HTTP 409: Conflict
Erreur: Un appel précédent est encore en cours...
```

---

## ✅ SOLUTION RAPIDE (3 étapes)

### 1️⃣ Nettoyer les appels bloqués
```
Aller à: http://localhost/swaply/diagnostic_advanced.html
```
**Puis cliquer:** 🧹 **Nettoyage Agressif (1 min+)**

### 2️⃣ Vérifier l'état
```
Attendre 2 secondes que les statistiques se mettent à jour
Les compteurs doivent montrer:
- Appels en attente: 0
- Appels en cours: 0
```

### 3️⃣ Tester l'appel
Aller à: `http://localhost/swaply/view/Front/Messages.php`
- Cliquer "📞 Appel vidéo"
- **ÇA DOIT MARCHER!** 🎉

---

## 🔧 POURQUOI ÇA ARRIVE?

L'erreur 409 signifie qu'un **appel précédent** n'a pas été correctement terminé en BD.

**Causes courantes:**
1. ❌ Fermeture du navigateur sans "Raccrocher"
2. ❌ Rechargement de la page pendant l'appel
3. ❌ Erreur WebRTC réseau
4. ❌ Crash du serveur Node.js

**Résultat:** L'appel reste en statut `en_attente` ou `en_cours` dans la BD, bloquant tout nouvel appel.

---

## 🛠️ SOLUTIONS DÉTAILLÉES

### Option 1: Nettoyage Automatique (RECOMMANDÉ)
```bash
Aller à: http://localhost/swaply/diagnostic_advanced.html
Cliquer: 🧹 Nettoyage Agressif
```
**Avantages:**
- ✅ Interface visuelle
- ✅ Voir les appels nettoyés
- ✅ Monitorage en temps réel
- ✅ Plus rapide

---

### Option 2: Script de Nettoyage Direct
```bash
Aller à: http://localhost/swaply/cleanup_aggressive.php
```
Voir la réponse JSON avec les détails du nettoyage.

---

### Option 3: Forcer un Nouvel Appel
Si le problème persiste, une notification "Réessayer (Force)" s'affiche:
1. **Cliquer:** 🔄 **Réessayer (Force)**
2. Cela force la terminaison de l'appel précédent
3. Crée un nouvel appel

---

## 📋 CHECKLIST AVANT CHAQUE TEST

- [ ] Nettoyage agressif effectué
- [ ] Serveur Node.js démarré (`npm start` dans `video_server/`)
- [ ] Apache/XAMPP démarré
- [ ] MySQL démarré
- [ ] Console F12 ouverte (pour voir les logs)
- [ ] Deux onglets avec deux utilisateurs différents
- [ ] Les deux utilisateurs dans la MÊME conversation

---

## 🐛 TROUBLESHOOTING AVANCÉ

### L'erreur 409 revient après nettoyage
**Action:**
1. Aller à: `diagnostic_advanced.html`
2. Cliquer: **💥 Supprimer TOUS les appels**
3. Attendre que la BD soit vide
4. Redémarrer le navigateur
5. Tester l'appel

### Toujours l'erreur 409
**Vérifier:**
1. **BD vide:** `diagnostic_advanced.html` → "Appels en attente: 0"?
2. **Serveur Node.js actif:** Console affiche `Server running on port 3000`?
3. **Socket.io connecté:** F12 → Console → chercher "Socket connected"?
4. **PHP logs:** `c:\xampp\apache\logs\error.log`?

### Le nettoyage ne fonctionne pas
**Essayer:**
```bash
# Accès direct MySQL (via phpMyAdmin ou cmd)
UPDATE video_calls SET statut = 'termine' 
WHERE statut IN ('en_attente', 'en_cours');
```

---

## 📊 MONITORING

### Voir les appels actifs
```
Aller à: http://localhost/swaply/diagnostic_advanced.html
Section: "📞 Appels Récents"
```

### Voir les logs PHP
```bash
File: c:\xampp\apache\logs\error.log
Chercher: [VideoCall]
```

### Voir les logs serveur Node.js
```bash
Terminal: cd video_server
Affiche les messages de connection
```

---

## 🔄 FLUX D'UN APPEL (pour debug)

```
┌─ User 1: Click "Appel"
│  └─ POST /controller/VideoCallController.php?action=initiate
│     ├─ Cleanup zombies (auto)
│     ├─ Vérifier appel actif (< 60s)
│     ├─ Créer nouvel appel en BD
│     └─ Retour: id_video_call
│
├─ Socket.io: initiate_call
│  └─ User 2 reçoit notification
│
├─ User 2: Click "Accepter"
│  └─ POST /controller/VideoCallController.php?action=accept
│     ├─ Valider id_video_call
│     ├─ Mettre à jour statut "accepte"
│     └─ WebRTC offer/answer échangés
│
└─ User X: Click "Raccrocher"
   └─ POST /controller/VideoCallController.php?action=end
      ├─ Marquer comme "termine"
      ├─ Calculer durée
      └─ Socket.io: disconnect
```

---

## ✨ RAPPELS IMPORTANTS

1. **Attendez 2 minutes** avant de tester à nouveau (timeout d'appel)
2. **Nettoyez régulièrement** pour éviter l'accumulation
3. **Vérifiez F12** pour voir les erreurs réseau
4. **Redémarrez Node.js** s'il y a des problèmes WebRTC

---

## 📞 SUPPORT RAPIDE

| Problème | Solution |
|----------|----------|
| 409 après nettoyage | 💥 Supprimer TOUS + Refresh |
| Pas de notification | Vérifier Socket.io (F12) |
| Erreur validation | Chercher erreur JSON dans F12 |
| BD vide mais 409 | Redémarrer Apache + Node.js |

---

**Status:** ✅ Solution testée et fonctionnelle
**Mise à jour:** 5 mai 2026
