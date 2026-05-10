<?php
require_once __DIR__ . '/../config/Database.php';

class Publication
{
    private $conn;
    private $table_name = 'publications';

    public $id_pub;
    public $titre;
    public $contenu;
    public $id_client;
    public $image;
    public $musique;
    public $likes;
    public $date_pub;

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
        $sql = "CREATE TABLE IF NOT EXISTS `publications` (
            `id_pub` INT(11) NOT NULL AUTO_INCREMENT,
            `titre` VARCHAR(255) NOT NULL,
            `contenu` TEXT NOT NULL,
            `id_client` INT(11) NOT NULL,
            `image` VARCHAR(1024) DEFAULT NULL,
            `musique` VARCHAR(1024) DEFAULT NULL,
            `likes` INT(11) NOT NULL DEFAULT 0,
            `date_pub` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_pub`),
            KEY `idx_id_client` (`id_client`),
            CONSTRAINT `fk_publication_user` FOREIGN KEY (`id_client`) REFERENCES `utilisateurs` (`id_u`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $likesSql = "CREATE TABLE IF NOT EXISTS `publication_likes` (
            `id_like` INT(11) NOT NULL AUTO_INCREMENT,
            `id_pub` INT(11) NOT NULL,
            `id_u` INT(11) NOT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_like`),
            UNIQUE KEY `uniq_pub_user` (`id_pub`, `id_u`),
            KEY `idx_id_pub` (`id_pub`),
            KEY `idx_id_u` (`id_u`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        try {
            $this->conn->exec($sql);
            $this->conn->exec($likesSql);
        } catch (Exception $e) {
            // ignore table creation errors
        }
    }

    public function readAll($search = '', $sortBy = 'date_pub', $order = 'DESC')
    {
        $query = "SELECT p.*, u.nom, u.prenom, u.photo 
                  FROM `" . $this->table_name . "` p 
                  JOIN utilisateurs u ON p.id_client = u.id_u 
                  WHERE 1=1";
        
        $params = [];
        
        if (!empty($search)) {
            $query .= " AND (p.titre LIKE ? OR p.contenu LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        // Gestion du tri
        $allowedSortFields = ['date_pub', 'likes', 'titre'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'date_pub';
        }
        
        if ($sortBy === 'likes') {
            $query .= " ORDER BY (SELECT COUNT(*) FROM publication_likes pl WHERE pl.id_pub = p.id_pub) $order, p.date_pub DESC";
        } else {
            $query .= " ORDER BY p.$sortBy $order";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    public function create()
    {
        $query = "INSERT INTO `" . $this->table_name . "` (titre, contenu, id_client, image, musique, likes)
                  VALUES (:titre, :contenu, :id_client, :image, :musique, 0)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':titre', $this->titre);
        $stmt->bindParam(':contenu', $this->contenu);
        $stmt->bindParam(':id_client', $this->id_client, PDO::PARAM_INT);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':musique', $this->musique);

        if ($stmt->execute()) {
            $this->id_pub = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    public function update()
    {
        $query = "UPDATE `" . $this->table_name . "` SET titre = :titre, contenu = :contenu, image = :image, musique = :musique WHERE id_pub = :id_pub";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':titre', $this->titre);
        $stmt->bindParam(':contenu', $this->contenu);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':musique', $this->musique);
        $stmt->bindParam(':id_pub', $this->id_pub, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function delete()
    {
        $query = "DELETE FROM `" . $this->table_name . "` WHERE id_pub = :id_pub";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_pub', $this->id_pub, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getById($id_pub)
    {
        $query = "SELECT p.*, u.nom, u.prenom, u.photo FROM `" . $this->table_name . "` p
                  JOIN `utilisateurs` u ON p.id_client = u.id_u
                  WHERE p.id_pub = :id_pub LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_pub', $id_pub, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function isOwner($id_pub, $id_client)
    {
        $query = "SELECT 1 FROM `" . $this->table_name . "` WHERE id_pub = ? AND id_client = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_pub, $id_client]);
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function toggleLike($userId)
    {
        $stmt = $this->conn->prepare("SELECT id_like FROM publication_likes WHERE id_pub = ? AND id_u = ?");
        $stmt->execute([$this->id_pub, $userId]);
        $liked = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($liked) {
            $delete = $this->conn->prepare("DELETE FROM publication_likes WHERE id_pub = ? AND id_u = ?");
            $delete->execute([$this->id_pub, $userId]);
            $result = false;
        } else {
            $insert = $this->conn->prepare("INSERT INTO publication_likes (id_pub, id_u) VALUES (?, ?)");
            $insert->execute([$this->id_pub, $userId]);
            $result = true;
        }

        $count = $this->getLikes();
        $updateLikes = $this->conn->prepare("UPDATE `" . $this->table_name . "` SET likes = ? WHERE id_pub = ?");
        $updateLikes->execute([$count, $this->id_pub]);

        return $result;
    }

    public function hasUserLiked($id_pub, $userId)
    {
        $stmt = $this->conn->prepare("SELECT 1 FROM publication_likes WHERE id_pub = ? AND id_u = ?");
        $stmt->execute([$id_pub, $userId]);
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserLikedPublicationIds($userId)
    {
        $stmt = $this->conn->prepare("SELECT id_pub FROM publication_likes WHERE id_u = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0) ?: [];
    }

    public function getLikes()
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM publication_likes WHERE id_pub = ?");
        $stmt->execute([$this->id_pub]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['total'] : 0;
    }

    public function getLikeCount($pubId)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM publication_likes WHERE id_pub = ?");
        $stmt->execute([$pubId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['total'] : 0;
    }
}

