<?php
require_once __DIR__ . '/../config/Database.php';

class Story
{
    private $conn;
    private $table_name = 'stories';

    public $id_story;
    public $id_client;
    public $contenu;
    public $image;
    public $date_creation;

    public function __construct($db = null)
    {
        if ($db !== null) {
            $this->conn = $db;
        } else {
            $database = new Database();
            $this->conn = $database->connect();
        }
        $this->ensureTablesExist();
    }

    private function ensureTablesExist()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `stories` (
            `id_story` INT(11) NOT NULL AUTO_INCREMENT,
            `id_client` INT(11) NOT NULL,
            `contenu` TEXT DEFAULT NULL,
            `image` VARCHAR(1024) DEFAULT NULL,
            `date_creation` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_story`),
            KEY `idx_story_user` (`id_client`),
            CONSTRAINT `fk_story_user` FOREIGN KEY (`id_client`) REFERENCES `utilisateurs` (`id_u`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $likesSql = "CREATE TABLE IF NOT EXISTS `story_likes` (
            `id_like` INT(11) NOT NULL AUTO_INCREMENT,
            `id_story` INT(11) NOT NULL,
            `id_user` INT(11) NOT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_like`),
            UNIQUE KEY `uniq_story_user` (`id_story`, `id_user`),
            KEY `idx_id_story` (`id_story`),
            KEY `idx_id_user` (`id_user`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $commentsSql = "CREATE TABLE IF NOT EXISTS `story_comments` (
            `id_com` INT(11) NOT NULL AUTO_INCREMENT,
            `id_story` INT(11) NOT NULL,
            `id_client` INT(11) NOT NULL,
            `contenu` TEXT NOT NULL,
            `date_com` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `likes` INT(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id_com`),
            KEY `idx_id_story` (`id_story`),
            KEY `idx_id_client` (`id_client`),
            CONSTRAINT `fk_story_comment` FOREIGN KEY (`id_story`) REFERENCES `stories` (`id_story`) ON DELETE CASCADE,
            CONSTRAINT `fk_story_comment_user` FOREIGN KEY (`id_client`) REFERENCES `utilisateurs` (`id_u`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $commentLikesSql = "CREATE TABLE IF NOT EXISTS `story_comment_likes` (
            `id_like` INT(11) NOT NULL AUTO_INCREMENT,
            `id_com` INT(11) NOT NULL,
            `id_u` INT(11) NOT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_like`),
            UNIQUE KEY `uniq_story_comment_user` (`id_com`, `id_u`),
            KEY `idx_id_com` (`id_com`),
            KEY `idx_id_u` (`id_u`),
            CONSTRAINT `fk_story_comment_like` FOREIGN KEY (`id_com`) REFERENCES `story_comments` (`id_com`) ON DELETE CASCADE,
            CONSTRAINT `fk_story_comment_like_user` FOREIGN KEY (`id_u`) REFERENCES `utilisateurs` (`id_u`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        try {
            $this->conn->exec($sql);
            $this->conn->exec($likesSql);
            $this->conn->exec($commentsSql);
            $this->conn->exec($commentLikesSql);
        } catch (Exception $e) {
            // ignore table creation errors
        }
    }

    public function create()
    {
        $query = "INSERT INTO `" . $this->table_name . "` (id_client, contenu, image)
                  VALUES (:id_client, :contenu, :image)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_client', $this->id_client, PDO::PARAM_INT);
        $stmt->bindParam(':contenu', $this->contenu);
        $stmt->bindParam(':image', $this->image);
        if ($stmt->execute()) {
            $this->id_story = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function readAllActive()
    {
        $query = "SELECT s.*, u.nom, u.prenom, u.photo,
                         COALESCE(like_counts.total, 0) AS like_count,
                         COALESCE(comment_counts.total, 0) AS comment_count
                  FROM `" . $this->table_name . "` s
                  JOIN `utilisateurs` u ON s.id_client = u.id_u
                  LEFT JOIN (
                      SELECT id_story, COUNT(*) AS total FROM story_likes GROUP BY id_story
                  ) like_counts ON like_counts.id_story = s.id_story
                  LEFT JOIN (
                      SELECT id_story, COUNT(*) AS total FROM story_comments GROUP BY id_story
                  ) comment_counts ON comment_counts.id_story = s.id_story
                  WHERE s.date_creation > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                  ORDER BY s.date_creation DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getUserStories($userId)
    {
        $query = "SELECT * FROM `" . $this->table_name . "`
                  WHERE id_client = ? AND date_creation > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                  ORDER BY date_creation DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId]);
        return $stmt;
    }

    public function getById($storyId)
    {
        $query = "SELECT s.*, u.nom, u.prenom, u.photo,
                         COALESCE(like_counts.total, 0) AS like_count,
                         COALESCE(comment_counts.total, 0) AS comment_count
                  FROM `" . $this->table_name . "` s
                  JOIN `utilisateurs` u ON s.id_client = u.id_u
                  LEFT JOIN (
                      SELECT id_story, COUNT(*) AS total FROM story_likes GROUP BY id_story
                  ) like_counts ON like_counts.id_story = s.id_story
                  LEFT JOIN (
                      SELECT id_story, COUNT(*) AS total FROM story_comments GROUP BY id_story
                  ) comment_counts ON comment_counts.id_story = s.id_story
                  WHERE s.id_story = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$storyId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserLikedStoryIds($userId)
    {
        $stmt = $this->conn->prepare("SELECT id_story FROM story_likes WHERE id_user = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0) ?: [];
    }

    public function toggleLike($userId)
    {
        $stmt = $this->conn->prepare("SELECT id_like FROM story_likes WHERE id_story = ? AND id_user = ?");
        $stmt->execute([$this->id_story, $userId]);
        $exists = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($exists) {
            $delete = $this->conn->prepare("DELETE FROM story_likes WHERE id_story = ? AND id_user = ?");
            $delete->execute([$this->id_story, $userId]);
            return false;
        }

        $insert = $this->conn->prepare("INSERT INTO story_likes (id_story, id_user) VALUES (?, ?)");
        $insert->execute([$this->id_story, $userId]);
        return true;
    }

    public function getLikes()
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM story_likes WHERE id_story = ?");
        $stmt->execute([$this->id_story]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['total'] : 0;
    }

    public function isLikedByUser($storyId, $userId)
    {
        $stmt = $this->conn->prepare("SELECT 1 FROM story_likes WHERE id_story = ? AND id_user = ?");
        $stmt->execute([$storyId, $userId]);
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function isOwner($storyId, $userId)
    {
        $query = "SELECT 1 FROM `" . $this->table_name . "` WHERE id_story = ? AND id_client = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$storyId, $userId]);
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getComments($storyId)
    {
        $query = "SELECT c.*, u.nom, u.prenom, u.photo,
                         COALESCE(like_counts.total, 0) AS likes
                  FROM `story_comments` c
                  JOIN `utilisateurs` u ON c.id_client = u.id_u
                  LEFT JOIN (
                      SELECT id_com, COUNT(*) AS total FROM story_comment_likes GROUP BY id_com
                  ) like_counts ON like_counts.id_com = c.id_com
                  WHERE c.id_story = ?
                  ORDER BY c.date_com ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$storyId]);
        return $stmt;
    }

    public function addComment($storyId, $clientId, $contenu)
    {
        $query = "INSERT INTO `story_comments` (id_story, id_client, contenu)
                  VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$storyId, $clientId, $contenu]);
    }

    public function deleteComment($commentId, $clientId)
    {
        $query = "DELETE FROM `story_comments` WHERE id_com = ? AND id_client = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$commentId, $clientId]);
    }

    public function toggleCommentLike($commentId, $userId)
    {
        $stmt = $this->conn->prepare("SELECT id_like FROM story_comment_likes WHERE id_com = ? AND id_u = ?");
        $stmt->execute([$commentId, $userId]);
        $liked = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($liked) {
            $delete = $this->conn->prepare("DELETE FROM story_comment_likes WHERE id_com = ? AND id_u = ?");
            $delete->execute([$commentId, $userId]);
            $result = false;
        } else {
            $insert = $this->conn->prepare("INSERT INTO story_comment_likes (id_com, id_u) VALUES (?, ?)");
            $insert->execute([$commentId, $userId]);
            $result = true;
        }

        $count = $this->getCommentLikes($commentId);
        $updateLikes = $this->conn->prepare("UPDATE `story_comments` SET likes = ? WHERE id_com = ?");
        $updateLikes->execute([$count, $commentId]);

        return $result;
    }

    public function getCommentLikes($commentId)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM story_comment_likes WHERE id_com = ?");
        $stmt->execute([$commentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['total'] : 0;
    }

    public function getUserLikedCommentIds($userId)
    {
        $stmt = $this->conn->prepare("SELECT id_com FROM story_comment_likes WHERE id_u = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0) ?: [];
    }

    public function deleteExpired()
    {
        $query = "SELECT id_story, image FROM `" . $this->table_name . "`
                  WHERE date_creation <= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($row['image'])) {
                $filePath = __DIR__ . '/../' . trim($row['image']);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }

        $deleteQuery = "DELETE FROM `" . $this->table_name . "` WHERE date_creation <= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $deleteStmt = $this->conn->prepare($deleteQuery);
        return $deleteStmt->execute();
    }

    public function getAllStories()
    {
        $query = "SELECT s.*, u.nom, u.prenom, u.photo 
                  FROM `" . $this->table_name . "` s 
                  JOIN utilisateurs u ON s.id_client = u.id_u 
                  ORDER BY s.date_creation DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLikeCount($storyId)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM story_likes WHERE id_story = ?");
        $stmt->execute([$storyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['total'] : 0;
    }

    public function getCommentCount($storyId)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM story_comments WHERE id_story = ?");
        $stmt->execute([$storyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['total'] : 0;
    }

    public function delete()
    {
        // Supprimer l'image si elle existe
        $query = "SELECT image FROM `" . $this->table_name . "` WHERE id_story = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id_story]);
        $story = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($story && !empty($story['image'])) {
            $filePath = __DIR__ . '/../' . trim($story['image']);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Supprimer la story
        $deleteQuery = "DELETE FROM `" . $this->table_name . "` WHERE id_story = ?";
        $deleteStmt = $this->conn->prepare($deleteQuery);
        return $deleteStmt->execute([$this->id_story]);
    }
}
