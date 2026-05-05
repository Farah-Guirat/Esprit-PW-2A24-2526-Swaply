# 🐛 Dépannage - Messages Vocaux

## Problème 1: "Type de fichier audio non autorisé"

### Causes Possibles

1. **Type MIME non reconnu par `finfo_file()`**
   - Le navigateur envoie le fichier WebM avec un type MIME incorrect
   - Certains navigateurs ne supportent pas WebM correctement

2. **Extension de fichier invalide**
   - Le fichier n'est pas envoyé avec l'extension `.webm`

### Solutions

#### ✅ Solution 1: Vérifier les types MIME disponibles
Créez un fichier `test_mime.php`:
```php
<?php
$file = $_FILES['voix']['tmp_name'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file);
finfo_close($finfo);
echo "Type MIME détecté: " . $mime;
?>
```

Utilisez-le pour voir quel type MIME votre navigateur envoie:
- Si c'est `audio/webm`, il est supporté ✅
- Si c'est quelque chose d'autre, ajoutez-le à la liste dans `controller/MessageController.php`

#### ✅ Solution 2: Élargir les types MIME acceptés
Dans `controller/MessageController.php`, ligne ~525:
```php
$allowedMimes = [
    'audio/webm',
    'audio/mpeg',
    'audio/mp3',
    'audio/wav',
    'audio/x-wav',
    'audio/ogg',
    'audio/mp4',
    'audio/x-m4a',
    'audio/x-aac',
    'audio/aac',
    'audio/flac',
    'audio/x-flac',
    'audio/opus',
    'audio/x-opus',
    'application/octet-stream'  // Fallback
];
```

#### ✅ Solution 3: Modifier la validation
La nouvelle validation (~v2) est **moins stricte**:
- ✅ Vérifie l'extension EN PREMIER (plus fiable)
- ✅ Accepte `audio/*` (tous les audio)
- ✅ Accepte `application/octet-stream` (fallback)
- ⚠️ Si l'extension est `.webm`, c'est accepté même si MIME est inconnu

---

## Problème 2: "Cliquer sur le micro prend longtemps"

### Cause
Le navigateur demande la permission d'accès au microphone. C'est normal et peut prendre 1-3 secondes.

### Solution Visuelle
J'ai ajouté un feedback utilisateur:
- ⏳ Le bouton affiche "⏳" pendant que le microphone se connecte
- 🎤 Le bouton revient à "🎤" quand c'est prêt

**Comportement attendu:**
1. Cliquez sur 🎤
2. Le bouton devient ⏳ (2-3 secondes)
3. Une popup demande "Autoriser l'accès au microphone?"
4. Vous cliquez "Autoriser"
5. Le bouton redevient 🎤 et l'enregistrement commence

---

## Problème 3: "Je ne peux pas envoyer un message vocal seul"

### Cause
La validation du formulaire demandait du texte OU un fichier OU un message vocal.

### Solution ✅
La validation a été **corrigée**:
```javascript
if (!textarea_val && !hasFile && !hasVoice) {
    errors.push('Le message ne peut pas être vide.');
}
```

Maintenant vous pouvez:
- ✅ Envoyer du texte seul
- ✅ Envoyer un fichier seul  
- ✅ Envoyer un message vocal SEUL (nouveau!)
- ✅ Combiner texte + fichier
- ✅ Combiner texte + message vocal

---

## Dépannage Pas à Pas

### Étape 1: Vérifier la migration
```bash
php config/migrate_voice_messages.php
```

Attendez-vous à voir:
```
✅ Colonne 'type_message' ajoutée avec succès
✅ Colonne 'voix_duree' ajoutée avec succès
✅ Index 'idx_type_message' créé avec succès
✅ Dossier 'uploads/voice' créé avec succès

✨ Migration réussie !
```

### Étape 2: Tester le système
Accédez à: `http://localhost/swaply/test_voice_messages.php`

Vérifiez que tout affiche ✅:
- ✅ Colonne 'type_message' présente
- ✅ Colonne 'voix_duree' présente
- ✅ Dossier 'uploads/voice' existe
- ✅ Dossier 'uploads/voice' est accessible en ÉCRITURE

### Étape 3: Tester l'enregistrement
1. Allez sur la messagerie
2. Ouvrez une conversation
3. Vous devriez voir le bouton 🎤
4. Cliquez dessus
5. Accorder l'accès au microphone quand demandé
6. Parlez
7. Cliquez le bouton 🎤 à nouveau pour arrêter
8. Cliquez ✓ pour envoyer

---

## Messages d'Erreur Courants

| Message | Cause | Solution |
|---------|-------|----------|
| "Impossible d'accéder au microphone" | Permissions refusées | Vérifier permissions navigateur HTTPS |
| "Type de fichier audio non autorisé" | MIME type inconnu | Ajouter le MIME type à la liste |
| "Le fichier audio est trop volumineux" | Fichier > 5 MB | Enregistrement trop long ou mauvaise compression |
| "Erreur lors de l'upload du fichier audio" | Permissions dossier | Vérifier permissions de `uploads/voice/` |

---

## Console Navigateur - Logs Utiles

Ouvrez la console (F12) pour voir les logs:

```javascript
// Démarrage enregistrement
"Enregistrement démarré"

// Envoi
"Audio blob:", Blob
"Durée:", 30, "secondes"

// Réponse serveur
"Réponse:", {success: true, message: "...", voix_path: "..."}
```

---

## Fichiers Modifiés (v2)

### `view/Front/Messages.php`
- ✅ Feedback ⏳ pendant la connexion microphone
- ✅ Bouton redevient 🎤 quand prêt
- ✅ Gestion des erreurs améliorée

### `controller/MessageController.php`
- ✅ Types MIME élargis (ajout de variantes)
- ✅ Validation moins stricte (extension en premier)
- ✅ Fallback pour `application/octet-stream`
- ✅ Logs améliorés

---

## Tests à Faire

### Test 1: Envoyer un message vocal SEUL
1. Ouvrir messagerie
2. Cliquer 🎤
3. Enregistrer 5 secondes
4. Cliquer 🎤 pour arrêter
5. Cliquer ✓ pour envoyer (PAS de texte)
6. ✅ Devrait fonctionner!

### Test 2: Envoyer texte + message vocal
1. Écrire du texte
2. Cliquer 🎤
3. Enregistrer 5 secondes
4. Cliquer 🎤 pour arrêter
5. Cliquer ✓ pour envoyer
6. ✅ Devrait fonctionner!

### Test 3: Écouter les messages vocaux
1. Recevoir un message vocal
2. Cliquer ▶️ pour écouter
3. ✅ L'audio devrait se lire

---

## Fichiers Importants

| Fichier | Rôle |
|---------|------|
| `controller/MessageController.php` | Gestion upload/validation |
| `view/Front/Messages.php` | Interface + JavaScript |
| `model/Message.php` | Création messages vocaux |
| `config/migrate_voice_messages.php` | Migration base de données |
| `uploads/voice/` | Stockage fichiers audio |

---

## Prochaines Étapes si Problème Persiste

1. **Vérifier les logs du serveur:**
   ```bash
   # Pour Apache/PHP
   tail -f /var/log/php-errors.log
   # Ou dans xampp:
   tail -f xampp/php/logs/php_error.log
   ```

2. **Tester avec un autre navigateur** (Chrome, Firefox, Safari, Edge)

3. **Vérifier les permissions:**
   ```bash
   ls -la uploads/voice/
   # Devrait afficher: drwxr-xr-x
   ```

4. **Tester l'upload de fichier audio seul** (pas via WebRTC) pour isoler le problème

---

## Support Navigateurs

| Navigateur | WebRTC | MediaRecorder | Audio HTML5 | Status |
|------------|--------|---------------|-------------|--------|
| Chrome 47+ | ✅ | ✅ | ✅ | ✅ Supporté |
| Firefox 49+ | ✅ | ✅ | ✅ | ✅ Supporté |
| Safari 15+ | ✅ | ✅ (limité) | ✅ | ⚠️ Limité |
| Edge 79+ | ✅ | ✅ | ✅ | ✅ Supporté |
| Opera 34+ | ✅ | ✅ | ✅ | ✅ Supporté |
| Safari 14- | ⚠️ | ❌ | ✅ | ❌ Non supporté |
| IE 11 | ❌ | ❌ | ❌ | ❌ Non supporté |

