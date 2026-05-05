-- Créer les tables pour le système d'appels vidéo Swaply

-- Table des appels vidéo
CREATE TABLE IF NOT EXISTS video_calls (
    id_video_call INT AUTO_INCREMENT PRIMARY KEY,
    id_conversation INT NOT NULL,
    id_initiateur INT NOT NULL,
    type_appel VARCHAR(10) DEFAULT '1to1' COMMENT '1to1 ou group',
    statut VARCHAR(20) DEFAULT 'pending' COMMENT 'pending, active, ended, declined',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_debut TIMESTAMP NULL,
    date_fin TIMESTAMP NULL,
    duree INT DEFAULT 0 COMMENT 'Durée en secondes',
    INDEX idx_conversation (id_conversation),
    INDEX idx_initiateur (id_initiateur),
    INDEX idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des participants aux appels vidéo
CREATE TABLE IF NOT EXISTS video_call_participants (
    id_participant INT AUTO_INCREMENT PRIMARY KEY,
    id_video_call INT NOT NULL,
    id_user INT NOT NULL,
    date_joined TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_left TIMESTAMP NULL,
    a_accepte TINYINT(1) DEFAULT 0,
    a_rejecte TINYINT(1) DEFAULT 0,
    FOREIGN KEY (id_video_call) REFERENCES video_calls(id_video_call) ON DELETE CASCADE,
    INDEX idx_video_call (id_video_call),
    INDEX idx_user (id_user),
    UNIQUE KEY unique_call_user (id_video_call, id_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index pour les performances
CREATE INDEX idx_video_calls_creation ON video_calls(date_creation);
CREATE INDEX idx_video_calls_statut_conversation ON video_calls(statut, id_conversation);
CREATE INDEX idx_participants_joined ON video_call_participants(date_joined);
