<?php

require_once __DIR__ . '/../Config/Database.php';


require_once __DIR__ . '/../Models/demande.php';

use Entity\Demande;

class DashboardControllerd {


public function getDemandeDetails(int $id): void {

    $pdo = Database::getInstance();

    $stmt = $pdo->prepare("SELECT * FROM demandes WHERE id_demande = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        header("Location: index.php?action=dashboardd");
        exit;
    }

    $demande = new \Entity\Demande(
        $row['titre'] ?? '',
        $row['description'] ?? '',
        $row['categorie'] ?? '',
        $row['niveau'] ?? '',
        $row['statut'] ?? 'inactive',
        !empty($row['date_creation']) ? new DateTime($row['date_creation']) : null,
        $row['id_u'] ?? 0
    );

    $demande->setIdDemande((int)$row['id_demande']);

    require __DIR__ . '/../view/BackOffice/demande_detailsBack.php';
}


public function showDashboardDemandes(): void {
    try {
        $pdo = Database::getInstance();

        $stmt = $pdo->query("SELECT * FROM demandes ORDER BY id_demande DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $demandes = [];

        foreach ($rows as $row) {
            $demande = new Demande(
                $row['titre'] ?? '',
                $row['description'] ?? '',
                $row['categorie'] ?? '',
                $row['niveau'] ?? '',
                $row['statut'] ?? 'inactive',
                !empty($row['date_creation']) ? new DateTime($row['date_creation']) : null,
                $row['id_u'] ?? 0
            );

            $demande->setIdDemande((int)$row['id_demande']);
            $demandes[] = $demande;
        }

        $stats = $this->calculateStats($demandes);

        require __DIR__ . '/../view/BackOffice/dashboard_demandes.php';

    } catch (Exception $e) {
        die("Erreur Dashboard Demandes : " . $e->getMessage());
    }
}




 private function calculateStats(array $offres): array {
        $total = count($offres);
        $offresActives = 0;
        $demandesActives = 0;
        $expirees = 0;

        foreach ($offres as $offre) {
            $statut = strtolower($offre->getStatut());
            $categorie = strtolower($offre->getCategorie());

            if ($statut === 'active') {
                $offresActives++;

                if (in_array($categorie, ['demande', 'demandes'])) {
                    $demandesActives++;
                }
            }

            if (in_array($statut, ['expirée','expiree','fermée','fermee','bloque','bloquée','inactive'])) {
                $expirees++;
            }
        }

        return [
            'total' => $total,
            'offresActives' => $offresActives,
            'demandesActives' => $demandesActives,
            'expirees' => $expirees
        ];
    }




  public function deleteDemande(int $id): void {
    try {
        $pdo = Database::getInstance();

        $stmt = $pdo->prepare("DELETE FROM demandes WHERE id_demande = :id");
        $stmt->execute(['id' => $id]);

        if ($stmt->rowCount() > 0) {
            $this->flash("Demande supprimée avec succès", "success");
        } else {
            $this->flash("Demande introuvable", "error");
        }

        $this->redirect();

    } catch (Exception $e) {
        $this->flash("Erreur suppression", "error");
        $this->redirect();
    }
}



public function blockDemande(int $id): void {
    try {
        if (!$id || !is_numeric($id)) {
            $this->flash("ID invalide", "error");
            $this->redirect();
        }

        $pdo = Database::getInstance();

        $stmt = $pdo->prepare("
            UPDATE demandes 
            SET statut = CASE 
                WHEN statut = 'active' THEN 'bloque'
                ELSE 'active'
            END
            WHERE id_demande = :id
        ");

        $stmt->execute(['id' => (int)$id]);

        $this->flash("Statut demande mis à jour", "success");
        $this->redirect();

    } catch (Exception $e) {
        $this->flash("Erreur block demande", "error");
        $this->redirect();
    }
}



 // ================= FLASH MESSAGE =================
    private function flash(string $message, string $type): void {
        $_SESSION['toastr'] = [
            'message' => $message,
            'type' => $type
        ];
    }

    // ================= REDIRECT =================
    private function redirect(): void {
        header("Location: index.php?action=dashboardd");
        exit;
    }


}




