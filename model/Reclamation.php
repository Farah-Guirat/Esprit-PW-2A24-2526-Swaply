<?php
require_once __DIR__ . '/../config/database.php';

class Reclamation {

    public function getAll() {
        global $conn;
        $sql = "SELECT * FROM reclamations";
        return $conn->query($sql);
    }

    public function add($id_user, $description, $rating, $type, $username_cible) {
        global $conn;
        $sql  = "INSERT INTO reclamations (id_user, description, rating, type, username_cible) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $ok   = $stmt->execute([$id_user, $description, $rating, $type, $username_cible]);

        if ($ok) {
            $id_reclamation = $conn->lastInsertId();
            $sql2  = "INSERT INTO reponses (id_reclamation, contenu, status) VALUES (?, ?, ?)";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->execute([$id_reclamation, '', 'en cours']);
        }

        return $ok;
    }

    public function getById($id) {
        global $conn;
        $sql  = "SELECT * FROM reclamations WHERE id_reclamation=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function update($id, $description, $rating, $type) {
        global $conn;
        $sql  = "UPDATE reclamations SET description = ?, rating = ?, type = ? WHERE id_reclamation = ?";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([$description, $rating, $type, $id]);
    }

    public function delete($id) {
        global $conn;
        $sql  = "DELETE FROM reclamations WHERE id_reclamation = ?";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function updateStatut($id, $statut) {
        global $conn;
        $sql  = "UPDATE reclamations SET statut = ? WHERE id_reclamation = ?";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([$statut, $id]);
    }
}
?>