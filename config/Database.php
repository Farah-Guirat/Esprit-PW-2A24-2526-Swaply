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
            die("Erreur de connexion à la base de données : " . $e->getMessage() . "\n"
                . "Vérifiez que MySQL est démarré et que les identifiants sont corrects.");
        }
    }

    // ✅ Singleton - retourne toujours une connexion valide ou meurt
    public static function getInstance() {
        if (self::$instance === null) {
            $db = new Database();
            self::$instance = $db->connect();
        }

        return self::$instance;
    }
}

?>