<?php
require_once __DIR__ . "/../config/Database.php";

class Competence {

    public function getAll() {
        $pdo = Database::getInstance();
        $stmt = $pdo->query("SELECT * FROM competences");
        return $stmt->fetchAll();
    }

   public function add($nom, $niveau, $id_u) {
    $pdo = Database::getInstance();
    $stmt = $pdo->prepare("INSERT INTO competences(nom_competence, niveau, id_u) VALUES (?, ?, ?)");
    return $stmt->execute([$nom, $niveau, $id_u]);
}

    public function delete($id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("DELETE FROM projet_competence WHERE id_competence = ?");
        $stmt->execute([$id]);

        $stmt = $pdo->prepare("DELETE FROM competences WHERE id_competence = ?");
        return $stmt->execute([$id]);
    }

    public function update($id, $nom, $niveau) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("UPDATE competences SET nom_competence = ?, niveau = ? WHERE id_competence = ?");
        return $stmt->execute([$nom, $niveau, $id]);
    }
}
?>