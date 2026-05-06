<?php

class Database {
    private static $instance = null;

    private $host = "localhost";
    private $db_name = "swaply";
    private $username = "root";
    private $password = "";

    public function connect() {
        try {
            $conn = new PDO(
                "mysql:host=$this->host;dbname=$this->db_name",
                $this->username,
                $this->password
            );

            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;

        } catch(PDOException $e) {
            echo "Erreur : " . $e->getMessage();
            return null;
        }
    }

    // ✅ Singleton version compatible avec connect()
    public static function getInstance() {
        if (self::$instance === null) {
            $db = new Database();
            self::$instance = $db->connect(); // utilise ta méthode existante
        }

        return self::$instance;
    }
}

?>