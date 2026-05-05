# 🎤 Guide Complet - Messages Vocaux Swaply

## 📋 Fonctionnalités Implémentées

### ✅ 1. Enregistrement de messages vocaux
- **Bouton 🎤** dans le formulaire de messagerie pour enregistrer un message vocal
- **Visualisation du son** en direct avec un waveform animé
- **Durée automatique** de l'enregistrement
- **Format audio** : WebM (compatible tous navigateurs modernes)
- **Limite de taille** : 5 MB par message vocal

**Navigateurs supportés:**
- Chrome/Edge 47+
- Firefox 49+
- Safari 15+
- Opera 32+

### ✅ 2. Envoi de messages vocaux
- **Enregistrement et envoi** via AJAX
- **Pas de rechargement** nécessaire après envoi
- **Stockage sécurisé** dans `uploads/voice/`
- **Métadonnées** stockées en base de données

### ✅ 3. Lecture de messages vocaux
- **Lecteur audio intégré** dans le chat
- **Contrôles de lecture** (play/pause)
- **Affichage de la durée** du message vocal
- **Affichage de la taille** du fichier audio

### ✅ 4. Gestion des messages vocaux
- Messages vocaux distingués des messages texte et fichiers
- Colonne `type_message` pour identifier le type
- Colonne `voix_duree` pour stocker la durée
- Index pour optimiser les requêtes

---

## 🚀 Guide d'Installation

### 1️⃣ Préparer la Base de Données

Exécuter la migration:
```bash
php config/migrate_voice_messages.php
```

Ou manuellement via phpMyAdmin:
```sql
ALTER TABLE messages ADD COLUMN type_message VARCHAR(20) DEFAULT 'texte' AFTER fichier_taille;
ALTER TABLE messages ADD COLUMN voix_duree INT DEFAULT 0 AFTER type_message;
CREATE INDEX idx_type_message ON messages(type_message);
```

### 2️⃣ Créer les dossiers nécessaires

```bash
mkdir -p uploads/voice
chmod 755 uploads/voice
```

### 3️⃣ Vérifier les permissions

Le serveur web doit avoir les permissions d'écriture:
- `uploads/voice/` : lecture/écriture pour stocker les fichiers audio
- `tmp/` : déjà créé pour les fichiers temporaires

---

## 💻 Architecture Technique

### Base de Données
```sql
CREATE TABLE messages (
    id_message INT PRIMARY KEY AUTO_INCREMENT,
    contenu TEXT NOT NULL,
    id_expediteur INT NOT NULL,
    id_conversation INT NOT NULL,
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lu BOOLEAN DEFAULT 0,
    
    -- Colonnes pour fichiers/voix
    fichier_path VARCHAR(255) NULL,
    fichier_nom_original VARCHAR(255) NULL,
    fichier_type VARCHAR(50) NULL,
    fichier_taille INT NULL,
    
    -- Nouvelles colonnes pour messages vocaux
    type_message VARCHAR(20) DEFAULT 'texte',
    voix_duree INT DEFAULT 0,
    
    INDEX idx_type_message (type_message),
    FOREIGN KEY (id_expediteur) REFERENCES utilisateurs(id_u),
    FOREIGN KEY (id_conversation) REFERENCES conversations(id_conversation)
);
```

### Modèle (`model/Message.php`)
```php
// Créer un message vocal
$messageModel->createVoiceMessage(
    voix_path: 'uploads/voice/voice_123.webm',
    id_expediteur: $id_user,
    id_conversation: $id_conv,
    voix_nom_original: 'voice_message',
    voix_type: 'audio/webm',
    voix_taille: 150000,
    voix_duree: 30  // en secondes
);

// Créer un message texte (aucun changement)
$messageModel->create($contenu, $id_user, $id_conv);

// Créer un message avec fichier (aucun changement)
$messageModel->create(
    $contenu, $id_user, $id_conv,
    $fichier_path, $fichier_nom_original,
    $fichier_type, $fichier_taille
);
```

### Contrôleur (`controller/MessageController.php`)
```php
// Route AJAX pour envoyer un message vocal
GET/POST /controller/MessageController.php?action=sendVoice

// Paramètres POST:
// - id_conversation: int
// - voix: Blob (fichier audio)
// - duree: int (secondes)

// Réponse JSON:
{
    "success": true,
    "message": "Message vocal envoyé",
    "voix_path": "uploads/voice/voice_123.webm"
}
```

### Vue (`view/Front/Messages.php`)
- **Bouton d'enregistrement** 🎤 dans la barre d'outils
- **Prévisualisation** avec waveform animé
- **Lecteur audio** intégré pour écouter les messages vocaux
- **Affichage des contrôles** : play/pause, durée

---

## 🎯 Flux d'Utilisation

### Pour envoyer un message vocal:
1. Cliquer sur le bouton 🎤
2. **Accorder les permissions** d'accès au microphone (navigateur demande)
3. Parler dans le microphone
4. Cliquer à nouveau sur 🎤 (ou attendre que l'enregistrement s'arrête)
5. Le waveform s'affiche avec la durée
6. Cliquer sur **Envoyer** (✓ en bas à droite)
7. Le message vocal est envoyé et affiché

### Pour écouter un message vocal:
1. Voir le lecteur audio dans la conversation
2. Cliquer sur ▶ pour écouter
3. ⏸ s'affiche pendant la lecture
4. La durée et la taille sont affichées

---

## 🔧 Configuration

### Types MIME supportés
```php
'audio/webm'    // Recommandé
'audio/mpeg'    // MP3
'audio/wav'     // WAV
'audio/ogg'     // Ogg Vorbis
'audio/mp4'     // M4A/AAC
'audio/x-m4a'   // M4A alternativ
```

### Formats de fichier supportés
- `.webm` (recommandé - compression WebM)
- `.mp3` (MPEG Audio)
- `.wav` (WAV brut)
- `.ogg` (Ogg Vorbis)
- `.m4a` (MPEG-4 Audio)

### Limites
- **Taille max** : 5 MB par message vocal
- **Format par défaut** : WebM (MediaRecorder)
- **Qualité audio** : Dépend du navigateur

---

## ⚙️ Stockage des Fichiers

### Structure des dossiers
```
uploads/
├── voice/              # Nouveaux messages vocaux
│   ├── voice_123456_1234567890.webm
│   ├── voice_234567_1234567891.webm
│   └── voice_345678_1234567892.webm
├── messages/           # Fichiers attachés
│   └── ...
```

### Nommage des fichiers
- Format : `voice_<uniqid>_<timestamp>.<ext>`
- Exemple : `voice_507f1f77bcf86cd799439011_1620000000.webm`
- Garantit l'unicité et permet la suppression sécurisée

---

## 🛡️ Sécurité

### Validations
- ✅ Vérification du type MIME avec `finfo_file()`
- ✅ Vérification de l'extension de fichier
- ✅ Vérification de la taille (5 MB max)
- ✅ Authentification utilisateur requise
- ✅ Vérification d'accès à la conversation

### Stockage
- 📁 Fichiers stockés **en dehors** de la racine web (uploads/)
- 🔐 Noms de fichiers uniques et aléatoires
- 🗂️ Permissions correctes (755)

---

## 📊 Statistiques

Les statistiques du back office comprennent maintenant:
- Nombre total de messages
- **Nombre de messages vocaux** (type = 'voix')
- **Nombre de fichiers** (type = 'fichier')
- **Nombre de messages texte** (type = 'texte')

---

## 🐛 Dépannage

### Le bouton 🎤 n'apparaît pas
- ✓ Vérifier que les colonnes sont ajoutées en base
- ✓ Rafraîchir la page (Ctrl+F5)

### Erreur "Impossible d'accéder au microphone"
- ✓ Vérifier les permissions du navigateur
- ✓ HTTPS requis sur production (HTTP sur localhost ok)
- ✓ Essayer un autre navigateur

### Le fichier audio ne s'envoie pas
- ✓ Vérifier que `uploads/voice/` existe et a les bonnes permissions
- ✓ Vérifier l'espace disque
- ✓ Voir les logs du serveur

### Le lecteur audio ne fonctionne pas
- ✓ Vérifier que le fichier existe dans `uploads/voice/`
- ✓ Vérifier les permissions du fichier (644)
- ✓ Essayer un autre navigateur

---

## 📝 Exemples de Code

### Créer un message vocal en PHP
```php
$messageModel = new Message();
$success = $messageModel->createVoiceMessage(
    voix_path: 'uploads/voice/voice_123.webm',
    id_expediteur: 42,
    id_conversation: 7,
    voix_nom_original: 'voice_message',
    voix_type: 'audio/webm',
    voix_taille: 150000,
    voix_duree: 45
);
```

### Récupérer les messages vocaux d'une conversation
```php
$messages = $messageModel->getByConversation(7);

foreach ($messages as $msg) {
    if ($msg['type_message'] === 'voix') {
        echo "Message vocal de " . $msg['prenom'] . " (" . $msg['voix_duree'] . "s)";
        echo "<audio controls>";
        echo "<source src='../../" . $msg['fichier_path'] . "' type='" . $msg['fichier_type'] . "'>";
        echo "</audio>";
    }
}
```

### JavaScript - Envoyer un message vocal
```javascript
const formData = new FormData();
formData.append('id_conversation', 42);
formData.append('voix', recordedAudioBlob, 'voice_message.webm');
formData.append('duree', 45);

fetch('/controller/MessageController.php?action=sendVoice', {
    method: 'POST',
    body: formData
})
.then(r => r.json())
.then(data => console.log(data));
```

---

## 📈 Améliorations Futures

### À considérer:
- 🔄 Support de la transcription vocale (speech-to-text)
- 🎙️ Amélioration de la qualité audio
- 📊 Compteur de messages vocaux
- 🔔 Notifications pour messages vocaux
- 📱 Interface mobile optimisée
- 🎵 Support de plusieurs formats audio

---

## 📞 Support

Pour toute question sur les messages vocaux, consultez:
- [Documentation WebRTC API](https://developer.mozilla.org/en-US/docs/Web/API/WebRTC_API)
- [Documentation MediaRecorder](https://developer.mozilla.org/en-US/docs/Web/API/MediaRecorder)
- [Audio HTML5](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/audio)

