<?php
require_once __DIR__ . '/../config/database.php';

class Message {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
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

    // ─── Créer un message (texte OU fichier OU voix) ─────────────────────────────────
    // CORRECTION : fichier_path et colonnes fichier sont maintenant NULL par défaut
    // → un message texte sans fichier fonctionne parfaitement
    public function create(
        string  $contenu,
        int     $id_expediteur,
        int     $id_conversation,
        ?string $fichier_path          = null,
        ?string $fichier_nom_original  = null,
        ?string $fichier_type          = null,
        ?int    $fichier_taille        = null,
        ?string $type_message          = 'texte'  // 'texte', 'fichier', 'voix'
    ): bool {
        $stmt = $this->pdo->prepare(
            "INSERT INTO messages
                (contenu, id_expediteur, id_conversation,
                 fichier_path, fichier_nom_original, fichier_type, fichier_taille, type_message)
             VALUES
                (:contenu, :id_expediteur, :id_conversation,
                 :fichier_path, :fichier_nom_original, :fichier_type, :fichier_taille, :type_message)"
        );
        return $stmt->execute([
            ':contenu'               => $contenu,
            ':id_expediteur'         => $id_expediteur,
            ':id_conversation'       => $id_conversation,
            ':fichier_path'          => $fichier_path,          // NULL si message texte
            ':fichier_nom_original'  => $fichier_nom_original,  // NULL si message texte
            ':fichier_type'          => $fichier_type,          // NULL si message texte
            ':fichier_taille'        => $fichier_taille,        // NULL si message texte
            ':type_message'          => $type_message           // 'texte', 'fichier', ou 'voix'
        ]);
    }

    // ─── Créer un message vocal ─────────────────────────────────────────────────────
    /**
     * Créer un message vocal
     * @param string $voix_path Chemin du fichier audio (ex: /uploads/voice/msg_123.webm)
     * @param int $id_expediteur ID de l'utilisateur envoyant le message
     * @param int $id_conversation ID de la conversation
     * @param string $voix_nom_original Nom du fichier original
     * @param string $voix_type Type MIME (ex: audio/webm, audio/mpeg)
     * @param int $voix_taille Taille en octets
     * @param int $voix_duree Durée en secondes
     * @return bool
     */
    public function createVoiceMessage(
        string $voix_path,
        int $id_expediteur,
        int $id_conversation,
        string $voix_nom_original = 'voice_message',
        string $voix_type = 'audio/webm',
        int $voix_taille = 0,
        int $voix_duree = 0
    ): bool {
        $contenu = "🎤 Message vocal";
        return $this->create(
            contenu: $contenu,
            id_expediteur: $id_expediteur,
            id_conversation: $id_conversation,
            fichier_path: $voix_path,
            fichier_nom_original: $voix_nom_original,
            fichier_type: $voix_type,
            fichier_taille: $voix_taille,
            type_message: 'voix'
        );
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

    /**
     * Récupérer tous les messages d'une conversation avec les réactions
     */
    public function getByConversationWithReactions(int $id_conversation): array {
        $stmt = $this->pdo->prepare(
            "SELECT m.*, u.nom, u.prenom
             FROM messages m
             JOIN utilisateurs u ON u.id_u = m.id_expediteur
             WHERE m.id_conversation = :id
             ORDER BY m.date_envoi ASC"
        );
        $stmt->execute([':id' => $id_conversation]);
        $messages = $stmt->fetchAll();
        
        // Ajouter les réactions pour chaque message
        foreach ($messages as &$message) {
            $reactionStmt = $this->pdo->prepare(
                "SELECT emoji, COUNT(*) as count, GROUP_CONCAT(DISTINCT id_user) as users
                 FROM message_reactions
                 WHERE id_message = :id_message
                 GROUP BY emoji"
            );
            $reactionStmt->execute([':id_message' => $message['id_message']]);
            $message['reactions'] = $reactionStmt->fetchAll();
        }
        
        return $messages;
    }

    /**
     * Récupérer un message par ID avec les réactions
     */
    public function getByIdWithReactions(int $id): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT m.*, u.nom, u.prenom
             FROM messages m
             JOIN utilisateurs u ON u.id_u = m.id_expediteur
             WHERE m.id_message = :id"
        );
        $stmt->execute([':id' => $id]);
        $message = $stmt->fetch();
        
        if (!$message) {
            return null;
        }

        // Ajouter les réactions au message
        $reactionStmt = $this->pdo->prepare(
            "SELECT emoji, COUNT(*) as count, GROUP_CONCAT(DISTINCT id_user) as users
             FROM message_reactions
             WHERE id_message = :id_message
             GROUP BY emoji"
        );
        $reactionStmt->execute([':id_message' => $id]);
        $message['reactions'] = $reactionStmt->fetchAll();

        return $message;
    }
}
?>