<?php
// model/Commentaire.php
class Commentaire {
    private $conn;
    private $table_name = "commentaires";

    public $id_com;
    public $id_pub;
    public $id_client;
    public $contenu;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (id_pub, id_client, contenu) VALUES (:id_pub, :id_client, :contenu)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_pub", $this->id_pub);
        $stmt->bindParam(":id_client", $this->id_client);
        $stmt->bindParam(":contenu", htmlspecialchars(strip_tags($this->contenu)));
        return $stmt->execute();
    }

    public function readByPub($id_pub) {
        $query = "SELECT com.*, c.nom FROM " . $this->table_name . " com 
                  JOIN clients c ON com.id_client = c.id_client 
                  WHERE com.id_pub = ? ORDER BY com.date_com ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_pub]);
        return $stmt;
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_com = ?";
        return $this->conn->prepare($query)->execute([$id]);
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET contenu = :contenu WHERE id_com = :id_com";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":contenu", htmlspecialchars(strip_tags($this->contenu)));
        $stmt->bindParam(":id_com", $this->id_com);
        return $stmt->execute();
    }
}