<?php
session_start();
require_once __DIR__ . "/../model/projet.php";
require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../lib/sendMail.php";

$id_u = 17;
$projet = new Projet();

if (isset($_POST['add'])) {
    $projet->add($_POST['nom'], $_POST['desc'], $_POST['statut'], $id_u);

    $historique = $projet->getAll();

    sendProjetEmail(
        'farahguirat4@gmail.com',
        'farah',
        $_POST['nom'],
        $_POST['desc'],
        $_POST['statut'],
        $historique
    );

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

if (isset($_GET['favorite'])) {
    $projet->setFavorite($_GET['favorite']);
    header("Location: ../view/front/projets.php");
    exit();
}

if (isset($_GET['hide'])) {
    $projet->hide($_GET['hide']);
    header("Location: ../view/front/projets.php");
    exit();
}

if (isset($_GET['unhide'])) {
    $projet->unhide($_GET['unhide']);
    header("Location: ../view/front/projets.php");
    exit();
}

if (isset($_GET['unarchive'])) {
    $projet->unarchive($_GET['unarchive']);
    header("Location: ../view/front/projets.php");
    exit();
}

if (isset($_GET['archive'])) {
    $projet->archive($_GET['archive']);
    header("Location: ../view/front/projets.php");
    exit();
}
?>