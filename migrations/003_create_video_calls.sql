-- Table pour gérer les appels vidéo (1-to-1 et groupe)
CREATE TABLE IF NOT EXISTS video_calls (
    id_video_call INT AUTO_INCREMENT PRIMARY KEY,
    id_conversation INT NOT NULL,
    id_initiateur INT NOT NULL,
    type_appel ENUM('1to1', 'groupe') NOT NULL DEFAULT '1to1',
    statut ENUM('en_attente', 'en_cours', 'termine', 'rejete', 'manque') NOT NULL DEFAULT 'en_attente',
    date_debut DATETIME,
    date_fin DATETIME,
    duree_secondes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_conversation) REFERENCES conversations(id_conversation) ON DELETE CASCADE,
    FOREIGN KEY (id_initiateur) REFERENCES utilisateurs(id_u) ON DELETE CASCADE,
    INDEX idx_conversation (id_conversation),
    INDEX idx_statut (statut),
    INDEX idx_date_debut (date_debut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table pour tracker les participants aux appels vidéo
CREATE TABLE IF NOT EXISTS video_call_participants (
    id_participant INT AUTO_INCREMENT PRIMARY KEY,
    id_video_call INT NOT NULL,
    id_user INT NOT NULL,
    statut_participant ENUM('en_attente', 'accepte', 'rejete', 'deconnecte') NOT NULL DEFAULT 'en_attente',
    date_acceptation DATETIME,
    date_depart DATETIME,
    duree_participation_secondes INT DEFAULT 0,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_video_call) REFERENCES video_calls(id_video_call) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES utilisateurs(id_u) ON DELETE CASCADE,
    UNIQUE KEY unique_call_user (id_video_call, id_user),
    INDEX idx_video_call (id_video_call),
    INDEX idx_user (id_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
