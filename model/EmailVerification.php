<?php
/**
 * Modèle pour gérer les tokens de vérification d'email
 */

class EmailVerification {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Créer un token de vérification
     */
    public function createToken($email, $userData) {
        // Supprimer les anciens tokens pour cet email
        $this->deleteTokensByEmail($email);
        
        // Générer un token unique
        $token = bin2hex(random_bytes(32));
        
        // Convertir les données en JSON
        $userDataJson = json_encode($userData);
        
        // Token valide pendant 24 heures
        $expiresAt = date('Y-m-d H:i:s', time() + (24 * 60 * 60));
        
        try {
            $sql = "INSERT INTO email_verification_tokens (email, token, user_data, expires_at) 
                   VALUES (:email, :token, :user_data, :expires_at)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':user_data', $userDataJson);
            $stmt->bindParam(':expires_at', $expiresAt);
            
            if ($stmt->execute()) {
                return $token;
            }
        } catch (Exception $e) {
            error_log("Error creating verification token: " . $e->getMessage());
        }
        
        return false;
    }

    public function createResetToken($email) {
        $token = bin2hex(random_bytes(32));
        $resetData = json_encode(['email' => $email, 'purpose' => 'reset_password']);
        $createdAt = date('Y-m-d H:i:s');
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);

        try {
            $sql = "INSERT INTO email_verification_tokens (email, token, user_data, created_at, expires_at, verified)
                   VALUES (:email, :token, :user_data, :created_at, :expires_at, 0)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':user_data', $resetData);
            $stmt->bindParam(':created_at', $createdAt);
            $stmt->bindParam(':expires_at', $expiresAt);

            if ($stmt->execute()) {
                return $token;
            }
        } catch (Exception $e) {
            error_log("Error creating reset token: " . $e->getMessage());
        }

        return false;
    }

    public function deleteTokensByEmail($email) {
        try {
            $sql = "DELETE FROM email_verification_tokens WHERE email = :email";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Error deleting tokens for email $email: " . $e->getMessage());
        }
    }

    public function getResetTokenData($token) {
        try {
            $sql = "SELECT * FROM email_verification_tokens WHERE token = :token AND expires_at > NOW()";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                return false;
            }

            $data = json_decode($result['user_data'], true);
            if (!is_array($data) || !isset($data['purpose']) || $data['purpose'] !== 'reset_password') {
                return false;
            }

            return [
                'email' => $data['email'] ?? $result['email']
            ];
        } catch (Exception $e) {
            error_log("Error reading reset token data: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Vérifier et valider un token
     */
    public function verifyToken($token, $action = 'confirm') {
        try {
            // Chercher le token
            $sql = "SELECT * FROM email_verification_tokens WHERE token = :token AND expires_at > NOW()";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return array('status' => 'error', 'message' => 'Token invalide ou expiré.');
            }
            
            if ($action === 'confirm') {
                return array(
                    'status' => 'success',
                    'message' => 'Email vérifiée avec succès.',
                    'userData' => json_decode($result['user_data'], true)
                );
            } elseif ($action === 'reject') {
                // Supprimer le token
                $deleteSql = "DELETE FROM email_verification_tokens WHERE token = :token";
                $deleteStmt = $this->conn->prepare($deleteSql);
                $deleteStmt->bindParam(':token', $token);
                $deleteStmt->execute();
                
                return array(
                    'status' => 'rejected',
                    'message' => 'Demande d\'inscription annulée.'
                );
            }
        } catch (Exception $e) {
            error_log("Error verifying token: " . $e->getMessage());
            return array('status' => 'error', 'message' => 'Erreur lors de la vérification.');
        }
        
        return array('status' => 'error', 'message' => 'Action invalide.');
    }
    
    /**
     * Obtenir les données d'un token
     */
    public function getTokenData($token) {
        try {
            $sql = "SELECT * FROM email_verification_tokens WHERE token = :token AND expires_at > NOW()";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return array(
                    'email' => $result['email'],
                    'userData' => json_decode($result['user_data'], true)
                );
            }
        } catch (Exception $e) {
            error_log("Error getting token data: " . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Nettoyer les tokens expirés (optionnel)
     */
    public function cleanExpiredTokens() {
        try {
            $sql = "DELETE FROM email_verification_tokens WHERE expires_at < NOW()";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error cleaning expired tokens: " . $e->getMessage());
            return false;
        }
    }
}

?>
