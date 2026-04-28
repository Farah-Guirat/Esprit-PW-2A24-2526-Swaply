# ✅ VÉRIFICATION DES RÈGLES DE PROJET - SWAPLY

Date: 26 Avril 2026
Statut: **CONFORME** (après corrections)

---

## 📋 RÈGLES À VÉRIFIER

### 1. ✅ Contrôle de saisie fonctionnel dans tous les formulaires

**Validations implémentées:**

#### Envoi de message (MessageController.php, lignes 48-51):
```php
// Validation serveur (sans HTML5)
if ($contenu === '' && empty($_FILES['fichier']))  
    $errors[] = "Le message ne peut pas être vide.";
elseif (strlen($contenu) > 2000)  
    $errors[] = "Le message ne peut pas dépasser 2000 caractères.";
if ($id_conversation <= 0)        
    $errors[] = "Conversation invalide.";
```

#### Validation fichier attaché (MessageController.php, lignes 117-152):
- **Taille max:** 10 MB
- **Extensions autorisées:** PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, ZIP, JPG, JPEG, PNG, GIF
- **Vérification MIME types:** Validation stricte des types MIME
- **Accès utilisateur:** Vérification que la conversation appartient à l'utilisateur

#### Sélection utilisateur (select_user.php):
- Validation de l'ID utilisateur
- Récupération des infos depuis la BDD avant session

---

### 2. ✅ Les contrôles de saisie avec HTML5 ne seront pas acceptés

**Status:** CONFORME (corrections appliquées)

**Avant (INCORRECT):**
```html
<textarea ... maxlength="2000"></textarea>
<input type="file" accept=".pdf,.doc,..."></input>
```

**Après (CORRECT):**
```html
<textarea ...></textarea>
<input type="file"></input>
```

**Attributs HTML5 retirés:**
- ❌ `maxlength="2000"` du textarea
- ❌ `accept=".pdf,.doc,.docx,..."` de l'input file

**Raison:** La validation est entièrement gérée côté serveur par le controller PHP.

**Formulaire avec `novalidate`:**
```html
<form ... novalidate ...>
```
L'attribut `novalidate` désactive la validation HTML5 du navigateur.

---

### 3. ✅ Modèle MVC (Model / View / Controller)

**Structure respectée:**

```
/controller/
  ├── RealtimeController.php
  ├── MessageController.php
  └── ConversationController.php

/model/
  ├── Message.php
  └── Conversation.php

/view/
  ├── Front/
  │   ├── Messages.php (principale)
  │   ├── messagerie.php
  │   ├── select_user.php
  │   └── ...
  └── Back/
      └── ...
```

**Workflow MVC:**
1. **VIEW** (Messages.php, select_user.php) → Affiche interface utilisateur
2. **CONTROLLER** (MessageController.php) → Traite requête POST
3. **MODEL** (Message.php, Conversation.php) → Accès BDD via PDO
4. **CONTROLLER** → Retour à VIEW avec résultats/erreurs

---

### 4. ✅ Principes de la programmation orientée objet

**Implémentation POO:**

#### Classes principales:
```php
class Database {
    // Singleton pattern pour connexion unique
    private static $instance = null;
}

class Message {
    private PDO $pdo;
    public function getByConversation(int $id): array {}
    public function create(...): bool {}
    public function update(...): bool {}
    public function delete(int $id): bool {}
}

class Conversation {
    private PDO $pdo;
    public function getByUser(int $id_user): array {}
    public function create(...): int {}
    public function delete(int $id): bool {}
}

class MessageController {
    private Message $messageModel;
    private Conversation $conversationModel;
    public function sendMessage(): void {}
}
```

**Principes appliqués:**
- ✅ **Encapsulation:** Propriétés privées, methods publiques
- ✅ **Injection de dépendances:** Models injectés dans Controllers
- ✅ **Singleton pattern:** Database::getInstance()
- ✅ **Typage:** Déclaration de types (int, string, array, PDO, bool)
- ✅ **Héritage potentiel:** Préparation pour extensions futures

---

### 5. ✅ Utilisation de PDO (obligatoire)

**Configuration (config/database.php):**
```php
private $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";

$this->pdo = new PDO($dsn, $this->username, $this->password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
```

**Prepared Statements (partout dans le code):**
```php
// Message.php - Exemples
$stmt = $this->pdo->prepare("SELECT * FROM messages WHERE id_message = :id");
$stmt->execute([':id' => $id]);

// Conversation.php - Exemples
$stmt = $this->pdo->prepare("INSERT INTO conversations (...) VALUES (...)");
$stmt->execute([':u1' => $id_user1, ':u2' => $id_user2]);
```

**Sécurité:**
- ✅ Protection contre les injections SQL
- ✅ Pas de requêtes concaténées directement
- ✅ Tous les paramètres liés correctement
- ✅ Exception handling avec PDOException

---

## 📊 RÉSUMÉ DE LA CONFORMITÉ

| Règle | Status | Fichiers impliqués |
|-------|--------|------------------|
| Contrôles de saisie fonctionnels | ✅ CONFORME | MessageController.php, select_user.php |
| Pas de validation HTML5 | ✅ CONFORME (corrigé) | view/Front/Messages.php |
| Modèle MVC | ✅ CONFORME | Structure complète avec controller/, model/, view/ |
| POO appliquée | ✅ CONFORME | Database.php, Message.php, Conversation.php, *Controller.php |
| PDO obligatoire | ✅ CONFORME | config/database.php, tous les Models |

---

## 🔧 CORRECTIONS APPLIQUÉES

### Fichier: `view/Front/Messages.php`

**Suppression des contrôles HTML5:**
1. Ligne 407: Suppression de `maxlength="2000"` du textarea
2. Ligne 414: Suppression de `accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.jpg,.jpeg,.png,.gif"` de l'input file

**Raison:** La validation complète est effectuée côté serveur par MessageController.php (lignes 48-152)

---

## ✨ CONCLUSION

Le projet **SWAPLY** respecte **TOUTES LES RÈGLES** requises:
- ✅ Contrôles de saisie fonctionnels (côté serveur PHP)
- ✅ Aucune validation HTML5 (attributs supprimés)
- ✅ Architecture MVC complète
- ✅ POO correctement implémentée
- ✅ PDO utilisé exclusivement

**Le projet est VALIDABLE.**
