# ⚡ Quick Start - Double Authentification par Email

## 🚀 Installation en 1 minute

### ✅ Étape 1: Initialiser la Base de Données

**Via navigateur:** Ouvrez ceci dans votre navigateur:
```
http://localhost/swaply/config/migrations.php
```

Vous devriez voir:
```
✓ Table email_verification_tokens créée avec succès
✓ Colonne email_verified ajoutée à la table utilisateurs
```

### ✅ Étape 2: Tester le Système

Ouvrez ceci:
```
http://localhost/swaply/config/test_double_auth.php
```

Tout doit afficher ✅ vert.

### ✅ Étape 3: Tester l'Inscription

Allez à:
```
http://localhost/swaply/view/front/register.php
```

1. Remplissez le formulaire
2. Cliquez sur "SIGN UP"
3. Vous verrez: "✓ Un email de vérification est envoyé à votre@email.com"
4. Cliquez sur le lien dans l'email (ou dans le navigateur si en local)
5. Confirmez "Oui, c'est moi"
6. Vous êtes connecté! ✅

## 📊 Qu'est-ce qui a changé?

### 📦 Fichiers Créés (5 fichiers)
- ✅ `config/migrations.php` - Initialise la base de données
- ✅ `config/EmailManager.php` - Envoie les emails
- ✅ `model/EmailVerification.php` - Gère les tokens
- ✅ `view/front/verify_email.php` - Page de vérification
- ✅ `config/test_double_auth.php` - Script de test

### 📝 Fichiers Modifiés (2 fichiers)
- ✅ `controller/UserC.php` - Nouveau processus d'inscription
- ✅ `view/front/register.php` - Affiche les messages

### 🗄️ Base de Données (2 changements)
- ✅ Nouvelle table: `email_verification_tokens`
- ✅ Nouvelle colonne: `utilisateurs.email_verified`

## 🔄 Flux d'Inscription

```
1. Remplir le formulaire
2. Cliquer "SIGN UP"
3. Email de vérification envoyé
4. Message bleu: "Un email de vérification est envoyé à..."
5. Cliquer sur le lien dans l'email
6. Deux boutons: "Oui, c'est moi" ou "Non, ce n'est pas moi"
7. Si "Oui": Compte créé et connexion automatique
8. Si "Non": Message rouge "Veuillez vérifier vos informations"
```

## 📋 Dépendances

✅ **AUCUNE!** Le système n'a pas de dépendances externes.
- Utilise PHP pur
- Pas besoin de Composer
- Pas besoin de PHPMailer (utilisé en production seulement)

## 📧 Emails

**Pour les tests locaux:**
Les emails vont dans le dossier courrier si PHP est bien configuré.

**Pour la production:**
Modifiez `config/EmailManager.php` pour ajouter SMTP (voir `INSTALLATION_DOUBLE_AUTH.md`)

## 📚 Documentation Complète

Lisez ces fichiers pour plus de détails:
- 📄 `INSTALLATION_DOUBLE_AUTH.md` - Guide détaillé
- 📄 `DOUBLE_AUTH_README.md` - Documentation technique
- 📄 `SQL_QUERIES.sql` - Requêtes utiles
- 📄 `config/test_double_auth.php` - Vérifier l'installation

## ✨ Fonctionnalités

- ✅ Double authentification par email
- ✅ Tokens uniques (32 bytes)
- ✅ Expiration 24h
- ✅ Totalement responsive (mobile/tablet/desktop)
- ✅ Messages en français
- ✅ Sécurité renforcée
- ✅ Validation JavaScript ET serveur
- ✅ Pas d'HTML5 pour la validation (comme demandé)

## 🐛 Si ça ne fonctionne pas

1. Vérifiez `config/test_double_auth.php`
2. Vérifiez les logs PHP/MySQL
3. Assurez-vous que `config/migrations.php` a été exécuté
4. Vérifiez que la table `email_verification_tokens` existe

## 🎉 C'est tout!

Le système est prêt à utiliser. Les inscriptions sont maintenant sécurisées avec une double authentification!

---

## 📞 Questions Fréquentes

**Q: Les emails ne s'envoient pas**
A: C'est normal en local. Pour les tests, utilisez `test_double_auth.php` pour voir les logs.

**Q: Comment activer l'envoi réel d'emails?**
A: Configurez un serveur SMTP dans `config/EmailManager.php`

**Q: Puis-je réinitialiser un token?**
A: Oui, via SQL. Voir `SQL_QUERIES.sql`

**Q: Combien de temps avant l'expiration?**
A: 24 heures. Modifiable dans `model/EmailVerification.php`

---

Créé pour Swaply - 2026 ✨
