<?php
require_once __DIR__ . "/../model/projet.php";

$projet = new Projet();

if (isset($_POST['add'])) {
    $projet->add($_POST['nom'], $_POST['desc'], $_POST['statut']);
    header("Location: ../view/front/projets.php");
    exit();
}

if (isset($_GET['delete'])) {
    $projet->delete($_GET['delete']);
    header("Location: ../view/front/projets.php");
    exit();
}

if (isset($_POST['update'])) {
    $projet->update($_POST['id'], $_POST['nom'], $_POST['desc'], $_POST['statut']);
    header("Location: ../view/front/projets.php");
    exit();
}
?>