<?php
require_once __DIR__ . '/../config/database.php';

class Message {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // Récupérer tous les messages d'une conversation
    public function getByConversation(int $id_conversation): array {
        $stmt = $this->pdo->prepare(
            "SELECT m.*, u.nom, u.prenom
             FROM messages m
             JOIN utilisateurs u ON u.id_u = m.id_expediteur
             WHERE m.id_conversation = :id
             ORDER BY m.date_envoi ASC"
        );
        $stmt->execute([':id' => $id_conversation]);
        return $stmt->fetchAll();
    }

    // Récupérer tous les messages (back office)
    public function getAll(): array {
        $stmt = $this->pdo->query(
            "SELECT m.*, u.nom, u.prenom
             FROM messages m
             JOIN utilisateurs u ON u.id_u = m.id_expediteur
             ORDER BY m.date_envoi DESC"
        );
        return $stmt->fetchAll();
    }

    // Récupérer un message par ID
    public function getById(int $id): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT m.*, u.nom, u.prenom
             FROM messages m
             JOIN utilisateurs u ON u.id_u = m.id_expediteur
             WHERE m.id_message = :id"
        );
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // Créer un message
    public function create(string $contenu, int $id_expediteur, int $id_conversation): bool {
        $stmt = $this->pdo->prepare(
            "INSERT INTO messages (contenu, id_expediteur, id_conversation)
             VALUES (:contenu, :id_expediteur, :id_conversation)"
        );
        return $stmt->execute([
            ':contenu'         => $contenu,
            ':id_expediteur'   => $id_expediteur,
            ':id_conversation' => $id_conversation,
        ]);
    }

    // Modifier un message
    public function update(int $id, string $contenu): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE messages SET contenu = :contenu WHERE id_message = :id"
        );
        return $stmt->execute([':contenu' => $contenu, ':id' => $id]);
    }

    // Supprimer un message
    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM messages WHERE id_message = :id");
        return $stmt->execute([':id' => $id]);
    }

    // Marquer les messages d'une conversation comme lus
    public function markAsRead(int $id_conversation, int $id_user): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE messages SET lu = 1
             WHERE id_conversation = :id_conv AND id_expediteur != :id_user AND lu = 0"
        );
        return $stmt->execute([':id_conv' => $id_conversation, ':id_user' => $id_user]);
    }

    // Compter les messages non lus d'un utilisateur
    public function countUnread(int $id_user): int {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM messages m
             JOIN conversations c ON c.id_conversation = m.id_conversation
             WHERE (c.id_user1 = :u OR c.id_user2 = :u2)
               AND m.id_expediteur != :u3
               AND m.lu = 0"
        );
        $stmt->execute([':u' => $id_user, ':u2' => $id_user, ':u3' => $id_user]);
        return (int)$stmt->fetchColumn();
    }
}
?>