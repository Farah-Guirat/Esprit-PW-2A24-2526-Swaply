<?php

require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../Models/Offre.php';

use Entity\Offre;

class DashboardController {










public function getOffreDetails(int $id): void {

    $pdo = Database::getInstance();

    $stmt = $pdo->prepare("SELECT * FROM offres WHERE id_offre = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if (!$row) {
        header("Location: index.php?action=dashboard");
        exit;
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

       require __DIR__ . '/../view/BackOffice/offre_detailsBack.php';
}

    // ================= DASHBOARD =================
    public function showDashboard(): void {
        try {
            $pdo = Database::getInstance();

            $stmt = $pdo->query("SELECT * FROM offres ORDER BY id_offre DESC");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $offres = [];

            foreach ($rows as $row) {
                $offre = new Offre(
                    $row['titre'] ?? '',
                    $row['description'] ?? '',
                    $row['categorie'] ?? '',
                    $row['niveau'] ?? '',
                    $row['statut'] ?? 'inactive',
                    !empty($row['date_limite']) ? new DateTime($row['date_limite']) : null,
                    $row['id_u'] ?? 0,
                    $row['vues'] ?? 0
                );

                $offre->setIdOffre((int)$row['id_offre']);
                $offres[] = $offre;
            }

            $stats = $this->calculateStats($offres);

            require __DIR__ . '/../view/BackOffice/dashboard.php';

        } catch (Exception $e) {
            die("Erreur Dashboard : " . $e->getMessage());
        }
    }

    // ================= STATS =================
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

    // ================= DELETE =================
    public function deleteOffre(): void {
        try {
            $id = $_GET['id'] ?? null;

            if (!$id || !is_numeric($id)) {
                $this->flash("ID invalide", "error");
                $this->redirect();
            }

            $pdo = Database::getInstance();
            $stmt = $pdo->prepare("DELETE FROM offres WHERE id_offre = :id");
            $stmt->execute(['id' => (int)$id]);

            if ($stmt->rowCount() > 0) {
                $this->flash("Offre supprimée avec succès", "success");
            } else {
                $this->flash("Offre introuvable", "error");
            }

            $this->redirect();

        } catch (Exception $e) {
            $this->flash("Erreur suppression", "error");
            $this->redirect();
        }
    }

    // ================= BLOCK =================
    public function blockOffre(): void {
        try {
            $id = $_GET['id'] ?? null;

            if (!$id || !is_numeric($id)) {
                $this->flash("ID invalide", "error");
                $this->redirect();
            }

            $pdo = Database::getInstance();

            $stmt = $pdo->prepare("
                UPDATE offres 
                SET statut = CASE 
                    WHEN statut = 'active' THEN 'bloque'
                    ELSE 'active'
                END
                WHERE id_offre = :id
            ");

            $stmt->execute(['id' => (int)$id]);

            if ($stmt->rowCount() > 0) {
                $this->flash("Statut mis à jour", "success");
            } else {
                $this->flash("Offre introuvable", "error");
            }

            $this->redirect();

        } catch (Exception $e) {
            $this->flash("Erreur block", "error");
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
        header("Location: index.php?action=dashboard");
        exit;
    }
}