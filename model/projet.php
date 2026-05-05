<?php
require_once __DIR__ . "/../config/config.php";

class Projet {

    public function getAll() {
        
        $pdo = config::getConnexion();
        $stmt = $pdo->query("SELECT * FROM projets");
        return $stmt->fetchAll();
    }
    

    public function add($nom, $desc, $statut) {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("INSERT INTO projets(nom_projet, description, statut) VALUES (?, ?, ?)");
        return $stmt->execute([$nom, $desc, $statut]);
    }

    public function getStatistiquesStatut() {
    $pdo = config::getConnexion();
    $stmt = $pdo->query("SELECT statut, COUNT(*) as total FROM projets GROUP BY statut");
    return $stmt->fetchAll();
}

public function getStatistiquesDate() {
    $pdo = config::getConnexion();
    $stmt = $pdo->query("
        SELECT DATE_FORMAT(date_creation, '%Y-%m') as mois, COUNT(*) as total 
        FROM projets 
        GROUP BY mois 
        ORDER BY mois
    ");
    return $stmt->fetchAll();
}

    public function delete($id) {
        $pdo = config::getConnexion();

        // 1. delete links first
        $stmt = $pdo->prepare("DELETE FROM projet_competence WHERE id_projet = ?");
        $stmt->execute([$id]);

        // 2. delete project
        $stmt = $pdo->prepare("DELETE FROM projets WHERE id_projet = ?");
        return $stmt->execute([$id]);
    }

    public function update($id, $nom, $desc, $statut) {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("UPDATE projets SET nom_projet = ?, description = ?, statut = ? WHERE id_projet = ?");
        return $stmt->execute([$nom, $desc, $statut, $id]);
    }

    public function getCompetences($id_projet) {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("
            SELECT c.nom_competence, c.niveau
            FROM competences c
            JOIN projet_competence pc ON c.id_competence = pc.id_competence
            WHERE pc.id_projet = ?
        ");
        $stmt->execute([$id_projet]);
        return $stmt->fetchAll();
    }

    public function setFavorite($id) {
    $pdo = config::getConnexion();
    $stmt = $pdo->prepare("UPDATE projets SET is_favorite = 1 WHERE id_projet = ?");
    return $stmt->execute([$id]);
}

public function hide($id) {
    $pdo = config::getConnexion();
    $stmt = $pdo->prepare("UPDATE projets SET is_hidden = 1 WHERE id_projet = ?");
    return $stmt->execute([$id]);
}

public function unhide($id) {
    $pdo = config::getConnexion();
    $stmt = $pdo->prepare("UPDATE projets SET is_hidden = 0 WHERE id_projet = ?");
    return $stmt->execute([$id]);
}

public function unarchive($id) {
    $pdo = config::getConnexion();
    $stmt = $pdo->prepare("UPDATE projets SET is_archived = 0 WHERE id_projet = ?");
    return $stmt->execute([$id]);
}

public function archive($id) {
    $pdo = config::getConnexion();
    $stmt = $pdo->prepare("UPDATE projets SET is_archived = 1 WHERE id_projet = ?");
    return $stmt->execute([$id]);
}
}
?>