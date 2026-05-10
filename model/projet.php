<?php
require_once __DIR__ . "/../config/Database.php";

class Projet {

    public function getAll($id_u = null) {
        $pdo = Database::getInstance();

        if ($id_u !== null) {
            $stmt = $pdo->prepare("SELECT * FROM projets WHERE id_u = ?");
            $stmt->execute([$id_u]);
        } else {
            $stmt = $pdo->query("SELECT * FROM projets");
        }

        return $stmt->fetchAll();
    }

   public function add($nom, $desc, $statut, $id_u) {
    $pdo = Database::getInstance();
    $stmt = $pdo->prepare("INSERT INTO projets(nom_projet, description, statut, id_u) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$nom, $desc, $statut, $id_u]);
}

    public function getStatistiquesStatut() {
        $pdo = Database::getInstance();
        $stmt = $pdo->query("SELECT statut, COUNT(*) as total FROM projets GROUP BY statut");
        return $stmt->fetchAll();
    }

    public function getStatistiquesDate() {
        $pdo = Database::getInstance();
        $stmt = $pdo->query("
            SELECT DATE_FORMAT(date_creation, '%Y-%m') as mois, COUNT(*) as total 
            FROM projets 
            GROUP BY mois 
            ORDER BY mois
        ");
        return $stmt->fetchAll();
    }

    public function delete($id) {
        $pdo = Database::getInstance();

        // 1. delete links first
        $stmt = $pdo->prepare("DELETE FROM projet_competence WHERE id_projet = ?");
        $stmt->execute([$id]);

        // 2. delete project
        $stmt = $pdo->prepare("DELETE FROM projets WHERE id_projet = ?");
        return $stmt->execute([$id]);
    }

    public function update($id, $nom, $desc, $statut) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("UPDATE projets SET nom_projet = ?, description = ?, statut = ? WHERE id_projet = ?");
        return $stmt->execute([$nom, $desc, $statut, $id]);
    }

    public function getCompetences($id_projet) {
        $pdo = Database::getInstance();
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
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("UPDATE projets SET is_favorite = 1 WHERE id_projet = ?");
        return $stmt->execute([$id]);
    }

    public function hide($id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("UPDATE projets SET is_hidden = 1 WHERE id_projet = ?");
        return $stmt->execute([$id]);
    }

    public function unhide($id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("UPDATE projets SET is_hidden = 0 WHERE id_projet = ?");
        return $stmt->execute([$id]);
    }

    public function unarchive($id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("UPDATE projets SET is_archived = 0 WHERE id_projet = ?");
        return $stmt->execute([$id]);
    }

    public function archive($id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("UPDATE projets SET is_archived = 1 WHERE id_projet = ?");
        return $stmt->execute([$id]);
    }
}
?>