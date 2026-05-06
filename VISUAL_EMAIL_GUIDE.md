# 📧 GUIDE VISUEL - Configurer Gmail SMTP (Étapes par Étapes)

## 🎯 Objectif

Envoyer les **vrais emails** de vérification à votre boîte Gmail.

---

## 📍 ÉTAPE 1: Générer un Google App Password

### ▶️ Étape 1.1: Aller sur Google Account

1. **Ouvrez votre navigateur**
2. **Allez sur:** `https://myaccount.google.com/apppasswords`
3. **Connectez-vous** avec votre compte Gmail (klaiaziz07@gmail.com)

⏱️ **Durée:** 30 secondes

### ▶️ Étape 1.2: Sélectionner Application et Appareil

**Vous devriez voir cette fenêtre:**

```
┌─────────────────────────────────────┐
│ Générer un mot de passe d'app       │
├─────────────────────────────────────┤
│                                     │
│  Sélectionnez une application:      │
│  [Courrier        ▼]                │
│                                     │
│  Sélectionnez un appareil:          │
│  [Windows         ▼]                │
│                                     │
│               [Générer]             │
│                                     │
└─────────────────────────────────────┘
```

**Faites ceci:**
1. Cliquez sur le premier `▼` (Application)
2. Sélectionnez **"Mail"** ou **"Courrier"**
3. Cliquez sur le deuxième `▼` (Appareil)
4. Sélectionnez **"Windows"**
5. **Cliquez "Générer"**

⏱️ **Durée:** 1 minute

### ▶️ Étape 1.3: Copier le Mot de Passe

**Google vous montrera ceci:**

```
┌─────────────────────────────────────┐
│ Votre mot de passe d'app pour       │
│ Courrier et Windows                 │
├─────────────────────────────────────┤
│                                     │
│  ┌─────────────────────────────┐   │
│  │ abcd efgh ijkl mnop         │   │
│  └─────────────────────────────┘   │
│         [Copier]                    │
│                                     │
└─────────────────────────────────────┘
```

**Faites ceci:**
1. **Sélectionnez** le mot de passe (16 caractères)
2. **Copiez-le** (Ctrl+C)
3. **Gardez-le** pour l'étape suivante

⏱️ **Durée:** 30 secondes

---

## 📍 ÉTAPE 2: Configurer le Fichier

### ▶️ Étape 2.1: Ouvrir le Fichier de Configuration

1. **Ouvrez VS Code** (ou votre éditeur)
2. **Ouvrez le fichier:**
   ```
   C:\xampp\htdocs\swaply\config\smtp-config.php
   ```

**Vous devriez voir:**
```php
<?php
return [
    'smtp' => [
        'enabled' => false,
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'YOUR_EMAIL@gmail.com',
        'password' => 'YOUR_16_CHAR_PASSWORD',
        'encryption' => 'tls',
    ],
    'from_email' => 'noreply@swaply.local',
    'from_name' => 'Swaply - Vérification Email',
];
?>
```

⏱️ **Durée:** 30 secondes

### ▶️ Étape 2.2: Remplacer l'Email

1. **Trouvez cette ligne:**
   ```php
   'username' => 'YOUR_EMAIL@gmail.com',
   ```

2. **Remplacez** `YOUR_EMAIL@gmail.com` par **votre email Gmail**
   
   **Exemple:**
   ```php
   'username' => 'klaiaziz07@gmail.com',
   ```

3. **Vérifiez** qu'il n'y a plus de `YOUR_EMAIL`

⏱️ **Durée:** 30 secondes

### ▶️ Étape 2.3: Remplacer le Mot de Passe

1. **Trouvez cette ligne:**
   ```php
   'password' => 'YOUR_16_CHAR_PASSWORD',
   ```

2. **Remplacez** `YOUR_16_CHAR_PASSWORD` par le mot de passe copié à l'étape 1.3
   
   **Exemple:**
   ```php
   'password' => 'abcd efgh ijkl mnop',
   ```

3. **Attention:** Le mot de passe peut contenir des **espaces**, c'est normal!

⏱️ **Durée:** 30 secondes

### ▶️ Étape 2.4: Activer SMTP

1. **Trouvez cette ligne:**
   ```php
   'enabled' => false,
   ```

2. **Changez** `false` en `true`
   ```php
   'enabled' => true,
   ```

3. **Vérifiez** que c'est bien `true` et non `false`

⏱️ **Durée:** 10 secondes

### ▶️ Étape 2.5: Sauvegarder

1. **Appuyez sur:** `Ctrl+S`
2. **Vérifiez** que le fichier est sauvegardé (pas de point blanc sur l'onglet)

⏱️ **Durée:** 5 secondes

---

## 📍 ÉTAPE 3: Tester l'Envoi d'Email

### ▶️ Étape 3.1: Aller au Formulaire d'Inscription

1. **Ouvrez votre navigateur**
2. **Allez à:**
   ```
   http://localhost/swaply/view/front/register.php
   ```

**Vous devriez voir le formulaire d'inscription**

⏱️ **Durée:** 20 secondes

### ▶️ Étape 3.2: Remplir le Formulaire

**Remplissez avec ces données:**

```
┌─────────────────────────────────────┐
│ First Name:    Test                 │
│ Last Name:     User                 │
│ Email:         [VOTRE EMAIL GMAIL]  │
│ Phone:         0612345678           │
│ Date:          1990-01-01           │
│ Gender:        Male                 │
│ Password:      Password123          │
│ Captcha:       [Répondez à la Q]    │
└─────────────────────────────────────┘
```

**⚠️ IMPORTANT:** Utilisez **VOTRE EMAIL GMAIL** dans le champ Email!

Exemple:
```
Email: klaiaziz07@gmail.com
```

⏱️ **Durée:** 1 minute

### ▶️ Étape 3.3: Soumettre

1. **Cliquez le bouton:** `SIGN UP`
2. **Attendez** 2-3 secondes

⏱️ **Durée:** 5 secondes

### ▶️ Étape 3.4: Vérifier le Message

**Vous devriez voir ce message BLEU:**

```
┌──────────────────────────────────────────┐
│ ✓ Un email de vérification est envoyé à  │
│   klaiaziz07@gmail.com                   │
│                                          │
│ Veuillez cliquer sur le lien dans l'email│
│ pour confirmer votre compte.              │
│ Le lien est valide pendant 24 heures.    │
└──────────────────────────────────────────┘
```

⏱️ **Durée:** 5 secondes

### ▶️ Étape 3.5: Vérifier Gmail

1. **Ouvrez votre compte Gmail:**
   ```
   https://mail.google.com
   ```

2. **Vérifiez la boîte de réception**

3. **Vous devriez voir un email de "Swaply"**
   - Objet: "Vérification de votre compte Swaply"
   - En moins de 30 secondes!

**Si ce n'est pas dans la boîte de réception:**
- ✓ Vérifiez le dossier **Spam**
- ✓ Vérifiez le dossier **Promotions**

⏱️ **Durée:** 30 secondes

### ▶️ Étape 3.6: Cliquer le Lien

**L'email contient:**

```
┌─────────────────────────────────────┐
│ Vérification de Compte Swaply       │
├─────────────────────────────────────┤
│                                     │
│ Email: klaiaziz07@gmail.com         │
│                                     │
│ [✓ Oui, c'est moi]                  │
│ [✗ Non, ce n'est pas moi]           │
│                                     │
│ Liens valides: 24 heures            │
└─────────────────────────────────────┘
```

**Cliquez:** `✓ Oui, c'est moi`

⏱️ **Durée:** 10 secondes

### ▶️ Étape 3.7: Vérifier la Création de Compte

**Vous devriez être redirigé vers:** `http://localhost/swaply/view/front/swaplyf.php`

**Et vous êtes connecté!** ✅

---

## ✅ Résumé Visuel

```
Étape 1: Google Account (1.5 min)
├─ Aller sur apppasswords
├─ Sélectionner Mail + Windows
└─ Copier le mot de passe

Étape 2: Configuration (2 min)
├─ Ouvrir smtp-config.php
├─ Remplacer email
├─ Remplacer mot de passe
├─ Activer SMTP (true)
└─ Sauvegarder

Étape 3: Test (2 min)
├─ Remplir le formulaire
├─ Cliquer SIGN UP
├─ Voir le message bleu
├─ Vérifier Gmail
├─ Cliquer le lien
└─ Être connecté ✓

TOTAL: 5-6 MINUTES
```

---

## 🎉 C'est Fait!

Vous avez maintenant:
✅ Configuration Gmail
✅ Emails en temps réel
✅ Vérification d'email fonctionnelle
✅ Sécurité renforcée

---

## 🐛 Dépannage Rapide

### Problème: Email n'arrive pas

**Vérifiez dans cet ordre:**
1. [ ] `enabled` = true dans smtp-config.php
2. [ ] Email et mot de passe sont corrects
3. [ ] Pas de caractères `YOUR_` dans le fichier
4. [ ] Le fichier est sauvegardé
5. [ ] Vérifiez le dossier Spam/Promotions

### Problème: "SMTP Connection Failed"

**Solution:**
- Votre pare-feu bloque le port 587
- Autorisez XAMPP/PHP dans le pare-feu

### Problème: "SMTP Auth failed"

**Solution:**
- Le mot de passe est incorrect
- Regénérez un nouveau App Password
- Remplacez dans le fichier

---

## 📞 Besoin d'Aide?

Consultez le fichier: `GMAIL_SETUP.md` (plus détaillé)

---

**Créé pour Swaply - 2026** ✨

Bon email! 📧
