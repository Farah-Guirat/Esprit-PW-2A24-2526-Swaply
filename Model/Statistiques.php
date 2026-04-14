<?php
// model/Statistiques.php
class Statistiques {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Nombre total de publications
    public function countTotalPublications() {
        $query = "SELECT COUNT(*) as total FROM publications";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // Nombre total de commentaires
    public function countTotalCommentaires() {
        $query = "SELECT COUNT(*) as total FROM commentaires";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // Récupérer les 5 dernières activités (Publications ou Commentaires)
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