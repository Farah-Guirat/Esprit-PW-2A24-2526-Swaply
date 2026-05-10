<?php
require_once __DIR__ . '/../config/database.php';

class Reaction {
    private PDO $pdo;
    
    // Emojis acceptés pour les réactions
    private array $allowedEmojis = ['👍', '❤️', '😂', '😮', '😢', '🔥', '👎', '🤔', '✨', '🎉'];

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    /**
     * Ajouter une réaction à un message
     */
    public function add(int $id_message, int $id_user, string $emoji): bool {
        // Valider l'emoji
        if (!in_array($emoji, $this->allowedEmojis)) {
            throw new Exception("Emoji non autorisé: $emoji");
        }

        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO message_reactions (id_message, id_user, emoji)
                 VALUES (:id_message, :id_user, :emoji)
                 ON DUPLICATE KEY UPDATE date_creation = CURRENT_TIMESTAMP"
            );
            return $stmt->execute([
                ':id_message' => $id_message,
                ':id_user' => $id_user,
                ':emoji' => $emoji
            ]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de l'ajout de la réaction: " . $e->getMessage());
        }
    }

    /**
     * Supprimer une réaction
     */
    public function remove(int $id_message, int $id_user, string $emoji): bool {
        $stmt = $this->pdo->prepare(
            "DELETE FROM message_reactions 
             WHERE id_message = :id_message AND id_user = :id_user AND emoji = :emoji"
        );
        return $stmt->execute([
            ':id_message' => $id_message,
            ':id_user' => $id_user,
            ':emoji' => $emoji
        ]);
    }

    /**
     * Récupérer toutes les réactions d'un message groupées par emoji
     */
    public function getByMessage(int $id_message): array {
        $stmt = $this->pdo->prepare(
            "SELECT emoji, COUNT(*) as count, GROUP_CONCAT(id_user) as users
             FROM message_reactions
             WHERE id_message = :id_message
             GROUP BY emoji"
        );
        $stmt->execute([':id_message' => $id_message]);
        return $stmt->fetchAll();
    }

    /**
     * Récupérer les réactions d'un utilisateur sur un message
     */
    public function getUserReactions(int $id_message, int $id_user): array {
        $stmt = $this->pdo->prepare(
            "SELECT emoji FROM message_reactions
             WHERE id_message = :id_message AND id_user = :id_user"
        );
        $stmt->execute([
            ':id_message' => $id_message,
            ':id_user' => $id_user
        ]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Vérifier si un utilisateur a réagi avec cet emoji
     */
    public function hasReacted(int $id_message, int $id_user, string $emoji): bool {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM message_reactions
             WHERE id_message = :id_message AND id_user = :id_user AND emoji = :emoji"
        );
        $stmt->execute([
            ':id_message' => $id_message,
            ':id_user' => $id_user,
            ':emoji' => $emoji
        ]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Obtenir la liste des emojis autorisés
     */
    public function getAllowedEmojis(): array {
        return $this->allowedEmojis;
    }

    /**
     * Supprimer toutes les réactions d'un message
     */
    public function removeAll(int $id_message): bool {
        $stmt = $this->pdo->prepare("DELETE FROM message_reactions WHERE id_message = :id_message");
        return $stmt->execute([':id_message' => $id_message]);
    }
}
?>
