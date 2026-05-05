<?php
require_once __DIR__ . '/../model/Reclamation.php';

class ReclamationController {

    public function afficher() {
        $model = new Reclamation();
        return $model->getAll();
    }

    /**
     * Ajouter une réclamation et envoyer les notifications (email + WhatsApp)
     * à l'utilisateur connecté (id_user).
     */
    public function ajouter($id_user, $description, $rating, $type, $username_cible) {
        global $conn;

        // 1. Njibou el info mta' el user elli baath[cite: 9]
        $stmt = $conn->prepare("SELECT mail, num_tel, prenom FROM utilisateurs WHERE id = ?");
        $stmt->execute([$id_user]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $model = new Reclamation();
        $ok = $model->add($id_user, $description, $rating, $type, $username_cible);

        // 2. Ken tsabet el reclamation, nabathou el klab (Notifs)[cite: 9]
        if ($ok && $user) {
            $mail   = $user['mail'];
            $tel    = $user['num_tel']; // Lezem ykoun b +216...[cite: 8]
            $prenom = $user['prenom'];

            if (!empty($mail)) {
                $model->sendEmailConfirmation($mail, $prenom, $description, $type, $rating, $username_cible);
            }
        }
        return $ok;
    }

    public function getById($id) {
        $model = new Reclamation();
        return $model->getById($id);
    }

    public function supprimer($id) {
        $model = new Reclamation();
        return $model->delete($id);
    }

    public function modifier($id, $description, $rating, $type) {
        $model = new Reclamation();
        return $model->update($id, $description, $rating, $type);
    }

    public function changerStatut($id, $statut) {
        $model = new Reclamation();
        return $model->updateStatut($id, $statut);
    }
}
?>