<?php
require_once __DIR__ . "/../model/competence.php";
require_once __DIR__ . "/../config/Database.php";

$comp = new Competence();

$id_u = 38; // 👈 hardcoded for now, replace with $_SESSION['id_u'] later

if (isset($_POST['add'])) {

    $nom       = $_POST['nom'];
    $niveau    = $_POST['niveau'];
    $id_projet = $_POST['id_projet'];

    // 1. insert competence with id_u
    $comp->add($nom, $niveau, $id_u);

    // 2. get last inserted id
    $pdo = Database::getInstance();
    $id_competence = $pdo->lastInsertId();

    // 3. link table
    if ($id_projet) {
        $stmt = $pdo->prepare("INSERT INTO projet_competence(id_projet, id_competence) VALUES (?, ?)");
        $stmt->execute([$id_projet, $id_competence]);
    }

    header("Location: ../view/front/competences.php?id_projet=$id_projet");
    exit();
}

if (isset($_GET['delete'])) {
    $comp->delete($_GET['delete']);
    header("Location: ../view/front/competences.php");
    exit();
}

if (isset($_POST['update'])) {
    $comp->update($_POST['id'], $_POST['nom'], $_POST['niveau']);
    header("Location: ../view/front/competences.php");
    exit();
}
?>