# 📧 Guide Complet - Configurer Gmail SMTP pour Envoyer les Emails

## 🚀 4 Étapes Simples (5 minutes)

### Étape 1️⃣: Générer un Google App Password (2 min)

**⚠️ IMPORTANT:** Vous devez avoir l'authentification 2FA activée sur votre compte Google

1. **Allez sur:** https://myaccount.google.com/apppasswords
   
2. **Connectez-vous** avec votre compte Gmail

3. **Sélectionnez:**
   - **Application:** Mail
   - **Appareil:** Windows (ou votre OS)

4. **Cliquez "Générer"**
   - Google vous donnera un **mot de passe de 16 caractères**
   - Exemple: `abcd efgh ijkl mnop`
   - **Copiez-le!** (vous ne le reverrez pas)

---

### Étape 2️⃣: Configurer le Fichier (1 min)

1. **Ouvrez:** `config/smtp-config.php`

2. **Remplacez ces deux lignes:**

**AVANT:**
```php
'username' => 'YOUR_EMAIL@gmail.com',
'password' => 'YOUR_16_CHAR_PASSWORD',
```

**APRÈS:**
```php
'username' => 'klaiaziz07@gmail.com',  // Votre email Gmail
'password' => 'abcd efgh ijkl mnop',   // Mot de passe généré à l'étape 1
```

3. **Sauvegardez le fichier** (Ctrl+S)

---

### Étape 3️⃣: Activer SMTP (1 min)

1. **Dans le même fichier `config/smtp-config.php`**

2. **Changez cette ligne:**

**AVANT:**
```php
'enabled' => false,
```

**APRÈS:**
```php
'enabled' => true,
```

3. **Sauvegardez** (Ctrl+S)

---

### Étape 4️⃣: Tester (1 min)

1. **Allez à:** `http://localhost/swaply/view/front/register.php`

2. **Remplissez le formulaire** avec:
   - First Name: `Test`
   - Last Name: `User`
   - Email: **VOTRE EMAIL GMAIL** (ex: klaiaziz07@gmail.com)
   - Phone: `0612345678`
   - Date: `1990-01-01`
   - Gender: `Male`
   - Password: `Password123`
   - Répondez au Captcha

3. **Cliquez "SIGN UP"**

4. **Vérifiez votre boîte email Gmail**
   - Cherchez un email de "Swaply"
   - **Il devrait arriver en moins de 30 secondes!**

5. **Si ce n'est pas dans la boîte de réception:**
   - Vérifiez le dossier **Spam/Promotions**
   - Marquez-le comme "Non-spam"

---

## ✅ Vous Avez Reçu l'Email?

### ✓ OUI - Parfait!

1. **Cliquez sur "✓ Oui, c'est moi"** dans l'email
2. **Vous êtes redirigé vers swaplyf.php**
3. **Vous êtes automatiquement connecté!** 🎉

### ✗ NON - Dépannage

Vérifiez ces points:

#### 1️⃣ Configuration Correcte?

Ouvrez `config/smtp-config.php` et vérifiez:

```php
'enabled' => true,  // ← Doit être true
'username' => 'klaiaziz07@gmail.com',  // ← Doit être votre email
'password' => 'xxxx xxxx xxxx xxxx',   // ← Doit avoir 16 caractères
```

#### 2️⃣ Gmail App Password Correct?

- ✅ Est-ce un App Password? (généré sur https://myaccount.google.com/apppasswords)
- ✅ Est-ce le bon? (pas votre mot de passe normal Gmail)
- ✅ N'a-t-il pas d'espace? (copier-coller tel quel)

**Sinon, regénérez-en un:**
1. Allez sur https://myaccount.google.com/apppasswords
2. Supprimez l'ancien
3. Créez-en un nouveau
4. Remplacez dans le fichier

#### 3️⃣ Vérifiez les Logs PHP

1. Allez à: `C:\xampp\apache\logs\error.log`
2. Cherchez les messages d'erreur
3. Si vous voyez "SMTP Connection Failed":
   - Votre pare-feu bloque le port 587
   - Autorisez PHP à accéder à internet

#### 4️⃣ Test Simple

Créez un fichier `test_email.php` dans le dossier Swaply:

```php
<?php
require 'config/EmailManager.php';
$email = new EmailManager();
$sent = $email->sendVerificationEmail('votre-email@gmail.com', 'http://test.local');
echo $sent ? "✓ Email envoyé!" : "✗ Erreur d'envoi";
?>
```

Ouvrez: `http://localhost/swaply/test_email.php`

Vous devriez voir: `✓ Email envoyé!`

---

## 📊 Comparaison: Avant vs Après

### AVANT (Sans SMTP)
```
Utilisateur → Formulaire → mail() PHP
                            ↓
                      (Ne s'envoie pas en local)
```

### APRÈS (Avec Gmail SMTP)
```
Utilisateur → Formulaire → SMTP natif
                            ↓
                        Gmail SMTP
                            ↓
                        Boîte email
                            ✓
```

---

## 🔒 Sécurité

✅ **Mot de passe en clair?** Non! Google génère un mot de passe d'application spécifique
✅ **C'est sûr?** Oui! Vous ne donnez pas accès à tout votre compte
✅ **Peut-on le révoquer?** Oui! Sur https://myaccount.google.com/apppasswords

**Bonnes pratiques:**
- ✅ Gardez le mot de passe sécurisé
- ✅ Ne le partagez pas en public
- ✅ N'envoyez pas par email
- ✅ Utilisez un `.env` en production (voir ci-dessous)

---

## 🚀 Pour la Production

En production, **ne stockez PAS les credentials en clair!**

Utilisez des variables d'environnement:

1. **Créez un fichier `.env`:**
```
GMAIL_USER=votre-email@gmail.com
GMAIL_PASSWORD=votre-app-password
```

2. **Modifiez `smtp-config.php`:**
```php
return [
    'smtp' => [
        'enabled' => true,
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => getenv('GMAIL_USER'),
        'password' => getenv('GMAIL_PASSWORD'),
        'encryption' => 'tls',
    ],
    // ...
];
```

3. **Installez `vlucas/dotenv`:**
```bash
composer require vlucas/dotenv
```

---

## 📝 Autres Serveurs SMTP

Si vous ne voulez pas utiliser Gmail, vous pouvez utiliser:

### **SendGrid** (recommandé en production)
```php
'host' => 'smtp.sendgrid.net',
'port' => 587,
'username' => 'apikey',
'password' => 'SG.xxxxx...',  // Votre clé API SendGrid
```

### **Mailgun**
```php
'host' => 'smtp.mailgun.org',
'port' => 587,
'username' => 'postmaster@your-domain.com',
'password' => 'votre-clé-api',
```

### **Office 365**
```php
'host' => 'smtp.office365.com',
'port' => 587,
'username' => 'votre-email@example.com',
'password' => 'votre-mot-de-passe',
```

---

## ✅ Checklist Finale

- [ ] J'ai un compte Gmail
- [ ] J'ai activé l'authentification 2FA sur Gmail
- [ ] J'ai généré un App Password
- [ ] J'ai mis à jour `smtp-config.php` avec mes credentials
- [ ] J'ai activé SMTP (`'enabled' => true`)
- [ ] J'ai testé l'inscription
- [ ] J'ai reçu l'email!
- [ ] J'ai cliqué "Oui, c'est moi"
- [ ] Mon compte a été créé! ✓

---

## 🎉 C'est Fini!

Vos emails sont maintenant envoyés en temps réel! 🚀

**Prochaines étapes:**
1. Tester d'autres inscriptions
2. Tester le rejet ("Non, ce n'est pas moi")
3. Vérifier le stockage des tokens en BD
4. Déployer en production

---

## 📞 Besoin d'Aide?

**Erreur: "SMTP Connection Failed"**
- Votre pare-feu bloque le port 587
- Solution: Autorisez PHP/XAMPP dans le pare-feu

**Erreur: "SMTP Auth failed"**
- Le mot de passe est incorrect
- Solution: Regénérez un App Password

**Email en Spam**
- Gmail peut mettre en spam
- Solution: Marquez comme "Non-spam" la première fois
- Ou utilisez SendGrid/Mailgun pour plus de délivrabilité

**Pas de message d'erreur, mais pas d'email**
- Vérifiez les logs: `C:\xampp\apache\logs\error.log`
- Vérifiez que `enabled` = true
- Vérifiez la syntaxe du fichier (pas d'erreur PHP)

---

**Créé pour Swaply - 2026** ✨

Bon email! 📧
