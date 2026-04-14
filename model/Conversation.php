<?php
require_once __DIR__ . '/../config/database.php';

class Conversation {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // Toutes les conversations d'un utilisateur (front)
    public function getByUser(int $id_user): array {
        $stmt = $this->pdo->prepare(
            "SELECT c.*,
                    IF(c.id_user1 = :u, u2.nom, u1.nom) AS interlocuteur_nom,
                    IF(c.id_user1 = :u2, u2.prenom, u1.prenom) AS interlocuteur_prenom,
                    IF(c.id_user1 = :u3, c.id_user2, c.id_user1) AS interlocuteur_id,
                    (SELECT contenu FROM messages WHERE id_conversation = c.id_conversation ORDER BY date_envoi DESC LIMIT 1) AS dernier_message,
                    (SELECT date_envoi FROM messages WHERE id_conversation = c.id_conversation ORDER BY date_envoi DESC LIMIT 1) AS date_dernier_message,
                    (SELECT COUNT(*) FROM messages WHERE id_conversation = c.id_conversation AND id_expediteur != :u4 AND lu = 0) AS non_lus
             FROM conversations c
             JOIN utilisateurs u1 ON u1.id_u = c.id_user1
             JOIN utilisateurs u2 ON u2.id_u = c.id_user2
             WHERE (c.id_user1 = :u5 OR c.id_user2 = :u6) AND c.statut = 'active'
             ORDER BY date_dernier_message DESC"
        );
        $stmt->execute([
            ':u'  => $id_user, ':u2' => $id_user, ':u3' => $id_user,
            ':u4' => $id_user, ':u5' => $id_user, ':u6' => $id_user,
        ]);
        return $stmt->fetchAll();
    }

    // Toutes les conversations (back office)
    public function getAll(): array {
        $stmt = $this->pdo->query(
            "SELECT c.*,
                    u1.nom AS nom_user1, u1.prenom AS prenom_user1,
                    u2.nom AS nom_user2, u2.prenom AS prenom_user2,
                    (SELECT COUNT(*) FROM messages WHERE id_conversation = c.id_conversation) AS nb_messages
             FROM conversations c
             JOIN utilisateurs u1 ON u1.id_u = c.id_user1
             JOIN utilisateurs u2 ON u2.id_u = c.id_user2
             ORDER BY c.date_creation DESC"
        );
        return $stmt->fetchAll();
    }

    // Récupérer une conversation par ID
    public function getById(int $id): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT c.*,
                    u1.nom AS nom_user1, u1.prenom AS prenom_user1,
                    u2.nom AS nom_user2, u2.prenom AS prenom_user2
             FROM conversations c
             JOIN utilisateurs u1 ON u1.id_u = c.id_user1
             JOIN utilisateurs u2 ON u2.id_u = c.id_user2
             WHERE c.id_conversation = :id"
        );
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // Vérifier si une conversation existe entre deux users
    public function existsBetween(int $user1, int $user2): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM conversations
             WHERE ((id_user1 = :u1 AND id_user2 = :u2)
                 OR (id_user1 = :u3 AND id_user2 = :u4))
               AND statut = 'active'"
        );
        $stmt->execute([':u1' => $user1, ':u2' => $user2, ':u3' => $user2, ':u4' => $user1]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // Créer une conversation
    public function create(int $id_user1, int $id_user2): int {
        $existing = $this->existsBetween($id_user1, $id_user2);
        if ($existing) return $existing['id_conversation'];

        $stmt = $this->pdo->prepare(
            "INSERT INTO conversations (id_user1, id_user2) VALUES (:u1, :u2)"
        );
        $stmt->execute([':u1' => $id_user1, ':u2' => $id_user2]);
        return (int)$this->pdo->lastInsertId();
    }

    // Fermer une conversation
    public function close(int $id): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE conversations SET statut = 'fermee' WHERE id_conversation = :id"
        );
        return $stmt->execute([':id' => $id]);
    }

    // Supprimer une conversation (et ses messages)
    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM messages WHERE id_conversation = :id");
        $stmt->execute([':id' => $id]);
        $stmt2 = $this->pdo->prepare("DELETE FROM conversations WHERE id_conversation = :id");
        return $stmt2->execute([':id' => $id]);
    }

    // Modifier le statut d'une conversation
    public function update(int $id, string $statut): bool {
        $stmt = $this->pdo->prepare("UPDATE conversations SET statut = :statut WHERE id_conversation = :id");
        return $stmt->execute([':statut' => $statut, ':id' => $id]);
    }

    // Lister tous les utilisateurs pour créer une conv — colonne id_u
   
public function getAllUsers(): array
{
    $stmt = $this->pdo->query("SELECT id_u, nom, prenom, email FROM utilisateurs ORDER BY nom, prenom");
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}
}
