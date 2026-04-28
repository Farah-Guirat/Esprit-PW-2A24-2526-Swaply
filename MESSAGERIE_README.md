# 🎯 Guide Complet - Système de Messagerie Swaply

## 📋 Fonctionnalités Implémentées

### ✅ 1. Conversations entre deux utilisateurs
- Aziz peut voir sa conversation avec Farah
- Farah peut voir sa conversation avec Aziz  
- Chaque utilisateur ne voit que **ses propres conversations**
- Messages triés chronologiquement dans chaque conversation

**Fichiers:** [view/Front/Messages.php](view/Front/Messages.php), [controller/MessageController.php](controller/MessageController.php)

### ✅ 2. Tri des conversations
Trois options de tri disponibles dans la sidebar:
- **📅 Plus récent** - Conversations les plus récentes d'abord
- **📅 Plus ancien** - Conversations les plus anciennes d'abord
- **🔤 A→Z** - Tri alphabétique croissant par nom
- **🔤 Z→A** - Tri alphabétique décroissant par nom

**Implémentation:** JavaScript côté client dans `sortConvs()` fonction

### ✅ 3. Indicateur "Typing" en temps réel
Quand quelqu'un tape un message, son interlocuteur voit:
- **"En train d'écrire"** avec animation de points
- Indicateur disparaît 3 secondes après l'arrêt de la saisie

**Architecture:**
- `controller/RealtimeController.php` - Gestion du statut typing
- Fichiers temporaires dans `tmp/` - Stockage du statut
- Polling AJAX toutes les 2 secondes

### ✅ 4. Statut en ligne/hors ligne en temps réel
Le header affiche:
- **🟢 En ligne** - Utilisateur actif dans les 30 dernières secondes
- **⚫ Hors ligne** - Avec "Vu il y a Xm/Xh"
- Mise à jour auto toutes les 2 secondes

**Architecture:** 
- `RealtimeController.php` gère la persistence du statut
- Fichiers `online_*.tmp` tracent la dernière activité
- Timeout: 30 secondes

### ✅ 5. Upload de fichiers/documents
- **Bouton 📎** dans le formulaire pour attacher un fichier
- Fichiers supportés: `.pdf`, `.doc`, `.docx`, `.xls`, `.xlsx`, `.ppt`, `.pptx`, `.txt`, `.zip`, `.jpg`, `.jpeg`, `.png`, `.gif`
- **Limite de taille:** 10 MB
- **Stockage:** Dossier `uploads/messages/`
- Affichage avec lien de téléchargement dans les messages

**Base de données:**
```sql
ALTER TABLE messages ADD COLUMN fichier_path VARCHAR(255) NULL;
ALTER TABLE messages ADD COLUMN fichier_nom_original VARCHAR(255) NULL;
ALTER TABLE messages ADD COLUMN fichier_type VARCHAR(50) NULL;
ALTER TABLE messages ADD COLUMN fichier_taille INT NULL;
```

### ✅ 6. Statistiques en back office
Accès via: `view/Front/back_office_stats.php`

**Statistiques affichées:**
- Total conversations et messages
- Moyenne de messages par conversation
- % de messages lus
- **Top 10 conversations les plus actives**
- **Top 10 utilisateurs les plus actifs**
- **Top 10 utilisateurs les plus sociaux** (plus de conversations)
- **Activité par jour** (derniers 30 jours) avec graphiques

---

## 🚀 Guide d'Installation

### 1️⃣ Préparer la Base de Données

Exécuter la migration:
```bash
php config/migrate_files.php
```

Ou manuellement via phpMyAdmin:
```sql
ALTER TABLE messages ADD COLUMN fichier_path VARCHAR(255) NULL;
ALTER TABLE messages ADD COLUMN fichier_nom_original VARCHAR(255) NULL;
ALTER TABLE messages ADD COLUMN fichier_type VARCHAR(50) NULL;
ALTER TABLE messages ADD COLUMN fichier_taille INT NULL;
```

### 2️⃣ Créer les dossiers nécessaires

```bash
mkdir -p uploads/messages
mkdir -p tmp
chmod 755 uploads/messages
chmod 755 tmp
```

### 3️⃣ Activer le Realtime Controller

Le fichier [controller/RealtimeController.php](controller/RealtimeController.php) gère:
- Les statuts "typing"
- Les statuts "en ligne"

Il est appelé automatiquement via AJAX par [view/Front/Messages.php](view/Front/Messages.php)

### 4️⃣ Tester les Fonctionnalités

#### Tester la conversation
1. Créer au moins 2 comptes utilisateur
2. Se connecter avec le 1er utilisateur
3. Créer une conversation avec le 2e utilisateur
4. Envoyer un message

#### Tester le typing
1. Ouvrir la conversation dans 2 onglets/navigateurs différents
2. Commencer à taper dans l'un
3. L'autre doit voir "En train d'écrire..."

#### Tester le statut en ligne
1. Garder une conversation ouverte
2. Observer l'indicateur de statut (🟢 En ligne)
3. Fermer l'onglet après 30 secondes
4. L'indicateur doit passer à ⚫ Hors ligne

#### Tester l'upload
1. Cliquer le bouton 📎 dans le formulaire
2. Sélectionner un fichier (max 10 MB)
3. Le fichier s'affiche dans l'aperçu
4. Envoyer le message
5. Le fichier doit être visible avec lien de téléchargement

#### Tester les statistiques
1. Aller à `/view/Front/back_office_stats.php`
2. Voir toutes les statistiques de conversations et messages

---

## 📁 Structure des Fichiers

### Fichiers modifiés:
- ✏️ [view/Front/Messages.php](view/Front/Messages.php) - Interface principale, intégration temps réel
- ✏️ [controller/MessageController.php](controller/MessageController.php) - Gestion des messages avec upload
- ✏️ [model/Message.php](model/Message.php) - Support fichiers dans la BD

### Nouveaux fichiers:
- ✨ [controller/RealtimeController.php](controller/RealtimeController.php) - Temps réel (typing, online)
- ✨ [view/Front/back_office_stats.php](view/Front/back_office_stats.php) - Statistiques back office
- ✨ [config/migrate_files.php](config/migrate_files.php) - Migration BD

### Dossiers créés:
- 📁 `uploads/messages/` - Fichiers uploadés
- 📁 `tmp/` - Fichiers temporaires (typing, online)

---

## 🔧 Configuration & Personnalisation

### Modifier la limite de taille de fichier
Dans [controller/MessageController.php](controller/MessageController.php) ligne ~80:
```php
$maxSize = 10 * 1024 * 1024; // 10 MB - Changer ici
```

### Modifier les extensions autorisées
Dans [controller/MessageController.php](controller/MessageController.php) ligne ~82:
```php
$allowedExts = ['pdf', 'doc', 'docx', ...]; // Ajouter/retirer extensions
```

### Modifier les timeouts (typing, online)
Dans [controller/RealtimeController.php](controller/RealtimeController.php) lignes 13-14:
```php
private const TYPING_TIMEOUT = 3;    // Secondes avant arrêt du typing
private const ONLINE_TIMEOUT = 30;   // Secondes pour rester online
```

### Modifier l'intervalle de polling (vérification)
Dans [view/Front/Messages.php](view/Front/Messages.php) ligne ~330:
```javascript
setInterval(() => {
    // Polling toutes les 2000ms (2 secondes)
}, 2000); // Changer ici
```

---

## 🎨 Interface Utilisateur

### Sidebar des Conversations
- ✏️ Recherche en temps réel
- 📊 Dropdown de tri (4 options)
- 👤 Avatar avec initiales
- 💬 Aperçu du dernier message
- 🔴 Badge nombre messages non lus

### Zone de Chat
- 💭 Bulles de message avec avatar
- ✓✓ Indicateur "Lu" (✓ non lu, ✓✓ lu)
- 🟢 Indicateur statut en ligne/hors ligne
- ⌨️ Indicateur "En train d'écrire..."
- 📎 Aperçu fichier attaché

### Formulaire d'envoi
- 📎 Bouton d'attachement avec aperçu
- ⌨️ Textarea avec redimensionnement auto
- ✉️ Bouton envoi (Entrée aussi)
- 📊 Compteur caractères

---

## 🔐 Sécurité & Validation

### Validations côté serveur:
✅ Vérification du MIME type des fichiers
✅ Vérification de l'extension
✅ Limite de taille fichier
✅ Vérification de l'accès (utilisateur doit être dans la conversation)
✅ Sanitization HTML (htmlspecialchars)
✅ Préparation des requêtes (PDO prepared statements)

### Permissions:
- Chaque utilisateur ne voit que SES conversations
- Seul l'expediteur peut modifier/supprimer son message
- Seul l'expediteur peut supprimer la conversation

---

## 📊 Base de Données

### Nouvelle structure (messages):
```sql
CREATE TABLE messages (
    id_message INT PRIMARY KEY AUTO_INCREMENT,
    contenu TEXT,
    id_expediteur INT,
    id_conversation INT,
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lu BOOLEAN DEFAULT 0,
    fichier_path VARCHAR(255) NULL,           -- NEW
    fichier_nom_original VARCHAR(255) NULL,   -- NEW
    fichier_type VARCHAR(50) NULL,            -- NEW
    fichier_taille INT NULL,                  -- NEW
    FOREIGN KEY (id_expediteur) REFERENCES utilisateurs(id_u),
    FOREIGN KEY (id_conversation) REFERENCES conversations(id_conversation)
);
```

---

## 🐛 Dépannage

### Les fichiers n'uploadent pas
1. Vérifier que les dossiers existent: `uploads/messages/`, `tmp/`
2. Vérifier les permissions: `chmod 755 uploads/messages/`
3. Vérifier la limite PHP upload_max_filesize dans `php.ini`

### Le typing ne s'affiche pas
1. Vérifier que le dossier `tmp/` existe et est accessible
2. Vérifier la console navigateur (F12) pour les erreurs AJAX
3. Vérifier que RealtimeController.php est accessible

### Le statut en ligne ne se met pas à jour
1. Les fichiers `online_*.tmp` doivent être accessibles
2. Vérifier que les 2 utilisateurs sont dans des sessions différentes
3. Attendre 30+ secondes après fermeture pour voir "Hors ligne"

### Les statistiques ne s'affichent pas
1. Vérifier l'accès à `back_office_stats.php`
2. S'assurer que des conversations/messages existent
3. Vérifier les erreurs PHP dans les logs

---

## 📱 Responsive Design

Toute l'interface s'adapte aux appareils mobiles:
- Layout 2 colonnes sur desktop
- Layout collapse sur mobile
- Buttons accessibles au toucher
- Emojis utilisés pour meilleure UX

---

## 🚀 Améliorations Possibles (Future)

- [ ] WebSocket au lieu du polling (temps réel plus rapide)
- [ ] Notifications push pour messages non lus
- [ ] Archivage des conversations
- [ ] Recherche full-text dans les messages
- [ ] Réactions aux messages (émojis)
- [ ] Messages avec édition et suppression d'images
- [ ] Groupe de conversations
- [ ] Chiffrement end-to-end
- [ ] Backup automatique des conversations
- [ ] API REST pour clients mobiles

---

## ✉️ Support

Pour toute question ou problème, consultez:
1. Les commentaires dans le code
2. Les fichiers README spécifiques dans chaque dossier
3. Les logs d'erreur PHP (`/var/log/apache2/error.log`)

**Bon développement! 🎉**
