<?php
class Publication {
    private $conn;
    private $table_name = "publications";

    public $id_pub;
    public $titre;
    public $contenu;
    public $id_client;
    public $image;
    public $likes;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getOrCreateClient($nom) {
        $query = "SELECT id_client FROM clients WHERE nom = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$nom]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $row['id_client'];
        } else {
            $query = "INSERT INTO clients (nom, email) VALUES (?, ?)";
            $stmt = $this->conn->prepare($query);
            $email = strtolower(str_replace(' ', '', $nom)) . "@swaply.com";
            $stmt->execute([$nom, $email]);
            return $this->conn->lastInsertId();
        }
    }

    public function readAll($search = '', $sort = 'date') {
        $query = "SELECT p.*, c.nom FROM " . $this->table_name . " p 
                  JOIN clients c ON p.id_client = c.id_client";
        if (!empty($search)) {
            $query .= " WHERE p.titre LIKE :search";
        }
        $query .= " ORDER BY " . ($sort == 'likes' ? 'p.likes DESC' : 'p.date_pub DESC');
        $stmt = $this->conn->prepare($query);
        if (!empty($search)) {
            $stmt->bindValue(':search', '%' . $search . '%');
        }
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (titre, contenu, id_client, image, likes) 
                  VALUES (:titre, :contenu, :id_client, :image, 0)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":titre", $this->titre);
        $stmt->bindParam(":contenu", $this->contenu);
        $stmt->bindParam(":id_client", $this->id_client);
        $stmt->bindParam(":image", $this->image);
        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET titre = :titre, contenu = :contenu, image = :image 
                  WHERE id_pub = :id_pub";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":titre", $this->titre);
        $stmt->bindParam(":contenu", $this->contenu);
        $stmt->bindParam(":image", $this->image);
        $stmt->bindParam(":id_pub", $this->id_pub);
        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_pub = :id_pub";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_pub", $this->id_pub);
        return $stmt->execute();
    }

    public function incrementLike() {
        $query = "UPDATE " . $this->table_name . " SET likes = likes + 1 WHERE id_pub = :id_pub";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_pub", $this->id_pub);
        return $stmt->execute();
    }

    public function getLikes() {
        $query = "SELECT likes FROM " . $this->table_name . " WHERE id_pub = :id_pub";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_pub", $this->id_pub);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['likes'] : 0;
    }
}