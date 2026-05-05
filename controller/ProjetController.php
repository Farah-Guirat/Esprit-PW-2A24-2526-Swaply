<?php
require_once __DIR__ . "/../model/projet.php";
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../lib/sendMail.php";

$projet = new Projet();

if (isset($_POST['add'])) {

    // Add the project
    $projet->add($_POST['nom'], $_POST['desc'], $_POST['statut']);

    // Get all projects for historique
    $historique = $projet->getAll();

    // Send email to user
    sendProjetEmail(
        'farahguirat4@gmail.com',  // 👈 replace with session email when you have login
        'farah',           // 👈 replace with session name when you have login
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