<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/Statistiques.php';
require_once __DIR__ . '/../model/Publication.php';
require_once __DIR__ . '/../model/Commentaire.php';
require_once __DIR__ . '/../model/Story.php';
require_once __DIR__ . '/../model/Report.php';

class AdminController {
    private $db;
    private $statsModel;
    private $pubModel;
    private $comModel;
    private $storyModel;
    private $reportModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->statsModel = new Statistiques($this->db);
        $this->pubModel = new Publication($this->db);
        $this->comModel = new Commentaire($this->db);
        $this->storyModel = new Story($this->db);
        $this->reportModel = new Report($this->db);
    }

    public function getDashboardData($sortLikes = 'date') {
        return [
            'total_pubs' => $this->statsModel->countTotalPublications(),
            'total_coms' => $this->statsModel->countTotalCommentaires(),
            'total_likes' => $this->statsModel->countTotalLikes(),
            'total_stories' => $this->statsModel->countTotalStories(),
            'total_reports' => $this->statsModel->countTotalReports(),
            'recent_activities' => $this->statsModel->getRecentActivity()->fetchAll(PDO::FETCH_ASSOC),
            'all_publications' => $this->getAllPublications($sortLikes),
            'all_stories' => $this->getAllStories(),
            'all_commentaires' => $this->getAllCommentaires(),
            'all_reports' => $this->getAllReports(),
            'all_likes' => $this->getAllLikes($sortLikes)
        ];
    }

    private function getAllPublications($sort = 'date') {
        $orderBy = $sort === 'likes' ? 'COALESCE(l.like_count, 0) DESC, p.date_pub DESC' : 'p.date_pub DESC';
        $query = "SELECT p.*, u.nom, u.prenom, u.photo, COALESCE(l.like_count, 0) AS likes FROM publications p
                  JOIN utilisateurs u ON p.id_client = u.id_u
                  LEFT JOIN (SELECT id_pub, COUNT(*) AS like_count FROM publication_likes GROUP BY id_pub) l ON p.id_pub = l.id_pub
                  ORDER BY $orderBy";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getAllStories() {
        $query = "SELECT s.*, u.nom, u.prenom, COALESCE(l.like_count, 0) AS likes FROM stories s
                  JOIN utilisateurs u ON s.id_client = u.id_u
                  LEFT JOIN (SELECT id_story, COUNT(*) AS like_count FROM story_likes GROUP BY id_story) l ON s.id_story = l.id_story
                  ORDER BY s.date_creation DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getAllLikes($sort = 'date') {
        $orderBy = $sort === 'likes' ? 'likes DESC' : 'p.date_pub DESC';
        $query = "SELECT p.id_pub, p.titre, u.nom, COUNT(l.id_like) AS likes, p.date_pub FROM publications p
                  JOIN utilisateurs u ON p.id_client = u.id_u
                  LEFT JOIN publication_likes l ON p.id_pub = l.id_pub
                  GROUP BY p.id_pub, p.titre, u.nom, p.date_pub
                  ORDER BY $orderBy";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPublicationStats($id_pub) {
        // Récupérer les infos de la publication
        $query = "SELECT p.*, u.nom as auteur, u.prenom FROM publications p
                  JOIN utilisateurs u ON p.id_client = u.id_u
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

        // Compter les likes en direct depuis publication_likes
        $query = "SELECT COUNT(*) as total FROM publication_likes WHERE id_pub = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id_pub]);
        $likeCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // Compter les reports
        $reportCount = $this->reportModel->getReportCountForPublication($id_pub);

        return [
            'id_pub' => $pub['id_pub'],
            'titre' => $pub['titre'],
            'contenu' => $pub['contenu'],
            'auteur' => $pub['auteur'] . ' ' . $pub['prenom'],
            'date_pub' => $pub['date_pub'],
            'likes' => $likeCount,
            'commentaires' => $comCount,
            'reports' => $reportCount,
            'image' => $pub['image']
        ];
    }

    public function getStoryStats($id_story) {
        // Récupérer les infos de la story
        $query = "SELECT s.*, u.nom as auteur, u.prenom FROM stories s
                  JOIN utilisateurs u ON s.id_client = u.id_u
                  WHERE s.id_story = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id_story]);
        $story = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$story) return false;

        // Compter les commentaires des stories
        $query = "SELECT COUNT(*) as count FROM story_comments WHERE id_story = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id_story]);
        $comCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Compter les likes des stories
        $query = "SELECT COUNT(*) as total FROM story_likes WHERE id_story = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id_story]);
        $likeCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        return [
            'id_story' => $story['id_story'],
            'contenu' => $story['contenu'],
            'auteur' => $story['auteur'] . ' ' . $story['prenom'],
            'date_creation' => $story['date_creation'],
            'likes' => $likeCount,
            'commentaires' => $comCount,
            'image' => $story['image']
        ];
    }

    private function getAllCommentaires() {
        $query = "SELECT com.*, u.nom, u.prenom, p.titre FROM commentaires com
                  JOIN utilisateurs u ON com.id_client = u.id_u
                  JOIN publications p ON com.id_pub = p.id_pub
                  ORDER BY com.date_com DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getAllReports() {
        $query = "SELECT r.*, u.nom, u.prenom, u.email,
                         p.titre as publication_title, p.contenu as publication_content,
                         c.contenu as comment_content
                  FROM reports r
                  JOIN utilisateurs u ON r.id_user_report = u.id_u
                  LEFT JOIN publications p ON r.id_pub = p.id_pub
                  LEFT JOIN commentaires c ON r.id_com = c.id_com
                  ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deletePublication($id) {
        $this->pubModel->id_pub = $id;
        return $this->pubModel->delete();
    }

    public function deleteStory($id) {
        $this->storyModel->id_story = $id;
        return $this->storyModel->delete();
    }

    public function deleteComment($id) {
        return $this->comModel->delete($id);
    }

    public function updateComment($id, $contenu) {
        $this->comModel->id_com = $id;
        $this->comModel->contenu = $contenu;
        return $this->comModel->update();
    }

    public function deleteReport($id) {
        $query = "DELETE FROM reports WHERE id_report = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }
}