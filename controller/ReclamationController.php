<?php
require_once __DIR__ . '/../model/Reclamation.php';

class ReclamationController {

    public function afficher() {
        $model = new Reclamation();
        return $model->getAll();
    }

    public function ajouter($id_user, $description, $rating, $type, $username_cible) {
        $model = new Reclamation();
        return $model->add($id_user, $description, $rating, $type, $username_cible);
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