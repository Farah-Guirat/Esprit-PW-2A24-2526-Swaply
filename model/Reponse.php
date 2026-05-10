<?php

$host = "localhost";
$dbname = "swaply";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Erreur: " . $e->getMessage();
}


class Reponse {


    private function normalize(string $s): string {
        $s = mb_strtolower(trim($s), 'UTF-8');
        $from = ['é','è','ê','ë','à','â','î','ï','ô','û','ù','ç','ã','õ','ñ'];
        $to   = ['e','e','e','e','a','a','i','i','o','u','u','c','a','o','n'];
        return str_replace($from, $to, $s);
    }

    public function add($id_reclamation, $contenu, $status) {
        global $conn;

        $sql  = "INSERT INTO reponses (id_reclamation, contenu, status) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $ok   = $stmt->execute([$id_reclamation, $contenu, $status]);

        if ($ok) {
           
            $sNorm = $this->normalize($status);

         
            if (str_contains($sNorm, 'traite') || str_contains($sNorm, 'rejete') || str_contains($sNorm, 'rejet')) {
                $statutRec = 'traité';
            } else {
                $statutRec = 'en cours';
            }

          
            $sql2  = "UPDATE reclamations SET statut = ? WHERE id_reclamation = ?";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->execute([$statutRec, $id_reclamation]);
        }

        return $ok;
    }

    public function getByReclamation($id) {
        global $conn;
        $sql  = "SELECT * FROM reponses WHERE id_reclamation = ? ORDER BY date_reponse ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt;
    }
}
?>