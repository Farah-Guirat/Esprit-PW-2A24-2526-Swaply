# 📋 Guide - Système de Réactions aux Messages

## ✨ Fonctionnalités

Le système de réactions permet aux utilisateurs de réagir aux messages avec des emojis. Chaque utilisateur peut ajouter plusieurs emojis différents à un même message.

### ✅ Fonctionnalités Implémentées

1. **Ajouter une réaction** - Utilisateur peut réagir à un message avec un emoji
2. **Affichage des réactions** - Nombre de réactions par emoji avec indication de l'utilisateur
3. **Toggle de réaction** - Cliquer sur une réaction existante pour la retirer
4. **Picker d'emojis** - Interface pour sélectionner l'emoji de réaction
5. **Emojis autorisés** - 10 emojis populaires pour les réactions

### Emojis Disponibles
- 👍 (J'aime)
- ❤️ (Love)
- 😂 (Hilare)
- 😮 (Surprise)
- 😢 (Triste)
- 🔥 (Hot)
- 👎 (Je n'aime pas)
- 🤔 (Réfléchi)
- ✨ (Étincelant)
- 🎉 (Fête)

---

## 🚀 Installation & Configuration

### 1. Migration Base de Données

Exécuter le script de migration pour créer la table `message_reactions`:

```bash
php config/migrate_reactions.php
```

Ou intégrer dans votre script d'installation:
```batch
# Windows (install.bat)
php config\migrate_reactions.php
```

### 2. Structure de la Base de Données

La table `message_reactions` contient:
- `id_reaction` - Clé primaire
- `id_message` - Référence au message
- `id_user` - L'utilisateur qui a réagi
- `emoji` - L'emoji de réaction
- `date_creation` - Timestamp de création
- Contrainte UNIQUE: Un utilisateur ne peut avoir qu'une seule réaction avec un emoji par message

---

## 💻 API REST

### Endpoints disponibles

#### 1. Ajouter une réaction
```
POST controller/ReactionController.php
```

**Body:**
```json
{
  "action": "add",
  "id_message": 123,
  "emoji": "👍",
  "id_user": 456
}
```

**Réponse:**
```json
{
  "success": true,
  "reactions": [
    {
      "emoji": "👍",
      "count": 2,
      "users": "456,789"
    },
    {
      "emoji": "❤️",
      "count": 1,
      "users": "123"
    }
  ]
}
```

#### 2. Supprimer une réaction
```
POST controller/ReactionController.php
```

**Body:**
```json
{
  "action": "remove",
  "id_message": 123,
  "emoji": "👍",
  "id_user": 456
}
```

#### 3. Récupérer les réactions d'un message
```
GET controller/ReactionController.php?action=get&id_message=123
```

#### 4. Récupérer les réactions d'un utilisateur sur un message
```
GET controller/ReactionController.php?action=getUserReactions&id_message=123&id_user=456
```

#### 5. Obtenir les emojis autorisés
```
GET controller/ReactionController.php?action=getEmojis
```

---

## 🎨 Intégration dans la Vue

### 1. Inclure les fichiers

Dans votre fichier HTML (par exemple `view/Front/Messages.php`):

```html
<!-- CSS (optionnel, personnaliser selon vos besoins) -->
<style>
    .reaction-btn {
        background: none;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 4px 8px;
        cursor: pointer;
        font-size: 18px;
        transition: background-color 0.2s;
    }
    
    .reaction-btn:hover {
        background-color: #f0f0f0;
    }

    .reactions-container {
        margin-top: 8px;
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }
</style>

<!-- JavaScript -->
<script src="asset/js/reactions.js"></script>
```

### 2. Structure HTML pour les messages

```html
<div class="message-item" data-message-id="<?php echo $message['id_message']; ?>">
    <div class="message-content">
        <strong><?php echo htmlspecialchars($message['nom'] . ' ' . $message['prenom']); ?></strong>
        <p><?php echo htmlspecialchars($message['contenu']); ?></p>
    </div>

    <!-- Bouton de réaction -->
    <button class="reaction-btn" data-id-message="<?php echo $message['id_message']; ?>">
        😊 Réagir
    </button>

    <!-- Container pour les réactions -->
    <div class="reactions-container"></div>
</div>
```

### 3. Initialiser le gestionnaire

```html
<script>
    // L'ID de l'utilisateur actuel DOIT être défini
    var currentUserId = <?php echo $currentUserId ?? 0; ?>;
    
    // Les réactions se chargent automatiquement avec la classe ReactionManager
</script>
```

### 4. Charger les réactions au chargement des messages

```javascript
// Après avoir chargé les messages, charger les réactions
fetch('controller/MessageController.php?action=getByConversation&id=' + conversationId)
    .then(response => response.json())
    .then(data => {
        // Afficher les messages
        data.forEach(message => {
            // ... afficher le message ...
            
            // Charger les réactions
            if (window.reactionManager) {
                window.reactionManager.loadReactions(message.id_message);
            }
        });
    });
```

---

## 🔧 API PHP - Classe Reaction

### Utilisation en PHP

```php
require_once 'model/Reaction.php';

$reactionManager = new Reaction();

// Ajouter une réaction
$reactionManager->add($idMessage, $idUser, '👍');

// Supprimer une réaction
$reactionManager->remove($idMessage, $idUser, '👍');

// Récupérer les réactions d'un message
$reactions = $reactionManager->getByMessage($idMessage);
// Retour: [
//   ['emoji' => '👍', 'count' => 3, 'users' => '1,2,3'],
//   ['emoji' => '❤️', 'count' => 1, 'users' => '4']
// ]

// Récupérer les réactions d'un utilisateur
$userReactions = $reactionManager->getUserReactions($idMessage, $idUser);
// Retour: ['👍', '❤️']

// Vérifier si un utilisateur a réagi
$hasReacted = $reactionManager->hasReacted($idMessage, $idUser, '👍');

// Obtenir les emojis autorisés
$emojis = $reactionManager->getAllowedEmojis();
```

### Méthodes disponibles

| Méthode | Paramètres | Retour | Description |
|---------|-----------|--------|-------------|
| `add()` | idMessage, idUser, emoji | bool | Ajouter/mettre à jour une réaction |
| `remove()` | idMessage, idUser, emoji | bool | Supprimer une réaction |
| `getByMessage()` | idMessage | array | Toutes les réactions d'un message |
| `getUserReactions()` | idMessage, idUser | array | Les emojis auxquels a réagi l'utilisateur |
| `hasReacted()` | idMessage, idUser, emoji | bool | Vérifier si utilisateur a réagi |
| `getAllowedEmojis()` | - | array | Liste des emojis autorisés |
| `removeAll()` | idMessage | bool | Supprimer toutes les réactions d'un message |

---

## 📊 Personnalisation

### Ajouter/Modifier les emojis autorisés

Éditer la propriété `$allowedEmojis` dans [model/Reaction.php](model/Reaction.php):

```php
private array $allowedEmojis = ['👍', '❤️', '😂', '😮', '😢', '🔥', '👎', '🤔', '✨', '🎉', '🚀'];
```

### Personnaliser le CSS

Modifier les styles dans `reactions.js` ou créer un fichier CSS séparé:

```css
.emoji-picker {
    /* Personnaliser l'apparence du picker */
}

.reaction-badge {
    /* Personnaliser l'apparence des réactions */
}

.reaction-badge.user-reacted {
    /* Style quand l'utilisateur a réagi */
}
```

---

## 🐛 Dépannage

### Les réactions ne s'affichent pas
1. Vérifier que `currentUserId` est défini en JavaScript
2. Vérifier que la migration a été exécutée
3. Vérifier la console du navigateur pour les erreurs AJAX

### Erreur "Emoji non autorisé"
1. Utiliser uniquement les emojis de la liste autorisée
2. Vérifier que l'emoji est bien copié

### Problème de performance
- Les réactions sont requêtées par message via AJAX
- Considérer un système de cache côté client pour les conversations actives

---

## 📝 Fichiers Concernés

- [model/Reaction.php](model/Reaction.php) - Logique métier des réactions
- [controller/ReactionController.php](controller/ReactionController.php) - API REST
- [asset/js/reactions.js](asset/js/reactions.js) - Interface utilisateur
- [config/migrate_reactions.php](config/migrate_reactions.php) - Migration DB
- [model/Message.php](model/Message.php) - Méthodes pour récupérer les messages avec réactions

---

## 🎯 Cas d'Usage

```javascript
// Exemple complet d'utilisation
var currentUserId = 123;

// Ajouter une réaction au message #456 avec l'emoji 👍
reactionManager.addReaction(456, '👍');

// Supprimer la réaction
reactionManager.toggleReaction(456, '👍');

// Charger toutes les réactions d'un message
reactionManager.loadReactions(456);
```

---

## ✅ Tests & Vérification

Vérifier que la fonctionnalité fonctionne correctement:

1. ✓ Ajouter une réaction - doit s'afficher avec le compte = 1
2. ✓ Ajouter la même réaction depuis un autre compte - compte doit passer à 2
3. ✓ Cliquer sur la réaction - doit la supprimer
4. ✓ Le badge de réaction doit être en couleur bleue si l'utilisateur a réagi
5. ✓ Passer la souris sur le badge - couleur plus foncée

---

**Version:** 1.0  
**Date:** 2026-04-30
