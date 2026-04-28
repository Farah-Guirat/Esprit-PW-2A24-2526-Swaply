# 🔐 Guide - Accéder à 2 Utilisateurs Simultanément

## ✅ Solution Implémentée

Un nouveau système de **sélection d'utilisateur** a été créé pour tester les conversations entre deux utilisateurs.

---

## 📖 Comment Utiliser

### Étape 1: Accédez à la page de sélection
```
http://localhost/swaply/view/Front/select_user.php
```

### Étape 2: Sélectionnez votre premier utilisateur
- Vous verrez une liste de tous les utilisateurs disponibles
- Sélectionnez **Farah** ou **Aziz** (ou tout autre utilisateur)
- Cliquez sur **"Se connecter"**

### Étape 3: Ouvrir une 2ème session simultanément
**Très important ⭐**: Pour voir les deux utilisateurs en même temps:

#### Option A: Deux onglets navigateur (recommandé)
1. **Onglet 1** - Connecté comme **Farah** → allez à `messagerie.php`
2. Ouvrez un **nouvel onglet** (Ctrl+T ou Cmd+T)
3. Dans le nouvel onglet, allez à `select_user.php`
4. **Onglet 2** - Connectez-vous comme **Aziz**
5. Positionnez les deux onglets côte à côte pour voir les conversations en temps réel

#### Option B: Deux navigateurs différents
1. Ouvrez **Firefox** ou **Chrome**
2. Allez à `select_user.php` dans Firefox → Connectez-vous comme **Farah**
3. Ouvrez **un autre navigateur** (Edge, Safari, etc.)
4. Allez à `select_user.php` → Connectez-vous comme **Aziz**
5. Arrangez les deux fenêtres côte à côte

#### Option C: Mode incognito (même navigateur)
1. Fenêtre normalement → Connectez-vous comme **Farah**
2. Fenêtre incognito (Ctrl+Shift+N) → Connectez-vous comme **Aziz**
3. Positionnez côte à côte

---

## 🧪 Tester les Conversations

Une fois les deux sessions ouvertes:

### 👤 Dans le navigateur Farah:
1. Allez dans `messagerie.php`
2. Sélectionnez la conversation avec **Aziz**
3. Tapez un message: *"Coucou Aziz!"*

### 👤 Dans le navigateur Aziz:
1. Allez dans `messagerie.php`
2. Sélectionnez la conversation avec **Farah**
3. **Vous verrez le message en temps réel** 🎉
4. Répondez: *"Coucou Farah!"*

### ✨ Fonctionnalités à tester:
- ✅ Messages en temps réel
- ✅ Indicateur "En train d'écrire" 
- ✅ Statut en ligne/hors ligne
- ✅ Upload de fichiers/documents
- ✅ Tri des conversations (récent, ancien, A→Z)
- ✅ Affichage du timestamp

---

## 🔄 Changer d'Utilisateur

**Pour passer à un autre utilisateur dans la même session:**

1. Allez à: `select_user.php`
2. Sélectionnez un utilisateur différent
3. Cliquez "Se connecter"
4. Vous serez automatiquement redirigé vers `messagerie.php` avec le nouvel utilisateur

---

## 📂 Fichiers Modifiés

| Fichier | Modification |
|---------|--------------|
| `select_user.php` | ✅ **CRÉÉ** - Page de sélection d'utilisateur |
| `messagerie.php` | ✅ **MODIFIÉ** - Suppression de la session forcée, redirection vers `select_user.php` |

---

## 💡 Astuce Pro

Si vous utilisez **VS Code Live Server** ou **PHPStorm**, vous pouvez diviser l'écran:
- **Panneau gauche** → Onglet 1 avec Farah
- **Panneau droit** → Onglet 2 avec Aziz

Utiliser: `Cmd+K Cmd+\` (macOS) ou `Ctrl+K Ctrl+\` (Windows/Linux) pour créer une vue divisée.

---

## ⚠️ Important

- ❌ **NE PARTAGEZ PAS** `select_user.php` en production (risque de sécurité)
- ✅ En production, implémentez un **vrai système de login** avec session tokens
- 🔒 Pour production: Utilisez une authentification basée sur un mot de passe avec JWT ou sessions sécurisées

---

## 🐛 Dépannage

### Problème: "File not found" pour `select_user.php`
**Solution**: Assurez-vous que le fichier existe: `view/Front/select_user.php`

### Problème: Les messages ne s'actualisent pas entre les deux utilisateurs
**Solution**: 
- Vérifiez que vous êtes sur les **deux onglets différents** ou navigateurs différents
- Rafraîchissez la page (F5) si nécessaire
- Vérifiez que les deux utilisateurs se connaissent (ont une conversation créée)

### Problème: "Utilisateur actuel" n'apparaît pas
**Solution**: Vérifiez que le fichier `config/database.php` est correctement configuré

---

## 📞 Support

Si vous avez des questions sur le système de messagerie, consultez:
- [MESSAGERIE_README.md](../../../MESSAGERIE_README.md) - Documentation complète
- [controller/MessageController.php](../../controller/MessageController.php) - Code métier
