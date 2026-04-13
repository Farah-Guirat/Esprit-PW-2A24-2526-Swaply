<?php
require_once __DIR__ . "/../config/config.php";

class Competence {

    public function getAll() {
        global $conn;
        return $conn->query("SELECT * FROM competences");
    }

    public function add($nom, $niveau) {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO competences(nom_competence, niveau) VALUES (?, ?)");
        $stmt->bind_param("ss", $nom, $niveau);
        return $stmt->execute();
    }

    public function delete($id) {
    global $conn;

    $conn->query("DELETE FROM projet_competence WHERE id_competence=$id");

    return $conn->query("DELETE FROM competences WHERE id_competence=$id");
}

    public function update($id, $nom, $niveau) {
        global $conn;
        $stmt = $conn->prepare("UPDATE competences SET nom_competence=?, niveau=? WHERE id_competence=?");
        $stmt->bind_param("ssi", $nom, $niveau, $id);
        return $stmt->execute();
    }
}
?>