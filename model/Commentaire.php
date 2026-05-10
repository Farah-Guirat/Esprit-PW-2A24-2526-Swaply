<?php
require_once __DIR__ . '/../config/Database.php';

class Commentaire
{
    private $conn;
    private $table_name = 'commentaires';

    public $id_com;
    public $id_pub;
    public $id_client;
    public $contenu;
    public $date_com;

    public function __construct($db = null)
    {
        if ($db !== null) {
            $this->conn = $db;
        } else {
            $database = new Database();
            $this->conn = $database->connect();
        }
        $this->ensureTableExists();
    }

    private function ensureTableExists()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `commentaires` (
            `id_com` INT(11) NOT NULL AUTO_INCREMENT,
            `id_pub` INT(11) NOT NULL,
            `id_client` INT(11) NOT NULL,
            `contenu` TEXT NOT NULL,
            `date_com` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `likes` INT(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id_com`),
            KEY `idx_id_pub` (`id_pub`),
            KEY `idx_id_client` (`id_client`),
            CONSTRAINT `fk_comment_pub` FOREIGN KEY (`id_pub`) REFERENCES `publications` (`id_pub`) ON DELETE CASCADE,
            CONSTRAINT `fk_comment_user` FOREIGN KEY (`id_client`) REFERENCES `utilisateurs` (`id_u`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $likesSql = "CREATE TABLE IF NOT EXISTS `comment_likes` (
            `id_like` INT(11) NOT NULL AUTO_INCREMENT,
            `id_com` INT(11) NOT NULL,
            `id_u` INT(11) NOT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_like`),
            UNIQUE KEY `uniq_comment_user` (`id_com`, `id_u`),
            KEY `idx_id_com` (`id_com`),
            KEY `idx_id_u` (`id_u`),
            CONSTRAINT `fk_comment_like` FOREIGN KEY (`id_com`) REFERENCES `commentaires` (`id_com`) ON DELETE CASCADE,
            CONSTRAINT `fk_user_like` FOREIGN KEY (`id_u`) REFERENCES `utilisateurs` (`id_u`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        try {
            $this->conn->exec($sql);
            $this->conn->exec($likesSql);
        } catch (Exception $e) {
            // ignore table creation errors
        }
    }

    public function create()
    {
        $query = "INSERT INTO `" . $this->table_name . "` (id_pub, id_client, contenu)
                  VALUES (:id_pub, :id_client, :contenu)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_pub', $this->id_pub, PDO::PARAM_INT);
        $stmt->bindParam(':id_client', $this->id_client, PDO::PARAM_INT);
        $stmt->bindParam(':contenu', $this->contenu);
        return $stmt->execute();
    }

    public function readByPub($id_pub)
    {
        $query = "SELECT com.*, u.nom, u.prenom, u.photo FROM `" . $this->table_name . "` com
                  JOIN `utilisateurs` u ON com.id_client = u.id_u
                  WHERE com.id_pub = ? ORDER BY com.date_com ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_pub]);
        return $stmt;
    }

    public function update($id_com, $contenu)
    {
        $query = "UPDATE `" . $this->table_name . "` SET contenu = ? WHERE id_com = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$contenu, $id_com]);
    }

    public function getById($id_com)
    {
        $query = "SELECT com.*, u.nom, u.prenom, u.photo FROM `" . $this->table_name . "` com
                  JOIN `utilisateurs` u ON com.id_client = u.id_u
                  WHERE com.id_com = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_com]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function isOwner($id_com, $id_client)
    {
        $query = "SELECT 1 FROM `" . $this->table_name . "` WHERE id_com = ? AND id_client = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_com, $id_client]);
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function toggleLike($commentId, $userId)
    {
        $stmt = $this->conn->prepare("SELECT id_like FROM comment_likes WHERE id_com = ? AND id_u = ?");
        $stmt->execute([$commentId, $userId]);
        $liked = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($liked) {
            $delete = $this->conn->prepare("DELETE FROM comment_likes WHERE id_com = ? AND id_u = ?");
            $delete->execute([$commentId, $userId]);
            $result = false;
        } else {
            $insert = $this->conn->prepare("INSERT INTO comment_likes (id_com, id_u) VALUES (?, ?)");
            $insert->execute([$commentId, $userId]);
            $result = true;
        }

        $count = $this->getLikes($commentId);
        $updateLikes = $this->conn->prepare("UPDATE `" . $this->table_name . "` SET likes = ? WHERE id_com = ?");
        $updateLikes->execute([$count, $commentId]);

        return $result;
    }

    public function getLikes($commentId)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM comment_likes WHERE id_com = ?");
        $stmt->execute([$commentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['total'] : 0;
    }

    public function hasUserLiked($commentId, $userId)
    {
        $stmt = $this->conn->prepare("SELECT 1 FROM comment_likes WHERE id_com = ? AND id_u = ?");
        $stmt->execute([$commentId, $userId]);
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserLikedCommentIds($userId)
    {
        $stmt = $this->conn->prepare("SELECT id_com FROM comment_likes WHERE id_u = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0) ?: [];
    }

    public function delete($id_com)
    {
        $query = "DELETE FROM `" . $this->table_name . "` WHERE id_com = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id_com]);
    }
}
