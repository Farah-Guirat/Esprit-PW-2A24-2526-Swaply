<?php

use Entity\Offre;

require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../Model/Offre.php';
require_once __DIR__ . '/../Services/AIService.php';


class OffreController {

    public function listOffre(): void {

        $db = new Database();
        $pdo = $db->connect();
        // Pagination parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 4; // Number of offers per page
        $offset = ($page - 1) * $limit;

        // Get total count
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM offres");
        $total_offres = $stmt->fetch()['total'];
        $total_pages = ceil($total_offres / $limit);

        // Get paginated offers
        $stmt = $pdo->prepare("SELECT * FROM offres LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();

       
        $rows = $stmt->fetchAll();

        $offres = [];

        foreach ($rows as $row) {

            $offre = new Offre(
                $row['titre'],
                $row['description'],
                $row['categorie'],
                $row['niveau'],
                $row['statut'],
                $row['date_limite'] ? new DateTime($row['date_limite']) : null,
                $row['id_u'],
                $row['vues']
            );

            $offre->setIdOffre($row['id_offre']);

            $offres[] = $offre;
        }


        require __DIR__ . '/../view/front/offres_list.php';
    }












//DETAILS D'UNE OFFRE + INCREMENTATION VUES
public function showDetailsOffre(int $id): void {

    $pdo = Database::getInstance();

    // +1 VUE
    $stmt = $pdo->prepare("UPDATE offres SET vues = vues + 1 WHERE id_offre = ?");
    $stmt->execute([$id]);

    // Récupérer l'offre avec les infos de l'utilisateur (JOINTURE)
    $stmt = $pdo->prepare("
        SELECT o.*, u.nom, u.prenom, u.email, u.photo, u.date_naissance 
        FROM offres o
        LEFT JOIN utilisateurs u ON o.id_u = u.id_u
        WHERE o.id_offre = ?
    ");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if (!$row) {
        die("Offre introuvable");
    }

    $offre = new \Entity\Offre(
        $row['titre'],
        $row['description'],
        $row['categorie'],
        $row['niveau'],
        $row['statut'],
        $row['date_limite'] ? new \DateTime($row['date_limite']) : null,
        $row['id_u'],
        $row['vues']
    );

    $offre->setIdOffre($row['id_offre']);
    
    // Créer un tableau avec les infos de l'utilisateur
    $createur = [
        'id_u' => $row['id_u'],
        'nom' => $row['nom'],
        'prenom' => $row['prenom'],
        'email' => $row['email'],
        'photo' => $row['photo'],
        'date_naissance' => $row['date_naissance']
    ];
    
    $demandes = $this->matching($id);

    require __DIR__ . '/../view/front/offre_details.php';
}




public function deleteOffre(int $id): void {

    $pdo = Database::getInstance();

    $stmt = $pdo->prepare("DELETE FROM offres WHERE id_offre = ?");
    $stmt->execute([$id]);

    //  redirection vers liste après suppression
    header("Location: index.php?action=list");
    exit;
}

//ajout offre


public function ajoutOffre(): void
{
    session_start();

    $pdo = Database::getInstance();

    // 🔥 utilisateur connecté
    $id_u = $_SESSION['user']['id_u'];

    $stmt = $pdo->prepare("
        INSERT INTO offres
        (titre, description, categorie, niveau, statut, date_creation, date_limite, vues, id_u)
        VALUES (?, ?, ?, ?, 'active', CURDATE(), ?, 0, ?)
    ");

    $stmt->execute([
        $_POST['titre'],
        $_POST['description'],
        $_POST['categorie'],
        $_POST['niveau'],
        $_POST['date_limite'],
        $id_u
    ]);

    $id = $pdo->lastInsertId();

    $text = AIService::buildText($_POST);
    $embedding = AIService::getEmbedding($text);

    $stmt = $pdo->prepare("UPDATE offres SET embedding=? WHERE id_offre=?");
    $stmt->execute([
        json_encode($embedding),
        $id
    ]);

    header("Location: index.php?action=list");
    exit;
}
public function matching(int $id_offre): array
{
    $pdo = Database::getInstance();

    //  get offre
    $stmt = $pdo->prepare("SELECT * FROM offres WHERE id_offre = ?");
    $stmt->execute([$id_offre]);
    $offre = $stmt->fetch();

    if (!$offre || empty($offre['embedding'])) {
        return [];
    }

    $offreEmbedding = json_decode($offre['embedding'], true);

    //  get demandes actives
    $stmt = $pdo->query("SELECT * FROM demandes WHERE statut = 'active'");
    $demandes = $stmt->fetchAll();

    $results = [];

    foreach ($demandes as $d) {

        if (empty($d['embedding'])) continue;

        $demandeEmbedding = json_decode($d['embedding'], true);

        //  cosine similarity
        $score = AIService::cosineSimilarity($offreEmbedding, $demandeEmbedding);

        //  bonus métier
        if ($offre['categorie'] === $d['categorie']) {
            $score += 0.10;
        }

        if ($offre['niveau'] === $d['niveau']) {
            $score += 0.05;
        }

        // filter
        if ($score >= 0.60) {
            $d['score'] = round($score, 3);
            $results[] = $d;
        }
    }

    //  sort best matches first
    usort($results, function ($a, $b) {
        return $b['score'] <=> $a['score'];
    });

    return $results;
}







public function updateOffre(): void
{
    $pdo = Database::getInstance();

    $stmt = $pdo->prepare("
        UPDATE offres 
        SET titre = ?, description = ?, categorie = ?, niveau = ?, date_limite = ?
        WHERE id_offre = ?
    ");

    $stmt->execute([
        $_POST['titre'],
        $_POST['description'],
        $_POST['categorie'],
        $_POST['niveau'],
        $_POST['date_limite'],
        $_POST['id_offre']
    ]);

    //  rebuild text
    $text = AIService::buildText($_POST);

    //  new embedding
    $embedding = AIService::getEmbedding($text);

    $stmt = $pdo->prepare("UPDATE offres SET embedding=? WHERE id_offre=?");
    $stmt->execute([
        json_encode($embedding),
        $_POST['id_offre']
    ]);

    header("Location: index.php?action=list");
    exit;
}





 function getOffreById(int $id): array {
    $pdo = Database::getInstance();
    
    $stmt = $pdo->prepare("SELECT * FROM offres WHERE id_offre = ?");
    $stmt->execute([$id]);
    $offre = $stmt->fetch();

    if (!$offre) {
        die("Offre introuvable");
    }

    return $offre;
}

// Méthode edit mise à jour
public function editOffre(int $id): void {
    $offre = $this->getOffreById($id);   
    require __DIR__ . '/../view/front/offre_edit.php';
}





}