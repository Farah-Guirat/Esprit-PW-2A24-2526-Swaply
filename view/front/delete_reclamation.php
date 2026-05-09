<?php
require_once '../../controller/ReclamationController.php';

if(isset($_GET['id'])){
    $controller = new ReclamationController();
    $controller->supprimer($_GET['id']);
}

header("Location: reclamations.php");
exit;
?>