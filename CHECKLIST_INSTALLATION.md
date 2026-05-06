# ✅ Checklist d'Installation - Double Authentification par Email

## 📋 Avant de Commencer

### Prérequis
- [ ] XAMPP installé et en cours d'exécution
- [ ] Swaply accessible via `http://localhost/swaply/`
- [ ] MySQL/MariaDB en cours d'exécution
- [ ] PHP 7.4+ (XAMPP en vient avec)

---

## 🚀 Étape 1: Initialiser la Base de Données

### ✅ Exécuter le Script de Migration

**Option A: Via Navigateur (Recommandé)**
- [ ] Ouvrez votre navigateur
- [ ] Allez à: `http://localhost/swaply/config/migrations.php`
- [ ] Vérifiez les messages:
  - [ ] "✓ Table email_verification_tokens créée avec succès"
  - [ ] "✓ Colonne email_verified ajoutée"

**Option B: Via Terminal**
- [ ] Ouvrez Terminal/PowerShell
- [ ] Allez à: `cd C:\xampp\htdocs\swaply`
- [ ] Exécutez: `"C:\xampp\php\php.exe" config/migrations.php`
- [ ] Vérifiez les mêmes messages

### ✅ Vérifier la Base de Données

**Via phpMyAdmin:**
- [ ] Ouvrez: `http://localhost/phpmyadmin/`
- [ ] Sélectionnez la base `swaply`
- [ ] Vérifiez la table `email_verification_tokens` existe
- [ ] Vérifiez que `utilisateurs` a la colonne `email_verified`

---

## 🧪 Étape 2: Tester l'Installation

### ✅ Exécuter le Script de Test

- [ ] Ouvrez: `http://localhost/swaply/config/test_double_auth.php`
- [ ] Vérifiez que **TOUS** les tests sont en ✅ vert
- [ ] Notamment:
  - [ ] Connexion BD: ✅
  - [ ] Table `email_verification_tokens`: ✅
  - [ ] Colonne `email_verified`: ✅
  - [ ] Création de token: ✅
  - [ ] Tous les fichiers créés: ✅

### ✅ Si des tests échouent

- [ ] Lisez le message d'erreur
- [ ] Réexécutez `config/migrations.php`
- [ ] Redémarrez MySQL depuis XAMPP Control Panel
- [ ] Essayez à nouveau

---

## 📧 Étape 3: Tester le Flux d'Inscription

### ✅ Accéder au Formulaire

- [ ] Ouvrez: `http://localhost/swaply/view/front/register.php`
- [ ] Le formulaire s'affiche normalement

### ✅ Remplir et Soumettre

- [ ] Remplissez tous les champs:
  - [ ] First Name: `John`
  - [ ] Last Name: `Doe`
  - [ ] Email: `test@example.local` (locale en local)
  - [ ] Phone: `0612345678`
  - [ ] Date of Birth: `1990-01-01`
  - [ ] Gender: `Male`
  - [ ] Password: `Password123`
  - [ ] Captcha: Répondez à la question
- [ ] Vérifiez que JavaScript valide (pas HTML5):
  - [ ] Pas de popups HTML5
  - [ ] Validation personnalisée JavaScript
- [ ] Cliquez "SIGN UP"

### ✅ Vérifier le Message de Vérification

**Après soumettre le formulaire:**
- [ ] Vous restez sur `register.php`
- [ ] Un message BLEU s'affiche:
  ```
  ✓ Un email de vérification est envoyé à
    test@example.local
    
    Veuillez cliquer sur le lien...
  ```
- [ ] Le formulaire est réinitialisé
- [ ] L'email n'est pas créé encore

### ✅ Vérifier la Base de Données

**Via phpMyAdmin:**
- [ ] Table `email_verification_tokens`:
  - [ ] Un nouvel enregistrement pour `test@example.local`
  - [ ] `verified` = 0 (en attente)
- [ ] Table `utilisateurs`:
  - [ ] L'utilisateur N'EXISTE PAS encore

### ✅ Tester le Lien de Vérification

**Option A: Email Réel (Si configuré)**
- [ ] Vérifiez votre boîte email
- [ ] Vous devriez recevoir un email de "Swaply"
- [ ] Avec 2 boutons cliquables: "Oui" et "Non"

**Option B: Lien Manuel (Tests locaux)**
- [ ] Via phpMyAdmin, copier le `token` de `email_verification_tokens`
- [ ] Ouvrez: `http://localhost/swaply/view/front/verify_email.php?token=VOTRE_TOKEN`
- [ ] Vous devriez voir:
  - [ ] Email affiché: `test@example.local`
  - [ ] Deux boutons: "Oui, c'est moi" et "Non, ce n'est pas moi"

---

## 🎯 Étape 4: Tester la Confirmation

### ✅ Cliquer "Oui, c'est moi"

- [ ] Cliquez sur le bouton "✓ Oui, c'est moi"
- [ ] Vous êtes redirigé vers `swaplyf.php`
- [ ] Avec `?account_created=1` dans l'URL
- [ ] Vous êtes connecté (session active)

**Vérifier en BD:**
- [ ] Table `utilisateurs`:
  - [ ] L'utilisateur existe maintenant
  - [ ] `email_verified` = 1
- [ ] Table `email_verification_tokens`:
  - [ ] Le token est supprimé

---

## ❌ Étape 5: Tester le Rejet

### ✅ Créer une Nouvelle Demande

- [ ] Allez à `register.php`
- [ ] Remplissez avec un nouvel email: `test2@example.local`
- [ ] Cliquez "SIGN UP"
- [ ] Le message bleu s'affiche
- [ ] Un token est créé dans la BD

### ✅ Cliquer "Non, ce n'est pas moi"

**Via le lien (tests locaux):**
- [ ] Ouvrez: `http://localhost/swaply/view/front/verify_email.php?token=NOUVEAU_TOKEN&action=reject`
- [ ] Un message ROUGE s'affiche:
  ```
  ✗ Veuillez vérifier vos informations
  ```
- [ ] Vous êtes automatiquement redirigé

**Vérifier en BD:**
- [ ] Table `email_verification_tokens`:
  - [ ] Le token est supprimé
- [ ] Table `utilisateurs`:
  - [ ] L'utilisateur N'EXISTE PAS

---

## 📊 Étape 6: Vérifications Finales

### ✅ Fichiers Créés

Vérifiez que ces 5 fichiers existent:
- [ ] `config/migrations.php`
- [ ] `config/EmailManager.php`
- [ ] `model/EmailVerification.php`
- [ ] `view/front/verify_email.php`
- [ ] `config/test_double_auth.php`

### ✅ Fichiers Modifiés

Vérifiez que ces 2 fichiers ont été modifiés:
- [ ] `controller/UserC.php` (contient EmailManager et EmailVerification)
- [ ] `view/front/register.php` (contient les messages bleus et rouges)

### ✅ Documentation

Vérifiez que vous avez accès à:
- [ ] `QUICKSTART.md` - Démarrage rapide
- [ ] `INSTALLATION_DOUBLE_AUTH.md` - Guide détaillé
- [ ] `DOUBLE_AUTH_README.md` - Doc technique
- [ ] `RESUME_MODIFICATIONS.md` - Résumé complet
- [ ] `SQL_QUERIES.sql` - Requêtes utiles

### ✅ Configuration

- [ ] `config/EmailManager.php` configure pour PHP `mail()`
- [ ] Prêt pour SMTP en production
- [ ] Pas de dépendances Composer requises

---

## 🔒 Étape 7: Tests de Sécurité

### ✅ Tokens

- [ ] Token unique pour chaque demande
- [ ] Token expiré après 24h
- [ ] Un seul token actif par email
- [ ] Token supprimé après utilisation

### ✅ Validation

- [ ] JavaScript valide (pas HTML5)
- [ ] PHP valide aussi côté serveur
- [ ] Pas de compte créé sans email vérifié

### ✅ Données

- [ ] Mots de passe hachés avec PASSWORD_DEFAULT
- [ ] Données JSON stockées temporairement
- [ ] Email affiché de manière sécurisée

---

## 🚨 Résolution des Problèmes

### ❌ "Table n'existe pas"
- [ ] Exécutez `config/migrations.php`
- [ ] Vérifiez phpMyAdmin
- [ ] Redémarrez MySQL

### ❌ "Erreur de connexion BD"
- [ ] Vérifiez que MySQL est en cours d'exécution
- [ ] Vérifiez `config/Database.php` (host, user, password)
- [ ] Redémarrez XAMPP

### ❌ "Emails ne s'envoient pas"
- [ ] C'est normal en local avec `mail()`
- [ ] Vérifiez les logs PHP
- [ ] Configurez SMTP en production

### ❌ "Lien de vérification invalide"
- [ ] Le token a expiré (> 24h)
- [ ] Créez une nouvelle demande
- [ ] Utilisez le nouveau token

### ❌ "Aucun message ne s'affiche"
- [ ] Vérifiez la URL (doit être en GET avec `?verification_sent=1`)
- [ ] Videz le cache navigateur
- [ ] Vérifiez la console JavaScript (F12)

---

## ✅ Checklist Finale

Marquez tout comme ✅:

- [ ] BD initialisée
- [ ] Script de test réussi
- [ ] Message bleu affiché
- [ ] Email vérifié
- [ ] Compte créé
- [ ] Message rouge affiché
- [ ] Rejet fonctionne
- [ ] Tous les fichiers existent
- [ ] Documentation lisible
- [ ] Prêt pour la production!

---

## 🎉 Installation Terminée!

Si toutes les cases sont cochées ✅, votre système est **complètement fonctionnel**!

Vous avez maintenant:
- ✅ Double authentification par email
- ✅ Deux boutons dans l'email
- ✅ Messages bleus et rouges
- ✅ Sécurité renforcée
- ✅ Pas de dépendances externes

**Profitez!** 🚀

---

## 📞 Besoin d'Aide?

1. Relisez le `QUICKSTART.md`
2. Vérifiez les logs: `config/test_double_auth.php`
3. Consultez `INSTALLATION_DOUBLE_AUTH.md`
4. Vérifiez phpMyAdmin

---

Créé pour Swaply - 2026 ✨
