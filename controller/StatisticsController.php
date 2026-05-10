<?php
require_once __DIR__ . '/../model/Message.php';
require_once __DIR__ . '/../model/Conversation.php';

class StatisticsController {

    private Message $messageModel;
    private Conversation $conversationModel;

    public function __construct() {
        $this->messageModel      = new Message();
        $this->conversationModel = new Conversation();
    }

    public function getStats(): array {
        return [
            'conversations'       => $this->getConversationStats(),
            'messages'            => $this->getMessageStats(),
            'activite'            => $this->getActivityStats(),
            'fichiers'            => $this->getFileStats(),
        ];
    }

    // ─── Statistiques conversations ──────────────────────────────────────────
    private function getConversationStats(): array {
        $pdo = Database::getInstance()->getConnection();

        // Total conversations
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM conversations");
        $total = (int)$stmt->fetchColumn();

        // Conversations actives
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM conversations WHERE statut = 'active'");
        $active = (int)$stmt->fetchColumn();

        // Conversations fermées
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM conversations WHERE statut = 'fermee'");
        $closed = (int)$stmt->fetchColumn();

        // Conversations ce mois
        $stmt = $pdo->query(
            "SELECT COUNT(*) as total FROM conversations 
             WHERE MONTH(date_creation) = MONTH(NOW()) 
             AND YEAR(date_creation) = YEAR(NOW())"
        );
        $thisMonth = (int)$stmt->fetchColumn();

        // Utilisateurs uniques dans les conversations
        $stmt = $pdo->query(
            "SELECT COUNT(DISTINCT id_user1) + COUNT(DISTINCT id_user2) as total 
             FROM conversations"
        );
        $uniqueUsers = (int)$stmt->fetchColumn();

        // Moyenne messages par conversation
        $stmt = $pdo->query(
            "SELECT AVG(msg_count) as avg FROM (
                SELECT COUNT(*) as msg_count FROM messages 
                GROUP BY id_conversation
            ) as t"
        );
        $avgMsgsPerConv = round((float)$stmt->fetchColumn(), 1);

        return [
            'total'              => $total,
            'actives'            => $active,
            'fermees'            => $closed,
            'ce_mois'            => $thisMonth,
            'utilisateurs_uniq'  => $uniqueUsers,
            'msg_moyen_par_conv' => $avgMsgsPerConv,
        ];
    }

    // ─── Statistiques messages ───────────────────────────────────────────────
    private function getMessageStats(): array {
        $pdo = Database::getInstance()->getConnection();

        // Total messages
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM messages");
        $total = (int)$stmt->fetchColumn();

        // Messages ce mois
        $stmt = $pdo->query(
            "SELECT COUNT(*) as total FROM messages 
             WHERE MONTH(date_envoi) = MONTH(NOW()) 
             AND YEAR(date_envoi) = YEAR(NOW())"
        );
        $thisMonth = (int)$stmt->fetchColumn();

        // Messages aujourd'hui
        $stmt = $pdo->query(
            "SELECT COUNT(*) as total FROM messages 
             WHERE DATE(date_envoi) = DATE(NOW())"
        );
        $today = (int)$stmt->fetchColumn();

        // Messages avec fichiers
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM messages WHERE fichier_path IS NOT NULL");
        $withFiles = (int)$stmt->fetchColumn();

        // Messages lus vs non lus
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM messages WHERE lu = 1");
        $read = (int)$stmt->fetchColumn();
        $unread = $total - $read;

        // Longueur moyenne des messages
        $stmt = $pdo->query("SELECT AVG(LENGTH(contenu)) as avg FROM messages");
        $avgLength = round((float)$stmt->fetchColumn(), 0);

        return [
            'total'         => $total,
            'ce_mois'       => $thisMonth,
            'aujourd_hui'   => $today,
            'avec_fichiers' => $withFiles,
            'lus'           => $read,
            'non_lus'       => $unread,
            'longueur_avg'  => $avgLength,
        ];
    }

    // ─── Statistiques activité ───────────────────────────────────────────────
    private function getActivityStats(): array {
        $pdo = Database::getInstance()->getConnection();

        // Messages par jour (derniers 7 jours)
        $stmt = $pdo->query(
            "SELECT DATE(date_envoi) as jour, COUNT(*) as count 
             FROM messages 
             WHERE date_envoi >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             GROUP BY DATE(date_envoi)
             ORDER BY jour ASC"
        );
        $messagesParJour = [];
        foreach ($stmt->fetchAll() as $row) {
            $messagesParJour[] = [
                'jour'  => $row['jour'],
                'count' => (int)$row['count'],
            ];
        }

        // Top 5 utilisateurs (par nombre de messages)
        $stmt = $pdo->query(
            "SELECT u.id_u, u.prenom, u.nom, COUNT(m.id_message) as count
             FROM messages m
             JOIN utilisateurs u ON u.id_u = m.id_expediteur
             GROUP BY m.id_expediteur
             ORDER BY count DESC
             LIMIT 5"
        );
        $topUsers = $stmt->fetchAll();

        // Heures d'activité (derniers 7 jours)
        $stmt = $pdo->query(
            "SELECT HOUR(date_envoi) as heure, COUNT(*) as count 
             FROM messages 
             WHERE date_envoi >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             GROUP BY HOUR(date_envoi)
             ORDER BY heure ASC"
        );
        $activiteParHeure = [];
        for ($h = 0; $h < 24; $h++) {
            $activiteParHeure[$h] = 0;
        }
        foreach ($stmt->fetchAll() as $row) {
            $activiteParHeure[(int)$row['heure']] = (int)$row['count'];
        }

        return [
            'messages_par_jour' => $messagesParJour,
            'top_users'         => $topUsers,
            'activite_par_heure' => $activiteParHeure,
        ];
    }

    // ─── Statistiques fichiers ───────────────────────────────────────────────
    private function getFileStats(): array {
        $pdo = Database::getInstance()->getConnection();

        // Total fichiers
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM messages WHERE fichier_path IS NOT NULL");
        $totalFiles = (int)$stmt->fetchColumn();

        // Total taille fichiers
        $stmt = $pdo->query("SELECT SUM(fichier_taille) as total FROM messages WHERE fichier_path IS NOT NULL");
        $totalSize = (int)($stmt->fetchColumn() ?? 0);

        // Taille moyenne
        $avgSize = $totalFiles > 0 ? round($totalSize / $totalFiles, 0) : 0;

        // Fichiers par type
        $stmt = $pdo->query(
            "SELECT 
                CASE 
                    WHEN fichier_type LIKE 'application/pdf%' THEN 'PDF'
                    WHEN fichier_type LIKE 'image/%' THEN 'Image'
                    WHEN fichier_type LIKE 'application/vnd%' THEN 'Office'
                    WHEN fichier_type LIKE 'text/%' THEN 'Texte'
                    WHEN fichier_type LIKE 'application/zip%' THEN 'Archive'
                    ELSE 'Autre'
                END as type,
                COUNT(*) as count,
                SUM(fichier_taille) as total_size
             FROM messages 
             WHERE fichier_path IS NOT NULL
             GROUP BY type
             ORDER BY count DESC"
        );
        $filesByType = $stmt->fetchAll();

        // Fichiers ce mois
        $stmt = $pdo->query(
            "SELECT COUNT(*) as total FROM messages 
             WHERE fichier_path IS NOT NULL
             AND MONTH(date_envoi) = MONTH(NOW()) 
             AND YEAR(date_envoi) = YEAR(NOW())"
        );
        $thisMonth = (int)$stmt->fetchColumn();

        return [
            'total'      => $totalFiles,
            'taille_tot' => $totalSize,
            'taille_avg' => $avgSize,
            'par_type'   => $filesByType,
            'ce_mois'    => $thisMonth,
        ];
    }

    // Fonction helper pour formater tailles
    public static function formatBytes(int $bytes): string {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    public function view(): void {
        $stats = $this->getStats();
        require __DIR__ . '/../view/Back/statistics.php';
    }
}

// Router
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    if (!class_exists('Database')) require_once __DIR__ . '/../config/database.php';
    $ctrl = new StatisticsController();
    $action = $_GET['action'] ?? 'view';
    if ($action === 'view') $ctrl->view();
    else header('Location: ../view/Back/index.php');
}
?>
