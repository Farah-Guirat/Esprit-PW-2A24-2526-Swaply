# 🔐 Guide - Système de Login Email/Mot de Passe

## ✅ Mise en Place (IMPORTANT - À faire une seule fois)

### Étape 1: Exécuter la migration
Avant de pouvoir utiliser le login, vous devez ajouter les colonnes `email` et `password` à la table `utilisateurs`.

**Via navigateur:**
1. Allez à: `http://localhost/swaply/config/migrate_login.php`
2. Vous verrez les confirmations ✅:
   ```
   ✅ Colonne 'email' ajoutée avec succès
   ✅ Colonne 'password' ajoutée avec succès
   ✅ Utilisateurs de test insérés
   ```

**Ou via terminal (PHP):**
```bash
cd c:\xampp\htdocs\swaply
php config/migrate_login.php
```

### Identifiants de Test Créés Automatiquement:

```
👤 Utilisateur 1 (Farah):
   Email: farah@example.com
   Mot de passe: password123

👤 Utilisateur 2 (Aziz):
   Email: aziz@example.com
   Mot de passe: password123
```

---

## 🎯 Comment Utiliser le Login

### Accéder au Login:
```
http://localhost/swaply/view/Front/login.php
```

### Étape 1: Saisir les identifiants
- **Email**: `farah@example.com` (ou `aziz@example.com`)
- **Mot de passe**: `password123`
- Cliquez "Se Connecter"

### Étape 2: Vous êtes connecté!
- Vous serez redirigé vers `messagerie.php`
- Vous verrez vos conversations
- Un **menu utilisateur** apparaît en haut à droite (votre initial)

---

## 📖 Tester Avec 2 Utilisateurs Simultanément

### ✨ Méthode 1: Deux Onglets (Recommandé)

1. **Onglet 1 - Ouvrir le login normal:**
   - URL: `http://localhost/swaply/view/Front/login.php`
   - Sélectionnez **Farah**
   - Connectez-vous

2. **Onglet 2 - Ouvrir en mode incognito:**
   - `Ctrl+Shift+N` (Windows/Linux) ou `Cmd+Shift+N` (Mac)
   - URL: `http://localhost/swaply/view/Front/login.php`
   - Sélectionnez **Aziz**
   - Connectez-vous

3. **Résultat:**
   - Positionnez les deux onglets côte à côte
   - Chaque onglet a sa **propre session**
   - Les messages s'actualiseront **en temps réel** 🎉

### ✨ Méthode 2: Deux Navigateurs Différents

1. **Navigateur 1 (Firefox):**
   - Allez à `login.php`
   - Connectez-vous comme **Farah**

2. **Navigateur 2 (Chrome/Edge):**
   - Allez à `login.php`
   - Connectez-vous comme **Aziz**

3. **Résultat:**
   - Arrangez les deux navigateurs côte à côte
   - Chaque navigateur maintient une **session indépendante**

---

## 🧪 Tester les Conversations

### Depuis Farah (Onglet 1):
1. Allez dans `messagerie.php`
2. Sélectionnez la conversation avec **Aziz**
3. Écrivez: *"Coucou Aziz!"*
4. **Envoyez le message**

### Depuis Aziz (Onglet 2):
1. Allez dans `messagerie.php`
2. Sélectionnez la conversation avec **Farah**
3. **Vous verrez le message en temps réel** ✨
4. Répondez: *"Coucou Farah!"*

### ✅ Fonctionnalités à Tester:
- ✅ Messages en temps réel
- ✅ Indicateur "En train d'écrire" 
- ✅ Statut en ligne/hors ligne
- ✅ Upload de fichiers
- ✅ Tri des conversations
- ✅ Affichage du timestamp

---

## 👤 Menu Utilisateur

### Où le Trouver?
En haut à droite de la page `messagerie.php`, il y a un **cercle** avec votre initial (ex: **F** pour Farah).

### Que Faire Avec?
Cliquez sur le cercle pour voir:
```
┌─────────────────────┐
│ Farah Ksouri        │
│ farah@example.com   │
├─────────────────────┤
│ 🚪 Déconnexion      │
└─────────────────────┘
```

### Déconnexion:
- Cliquez sur **"Déconnexion"**
- Vous serez redirigé vers `login.php`
- Votre session sera **détruite**

---

## 🔒 Sécurité

### Comment les Mots de Passe Sont Stockés?
- ✅ **Hachés avec bcrypt** (l'algorithme le plus sûr)
- ❌ Les mots de passe en clair **ne sont jamais stockés**
- ❌ Les mots de passe **ne sont jamais affichés**

### Authentification par Session:
- ✅ Chaque connexion crée une **session PHP sécurisée**
- ✅ Les sessions sont **indépendantes** par onglet/navigateur
- ✅ La session **expire** à la fermeture du navigateur

---

## 📂 Fichiers Créés/Modifiés

| Fichier | Statut | Fonction |
|---------|--------|----------|
| `login.php` | ✅ CRÉÉ | Page de login avec formulaire email/password |
| `logout.php` | ✅ CRÉÉ | Déconnexion et destruction de session |
| `config/migrate_login.php` | ✅ CRÉÉ | Migration pour ajouter les colonnes email/password |
| `messagerie.php` | ✏️ MODIFIÉ | Redirection vers login.php si non connecté |
| `view/Front/Messages.php` | ✏️ MODIFIÉ | Menu utilisateur avec déconnexion |

---

## 🐛 Dépannage

### Problème: "File not found" pour `login.php`
**Solution**: Assurez-vous que le fichier existe: `view/Front/login.php`

### Problème: "Erreur de base de données" au login
**Vérifications:**
1. Vérifiez que `config/database.php` est bien configuré
2. Assurez-vous que MySQL est démarré
3. Exécutez la migration: `php config/migrate_login.php`

### Problème: "Email ou mot de passe incorrect"
**Vérifications:**
1. Vérifiez que vous avez exécuté la migration
2. Utilisez les identifiants de test (voir ci-dessus)
3. Assurez-vous que l'email existe dans la base de données

### Problème: La session persiste entre les onglets
**C'est normal!** 
- PHP utilise les **cookies de session** par défaut
- Utilisez le **mode incognito** pour une session totalement séparée
- Ou utilisez **deux navigateurs différents**

### Problème: Les messages ne s'actualisent pas
**Solutions:**
1. Rafraîchissez la page (F5)
2. Vérifiez que vous êtes sur deux **onglets/navigateurs différents**
3. Vérifiez que les deux utilisateurs ont une **conversation créée**

---

## 💡 Cas d'Usage Avancés

### Ajouter Vos Propres Utilisateurs
Pour ajouter un nouvel utilisateur manuellement:

```sql
INSERT INTO utilisateurs (prenom, nom, email, password) 
VALUES ('Jean', 'Dupont', 'jean@example.com', '<hash_bcrypt>');
```

Pour générer le hash bcrypt, vous pouvez utiliser:

```php
<?php
echo password_hash('votreMotDePasse123', PASSWORD_BCRYPT);
?>
```

---

## 📞 Support

Pour des questions sur:
- **La messagerie**: Consultez [MESSAGERIE_README.md](../../../MESSAGERIE_README.md)
- **Le code du login**: Regardez `login.php` et `logout.php`
- **La base de données**: Consultez `config/database.php`

---

## ⚠️ Avant la Production

Ce système de login est conçu pour **tester et développer localement**.

**Avant de mettre en production:**
- 🔒 Activez HTTPS (certificat SSL)
- 🔐 Renforcez la validation du formulaire
- 📊 Ajoutez un système d'oubli de mot de passe
- 🔑 Implémentez la vérification 2FA (optionnel)
- 📝 Ajoutez un journal des connexions
- ⏱️ Configurez les délais d'expiration de session

