<?php
require_once __DIR__ . '/../config/Database.php';

class Statistiques {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function countTotalPublications() {
        $query = "SELECT COUNT(*) as total FROM publications";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function countTotalCommentaires() {
        $query = "SELECT COUNT(*) as total FROM commentaires";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function countTotalLikes() {
        $query = "SELECT COUNT(*) as total FROM publication_likes";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }

    public function countTotalStories() {
        $query = "SELECT COUNT(*) as total FROM stories";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }

    public function countTotalReports() {
        $query = "SELECT COUNT(*) as total FROM reports";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }

    public function getRecentActivity() {
        $query = "(SELECT 'publication' as type, CONCAT('Nouvelle publication: ', titre) as detail, date_pub as date_act FROM publications)
                  UNION
                  (SELECT 'commentaire' as type, CONCAT('Nouveau commentaire sur: ', p.titre) as detail, c.date_com as date_act
                   FROM commentaires c JOIN publications p ON c.id_pub = p.id_pub)
                  UNION
                  (SELECT 'story' as type, CONCAT('Nouvelle story de: ', u.nom) as detail, s.date_creation as date_act
                   FROM stories s JOIN utilisateurs u ON s.id_client = u.id_u)
                  ORDER BY date_act DESC LIMIT 10";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}