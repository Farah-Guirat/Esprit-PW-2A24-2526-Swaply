<?php
require_once __DIR__ . "/../config/config.php";

class Competence {

    public function getAll() {
        $pdo = config::getConnexion();
        $stmt = $pdo->query("SELECT * FROM competences");
        return $stmt->fetchAll();
    }

    public function add($nom, $niveau) {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("INSERT INTO competences(nom_competence, niveau) VALUES (?, ?)");
        return $stmt->execute([$nom, $niveau]);
    }

    public function delete($id) {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("DELETE FROM projet_competence WHERE id_competence = ?");
        $stmt->execute([$id]);

        $stmt = $pdo->prepare("DELETE FROM competences WHERE id_competence = ?");
        return $stmt->execute([$id]);
    }

    public function update($id, $nom, $niveau) {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("UPDATE competences SET nom_competence = ?, niveau = ? WHERE id_competence = ?");
        return $stmt->execute([$nom, $niveau, $id]);
    }
}
?>