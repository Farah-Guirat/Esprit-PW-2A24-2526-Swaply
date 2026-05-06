# 🚀 Guide d'Installation - Double Authentification par Email

## ✅ Résumé des Changements

J'ai implémenté un système complet de double authentification par email pour votre formulaire d'inscription. Voici ce qui a été fait :

### 📦 Fichiers Créés (4 fichiers)

1. **`config/migrations.php`** - Script pour initialiser les tables de la base de données
2. **`config/EmailManager.php`** - Gestionnaire d'envoi d'emails
3. **`model/EmailVerification.php`** - Modèle pour gérer les tokens de vérification
4. **`view/front/verify_email.php`** - Page de vérification avec les deux boutons
5. **`config/test_double_auth.php`** - Script de test et diagnostique

### 📝 Fichiers Modifiés (2 fichiers)

1. **`controller/UserC.php`** - Modification du processus d'inscription
2. **`view/front/register.php`** - Ajout des messages de vérification et rejet

---

## 🔧 Installation (3 étapes simples)

### Étape 1️⃣ : Initialiser la Base de Données

Exécutez le script de migration **UNE SEULE FOIS** :

**Option A - Via le navigateur :**
1. Ouvrez votre navigateur
2. Accédez à: `http://localhost/swaply/config/migrations.php`
3. Vous devriez voir les messages:
   ```
   ✓ Table email_verification_tokens créée avec succès ou existe déjà
   ✓ Colonne email_verified ajoutée à la table utilisateurs
   ```

**Option B - Via la ligne de commande :**
```bash
cd C:\xampp\htdocs\swaply
"C:\xampp\php\php.exe" config/migrations.php
```

### Étape 2️⃣ : Vérifier l'Installation

Pour vérifier que tout est bien configuré :

Ouvrez: `http://localhost/swaply/config/test_double_auth.php`

Vous devriez voir tous les tests en ✅ vert.

### Étape 3️⃣ : Tester le Flux Complet

1. Ouvrez: `http://localhost/swaply/view/front/register.php`
2. Remplissez le formulaire avec des données valides
3. Cliquez sur **SIGN UP**
4. Vous devriez voir le message:
   ```
   ✓ Un email de vérification est envoyé à [votre@email.com]
   ```
5. Dans votre boîte email, cliquez sur le lien (ou accédez directement via le navigateur)
6. Vous verrez deux boutons:
   - **✓ Oui, c'est moi** → Crée le compte et vous connecte
   - **✗ Non, ce n'est pas moi** → Annule l'inscription et affiche un message en rouge

---

## 📋 Flux Complet d'Inscription

```
Utilisateur remplit le formulaire
        ↓
Validation JavaScript (SANS HTML5)
        ↓
Envoi au serveur
        ↓
Validation côté serveur
        ↓
Génération d'un token unique
        ↓
Envoi d'email avec lien de vérification
        ↓
Affichage du message:
"Un email de vérification est envoyé à user@email.com"
        ↓
L'utilisateur clique sur le lien
        ↓
    ┌─ Oui, c'est moi ─────────┐
    │                            │
    ↓                            ↓
Création du compte          Suppression du token
Connexion automatique       Redirection avec message rouge:
Redirection vers swaplyf    "Veuillez vérifier vos informations"
    │                            │
    └────────────────────────────┘
```

---

## 🔐 Sécurité

- ✅ **Tokens uniques** : Générés avec 32 bytes aléatoires
- ✅ **Un token par email** : Empêche les doublons
- ✅ **Expiration** : Tous les tokens expirent après 24 heures
- ✅ **Données sécurisées** : Les mots de passe sont hachés avec PASSWORD_DEFAULT
- ✅ **Pas de dépendances** : Utilise PHP pur (pas de Composer nécessaire)

---

## 📧 Format de l'Email Envoyé

L'email contient :

```
┌─────────────────────────────────────┐
│   Vérification de Compte Swaply     │
├─────────────────────────────────────┤
│                                     │
│  Bonjour,                           │
│                                     │
│  Quelqu'un a tenté de créer un      │
│  compte Swaply avec l'adresse email │
│                                     │
│  ► user@email.com                   │
│                                     │
│  Si c'est vous, confirmez votre     │
│  identité:                          │
│                                     │
│  [✓ Oui, c'est moi] [✗ Non, c'est] │
│                                     │
│  Le lien expire dans 24 heures      │
│                                     │
└─────────────────────────────────────┘
```

---

## 📊 Données en Base de Données

### Nouvelle table: `email_verification_tokens`

```sql
SELECT * FROM email_verification_tokens;
```

| Colonne | Type | Description |
|---------|------|-------------|
| id_token | INT | Identifiant unique |
| email | VARCHAR(255) | Email en attente de vérification |
| token | VARCHAR(255) | Token unique (32 bytes hex) |
| user_data | LONGTEXT | Données JSON du futur utilisateur |
| created_at | TIMESTAMP | Date de création |
| expires_at | DATETIME | Date d'expiration (24h) |
| verified | INT | 0 = en attente, 1 = vérifié |

### Colonne ajoutée: `utilisateurs.email_verified`

```sql
ALTER TABLE utilisateurs ADD COLUMN email_verified INT DEFAULT 0;
```

| Valeur | Signification |
|--------|---------------|
| 0 | Email pas vérifié (pour les inscriptions futures) |
| 1 | Email vérifié (utilisateur complet) |

---

## 🛠️ Configuration de l'Email

### Pour les Tests (Configuration Actuelle)

La solution utilise `mail()` en PHP, qui fonctionne pour les tests locaux.

**Si les emails ne sont pas reçus localement:**
1. Vérifiez votre configuration `php.ini`:
   ```
   [mail]
   mail.add_x_header = On
   ```
2. Les emails peuvent aller en spam - vérifiez ce dossier

### Pour la Production

Modifiez `config/EmailManager.php` pour utiliser SMTP avec PHPMailer:

```php
<?php
// À la place de mail(), utiliser:
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer();
$mail->Host = 'smtp.gmail.com';
$mail->Username = 'your-email@gmail.com';
// etc...
?>
```

Pour installer PHPMailer:
```bash
composer require phpmailer/phpmailer
```

---

## ✨ Messages Affichés

### ✅ Message de Vérification Envoyée (Bleu)
```
✓ Un email de vérification est envoyé à
  user@email.com
  
Veuillez cliquer sur le lien dans l'email pour confirmer votre compte.
Le lien est valide pendant 24 heures.
```

**Position:** Au-dessus du formulaire

### 🔴 Message de Rejet (Rouge)
```
✗ Veuillez vérifier vos informations
```

**Position:** Au-dessus du formulaire
**Affiché quand:** L'utilisateur clique sur "Non, ce n'est pas moi"

---

## 🚀 Validation

Le système **NE BRANCHE PAS** sur HTML5 (`required`, `type="email"`, etc.).

La validation se fait entièrement en JavaScript via la fonction `validateSignup()` existante, puis vérifié à nouveau côté serveur pour la sécurité.

---

## 🐛 Dépannage

### "Table email_verification_tokens n'existe pas"
**Solution:** Exécutez `config/migrations.php` via le navigateur ou terminal

### "Erreur lors de la création du compte"
**Solution:** Vérifiez que l'email n'existe pas déjà en base de données

### "Token invalide ou expiré"
**Solution:** Le lien a expiré (> 24h). L'utilisateur doit recommencer l'inscription

### "Un email est déjà utilisé"
**Solution:** L'email existe déjà. Utilisez un autre email ou réinitialisez le mot de passe

---

## 📱 Responsive Design

Le système est complètement responsive et fonctionne sur:
- ✅ Desktop (1920px+)
- ✅ Tablette (768px+)
- ✅ Mobile (320px+)

---

## 🔄 Cas d'Usage

### Cas 1: Inscription Réussie
1. Utilisateur remplit le formulaire
2. Email de vérification envoyé
3. Utilisateur confirme ("Oui, c'est moi")
4. Compte créé et utilisateur connecté automatiquement

### Cas 2: Fausse Tentative
1. Utilisateur reçoit un email pour un email qu'il n'a pas saisi
2. Utilisateur rejette ("Non, ce n'est pas moi")
3. Inscription annulée
4. Message rouge affiché
5. Formulaire réinitialisé pour une nouvelle tentative

### Cas 3: Expiration du Lien
1. Plus de 24 heures sont passées
2. Lien expiré
3. Utilisateur redirigé vers la page d'inscription
4. Message "Token invalide ou expiré"

---

## 📞 Support

Pour toute question ou problème:
1. Consultez le fichier `DOUBLE_AUTH_README.md`
2. Exécutez le script de test: `config/test_double_auth.php`
3. Vérifiez les logs PHP et MySQL

---

## 📄 Fichiers Importants

| Fichier | Ligne | Description |
|---------|-------|-------------|
| `register.php` | 24-44 | Gestion des messages |
| `UserC.php` | 12-65 | Nouveau processus SIGNUP |
| `verify_email.php` | 1-200 | Page de vérification |
| `EmailVerification.php` | 1-150 | Logique des tokens |
| `EmailManager.php` | 1-100 | Envoi des emails |

---

## ✅ Installation Terminée!

Vous pouvez maintenant:
1. ✅ Accéder à `register.php` pour tester
2. ✅ Consulter `test_double_auth.php` pour vérifier la configuration
3. ✅ Consulter `DOUBLE_AUTH_README.md` pour la documentation complète

**Bonne chance!** 🎉

---

Créé pour Swaply - 2026
