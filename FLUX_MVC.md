# 🔄 FLUX COMPLET - Architecture MVC de Swaply

## 📱 Scénario 1: Envoi d'un Message

```
┌─────────────────────────────────────────────────────────────────────┐
│ UTILISATEUR - Front Office (FrontOffice)                            │
├─────────────────────────────────────────────────────────────────────┤
│ 1. Voir conversation ouverte dans Messages.php                      │
│ 2. Remplir le formulaire avec du texte                              │
│ 3. Cliquer "Envoyer" → POST vers Controller                         │
└─────────────────────────────────────────────────────────────────────┘
                              │
                              ↓
                    HTTP POST ENVOYÉ
                              │
                              ↓
┌─────────────────────────────────────────────────────────────────────┐
│ CONTROLLER - MessageController.php::sendMessage()                   │
├─────────────────────────────────────────────────────────────────────┤
│ ACTION 1: Récupérer données du formulaire                           │
│ ├─ $contenu = trim($_POST['contenu'])                              │
│ ├─ $id_conversation = (int)$_POST['id_conversation']               │
│ └─ $id_user = $this->getUserId() from SESSION                      │
│                                                                     │
│ ACTION 2: VALIDATION PHP CÔTÉ SERVEUR ✅                           │
│ ├─ if ($contenu === '' && empty($_FILES['fichier']))              │
│ │   → $errors[] = "Le message ne peut pas être vide."             │
│ ├─ if (strlen($contenu) > 2000)                                   │
│ │   → $errors[] = "Dépasse 2000 caractères"                       │
│ ├─ if ($id_conversation <= 0)                                     │
│ │   → $errors[] = "Conversation invalide"                         │
│ ├─ if (!$conv || !userInConversation)                             │
│ │   → $errors[] = "Accès refusé à cette conversation"             │
│ └─ if ($_FILES['fichier']) → handleFileUpload() with MIME check   │
│                                                                     │
│ ACTION 3: SI PAS D'ERREURS → Appeler Model                        │
│ └─ $this->messageModel->create($contenu, $id_user, ...)           │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
                              │
                              ↓
┌─────────────────────────────────────────────────────────────────────┐
│ MODEL - Message.php::create()                                       │
├─────────────────────────────────────────────────────────────────────┤
│ PREPARE SQL                                                         │
│ ├─ $stmt = $this->pdo->prepare(                                    │
│ │   "INSERT INTO messages (...)                                    │
│ │    VALUES (:contenu, :id_expediteur, :id_conversation, ...)"    │
│ │ )                                                                │
│ └─ // ✅ PDO Prepared Statement - Pas d'injection SQL possible    │
│                                                                     │
│ EXECUTE                                                             │
│ └─ return $stmt->execute([                                         │
│      ':contenu' => $contenu,        // Sécurisé                   │
│      ':id_expediteur' => $id_user,  // Sécurisé                   │
│      ':id_conversation' => $id_conv // Sécurisé                   │
│    ])                                                              │
│                                                                     │
│ RÉSULTAT: ✅ Message inséré en BD                                  │
└─────────────────────────────────────────────────────────────────────┘
                              │
                              ↓
┌─────────────────────────────────────────────────────────────────────┐
│ CONTROLLER - Redirection                                            │
├─────────────────────────────────────────────────────────────────────┤
│ $this->redirectFront("?id=$id_conversation");                       │
│ → header('Location: ../view/Front/Messages.php?id=...')             │
└─────────────────────────────────────────────────────────────────────┘
                              │
                              ↓
┌─────────────────────────────────────────────────────────────────────┐
│ CONTROLLER - Nouvel appel showConversation()                        │
├─────────────────────────────────────────────────────────────────────┤
│ 1. Vérifier accès utilisateur                                       │
│ 2. Appeler Model: getByConversation()                               │
│ 3. Charger VIEW avec les données                                    │
└─────────────────────────────────────────────────────────────────────┘
                              │
                              ↓
┌─────────────────────────────────────────────────────────────────────┐
│ MODEL - Message.php::getByConversation()                            │
├─────────────────────────────────────────────────────────────────────┤
│ PREPARE SQL                                                         │
│ ├─ $stmt = $this->pdo->prepare(                                    │
│ │   "SELECT m.*, u.nom, u.prenom                                   │
│ │    FROM messages m                                               │
│ │    JOIN utilisateurs u ON ...                                    │
│ │    WHERE m.id_conversation = :id                                 │
│ │    ORDER BY m.date_envoi ASC"                                    │
│ │ )                                                                │
│                                                                     │
│ EXECUTE                                                             │
│ ├─ $stmt->execute([':id' => $id_conversation])                     │
│                                                                     │
│ FETCH ALL                                                           │
│ └─ return $stmt->fetchAll()  // ✅ PDO::FETCH_ASSOC                │
│    [                                                               │
│      ['id_message' => 1, 'contenu' => 'Bonjour!', ...],            │
│      ['id_message' => 2, 'contenu' => 'Salut!', ...],              │
│      ...                                                           │
│    ]                                                               │
└─────────────────────────────────────────────────────────────────────┘
                              │
                              ↓
┌─────────────────────────────────────────────────────────────────────┐
│ VIEW - Messages.php                                                 │
├─────────────────────────────────────────────────────────────────────┤
│ Reçoit: $messages = [array des messages du Model]                   │
│                                                                     │
│ AFFICHAGE                                                           │
│ ├─ foreach ($messages as $msg):                                    │
│ │   <div class="msg-bubble">                                      │
│ │     <?= htmlspecialchars($msg['contenu']) ?>  ✅ XSS Protection │
│ │   </div>                                                        │
│ │                                                                 │
│ │   <?php if (!empty($msg['fichier_path'])): ?>                  │
│ │     <a href="<?= $msg['fichier_path'] ?>" download>📎</a>      │
│ │   <?php endif; ?>                                              │
│ └─                                                                │
│                                                                     │
│ RÉSULTAT: Page HTML rendue au navigateur                            │
└─────────────────────────────────────────────────────────────────────┘
                              │
                              ↓
┌─────────────────────────────────────────────────────────────────────┐
│ UTILISATEUR - Page mis à jour ✅                                    │
├─────────────────────────────────────────────────────────────────────┤
│ 1. Voit le nouveau message affiché                                  │
│ 2. Peut cliquer pour télécharger le fichier si joint                │
│ 3. Voit l'indicateur "✓✓" (message lu)                              │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 🔄 Scénario 2: Modification d'un Message (Back Office)

```
┌─────────────────────────────────────────────────────────────────────┐
│ ADMIN - Back Office (BackOffice)                                    │
├─────────────────────────────────────────────────────────────────────┤
│ 1. Voir liste des messages dans view/Back/messages.php              │
│ 2. Cliquer bouton "Éditer" sur un message                           │
│ 3. Voir formulaire pré-rempli avec le contenu                       │
│ 4. Modifier le texte                                                │
│ 5. Cliquer "Enregistrer"                                            │
└─────────────────────────────────────────────────────────────────────┘
                              │
                              ↓
┌─────────────────────────────────────────────────────────────────────┐
│ CONTROLLER - MessageController.php::editBack()                      │
├─────────────────────────────────────────────────────────────────────┤
│ ACTION 1: Si GET → Récupérer message                               │
│ └─ $message = $this->messageModel->getById($id)                    │
│                                                                     │
│ ACTION 2: Si POST → Valider et modifier                            │
│ ├─ $contenu = trim($_POST['contenu'])                              │
│ ├─ VALIDATION:                                                      │
│ │  if (strlen($contenu) > 2000)                                    │
│ │    → $errors[] = "Trop long"                                     │
│ ├─ Appeler Model: update($id, $contenu)                            │
│ └─ Rediriger vers liste messages                                   │
│                                                                     │
│ ACTION 3: Charger Vue                                               │
│ └─ require __DIR__ . '/../view/Back/edit_message.php';             │
└─────────────────────────────────────────────────────────────────────┘
                              │
                              ↓
┌─────────────────────────────────────────────────────────────────────┐
│ MODEL - Message.php::update()                                       │
├─────────────────────────────────────────────────────────────────────┤
│ UPDATE SQL                                                          │
│ ├─ $stmt = $this->pdo->prepare(                                    │
│ │   "UPDATE messages SET contenu = :contenu WHERE id_message = :id"│
│ │ )                                                                │
│ └─ // ✅ Prepared Statement - SQL injection impossible             │
│                                                                     │
│ EXECUTE                                                             │
│ └─ $stmt->execute([':contenu' => $contenu, ':id' => $id])          │
│                                                                     │
│ RÉSULTAT: Message mis à jour en BD ✅                              │
└─────────────────────────────────────────────────────────────────────┘
                              │
                              ↓
┌─────────────────────────────────────────────────────────────────────┐
│ VIEW - view/Back/messages.php (Rafraîchie)                          │
├─────────────────────────────────────────────────────────────────────┤
│ Affiche liste mise à jour avec message modifié                      │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 🔍 Scénario 3: Suppression en Back Office

```
┌─────────────────────────────────────────────────────────────────────┐
│ ADMIN - BackOffice (view/Back/messages.php)                         │
├─────────────────────────────────────────────────────────────────────┤
│ 1. Voir message malveillant/spam dans la liste                      │
│ 2. Cliquer bouton "🗑️ Supprimer"                                    │
│ 3. Confirmer suppression dans modal                                 │
└─────────────────────────────────────────────────────────────────────┘
                              │
                              ↓
┌─────────────────────────────────────────────────────────────────────┐
│ CONTROLLER - MessageController.php::deleteBack()                    │
├─────────────────────────────────────────────────────────────────────┤
│ 1. Récupérer ID du message: $id = (int)$_GET['id']                 │
│ 2. Appeler Model: delete($id)                                       │
│ 3. Rediriger: header('Location: ../view/Back/messages.php?deleted'  │
└─────────────────────────────────────────────────────────────────────┘
                              │
                              ↓
┌─────────────────────────────────────────────────────────────────────┐
│ MODEL - Message.php::delete()                                       │
├─────────────────────────────────────────────────────────────────────┤
│ DELETE SQL                                                          │
│ ├─ $stmt = $this->pdo->prepare(                                    │
│ │   "DELETE FROM messages WHERE id_message = :id"                  │
│ │ )                                                                │
│ └─ // ✅ Prepared Statement sécurisé                               │
│                                                                     │
│ EXECUTE                                                             │
│ └─ $stmt->execute([':id' => $id])                                  │
│                                                                     │
│ RÉSULTAT: Message supprimé de la BD ✅                             │
└─────────────────────────────────────────────────────────────────────┘
                              │
                              ↓
┌─────────────────────────────────────────────────────────────────────┐
│ VIEW - messages.php Rafraîchie                                      │
├─────────────────────────────────────────────────────────────────────┤
│ Alert success: "Message supprimé avec succès"                       │
│ Message disparu de la liste                                         │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 📊 Tableau Récapitulatif des Flux

| Opération | View | Controller | Model | BD | Status |
|-----------|------|------------|-------|-----|--------|
| **CREATE** | Form POST | sendMessage() | create() | INSERT | ✅ |
| **READ** | Display | showConversation() | getByConversation() | SELECT | ✅ |
| **UPDATE** | Form POST | editMessage() | update() | UPDATE | ✅ |
| **DELETE** | Confirm | deleteMessage() | delete() | DELETE | ✅ |

---

## 🔐 Sécurité Appliquée à Chaque Étape

### ✅ Step 1: Input Validation (Controller)
```php
// Avant toute opération BD
if (strlen($contenu) > 2000) {
    $errors[] = "Message trop long";  // ✅ Validation
}
```

### ✅ Step 2: Type Safety
```php
$id_conversation = (int)$_POST['id_conversation'];  // ✅ Force entier
$contenu = trim($_POST['contenu']);                  // ✅ Trim espaces
```

### ✅ Step 3: PDO Prepared Statements
```php
// ✅ Variables substituées de manière sécurisée
$stmt->execute([':id' => $id, ':contenu' => $contenu]);
```

### ✅ Step 4: Output Escaping (View)
```php
<?= htmlspecialchars($msg['contenu']) ?>  // ✅ XSS Prevention
```

### ✅ Step 5: Access Control
```php
// Vérifier que l'utilisateur a accès
if ($message['id_expediteur'] != $id_user) {
    $this->redirectFront();  // ✅ Accès refusé
}
```

---

## 📁 Arborescence Finale

```
swaply/
├── 🔧 config/
│   ├── database.php           ✅ PDO Singleton
│   └── migrate_files.php      ✅ Migration BD
│
├── 🏗️ model/
│   ├── Message.php            ✅ CRUD Messages
│   ├── Conversation.php       ✅ CRUD Conversations
│   └── RealtimeController.php ✅ Status real-time
│
├── 🎮 controller/
│   ├── MessageController.php           ✅ Front + Back
│   ├── ConversationController.php      ✅ Front + Back
│   └── RealtimeController.php          ✅ AJAX endpoints
│
├── 👁️ view/
│   ├── Front/
│   │   ├── Messages.php               ✅ Messaging UI
│   │   ├── ajouter_message.php        ✅ New conversation
│   │   ├── edit_message.php           ✅ Edit message
│   │   └── back_office_stats.php      ✅ Statistics
│   │
│   └── Back/
│       ├── index.php                  ✅ Dashboard
│       ├── messages.php               ✅ List messages
│       ├── conversations.php          ✅ List conversations
│       ├── edit_message.php           ✅ Edit (admin)
│       └── view_conversation.php      ✅ Detail
│
├── 🎨 asset/
│   ├── css/
│   │   └── style.css
│   └── js/
│       ├── validation.js
│       └── script.js
│
├── 📂 uploads/
│   └── messages/              ✅ Fichiers uploadés
│
├── 📂 tmp/
│   └── (typing, online files) ✅ Real-time tracking
│
└── 📝 Documents:
    ├── MESSAGERIE_README.md              ✅ Documentation
    ├── VERIFICATION_CRITERES.md          ✅ Checklist
    ├── PREUVES_CODE.md                   ✅ Code samples
    └── FLUX_MVC.md                       ✅ Ce fichier
```

---

## ✅ Validation Complète MVC

```
┌─────────────────────────────────────────────────────┐
│ USER INPUT                                          │
│ (form, click, etc)                                  │
└────────────┬────────────────────────────────────────┘
             │
             ↓
┌─────────────────────────────────────────────────────┐
│ VIEW (view/Front/*.php)                             │
│ ✅ HTML/CSS display only                            │
│ ✅ No database queries                              │
│ ✅ No business logic                                │
│ ✅ Receives data from Controller                    │
└────────────┬────────────────────────────────────────┘
             │
             ↓
┌─────────────────────────────────────────────────────┐
│ CONTROLLER (controller/*.php)                       │
│ ✅ Validates input (PHP, not HTML5)                 │
│ ✅ Calls Model methods                              │
│ ✅ Handles file uploads                             │
│ ✅ Orchestrates flow                                │
└────────────┬────────────────────────────────────────┘
             │
             ↓
┌─────────────────────────────────────────────────────┐
│ MODEL (model/*.php)                                 │
│ ✅ Database queries only                            │
│ ✅ PDO prepared statements                          │
│ ✅ No validation (trusts Controller)                │
│ ✅ CRUD operations                                  │
└────────────┬────────────────────────────────────────┘
             │
             ↓
┌─────────────────────────────────────────────────────┐
│ DATABASE (MySQL via PDO)                            │
│ ✅ ACID transactions                                │
│ ✅ Data persistence                                 │
│ ✅ Query execution                                  │
└────────────┬────────────────────────────────────────┘
             │
             ↓
┌─────────────────────────────────────────────────────┐
│ MODEL Returns Data                                  │
│ (array of results)                                  │
└────────────┬────────────────────────────────────────┘
             │
             ↓
┌─────────────────────────────────────────────────────┐
│ CONTROLLER Processes Result                         │
│ (redirect, etc)                                     │
└────────────┬────────────────────────────────────────┘
             │
             ↓
┌─────────────────────────────────────────────────────┐
│ VIEW Renders HTML                                   │
│ (with data from Model)                              │
└────────────┬────────────────────────────────────────┘
             │
             ↓
┌─────────────────────────────────────────────────────┐
│ USER SEES RESULT ✅                                 │
└─────────────────────────────────────────────────────┘
```

---

*Architecture MVC Complete - Projet Swaply - 25/04/2026*
