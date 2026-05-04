# 🎯 RÉSUMÉ - Configuration Gmail pour Envoyer les Emails

## ✨ Vous Avez Maintenant

- ✅ **EmailManager.php amélioré** - Support SMTP natif (sans dépendances!)
- ✅ **smtp-config.php créé** - Fichier de configuration
- ✅ **GMAIL_SETUP.md créé** - Guide complet de configuration

---

## 🚀 3 Étapes RAPIDES (5 minutes)

### 1️⃣ Générer un Google App Password

1. Allez sur: **https://myaccount.google.com/apppasswords**
2. Sélectionnez: **Mail** et **Windows**
3. Cliquez **Générer**
4. **Copiez** le mot de passe 16 caractères

⏱️ **Temps:** 2 minutes

---

### 2️⃣ Configurer le Fichier

1. **Ouvrez:** `C:\xampp\htdocs\swaply\config\smtp-config.php`

2. **Remplacez:**
   - `YOUR_EMAIL@gmail.com` → Votre email Gmail (ex: klaiaziz07@gmail.com)
   - `YOUR_16_CHAR_PASSWORD` → Le mot de passe copié à l'étape 1

3. **Changez:** `'enabled' => false` → `'enabled' => true`

4. **Sauvegardez** (Ctrl+S)

⏱️ **Temps:** 2 minutes

---

### 3️⃣ Tester

1. Allez à: `http://localhost/swaply/view/front/register.php`
2. Remplissez le formulaire avec **VOTRE EMAIL GMAIL**
3. Cliquez **SIGN UP**
4. **Vérifiez votre boîte email** (30 secondes)
5. Cliquez le lien et confirmez!

⏱️ **Temps:** 1 minute

---

## 📄 Contenu de `smtp-config.php`

Voici le fichier créé:

```php
<?php
return [
    'smtp' => [
        'enabled' => false,  // ← CHANGER À true
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'YOUR_EMAIL@gmail.com',  // ← À REMPLACER
        'password' => 'YOUR_16_CHAR_PASSWORD', // ← À REMPLACER
        'encryption' => 'tls',
    ],
    'from_email' => 'noreply@swaply.local',
    'from_name' => 'Swaply - Vérification Email',
];
?>
```

---

## 📝 Modifications dans EmailManager.php

J'ai modifié la classe pour:

✅ Charger la configuration SMTP au démarrage
✅ Vérifier si SMTP est activé
✅ Utiliser SMTP natif si activé
✅ Revenir à `mail()` sinon

```php
public function __construct() {
    // Charger la configuration SMTP
    if (file_exists(__DIR__ . '/smtp-config.php')) {
        $this->smtpConfig = require __DIR__ . '/smtp-config.php';
    }
}

private function sendEmail($toEmail, $subject, $message) {
    // Si SMTP est configuré, l'utiliser
    if ($this->smtpConfig && $this->smtpConfig['smtp']['enabled']) {
        return $this->sendViaSMTP($toEmail, $subject, $message);
    }
    // Sinon utiliser mail()
    return @mail($toEmail, $subject, $message, $headers);
}
```

---

## 🎯 Commande Rapide

Voici la liste complète de ce que vous devez faire:

```
1. Allez sur: https://myaccount.google.com/apppasswords
2. Générez un App Password
3. Ouvrez: C:\xampp\htdocs\swaply\config\smtp-config.php
4. Remplacez YOUR_EMAIL et YOUR_PASSWORD
5. Changez enabled à true
6. Sauvegardez
7. Testez sur register.php
8. Vérifiez votre email Gmail
```

---

## 🔧 Fichiers Modifiés/Créés

| Fichier | État | Action |
|---------|------|--------|
| `config/EmailManager.php` | ✏️ Modifié | Support SMTP natif |
| `config/smtp-config.php` | ✅ Créé | Configuration Gmail |
| `GMAIL_SETUP.md` | ✅ Créé | Guide détaillé |

---

## ✅ Avant de Tester

Vérifiez que:
- [ ] `smtp-config.php` existe
- [ ] `EmailManager.php` est modifié
- [ ] Les credentials sont remplacés (pas de `YOUR_`)
- [ ] `'enabled' => true` est défini
- [ ] Le fichier est sauvegardé

---

## 🧪 Test Rapide

Créez un fichier `test_email.php` dans Swaply:

```php
<?php
require 'config/EmailManager.php';

$emailManager = new EmailManager();
$result = $emailManager->sendVerificationEmail(
    'votre-email@gmail.com',
    'http://localhost/swaply/view/front/verify_email.php?token=test'
);

echo $result ? "✓ Email envoyé!" : "✗ Erreur";
?>
```

Ouvrez: `http://localhost/swaply/test_email.php`

Vous devriez voir: `✓ Email envoyé!`

---

## 🚀 C'EST TOUT!

Vous avez maintenant:

✅ **EmailManager.php** avec SMTP natif
✅ **Configuration simple** sans dépendances
✅ **Support Gmail** clé en main
✅ **Documentation complète** dans `GMAIL_SETUP.md`

**Suivez les 3 étapes ci-dessus et c'est fini!** 🎉

---

## 📞 Questions?

**"Où trouver mon App Password?"**
→ https://myaccount.google.com/apppasswords

**"C'est pas mon vrai mot de passe?"**
→ Non! C'est un mot de passe d'application généré par Google

**"Ça marche avec d'autres fournisseurs?"**
→ Oui! SendGrid, Mailgun, Office 365, etc. (voir `GMAIL_SETUP.md`)

**"C'est sûr?"**
→ Oui! Le mot de passe peut être révoké à tout moment

---

**Bon email!** 📧✨

Créé pour Swaply - 2026
