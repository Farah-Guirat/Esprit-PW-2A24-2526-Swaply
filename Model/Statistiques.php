<?php
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
        $query = "SELECT SUM(likes) as total FROM publications";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }

    public function getRecentActivity() {
        $query = "(SELECT 'publication' as type, titre as detail, date_pub as date_act FROM publications)
                  UNION
                  (SELECT 'commentaire' as type, contenu as detail, date_com as date_act FROM commentaires)
                  ORDER BY date_act DESC LIMIT 5";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}