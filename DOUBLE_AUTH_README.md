# 📧 Système de Double Authentification par Email - Swaply

## 📋 Description

Ce système ajoute une vérification par email en deux étapes lors de l'inscription. Quand un utilisateur soumet un formulaire d'inscription valide :

1. **Un email de vérification** est envoyé à son adresse email
2. L'utilisateur doit cliquer sur un lien dans l'email
3. Deux options s'offrent à lui :
   - **✓ Oui, c'est moi** : Le compte est créé et il est connecté automatiquement
   - **✗ Non, ce n'est pas moi** : La demande est annulée et il revient à la page d'inscription

## 📦 Fichiers Créés et Modifiés

### ✅ Fichiers Créés

1. **`config/migrations.php`**
   - Script qui crée les tables nécessaires pour la vérification
   - À exécuter une fois au démarrage

2. **`config/EmailManager.php`**
   - Classe pour gérer l'envoi des emails
   - Utilise la fonction PHP `mail()` native
   - Format HTML professionnelle avec deux boutons cliquables

3. **`model/EmailVerification.php`**
   - Classe pour gérer les tokens de vérification
   - Crée des tokens uniques et les stocke en base de données
   - Valables pendant 24 heures
   - Peut être nettoyée des tokens expirés

4. **`view/front/verify_email.php`**
   - Page de vérification d'email
   - Affiche les deux boutons de confirmation
   - Crée le compte à la confirmation
   - Annule la demande au rejet

### 📝 Fichiers Modifiés

1. **`controller/UserC.php`**
   - Modified la fonction de SIGNUP
   - Au lieu de créer directement le compte, génère un token
   - Envoie un email de vérification
   - Stocke les données en attente de vérification

2. **`view/front/register.php`**
   - Ajout du CSS pour le message de vérification
   - Affichage du message "Un email de vérification est envoyé à..."
   - Affichage du message d'annulation si l'utilisateur a rejeté

3. **`model/User.php`**
   - Pas de modification majeure (compatible existant)

## 🗄️ Structure de Base de Données

### Nouvelle Table : `email_verification_tokens`

```sql
CREATE TABLE email_verification_tokens (
    id_token INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    token VARCHAR(255) NOT NULL UNIQUE,
    user_data LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    verified INT DEFAULT 0
)
```

### Colonne Ajoutée : `email_verified`

```sql
ALTER TABLE utilisateurs ADD COLUMN email_verified INT DEFAULT 0
```

## 🚀 Installation et Configuration

### Étape 1 : Initialiser la Base de Données

Exécutez le script de migration pour créer les tables :

```bash
php config/migrations.php
```

**OU** via un navigateur :
- Accédez à `http://localhost/swaply/config/migrations.php`

### Étape 2 : Configuration de l'Email (Optionnel)

Par défaut, le système utilise la fonction PHP `mail()`. Pour les tests locaux, cela fonctionne.

**Pour la production**, modifiez le fichier `config/EmailManager.php` pour utiliser un serveur SMTP :

```php
// Modifier la méthode sendEmail() pour utiliser SMTP
// Example avec PHPMailer:
// composer require phpmailer/phpmailer
```

### Étape 3 : Test

1. Accédez à `http://localhost/swaply/view/front/register.php`
2. Remplissez tous les champs correctement
3. Cliquez sur "SIGN UP"
4. Vous devez voir le message : "Un email de vérification est envoyé à [votre email]"
5. Dans votre boîte email, cliquez sur le lien
6. Confirmez en cliquant sur "Oui, c'est moi"
7. Vous êtes maintenant connecté et redirigé vers swaplyf.php

## 🔒 Sécurité

- Les tokens sont uniques et générés aléatoirement (32 bytes)
- Chaque email ne peut avoir qu'un seul token actif
- Les tokens expirent après 24 heures
- Les données de l'utilisateur sont stockées en JSON jusqu'à la confirmation
- Les mots de passe sont hachés avec PASSWORD_DEFAULT

## 📧 Format de l'Email

L'email envoyé contient :
- Un message expliquant la demande d'inscription
- L'adresse email utilisée
- **Deux boutons cliquables** :
  - "✓ Oui, c'est moi"
  - "✗ Non, ce n'est pas moi"
- Informations sur l'expiration du lien (24h)

## 🔄 Flux d'Authentification

```
1. Utilisateur remplit le formulaire
   ↓
2. Validation côté client (JavaScript) - SANS HTML5
   ↓
3. Envoi du formulaire au serveur
   ↓
4. Validation côté serveur
   ↓
5. Création d'un token
   ↓
6. Envoi d'un email avec le lien de vérification
   ↓
7. L'utilisateur clique sur le lien
   ↓
   ├─ Si "Oui, c'est moi"
   │   ├─ Création du compte
   │   ├─ Connexion automatique
   │   └─ Redirection vers swaplyf.php
   │
   └─ Si "Non, ce n'est pas moi"
       ├─ Suppression du token
       └─ Redirection vers register.php avec message
```

## 📞 Support et Maintenance

### Nettoyer les Tokens Expirés

Vous pouvez nettoyer les tokens expirés régulièrement :

```php
$emailVerification->cleanExpiredTokens();
```

### Vérifier les Tokens en Base de Données

```sql
SELECT * FROM email_verification_tokens WHERE verified = 0;
```

## ✅ Validation Côté Client

Le système conserve la validation JavaScript existante et ne branche **PAS** sur HTML5 pour le contrôle de saisie. La validation se fait entièrement en JavaScript dans la fonction `validateSignup()`.

## 🐛 Dépannage

### "Token invalide ou expiré"
- Le lien a expiré (plus de 24h)
- L'utilisateur doit recommencer l'inscription

### "Un email est déjà utilisé"
- L'email existe déjà en base de données
- L'utilisateur doit utiliser un email différent

### Email non reçu
- Vérifiez la configuration du serveur mail
- Vérifiez le dossier spam
- Pour les tests locaux, utilisez un serveur SMTP configuré

## 📄 Notes Importantes

- **Pas de dépendances externes** requises (utilise PHP pur)
- **Compatible** avec la structure existante du projet
- **Prêt pour la production** avec configuration SMTP
- **Entièrement en français** pour l'interface utilisateur

---

Créé pour Swaply - 2026
