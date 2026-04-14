<?php

class User {

    private $conn;

    // ✔ FIX 1: connect PDO correctly
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // ✔ REGISTER (use this only)
    public function register($nom, $prenom, $email, $password, $genre, $telephone, $date_naissance) {

        $sql = "INSERT INTO utilisateurs
        (nom, prenom, email, password, genre, telephone, date_naissance)
        VALUES 
        (:nom, :prenom, :email, :password, :genre, :telephone, :date_naissance)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':prenom', $prenom);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':genre', $genre);
        $stmt->bindParam(':telephone', $telephone);
        $stmt->bindParam(':date_naissance', $date_naissance);

        return $stmt->execute();
    }

    // ✔ LOGIN (correct)
    public function login($email, $password)
    {
        $sql = "SELECT * FROM utilisateurs WHERE email = :email";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    public function getAllUsersExceptAdmin($adminEmail, $searchTerm = '')
    {
        if ($searchTerm === '') {
            $sql = "SELECT id_u, nom, prenom, email, genre, telephone, date_naissance
                    FROM utilisateurs
                    WHERE email != :adminEmail";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':adminEmail', $adminEmail);
        } else {
            $sql = "SELECT id_u, nom, prenom, email, genre, telephone, date_naissance
                    FROM utilisateurs
                    WHERE email != :adminEmail
                    AND (nom LIKE :term OR prenom LIKE :term OR email LIKE :term)";
            $stmt = $this->conn->prepare($sql);
            $term = '%' . $searchTerm . '%';
            $stmt->bindParam(':adminEmail', $adminEmail);
            $stmt->bindParam(':term', $term);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countUsersExceptAdmin($adminEmail, $searchTerm = '')
    {
        if ($searchTerm === '') {
            $sql = "SELECT COUNT(*) as total FROM utilisateurs WHERE email != :adminEmail";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':adminEmail', $adminEmail);
        } else {
            $sql = "SELECT COUNT(*) as total FROM utilisateurs
                    WHERE email != :adminEmail
                    AND (nom LIKE :term OR prenom LIKE :term OR email LIKE :term)";
            $stmt = $this->conn->prepare($sql);
            $term = '%' . $searchTerm . '%';
            $stmt->bindParam(':adminEmail', $adminEmail);
            $stmt->bindParam(':term', $term);
        }

        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }


    public function signup($data)
    {
        $sql = "INSERT INTO utilisateurs 
        (nom, prenom, email, password, genre, telephone, date_naissance)
        VALUES (:nom, :prenom, :email, :password, :genre, :telephone, :date_naissance)";

        $db = $this->db->connect();
        $stmt = $db->prepare($sql);

        return $stmt->execute($data);
    }


    public function getUserById($id)
    {
        $sql = "SELECT * FROM utilisateurs WHERE id_u = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function updateUser($id, $nom, $prenom, $email, $telephone, $date_naissance)
    {
        $sql = "UPDATE utilisateurs 
                SET nom = ?, 
                    prenom = ?, 
                    email = ?, 
                    telephone = ?, 
                    date_naissance = ?
                WHERE id_u = ?";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([$nom, $prenom, $email, $telephone, $date_naissance, $id]);
    }


public function deleteUser($id)
{
    try {
        $query = "DELETE FROM utilisateurs WHERE id_u = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        $result = $stmt->execute();
        
        if (!$result) {
            error_log("Erreur SQL lors de la suppression: " . implode(", ", $stmt->errorInfo()));
        }
        
        return $result;
    } catch (Exception $e) {
        error_log("Exception lors de la suppression du compte: " . $e->getMessage());
        return false;
    }
}

    
}
?>