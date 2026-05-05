# 🔌 API Documentation - Messages Vocaux

## Endpoint: Send Voice Message

### URL
```
POST /controller/MessageController.php?action=sendVoice
```

### Content-Type
```
multipart/form-data
```

### Parameters

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| `id_conversation` | integer | ✅ | ID de la conversation cible |
| `voix` | file (blob) | ✅ | Fichier audio (webm/mp3/wav/ogg/m4a) |
| `duree` | integer | ⏳ | Durée en secondes (optionnel) |

### Response

**Succès (200 OK)**
```json
{
    "success": true,
    "message": "Message vocal envoyé",
    "voix_path": "uploads/voice/voice_123456_1620000000.webm"
}
```

**Erreur (4xx/5xx)**
```json
{
    "success": false,
    "message": "Description de l'erreur"
}
```

### Erreurs Possibles

| Code | Message | Cause |
|------|---------|-------|
| 401 | Non authentifié | Utilisateur non connecté |
| 403 | Accès refusé | L'utilisateur n'a pas accès à cette conversation |
| 400 | Conversation invalide | ID de conversation invalide |
| 400 | Erreur lors de l'upload audio | Fichier audio manquant/corrompu |
| 400 | Le fichier audio est trop volumineux | Taille > 5 MB |
| 400 | Type de fichier audio non autorisé | Format non supporté |
| 400 | Extension de fichier audio non autorisée | Extension non reconnue |
| 500 | Erreur lors de l'enregistrement | Problème base de données |

### Exemples

#### cURL
```bash
curl -X POST "http://localhost/controller/MessageController.php?action=sendVoice" \
  -F "id_conversation=42" \
  -F "voix=@voice_recording.webm" \
  -F "duree=30"
```

#### Fetch API (JavaScript)
```javascript
const formData = new FormData();
formData.append('id_conversation', 42);
formData.append('voix', audioBlob, 'voice_message.webm');
formData.append('duree', 30);

fetch('/controller/MessageController.php?action=sendVoice', {
    method: 'POST',
    body: formData
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Erreur:', error));
```

#### Axios (JavaScript)
```javascript
const formData = new FormData();
formData.append('id_conversation', 42);
formData.append('voix', audioBlob, 'voice_message.webm');
formData.append('duree', 30);

axios.post('/controller/MessageController.php?action=sendVoice', formData, {
    headers: {
        'Content-Type': 'multipart/form-data'
    }
})
.then(response => console.log(response.data))
.catch(error => console.error('Erreur:', error.response.data));
```

#### Python (Requests)
```python
import requests

files = {'voix': open('voice_message.webm', 'rb')}
data = {
    'id_conversation': 42,
    'duree': 30
}

response = requests.post(
    'http://localhost/controller/MessageController.php?action=sendVoice',
    files=files,
    data=data
)

print(response.json())
```

---

## Model Methods

### Message::createVoiceMessage()

**Signature**
```php
public function createVoiceMessage(
    string $voix_path,
    int $id_expediteur,
    int $id_conversation,
    string $voix_nom_original = 'voice_message',
    string $voix_type = 'audio/webm',
    int $voix_taille = 0,
    int $voix_duree = 0
): bool
```

**Description**
Crée un message vocal en base de données.

**Paramètres**
- `$voix_path` (string) - Chemin relatif du fichier audio (ex: `uploads/voice/voice_123.webm`)
- `$id_expediteur` (int) - ID de l'utilisateur envoyant le message
- `$id_conversation` (int) - ID de la conversation
- `$voix_nom_original` (string) - Nom du fichier original (défaut: `voice_message`)
- `$voix_type` (string) - Type MIME du fichier (défaut: `audio/webm`)
- `$voix_taille` (int) - Taille du fichier en octets (défaut: 0)
- `$voix_duree` (int) - Durée du message en secondes (défaut: 0)

**Retour**
- `bool` - `true` si succès, `false` sinon

**Exemple**
```php
$messageModel = new Message();
$success = $messageModel->createVoiceMessage(
    voix_path: 'uploads/voice/voice_507f1f77bcf86cd799439011_1620000000.webm',
    id_expediteur: 42,
    id_conversation: 7,
    voix_nom_original: 'voice_message',
    voix_type: 'audio/webm',
    voix_taille: 150000,
    voix_duree: 30
);
```

---

### Message::create() - Mise à Jour

**Nouvelle signature**
```php
public function create(
    string  $contenu,
    int     $id_expediteur,
    int     $id_conversation,
    ?string $fichier_path          = null,
    ?string $fichier_nom_original  = null,
    ?string $fichier_type          = null,
    ?int    $fichier_taille        = null,
    ?string $type_message          = 'texte'
): bool
```

**Nouveau paramètre**
- `$type_message` (string) - Type de message: `'texte'`, `'fichier'`, ou `'voix'`

**Exemple**
```php
// Message texte
$messageModel->create('Bonjour!', 42, 7, type_message: 'texte');

// Message avec fichier
$messageModel->create(
    'Voici un document',
    42, 7,
    'uploads/messages/doc_123.pdf',
    'document.pdf',
    'application/pdf',
    150000,
    'fichier'
);

// Message vocal
$messageModel->create(
    '🎤 Message vocal',
    42, 7,
    'uploads/voice/voice_123.webm',
    'voice_message',
    'audio/webm',
    150000,
    'voix'
);
```

---

## Controller Methods

### MessageController::sendVoiceMessage()

**Description**
Endpoint AJAX pour envoyer un message vocal. Valide l'authentification, l'accès à la conversation, et traite l'upload du fichier audio.

**Réponse JSON**
```json
{
    "success": bool,
    "message": "string (description)",
    "voix_path": "string (chemin du fichier)" // Si succès
}
```

### MessageController::handleVoiceUpload()

**Description**
Traite l'upload d'un fichier audio. Valide le type MIME, l'extension, et la taille.

**Paramètres**
- `$file` (array) - Élément du tableau `$_FILES`

**Retour**
```php
[
    'path' => 'uploads/voice/voice_123.webm',
    'name' => 'voice_message.webm',
    'type' => 'audio/webm',
    'size' => 150000
]
// OU en cas d'erreur:
['error' => 'Message d\'erreur']
```

---

## Schéma Base de Données

### Table: messages (colonnes pertinentes)

```sql
ALTER TABLE messages ADD COLUMN type_message VARCHAR(20) DEFAULT 'texte' AFTER fichier_taille;
ALTER TABLE messages ADD COLUMN voix_duree INT DEFAULT 0 AFTER type_message;
CREATE INDEX idx_type_message ON messages(type_message);
```

### Valeurs possibles de type_message
- `'texte'` - Message texte uniquement
- `'fichier'` - Message avec fichier attaché
- `'voix'` - Message vocal

### Exemple de ligne en base

```sql
INSERT INTO messages (contenu, id_expediteur, id_conversation, fichier_path, fichier_nom_original, fichier_type, fichier_taille, type_message, voix_duree)
VALUES (
    '🎤 Message vocal',
    42,
    7,
    'uploads/voice/voice_507f1f77bcf86cd799439011_1620000000.webm',
    'voice_message',
    'audio/webm',
    150000,
    'voix',
    30
);
```

---

## Sessions et Authentification

### Authentification Requise
Tous les endpoints de messages vocaux requièrent:
- Session PHP active
- `$_SESSION['id_user']` défini
- ID utilisateur valide et positif

### Vérification d'Accès
L'utilisateur doit avoir accès à la conversation:
- Être `id_user1` OU `id_user2` dans la table `conversations`
- La conversation doit être active (`statut = 'active'`)

---

## Limites et Contraintes

### Limites Techniques
- **Taille max** : 5 MB par fichier audio
- **Durée max** : ~600 secondes (10 minutes) pratiquement
- **Format par défaut** : WebM
- **Codecs** : Dépend du navigateur (généralement Opus audio)

### Limites de Base de Données
- `voix_duree` : INT (max 2,147,483,647 secondes)
- `fichier_taille` : INT (max 2 GB)
- `type_message` : VARCHAR(20)

---

## Cache et Performance

### Optimisations
- Index sur `type_message` pour requêtes rapides
- Noms de fichiers uniques évitent les collisions
- Fichiers compressés (WebM Opus)

### Requête Optimisée
```sql
-- Récupérer tous les messages vocaux d'une conversation
SELECT * FROM messages
WHERE id_conversation = 7
  AND type_message = 'voix'
ORDER BY date_envoi DESC;
```

---

## Logging et Débogage

### Logs Recommandés

**PHP**
```php
error_log('Message vocal reçu: ' . $_FILES['voix']['name']);
error_log('Taille: ' . $_FILES['voix']['size'] . ' bytes');
error_log('Stocké à: ' . $uploadPath);
```

**JavaScript**
```javascript
console.log('Enregistrement démarré');
console.log('Audio blob:', window.recordedAudioBlob);
console.log('Durée:', window.recordedAudioDuration, 'secondes');
console.log('Réponse:', data);
```

---

## Sécurité

### Validations Mises en Place
- ✅ Vérification du type MIME avec `finfo_file()`
- ✅ Vérification de l'extension
- ✅ Vérification de la taille
- ✅ Authentification obligatoire
- ✅ Vérification d'accès à la conversation

### Recommandations Supplémentaires
- 🔐 HTTPS sur production
- 🔒 CORS configuré correctement
- 📝 Auditer les uploads régulièrement
- 🗑️ Nettoyer les vieux fichiers périodiquement

