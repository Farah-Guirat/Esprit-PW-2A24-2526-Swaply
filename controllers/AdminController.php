<?php
// controllers/AdminController.php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/Statistiques.php';
require_once __DIR__ . '/../model/Publication.php';
require_once __DIR__ . '/../model/Commentaire.php';

class AdminController {
    private $db;
    private $statsModel;
    private $pubModel;
    private $comModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->statsModel = new Statistiques($this->db);
        $this->pubModel = new Publication($this->db);
        $this->comModel = new Commentaire($this->db);
    }

    // Prépare toutes les données pour le Dashboard
    public function getDashboardData() {
        return [
            'total_pubs' => $this->statsModel->countTotalPublications(),
            'total_coms' => $this->statsModel->countTotalCommentaires(),
            'recent_activities' => $this->statsModel->getRecentActivity()->fetchAll(PDO::FETCH_ASSOC),
            'all_publications' => $this->pubModel->readAll()->fetchAll(PDO::FETCH_ASSOC),
            'all_commentaires' => $this->getAllCommentaires()
        ];
    }

    private function getAllCommentaires() {
        $query = "SELECT com.*, c.nom, p.titre FROM commentaires com 
                  JOIN clients c ON com.id_client = c.id_client 
                  JOIN publications p ON com.id_pub = p.id_pub 
                  ORDER BY com.date_com DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Logique pour supprimer une publication depuis le backend
    public function deleteAction($id) {
        $this->pubModel->id_pub = $id;
        return $this->pubModel->delete();
    }

    public function deleteComment($id) {
        return $this->comModel->delete($id);
    }

    public function updateComment($id, $contenu) {
        $this->comModel->id_com = $id;
        $this->comModel->contenu = $contenu;
        return $this->comModel->update();
    }
}