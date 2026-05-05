# 📞 Appel Vidéo Swaply - Guide d'Utilisation Final

## ✅ CORRECTIONS APPORTÉES

### 1. **Erreur 409 Conflict - RÉSOLU**
- ✅ Amélioration de la détection des appels "zombies" (> 2 minutes inactifs)
- ✅ Ajout d'une option "force_new" pour forcer un nouvel appel
- ✅ Bouton "Réessayer (Force)" en cas d'erreur
- ✅ Nettoyage automatique via `cleanup_video_calls.php`

### 2. **Validation des Données - IMPLÉMENTÉ**
- ✅ Classe `FormValidator` (POO, serveur-side, pas HTML5)
- ✅ Validation appliquée à tous les endpoints
- ✅ Messages d'erreur clairs et détaillés
- ✅ Retour des erreurs en JSON

### 3. **Respect du MVC - APPLIQUÉ**
- ✅ Model: `VideoCall.php` + `Conversation.php`
- ✅ View: `VideoCallUI.html` + `Messages.php`
- ✅ Controller: `VideoCallController.php`
- ✅ Utilisation de PDO obligatoire

### 4. **Principes POO - RESPECTÉS**
- ✅ Classes vidéo: `VideoCallManager`, `VideoCallUI`, `VideoCall`, `FormValidator`
- ✅ Encapsulation des propriétés privées
- ✅ Méthodes publiques bien séparées
- ✅ Héritage et composition

---

## 🚀 DÉMARRAGE RAPIDE

### Étape 1: Vérifier le système
```
Aller à: http://localhost/swaply/test_video_call.html
```

### Étape 2: Nettoyer les appels bloqués
```
Aller à: http://localhost/swaply/cleanup_video_calls.php
```

### Étape 3: Démarrer le serveur Node.js
```bash
cd c:\xampp\htdocs\swaply\video_server
npm start
```

### Étape 4: Tester l'appel
1. **Onglet 1:** `http://localhost/swaply/view/Front/Messages.php` (Utilisateur 1)
2. **Onglet 2:** `http://localhost/swaply/view/Front/Messages.php` (Utilisateur 2, autre utilisateur)
3. **Onglet 1:** Cliquer "📞 Appel vidéo"
4. **Onglet 2:** Cliquer "Accepter" sur la notification

---

## 🔍 FICHIERS CLÉS MODIFIÉS

| Fichier | Modifications |
|---------|---------------|
| `VideoCallController.php` | Validation avec `FormValidator`, détection appels zombies |
| `VideoCallUI.js` | Bouton "Réessayer (Force)", meilleure gestion erreurs |
| `VideoCallManager.js` | Paramètre `forceNew`, meilleur logging |
| `FormValidator.php` | Nouvelle classe de validation (POO) |
| `cleanup_video_calls.php` | Nouveau script de nettoyage BD |
| `test_video_call.html` | Nouveau script de test |

---

## 📋 VALIDATION DES FORMULAIRES

### Exemple d'utilisation de FormValidator:
```php
<?php
require_once 'config/FormValidator.php';

$validator = new FormValidator($_POST);
$validator
    ->addRule('email', 'required', 'L\'email est obligatoire')
    ->addRule('email', 'email', 'L\'email invalide')
    ->addRule('password', 'required', 'Mot de passe obligatoire')
    ->addRule('password', 'min_length:8', 'Min 8 caractères')
    ->addRule('phone', 'phone', 'Téléphone invalide');

if ($validator->validate()) {
    // ✓ Données valides
    $email = $validator->get('email');
} else {
    // ❌ Erreurs
    echo json_encode([
        'success' => false,
        'errors' => $validator->getErrors()
    ]);
}
?>
```

### Règles disponibles:
- `required` - Champ obligatoire
- `email` - Email valide
- `min_length:N` - Longueur minimum
- `max_length:N` - Longueur maximum
- `numeric` - Nombre
- `integer` - Entier
- `positive` - Nombre positif
- `phone` - Téléphone
- `url` - URL valide
- `alphanumeric` - Alphanumérique
- `regex:pattern` - Expression régulière

---

## 🐛 TROUBLESHOOTING

### Erreur 409: Un appel est déjà en cours
**Solutions (dans l'ordre):**
1. Attendre 2 minutes
2. Cliquer "Réessayer (Force)"
3. Aller à `cleanup_video_calls.php`
4. Redémarrer le serveur Node.js

### L'autre utilisateur ne reçoit pas la notification
1. Vérifier qu'il est sur Messages.php
2. Vérifier que Socket.io est connecté (logs F12)
3. Redémarrer le serveur Node.js
4. Vérifier `http://localhost:3000/health`

### Erreur: "Données invalides"
1. Vérifier la console du navigateur (F12)
2. Vérifier les logs serveur PHP
3. Vérifier que les paramètres POST sont corrects
4. Consulter les erreurs de validation renvoyées

---

## 📊 STRUCTURE MVC

```
┌─ MODEL
│  ├─ VideoCall.php          (Gestion BD appels)
│  └─ Conversation.php       (Gestion conversations)
│
├─ VIEW
│  ├─ VideoCallUI.html       (Widget interface)
│  ├─ Messages.php           (Page principale)
│  └─ asset/js/
│     ├─ VideoCallUI.js      (Logique UI)
│     └─ VideoCallManager.js (Logique WebRTC)
│
└─ CONTROLLER
   └─ VideoCallController.php (API endpoints)

────────────────

VALIDATION
│
└─ config/FormValidator.php  (Classe validation POO)

DATABASE
│
├─ PDO (obligatoire)
├─ migrations/
│  └─ 003_create_video_calls.sql
```

---

## ✨ FONCTIONNALITÉS

### Côté Utilisateur Initiateur:
- ✅ Initier un appel
- ✅ Voir le statut en temps réel
- ✅ Activé/désactivé caméra/micro
- ✅ Timeout 30s si pas de réponse
- ✅ Bouton "Réessayer" en cas d'erreur 409

### Côté Utilisateur Destinataire:
- ✅ Recevoir notification d'appel entrant
- ✅ Voir le nom de l'appelant
- ✅ Accepter/rejeter l'appel
- ✅ Contrôles caméra/micro
- ✅ Terminer l'appel à tout moment

### Côté Serveur:
- ✅ Validation PDO stricte
- ✅ Validation des données (FormValidator)
- ✅ Gestion automatique des appels zombies
- ✅ Logs détaillés
- ✅ Nettoyage périodique (30s)

---

## 📞 FLUX COMPLET D'UN APPEL

```
User1 clique "Appel"
│
├─ Envoie HTTP POST + Socket.io
│  └─ Demande d'appel créée en BD
│
├─ User2 reçoit notification Socket.io
│  └─ Widget d'appel entrant s'affiche
│
├─ User2 clique "Accepter"
│  ├─ HTTP POST accept
│  └─ Socket.io accept_call
│
├─ WebRTC offer/answer échangés
│  └─ Videos connectées!
│
└─ Un utilisateur clique "Raccrocher"
   ├─ HTTP POST end
   ├─ Socket.io leave_call
   └─ Appel terminé en BD
```

---

## 🎯 PROCHAINES ÉTAPES

Pour une production complète:
1. ✅ Validation côté serveur (FAIT)
2. ✅ Gestion appels zombies (FAIT)
3. ✅ Respect MVC (FAIT)
4. ✅ Utilisation PDO (FAIT)
5. ✅ POO (FAIT)
6. À faire: Enregistrement appels en BD
7. À faire: Historique appels pour stats
8. À faire: Tests unitaires

---

**Status:** ✅ Opérationnel - Prêt pour validation

Rapport généré: 5 mai 2026
