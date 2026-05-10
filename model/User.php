<?php

class User {

    private $conn;

    // ✔ FIX 1: connect PDO correctly
    public function __construct($conn) {
        $this->conn = $conn;
    }

    private function normalizeFaceId(string $faceId): string {
        return rtrim(strtr($faceId, '+/', '-_'), '=');
    }

    private function alternateFaceId(string $faceId): string {
        return rtrim(strtr($faceId, '-_', '+/'), '=');
    }

    private function padBase64(string $base64): string {
        $padding = 4 - (strlen($base64) % 4);
        if ($padding < 4) {
            $base64 .= str_repeat('=', $padding);
        }
        return $base64;
    }

    private function faceIdVariants(string $faceId): array {
        $normalized = $this->normalizeFaceId($faceId);
        $alternate = $this->alternateFaceId($faceId);
        return [
            $normalized,
            $alternate,
            $this->padBase64($normalized),
            $this->padBase64($alternate),
        ];
    }

    // ✔ REGISTER (use this only)
    public function register($nom, $prenom, $email, $password, $genre, $telephone, $date_naissance, $face_id = null, $email_verified = 0) {
        // Check if email already exists
        if ($this->getUserByEmail($email)) {
            throw new Exception("L'email est déjà utilisé.");
        }

        $sql = "INSERT INTO utilisateurs
        (nom, prenom, email, password, genre, telephone, date_naissance, photo, face_id, banned, email_verified)
        VALUES 
        (:nom, :prenom, :email, :password, :genre, :telephone, :date_naissance, NULL, :face_id, 0, :email_verified)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':prenom', $prenom);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':genre', $genre);
        $stmt->bindParam(':telephone', $telephone);
        $stmt->bindParam(':date_naissance', $date_naissance);
        $stmt->bindParam(':face_id', $face_id);
        $stmt->bindParam(':email_verified', $email_verified);

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

        if ($user && password_verify($password, $user['password']) && (!isset($user['banned']) || $user['banned'] == 0)) {
            return $user;
        }

        return false;
    }

    public function getUserByEmail($email)
    {
        $sql = "SELECT * FROM utilisateurs WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function loginByFaceId($face_id)
    {
        $variants = $this->faceIdVariants($face_id);

        $sql = "SELECT * FROM utilisateurs WHERE face_id IN (:face_id, :face_id_alt, :face_id_pad, :face_id_alt_pad)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':face_id', $variants[0]);
        $stmt->bindParam(':face_id_alt', $variants[1]);
        $stmt->bindParam(':face_id_pad', $variants[2]);
        $stmt->bindParam(':face_id_alt_pad', $variants[3]);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && (!isset($user['banned']) || $user['banned'] == 0)) {
            return $user;
        }

        return false;
    }

    public function getUserByCredentialId($credentialId)
    {
        $variants = $this->faceIdVariants($credentialId);

        $sql = "SELECT * FROM utilisateurs WHERE face_id IN (:face_id, :face_id_alt, :face_id_pad, :face_id_alt_pad)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':face_id', $variants[0]);
        $stmt->bindParam(':face_id_alt', $variants[1]);
        $stmt->bindParam(':face_id_pad', $variants[2]);
        $stmt->bindParam(':face_id_alt_pad', $variants[3]);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function saveWebAuthnCredential($userId, $credentialId, $publicKeyPem, $signCount)
    {
        $sql = "UPDATE utilisateurs SET face_id = :face_id, face_pubkey = :face_pubkey, face_sign_count = :face_sign_count WHERE id_u = :id_u";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':face_id', $credentialId);
        $stmt->bindParam(':face_pubkey', $publicKeyPem);
        $stmt->bindParam(':face_sign_count', $signCount, PDO::PARAM_INT);
        $stmt->bindParam(':id_u', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function updateFaceSignCount($userId, $signCount)
    {
        $sql = "UPDATE utilisateurs SET face_sign_count = :face_sign_count WHERE id_u = :id_u";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':face_sign_count', $signCount, PDO::PARAM_INT);
        $stmt->bindParam(':id_u', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getAllUsersExceptAdmin($adminEmail, $searchTerm = '')
    {
        if ($searchTerm === '') {
            $sql = "SELECT id_u, nom, prenom, email, genre, telephone, date_naissance, photo, banned
                    FROM utilisateurs
                    WHERE email != :adminEmail";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':adminEmail', $adminEmail);
        } else {
            $sql = "SELECT id_u, nom, prenom, email, genre, telephone, date_naissance, photo, banned
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

    public function updatePasswordByEmail($email, $password)
    {
        $sql = "UPDATE utilisateurs SET password = :password WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':email', $email);

        return $stmt->execute();
    }

    public function updatePhoto($id, $photo)
    {
        $sql = "UPDATE utilisateurs SET photo = ? WHERE id_u = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$photo, $id]);
    }

    public function toggleBan($id)
    {
        $sql = "UPDATE utilisateurs SET banned = NOT banned WHERE id_u = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
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

    public function getGenderStats($adminEmail, $searchTerm = '')
    {
        $baseCondition = "WHERE email != :adminEmail";
        $params = [':adminEmail' => $adminEmail];

        if ($searchTerm !== '') {
            $baseCondition .= " AND (nom LIKE :term OR prenom LIKE :term OR email LIKE :term)";
            $params[':term'] = '%' . $searchTerm . '%';
        }

        $sql = "SELECT genre, COUNT(*) as count FROM utilisateurs $baseCondition GROUP BY genre";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}
?>