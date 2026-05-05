<?php
require_once __DIR__ . '/../config/database.php';

class VideoCall {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Créer un nouvel appel vidéo
     * @return int ID de l'appel créé
     */
    public function create(int $id_conversation, int $id_initiateur, string $type_appel = '1to1'): int {
        $stmt = $this->pdo->prepare(
            "INSERT INTO video_calls (id_conversation, id_initiateur, type_appel, statut)
             VALUES (:id_conversation, :id_initiateur, :type_appel, 'en_attente')"
        );
        $stmt->execute([
            ':id_conversation' => $id_conversation,
            ':id_initiateur' => $id_initiateur,
            ':type_appel' => $type_appel
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Récupérer un appel vidéo par ID
     */
    public function getById(int $id_video_call): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT vc.*, 
                    u.nom AS nom_initiateur, 
                    u.prenom AS prenom_initiateur,
                    COUNT(DISTINCT vcp.id_user) AS nb_participants
             FROM video_calls vc
             JOIN utilisateurs u ON u.id_u = vc.id_initiateur
             LEFT JOIN video_call_participants vcp ON vcp.id_video_call = vc.id_video_call
             WHERE vc.id_video_call = :id
             GROUP BY vc.id_video_call"
        );
        $stmt->execute([':id' => $id_video_call]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Récupérer les appels actifs d'une conversation
     */
    public function getActiveByConversation(int $id_conversation): ?array {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT vc.*, 
                        u.nom AS nom_initiateur, 
                        u.prenom AS prenom_initiateur
                 FROM video_calls vc
                 JOIN utilisateurs u ON u.id_u = vc.id_initiateur
                 WHERE vc.id_conversation = :id_conversation 
                 AND vc.statut IN ('en_attente', 'en_cours')
                 ORDER BY vc.id_video_call DESC
                 LIMIT 1"
            );
            $stmt->execute([':id_conversation' => $id_conversation]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            // Si la colonne statut n'existe pas, chercher sans filtre statut
            if (strpos($e->getMessage(), 'Unknown column') !== false) {
                $stmt = $this->pdo->prepare(
                    "SELECT vc.*, 
                            u.nom AS nom_initiateur, 
                            u.prenom AS prenom_initiateur
                     FROM video_calls vc
                     JOIN utilisateurs u ON u.id_u = vc.id_initiateur
                     WHERE vc.id_conversation = :id_conversation 
                     ORDER BY vc.id_video_call DESC
                     LIMIT 1"
                );
                $stmt->execute([':id_conversation' => $id_conversation]);
                return $stmt->fetch() ?: null;
            }
            throw $e;
        }
    }

    /**
     * Ajouter un participant à un appel vidéo
     */
    public function addParticipant(int $id_video_call, int $id_user, string $statut = 'en_attente'): bool {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO video_call_participants (id_video_call, id_user, statut_participant)
                 VALUES (:id_video_call, :id_user, :statut)
                 ON DUPLICATE KEY UPDATE statut_participant = :statut"
            );
            return $stmt->execute([
                ':id_video_call' => $id_video_call,
                ':id_user' => $id_user,
                ':statut' => $statut
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Accepter un appel vidéo
     */
    public function acceptCall(int $id_video_call, int $id_user): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE video_call_participants 
             SET statut_participant = 'accepte', date_acceptation = NOW()
             WHERE id_video_call = :id_video_call AND id_user = :id_user"
        );
        
        if (!$stmt->execute([
            ':id_video_call' => $id_video_call,
            ':id_user' => $id_user
        ])) {
            return false;
        }

        // Si au moins un participant a accepté, démarrer l'appel
        $this->startCallIfNeeded($id_video_call);
        return true;
    }

    /**
     * Rejeter un appel vidéo
     */
    public function rejectCall(int $id_video_call, int $id_user): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE video_call_participants 
             SET statut_participant = 'rejete'
             WHERE id_video_call = :id_video_call AND id_user = :id_user"
        );
        return $stmt->execute([
            ':id_video_call' => $id_video_call,
            ':id_user' => $id_user
        ]);
    }

    /**
     * Démarrer l'appel si au moins un participant a accepté
     */
    private function startCallIfNeeded(int $id_video_call): void {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) as has_accepted FROM video_call_participants 
             WHERE id_video_call = :id AND statut_participant = 'accepte'"
        );
        $stmt->execute([':id' => $id_video_call]);
        $result = $stmt->fetch();

        if ($result['has_accepted'] > 0) {
            $stmt = $this->pdo->prepare(
                "UPDATE video_calls 
                 SET statut = 'en_cours', date_debut = NOW()
                 WHERE id_video_call = :id AND statut = 'en_attente'"
            );
            $stmt->execute([':id' => $id_video_call]);
        }
    }

    /**
     * Obtenir les participants d'un appel
     */
    public function getParticipants(int $id_video_call): array {
        $stmt = $this->pdo->prepare(
            "SELECT vcp.*, u.nom, u.prenom, u.email
             FROM video_call_participants vcp
             JOIN utilisateurs u ON u.id_u = vcp.id_user
             WHERE vcp.id_video_call = :id
             ORDER BY vcp.joined_at ASC"
        );
        $stmt->execute([':id' => $id_video_call]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Terminer un appel vidéo
     */
    public function endCall(int $id_video_call): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE video_calls 
             SET statut = 'termine', date_fin = NOW()
             WHERE id_video_call = :id"
        );
        
        if (!$stmt->execute([':id' => $id_video_call])) {
            return false;
        }

        // Optionnel: Calculer la durée si la colonne existe
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE video_calls 
                 SET duree_secondes = TIMESTAMPDIFF(SECOND, date_debut, date_fin)
                 WHERE id_video_call = :id"
            );
            $stmt->execute([':id' => $id_video_call]);
        } catch (PDOException $e) {
            // Colonne n'existe pas, ignorer
        }

        return true;
    }

    /**
     * Marquer un participant comme déconnecté
     */
    public function markParticipantDisconnected(int $id_video_call, int $id_user): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE video_call_participants 
             SET statut_participant = 'deconnecte', date_depart = NOW()
             WHERE id_video_call = :id_video_call AND id_user = :id_user"
        );
        
        if (!$stmt->execute([
            ':id_video_call' => $id_video_call,
            ':id_user' => $id_user
        ])) {
            return false;
        }

        // Vérifier s'il reste des participants actifs
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) as active_count FROM video_call_participants 
             WHERE id_video_call = :id AND statut_participant = 'accepte'"
        );
        $stmt->execute([':id' => $id_video_call]);
        $result = $stmt->fetch();

        // Si plus personne n'est actif, terminer l'appel
        if ($result['active_count'] == 0) {
            $this->endCall($id_video_call);
        }

        return true;
    }

    /**
     * Obtenir l'historique des appels d'une conversation
     */
    public function getHistoryByConversation(int $id_conversation, int $limit = 50, int $offset = 0): array {
        $stmt = $this->pdo->prepare(
            "SELECT vc.*, 
                    u.nom AS nom_initiateur, 
                    u.prenom AS prenom_initiateur,
                    COUNT(DISTINCT vcp.id_user) AS nb_participants
             FROM video_calls vc
             JOIN utilisateurs u ON u.id_u = vc.id_initiateur
             LEFT JOIN video_call_participants vcp ON vcp.id_video_call = vc.id_video_call
             WHERE vc.id_conversation = :id_conversation 
             AND vc.statut IN ('termine', 'rejete', 'manque')
             GROUP BY vc.id_video_call
             ORDER BY vc.date_fin DESC
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':id_conversation', $id_conversation, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Récupérer les appels manqués d'un utilisateur
     */
    public function getMissedCalls(int $id_user, int $limit = 20): array {
        $stmt = $this->pdo->prepare(
            "SELECT vc.*, 
                    u.nom AS nom_initiateur, 
                    u.prenom AS prenom_initiateur,
                    c.id_user1, c.id_user2
             FROM video_calls vc
             JOIN utilisateurs u ON u.id_u = vc.id_initiateur
             JOIN conversations c ON c.id_conversation = vc.id_conversation
             JOIN video_call_participants vcp ON vcp.id_video_call = vc.id_video_call
             WHERE vcp.id_user = :id_user
             AND vcp.statut_participant IN ('en_attente', 'rejete')
             AND vc.statut IN ('termine', 'rejete')
             AND (c.id_user1 = :id_user OR c.id_user2 = :id_user)
             ORDER BY vc.date_fin DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':id_user', $id_user, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Obtenir les statistiques d'un utilisateur
     */
    public function getUserStats(int $id_user): array {
        $stmt = $this->pdo->prepare(
            "SELECT 
                COUNT(DISTINCT CASE WHEN vc.id_initiateur = :id_user THEN vc.id_video_call END) as appels_emis,
                COUNT(DISTINCT CASE WHEN vcp.id_user = :id_user AND vc.id_initiateur != :id_user THEN vc.id_video_call END) as appels_recus,
                COUNT(DISTINCT CASE WHEN vcp.id_user = :id_user AND vcp.statut_participant = 'accepte' THEN vc.id_video_call END) as appels_acceptes,
                COALESCE(SUM(CASE WHEN vcp.id_user = :id_user THEN vc.duree_secondes ELSE 0 END), 0) as total_secondes
             FROM video_calls vc
             LEFT JOIN video_call_participants vcp ON vcp.id_video_call = vc.id_video_call
             WHERE vc.statut = 'termine' AND (vc.id_initiateur = :id_user OR vcp.id_user = :id_user)"
        );
        $stmt->execute([':id_user' => $id_user]);
        return $stmt->fetch() ?: [];
    }
}
