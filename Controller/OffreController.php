<?php

use Entity\Offre;

require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../Models/Offre.php';

class OffreController {

    public function listOffre(): void {

        $pdo = Database::getInstance();

        $stmt = $pdo->query("SELECT * FROM offres");
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

        require __DIR__ . '/../view/FrontOffice/offres_list.php';
    }












//DETAILS D'UNE OFFRE + INCREMENTATION VUES


public function showDetailsOffre(int $id): void {

    $pdo = Database::getInstance();

    //  +1 VUE
    $stmt = $pdo->prepare("UPDATE offres SET vues = vues + 1 WHERE id_offre = ?");
    $stmt->execute([$id]);

    // 🔍 récupérer offre
    $stmt = $pdo->prepare("SELECT * FROM offres WHERE id_offre = ?");
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
        $demandes = $this->matching($id);


    require __DIR__ . '/../view/FrontOffice/offre_details.php';
}




public function deleteOffre(int $id): void {

    $pdo = Database::getInstance();

    $stmt = $pdo->prepare("DELETE FROM offres WHERE id_offre = ?");
    $stmt->execute([$id]);

    // 🔥 redirection vers liste après suppression
    header("Location: index.php?action=list");
    exit;
}

//ajout offre


public function ajoutOffre(): void {

    $pdo = Database::getInstance();

    $stmt = $pdo->prepare("
        INSERT INTO offres
        (titre, description, categorie, niveau, statut, date_creation, date_limite, vues)
        VALUES (?, ?, ?, ?, 'active', CURDATE(), ?, 0)
    ");

    $stmt->execute([
        $_POST['titre'] ?? '',
        $_POST['description'] ?? '',
        $_POST['categorie'] ?? '',
        $_POST['niveau'] ?? '',
        $_POST['date_limite'] ?? null
    ]);

    // ✅ REDIRECT CORRECT
    header("Location: index.php?action=list");
    exit;
}


public function matching(int $id_offre): array
{
    $pdo = Database::getInstance();

    $sql = "SELECT 
                d.id_demande,
                d.titre,
                d.description,
                d.categorie,
                d.niveau
            FROM offres o
            JOIN demandes d 
                ON o.categorie = d.categorie
                AND o.niveau = d.niveau
            WHERE o.id_offre = ?
            AND LOWER(o.statut) = 'active'
            AND LOWER(d.statut) = 'active'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_offre]);

    return $stmt->fetchAll();
}












public function updateOffre(): void {

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

    header("Location: index.php?action=list");
    exit;
}





// Nouvelle méthode recommandée
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
    $offre = $this->getOffreById($id);   // Utilise la nouvelle méthode
    require __DIR__ . '/../view/FrontOffice/offre_edit.php';
}

}