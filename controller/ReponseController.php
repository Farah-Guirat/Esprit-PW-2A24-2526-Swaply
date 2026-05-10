<?php
require_once __DIR__ . '/../model/Reponse.php';

class ReponseController {

    public function ajouter($id_reclamation, $contenu, $status) {
        $model = new Reponse();
        return $model->add($id_reclamation, $contenu, $status);
    }

    public function afficher($id_reclamation) {
        $model = new Reponse();
        return $model->getByReclamation($id_reclamation);
    }
}
?>