<?php
require_once __DIR__ . '/../config/Database.php';

class Report
{
    private $conn;
    private $table_name = 'reports';

    public $id_report;
    public $id_pub;
    public $id_comment;
    public $id_user;
    public $reason;
    public $description;
    public $status;
    public $created_at;

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
        $sql = "CREATE TABLE IF NOT EXISTS `reports` (
            `id_report` INT(11) NOT NULL AUTO_INCREMENT,
            `id_user_report` INT(11) NOT NULL,
            `id_pub` INT(11) DEFAULT NULL,
            `id_com` INT(11) DEFAULT NULL,
            `reason` VARCHAR(255) NOT NULL,
            `description` TEXT DEFAULT NULL,
            `status` ENUM('pending','in_review','resolved','rejected') NOT NULL DEFAULT 'pending',
            `token` VARCHAR(64) DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_report`),
            UNIQUE KEY `token` (`token`),
            KEY `idx_id_user_report` (`id_user_report`),
            KEY `idx_id_pub` (`id_pub`),
            KEY `idx_id_com` (`id_com`),
            KEY `idx_status` (`status`),
            CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`id_user_report`) REFERENCES `utilisateurs` (`id_u`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`id_pub`) REFERENCES `publications` (`id_pub`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        try {
            $this->conn->exec($sql);
        } catch (Exception $e) {
            // ignore table creation errors
        }
    }

    public function getReportCountForPublication($id_pub)
    {
        $query = "SELECT COUNT(*) as count FROM `" . $this->table_name . "` WHERE id_pub = ? AND status IN ('pending', 'in_review')";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_pub]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int) $result['count'] : 0;
    }

    public function getReportsForPublication($id_pub)
    {
        $query = "SELECT r.*, u.nom, u.prenom, u.email FROM `" . $this->table_name . "` r
                  JOIN `utilisateurs` u ON r.id_user_report = u.id_u
                  WHERE r.id_pub = ? AND r.status IN ('pending', 'in_review')
                  ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_pub]);
        return $stmt;
    }

    public function markReportsAsProcessed($id_pub)
    {
        $query = "UPDATE `" . $this->table_name . "` SET status = 'resolved' WHERE id_pub = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id_pub]);
    }

    public function createReport($id_user_report, $reason, $description = '', $id_pub = null, $id_comment = null)
    {
        $query = "INSERT INTO `" . $this->table_name . "` (id_user_report, id_pub, id_com, reason, description, status)
                  VALUES (?, ?, ?, ?, ?, 'pending')";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id_user_report, $id_pub, $id_comment, $reason, $description]);
    }

    public function checkExistingReport($id_user, $id_pub)
    {
        $query = "SELECT 1 FROM `" . $this->table_name . "` WHERE id_user_report = ? AND id_pub = ? AND status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_user, $id_pub]);
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllReports()
    {
        $query = "SELECT r.*, u.nom, u.prenom, u.email, 
                         p.titre as publication_title, p.contenu as publication_content,
                         c.contenu as comment_content
                  FROM `" . $this->table_name . "` r
                  JOIN `utilisateurs` u ON r.id_user_report = u.id_u
                  LEFT JOIN `publications` p ON r.id_pub = p.id_pub
                  LEFT JOIN `commentaires` c ON r.id_com = c.id_com
                  ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
