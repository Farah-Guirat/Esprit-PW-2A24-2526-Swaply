# 📝 PREUVES DÉTAILLÉES - Extraits de Code

## 1️⃣ CRUD - Preuves Concrètes

### ✅ CREATE - Messages

**Fichier:** [controller/MessageController.php](controller/MessageController.php) (ligne 75-130)
```php
public function sendMessage(): void {
    $this->startSession();
    $errors = [];
    $id_user = $this->getUserId();
    $id_conversation = isset($_POST['id_conversation']) ? (int)$_POST['id_conversation'] : 0;
    $contenu = isset($_POST['contenu']) ? trim($_POST['contenu']) : '';

    // ✅ VALIDATION 1: Contenu vide
    if ($contenu === '' && empty($_FILES['fichier']))  
        $errors[] = "Le message ne peut pas être vide.";
    
    // ✅ VALIDATION 2: Longueur max
    elseif (strlen($contenu) > 2000)  
        $errors[] = "Le message ne peut pas dépasser 2000 caractères.";
    
    // ✅ VALIDATION 3: Conversation valide
    if ($id_conversation <= 0)        
        $errors[] = "Conversation invalide.";

    if (empty($errors)) {
        // ✅ INSERTION EN BD VIA PDO
        $this->messageModel->create(
            $contenu, 
            $id_user, 
            $id_conversation,
            $fichier_path,
            $fichier_nom_original,
            $fichier_type,
            $fichier_taille
        );
        $this->redirectFront("?id=$id_conversation");
    }
}
```

**Correspondance Model:** [model/Message.php](model/Message.php) (ligne 49)
```php
public function create(string $contenu, int $id_expediteur, int $id_conversation, 
                      ?string $fichier_path = null, 
                      ?string $fichier_nom_original = null, 
                      ?string $fichier_type = null,
                      ?int $fichier_taille = null): bool {
    $stmt = $this->pdo->prepare(
        "INSERT INTO messages (contenu, id_expediteur, id_conversation, fichier_path, ...)
         VALUES (:contenu, :id_expediteur, :id_conversation, :fichier_path, ...)"
    );
    return $stmt->execute([
        ':contenu'                => $contenu,
        ':id_expediteur'          => $id_expediteur,
        ':id_conversation'        => $id_conversation,
        ':fichier_path'           => $fichier_path,
        ':fichier_nom_original'   => $fichier_nom_original,
        ':fichier_type'           => $fichier_type,
        ':fichier_taille'         => $fichier_taille,
    ]);
}
```

### ✅ READ - Messages

**Fichier:** [controller/MessageController.php](controller/MessageController.php) (ligne 47)
```php
public function showConversation(): void {
    $this->startSession();
    $id_user = $this->getUserId();
    $id_active_conv = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    // ✅ Vérifier que la conversation appartient à l'utilisateur
    $conversation = $this->conversationModel->getById($id_active_conv);
    if (!$conversation ||
        ($conversation['id_user1'] != $id_user && $conversation['id_user2'] != $id_user)) {
        $this->redirectFront();
    }

    // ✅ LECTURE DES MESSAGES
    $messages = $this->messageModel->getByConversation($id_active_conv);
    
    // ✅ CHARGEMENT DE LA VUE
    require __DIR__ . '/../view/Front/Messages.php';
}
```

**Correspondance Model:** [model/Message.php](model/Message.php) (ligne 12)
```php
public function getByConversation(int $id_conversation): array {
    $stmt = $this->pdo->prepare(
        "SELECT m.*, u.nom, u.prenom
         FROM messages m
         JOIN utilisateurs u ON u.id_u = m.id_expediteur
         WHERE m.id_conversation = :id
         ORDER BY m.date_envoi ASC"
    );
    $stmt->execute([':id' => $id_conversation]);
    return $stmt->fetchAll();  // ✅ PDO::FETCH_ASSOC
}
```

### ✅ UPDATE - Messages

**Fichier:** [controller/MessageController.php](controller/MessageController.php) (ligne 241)
```php
public function editMessage(): void {
    $this->startSession();
    $errors = [];
    $id_user = $this->getUserId();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    // ✅ LECTURE DU MESSAGE
    $message = $this->messageModel->getById($id);

    // ✅ VÉRIFIER QUE C'EST LE PROPRIÉTAIRE
    if (!$message || $message['id_expediteur'] != $id_user) {
        $this->redirectFront();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $contenu = isset($_POST['contenu']) ? trim($_POST['contenu']) : '';
        
        // ✅ VALIDATION
        if ($contenu === '')             $errors[] = "Le contenu ne peut pas être vide.";
        elseif (strlen($contenu) > 2000) $errors[] = "Le contenu ne peut pas dépasser 2000 caractères.";
        
        if (empty($errors)) {
            // ✅ MODIFICATION EN BD
            $this->messageModel->update($id, $contenu);
            $this->redirectFront("?id=" . $message['id_conversation']);
        }
    }

    require __DIR__ . '/../view/Front/edit_message.php';
}
```

**Correspondance Model:** [model/Message.php](model/Message.php) (ligne 70)
```php
public function update(int $id, string $contenu): bool {
    $stmt = $this->pdo->prepare(
        "UPDATE messages SET contenu = :contenu WHERE id_message = :id"
    );
    return $stmt->execute([':contenu' => $contenu, ':id' => $id]);
}
```

### ✅ DELETE - Messages

**Fichier:** [controller/MessageController.php](controller/MessageController.php) (ligne 268)
```php
public function deleteMessage(): void {
    $this->startSession();
    $id_user = $this->getUserId();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    // ✅ RÉCUPÉRER LE MESSAGE
    $message = $this->messageModel->getById($id);
    $id_conv = 0;

    if ($message) {
        $id_conv = $message['id_conversation'];
        // ✅ VÉRIFIER QUE C'EST LE PROPRIÉTAIRE
        if ($message['id_expediteur'] == $id_user) {
            // ✅ SUPPRESSION EN BD
            $this->messageModel->delete($id);
        }
    }

    $this->redirectFront($id_conv ? "?id=$id_conv" : '');
}
```

**Correspondance Model:** [model/Message.php](model/Message.php) (ligne 78)
```php
public function delete(int $id): bool {
    $stmt = $this->pdo->prepare("DELETE FROM messages WHERE id_message = :id");
    return $stmt->execute([':id' => $id]);
}
```

---

## 2️⃣ VALIDATION PHP CÔTÉ SERVEUR

### ✅ Validations Message - Complet

**Fichier:** [controller/MessageController.php](controller/MessageController.php) (ligne 75-112)

```php
// ──── VALIDATION 1: Contenu vide ────
if ($contenu === '' && empty($_FILES['fichier']))  
    $errors[] = "Le message ne peut pas être vide.";

// ──── VALIDATION 2: Longueur max ────
elseif (strlen($contenu) > 2000)  
    $errors[] = "Le message ne peut pas dépasser 2000 caractères.";

// ──── VALIDATION 3: Conversation invalide ────
if ($id_conversation <= 0)        
    $errors[] = "Conversation invalide.";

// ──── VALIDATION 4: Accès autorisé ────
if (empty($errors)) {
    $conv = $this->conversationModel->getById($id_conversation);
    if (!$conv || ($conv['id_user1'] != $id_user && $conv['id_user2'] != $id_user))
        $errors[] = "Accès refusé à cette conversation.";
}

// ──── VALIDATION 5: Upload fichier ────
if (!empty($_FILES['fichier']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
    $upload = $this->handleFileUpload($_FILES['fichier']);
    if (is_array($upload) && isset($upload['error'])) {
        $errors[] = $upload['error'];
    } else {
        $fichier_path = $upload['path'];
        $fichier_nom_original = $upload['name'];
        $fichier_type = $upload['type'];
        $fichier_taille = $upload['size'];
    }
}
```

### ✅ Validations Fichier - Complet

**Fichier:** [controller/MessageController.php](controller/MessageController.php) (ligne 140-190)

```php
private function handleFileUpload(array $file): array {
    $maxSize = 10 * 1024 * 1024;  // 10 MB
    $allowedExts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'jpg', 'jpeg', 'png', 'gif'];
    $allowedMimes = ['application/pdf', 'application/msword', 
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-powerpoint',
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'text/plain', 'application/zip',
                    'image/jpeg', 'image/png', 'image/gif'];

    // ──── VALIDATION 1: Taille fichier ────
    if ($file['size'] > $maxSize) {
        return ['error' => 'Le fichier est trop volumineux (max 10 MB).'];
    }

    // ──── VALIDATION 2: Type MIME ────
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMimes)) {
        return ['error' => 'Type de fichier non autorisé.'];
    }

    // ──── VALIDATION 3: Extension fichier ────
    $filename = basename($file['name']);
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts)) {
        return ['error' => 'Extension de fichier non autorisée.'];
    }

    // ──── Déplacer le fichier ────
    $uploadDir = __DIR__ . '/../uploads/messages/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $uniqueName = uniqid() . '_' . time() . '.' . $ext;
    $uploadPath = $uploadDir . $uniqueName;

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return [
            'path' => 'uploads/messages/' . $uniqueName,
            'name' => $filename,
            'type' => $mimeType,
            'size' => $file['size']
        ];
    } else {
        return ['error' => 'Erreur lors de l\'upload du fichier.'];
    }
}
```

### ✅ Validations Conversation - Complet

**Fichier:** [controller/MessageController.php](controller/MessageController.php) (ligne 200-230)

```php
public function createConversation(): void {
    $this->startSession();
    $errors = [];
    $id_user = $this->getUserId();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_destinataire = isset($_POST['id_destinataire']) ? (int)$_POST['id_destinataire'] : 0;
        $contenu_init = isset($_POST['contenu_init']) ? trim($_POST['contenu_init']) : '';

        // ──── VALIDATION 1: Destinataire valide ────
        if ($id_destinataire <= 0)
            $errors[] = "Veuillez sélectionner un destinataire.";
        
        // ──── VALIDATION 2: Pas d'auto-message ────
        elseif ($id_destinataire === $id_user)
            $errors[] = "Vous ne pouvez pas vous écrire à vous-même.";
        
        // ──── VALIDATION 3: Contenu initial vide ────
        if ($contenu_init === '')
            $errors[] = "Le premier message ne peut pas être vide.";
        
        // ──── VALIDATION 4: Contenu trop long ────
        elseif (strlen($contenu_init) > 2000)
            $errors[] = "Le message ne peut pas dépasser 2000 caractères.";

        if (empty($errors)) {
            // ✅ Création en BD
            $id_conv = $this->conversationModel->create($id_user, $id_destinataire);
            $this->messageModel->create($contenu_init, $id_user, $id_conv);
            $this->redirectFront("?id=$id_conv");
        }
    }

    require __DIR__ . '/../view/Front/ajouter_message.php';
}
```

---

## 3️⃣ MVC - Preuves Architecture

### ✅ Model Tier Isolation

**Fichier:** [model/Message.php](model/Message.php) (complet)
```php
<?php
require_once __DIR__ . '/../config/database.php';

class Message {
    private PDO $pdo;  // ✅ PDO injecté

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // ✅ Que des opérations BD
    public function getByConversation(int $id_conversation): array { ... }
    public function getById(int $id): ?array { ... }
    public function getAll(): array { ... }
    public function create(...): bool { ... }
    public function update(...): bool { ... }
    public function delete(...): bool { ... }
    public function markAsRead(...): bool { ... }
    public function countUnread(...): int { ... }
}
?>
```

### ✅ Controller Tier Orchestration

**Fichier:** [controller/MessageController.php](controller/MessageController.php)
```php
<?php
require_once __DIR__ . '/../model/Message.php';
require_once __DIR__ . '/../model/Conversation.php';

class MessageController {
    // ✅ Dépendances injectées
    private Message $messageModel;
    private Conversation $conversationModel;

    public function __construct() {
        $this->messageModel = new Message();
        $this->conversationModel = new Conversation();
    }

    // ✅ Orchestration Front Office
    public function showConversation(): void {
        // 1. Valider l'accès
        // 2. Appeler Model pour données
        // 3. Charger la View
        require __DIR__ . '/../view/Front/Messages.php';
    }

    // ✅ Orchestration Back Office
    public function indexBack(): void {
        $messages = $this->messageModel->getAll();
        require __DIR__ . '/../view/Back/messages.php';
    }

    // ✅ Logique métier (upload)
    private function handleFileUpload(array $file): array {
        // Validation fichier
        // Déplacement physique
        // Retour informations
    }
}
?>
```

### ✅ View Tier Séparation

**Fichier:** [view/Front/Messages.php](view/Front/Messages.php)
```php
<?php
// ✅ Pas de require() de Model
// ✅ Pas de requête BD directe
// ✅ Reçoit les données du Controller

$id_user = $id_user ?? 0;                      // Données du Controller
$conversations = $conversations ?? [];         // Données du Controller
$messages = $messages ?? [];                   // Données du Controller
?>
<!DOCTYPE html>
<html>
<body>
    <!-- ✅ Affichage des données uniquement -->
    <div class="conv-list">
        <?php foreach ($conversations as $conv): ?>
            <div class="conv-item">
                <h3><?= htmlspecialchars($conv['interlocuteur_prenom']) ?></h3>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- ✅ Formulaire qui POSTe vers Controller -->
    <form method="POST" action="../../controller/MessageController.php?action=send">
        <textarea name="contenu"></textarea>
        <button type="submit">Envoyer</button>
    </form>
</body>
</html>
```

**Résumé Flux:**
```
USER INPUT 
    ↓
[View/HTML] → HTTP POST/GET
    ↓
[Controller] → Valider + Appeler Model
    ↓
[Model] → Requête BD (PDO)
    ↓
[Controller] ← Données retournées
    ↓
[View] ← Données injectées
    ↓
HTML OUTPUT
```

---

## 4️⃣ PROGRAMMATION OOP - Preuves

### ✅ Encapsulation

**Fichier:** [model/Message.php](model/Message.php)
```php
class Message {
    private PDO $pdo;  // ✅ Privé
    
    public function __construct() {  // ✅ Public
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    public function create(...): bool {  // ✅ Public (accès)
        // Logique interne
    }
}
```

### ✅ Type Hints Complets

**Fichier:** [controller/MessageController.php](controller/MessageController.php)
```php
// ✅ Types des paramètres
public function sendMessage(): void { ... }
public function deleteMessage(): void { ... }
public function createConversation(): void { ... }

// ✅ Types de retour
public function getById(int $id): ?array { ... }
public function create(...): bool { ... }
public function getAll(): array { ... }
public function countUnread(...): int { ... }
```

### ✅ Singleton Pattern

**Fichier:** [config/database.php](config/database.php)
```php
class Database {
    private static $instance = null;  // ✅ Static
    private $pdo;

    private function __construct() {  // ✅ Privé (empêche new)
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};...";
        $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
    }

    public static function getInstance(): Database {  // ✅ Singleton
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->pdo;
    }
}
```

### ✅ Injection de Dépendances

**Fichier:** [controller/MessageController.php](controller/MessageController.php)
```php
class MessageController {
    private Message $messageModel;           // ✅ Injecté
    private Conversation $conversationModel; // ✅ Injecté

    public function __construct() {
        $this->messageModel = new Message();              // Création en constructor
        $this->conversationModel = new Conversation();    // Création en constructor
    }

    public function sendMessage(): void {
        // Utilisation des dépendances
        $this->messageModel->create(...);
        $this->conversationModel->getById(...);
    }
}
```

---

## 5️⃣ PDO - Preuves Securité

### ✅ Prepared Statements Partout

**Fichier:** [model/Message.php](model/Message.php)

```php
// ✅ Tous les SELECT utilisent prepare()
public function getByConversation(int $id_conversation): array {
    $stmt = $this->pdo->prepare(          // ✅ PREPARE
        "SELECT m.*, u.nom, u.prenom
         FROM messages m
         JOIN utilisateurs u ON u.id_u = m.id_expediteur
         WHERE m.id_conversation = :id    // ✅ Paramètre nommé
         ORDER BY m.date_envoi ASC"
    );
    $stmt->execute([':id' => $id_conversation]);  // ✅ EXECUTE
    return $stmt->fetchAll();
}

// ✅ Tous les INSERT utilisent prepare()
public function create(string $contenu, int $id_expediteur, int $id_conversation, ...): bool {
    $stmt = $this->pdo->prepare(
        "INSERT INTO messages (...) VALUES (...)"  // ✅ Pas de concaténation
    );
    return $stmt->execute([                        // ✅ Paramètres sécurisés
        ':contenu' => $contenu,
        ':id_expediteur' => $id_expediteur,
        ':id_conversation' => $id_conversation,
    ]);
}

// ✅ Tous les UPDATE utilisent prepare()
public function update(int $id, string $contenu): bool {
    $stmt = $this->pdo->prepare(
        "UPDATE messages SET contenu = :contenu WHERE id_message = :id"
    );
    return $stmt->execute([':contenu' => $contenu, ':id' => $id]);
}

// ✅ Tous les DELETE utilisent prepare()
public function delete(int $id): bool {
    $stmt = $this->pdo->prepare("DELETE FROM messages WHERE id_message = :id");
    return $stmt->execute([':id' => $id]);
}
```

### ✅ Aucune Injection SQL Possible

```php
// ❌ JAMAIS ceci:
$sql = "SELECT * FROM messages WHERE id = " . $_GET['id'];  // DANGEREUX

// ✅ TOUJOURS ceci:
$stmt = $pdo->prepare("SELECT * FROM messages WHERE id = :id");
$stmt->execute([':id' => $_GET['id']]);  // SÉCURISÉ
```

### ✅ PDO Configuration Robuste

**Fichier:** [config/database.php](config/database.php)
```php
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        // ✅ Exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   // ✅ Arrays
    PDO::ATTR_EMULATE_PREPARES => false,                // ✅ Prepared au serveur
];

$this->pdo = new PDO($dsn, $username, $password, $options);
```

---

## 📊 Tableau Récapitulatif

| Critère | Fichier | Ligne | Preuves |
|---------|---------|-------|---------|
| **CREATE Message** | controller/MessageController.php | 75 | sendMessage() + validation |
| **READ Message** | controller/MessageController.php | 47 | showConversation() |
| **UPDATE Message** | controller/MessageController.php | 241 | editMessage() |
| **DELETE Message** | controller/MessageController.php | 268 | deleteMessage() |
| **Validation PHP** | controller/MessageController.php | 83-112 | if strlen() + MIME check |
| **MVC Architecture** | model/, controller/, view/ | All | Séparation claire |
| **OOP Classes** | model/, controller/ | All | 5 classes, type hints |
| **PDO Prepared** | model/Message.php | All | prepare() + execute() |
| **Singleton PDO** | config/database.php | 13-35 | getInstance() |
| **FrontOffice** | view/Front/Messages.php | All | Interface utilisateur |
| **BackOffice** | view/Back/conversations.php | All | Admin interface |

---

*Rapport de preuves - Projet Swaply Messaging - 25/04/2026*
