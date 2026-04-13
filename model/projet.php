<?php
require_once __DIR__ . "/../config/config.php";

class Projet {

    public function getAll() {
        global $conn;
        return $conn->query("SELECT * FROM projets");
    }

    public function add($nom, $desc, $statut) {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO projets(nom_projet, description, statut) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nom, $desc, $statut);
        return $stmt->execute();
    }

   public function delete($id) {
    global $conn;

    // 1. delete links first
    $conn->query("DELETE FROM projet_competence WHERE id_projet=$id");

    // 2. delete project
    return $conn->query("DELETE FROM projets WHERE id_projet=$id");
}

    public function update($id, $nom, $desc, $statut) {
        global $conn;
        $stmt = $conn->prepare("UPDATE projets SET nom_projet=?, description=?, statut=? WHERE id_projet=?");
        $stmt->bind_param("sssi", $nom, $desc, $statut, $id);
        return $stmt->execute();
    }

    public function getCompetences($id_projet) {
    global $conn;
    return $conn->query("
        SELECT c.nom_competence , c.niveau
        FROM competences c
        JOIN projet_competence pc ON c.id_competence = pc.id_competence
        WHERE pc.id_projet = $id_projet
    ");
}
}
?>