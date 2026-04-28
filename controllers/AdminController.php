<?php
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

    public function getDashboardData($sortLikes = 'date') {
        return [
            'total_pubs' => $this->statsModel->countTotalPublications(),
            'total_coms' => $this->statsModel->countTotalCommentaires(),
            'total_likes' => $this->statsModel->countTotalLikes(),
            'recent_activities' => $this->statsModel->getRecentActivity()->fetchAll(PDO::FETCH_ASSOC),
            'all_publications' => $this->getAllPublications(),
            'all_commentaires' => $this->getAllCommentaires(),
            'all_likes' => $this->getAllLikes($sortLikes)
        ];
    }

    private function getAllPublications() {
        $query = "SELECT p.*, c.nom FROM publications p 
                  JOIN clients c ON p.id_client = c.id_client 
                  ORDER BY p.date_pub DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getAllLikes($sort = 'date') {
        $orderBy = $sort === 'likes' ? 'p.likes DESC' : 'p.date_pub DESC';
        $query = "SELECT p.*, c.nom FROM publications p 
                  JOIN clients c ON p.id_client = c.id_client 
                  WHERE p.likes > 0
                  ORDER BY $orderBy";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPublicationStats($id_pub) {
        // Récupérer les infos de la publication
        $query = "SELECT p.*, c.nom as auteur FROM publications p 
                  JOIN clients c ON p.id_client = c.id_client 
                  WHERE p.id_pub = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id_pub]);
        $pub = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pub) return false;

        // Compter les commentaires
        $query = "SELECT COUNT(*) as count FROM commentaires WHERE id_pub = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id_pub]);
        $comCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        return [
            'id_pub' => $pub['id_pub'],
            'titre' => $pub['titre'],
            'auteur' => $pub['auteur'],
            'date_pub' => $pub['date_pub'],
            'likes' => $pub['likes'] ?? 0,
            'commentaires' => $comCount
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