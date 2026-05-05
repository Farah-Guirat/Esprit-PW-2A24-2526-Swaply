# 🚀 Installation Rapide - Messages Vocaux

## Étape 1️⃣ : Exécuter la migration

Accédez au dossier du projet et exécutez:

```bash
php config/migrate_voice_messages.php
```

**Attendez-vous à voir:**
```
✅ Colonne 'type_message' ajoutée avec succès
✅ Colonne 'voix_duree' ajoutée avec succès
✅ Index 'idx_type_message' créé avec succès
✅ Dossier 'uploads/voice' créé avec succès

✨ Migration réussie ! Le système de messages vocaux est maintenant prêt.
```

## Étape 2️⃣ : Vérifier les permissions

Les dossiers doivent avoir les bonnes permissions:

```bash
chmod 755 uploads/voice
chmod 755 tmp
```

Sur Windows, les permissions sont généralement OK automatiquement.

## Étape 3️⃣ : Tester la fonctionnalité

1. Allez sur la page de messagerie
2. Ouvrez une conversation
3. Vous devriez voir un bouton **🎤** dans la barre de saisie
4. Cliquez dessus pour enregistrer un message vocal
5. Accordez l'accès au microphone quand le navigateur demande

## ✨ C'est prêt !

Les utilisateurs peuvent maintenant:
- 🎤 **Enregistrer** des messages vocaux
- 📤 **Envoyer** les messages vocaux
- ▶️ **Écouter** les messages vocaux reçus

---

## 📋 Fichiers Modifiés

### Backend
- `model/Message.php` - Ajout méthode `createVoiceMessage()`
- `controller/MessageController.php` - Ajout route `sendVoice` et `handleVoiceUpload()`
- `config/migrate_voice_messages.php` - **NOUVEAU** - Script de migration

### Frontend
- `view/Front/Messages.php` - Ajout UI + JavaScript pour enregistrement vocal

### Documentation
- `VOICE_MESSAGES_README.md` - **NOUVEAU** - Documentation complète

---

## 🔧 Configuration Optionnelle

### Changer la limite de taille

Dans `controller/MessageController.php`, méthode `handleVoiceUpload()`:

```php
// Limite de taille : 5 MB pour les messages vocaux
$maxSize = 5 * 1024 * 1024;  // ← Modifier cette ligne

// Exemple pour 10 MB:
$maxSize = 10 * 1024 * 1024;
```

### Changer les formats supportés

Même fichier, même méthode:

```php
$allowedExts = ['webm', 'mp3', 'wav', 'ogg', 'm4a'];
$allowedMimes = [
    'audio/webm',
    'audio/mpeg',
    'audio/wav',
    'audio/ogg',
    'audio/mp4',
    'audio/x-m4a'
];
```

---

## ✅ Checklist de Vérification

- [ ] Fichier `migrate_voice_messages.php` exécuté avec succès
- [ ] Colonnes `type_message` et `voix_duree` créées en base
- [ ] Dossier `uploads/voice/` créé
- [ ] Permissions correctes sur les dossiers
- [ ] Bouton 🎤 visible dans la messagerie
- [ ] Microphone accessible (permissions du navigateur accordées)
- [ ] Test d'enregistrement réussi
- [ ] Test d'envoi réussi
- [ ] Test de lecture réussi

---

## 🐛 Dépannage Rapide

| Problème | Solution |
|----------|----------|
| Bouton 🎤 n'apparaît pas | Vérifier que les colonnes sont en base (voir Étape 1) |
| Microphone inaccessible | Vérifier les permissions du navigateur |
| Erreur lors de l'upload | Vérifier permissions de `uploads/voice/` |
| Fichier audio ne s'envoie pas | Vérifier logs du serveur, espace disque |
| Lecteur audio ne fonctionne pas | Vérifier que le fichier existe dans `uploads/voice/` |

---

## 📞 Besoin d'Aide?

Consultez:
- 📖 [Documentation Complète](VOICE_MESSAGES_README.md)
- 🔧 [Code Source](view/Front/Messages.php)
- 📱 [API Contrôleur](controller/MessageController.php)

