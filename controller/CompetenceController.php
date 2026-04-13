<?php
require_once __DIR__ . "/../model/competence.php";
require_once __DIR__ . "/../config/config.php";

$comp = new Competence();

// CREATE + LINK
if (isset($_POST['add'])) {

    $nom = $_POST['nom'];
    $niveau = $_POST['niveau'];
    $id_projet = $_POST['id_projet'];

    // 1. insert competence
    $comp->add($nom, $niveau);

    // 2. get id
    global $conn;
    $id_competence = $conn->insert_id;

    // 3. link table
    if ($id_projet) {
        $stmt = $conn->prepare("INSERT INTO projet_competence(id_projet, id_competence) VALUES (?, ?)");
        $stmt->bind_param("ii", $id_projet, $id_competence);
        $stmt->execute();
    }

    header("Location: ../view/front/projets.php?id_projet=$id_projet");
    exit();
}

// DELETE
if (isset($_GET['delete'])) {
    $comp->delete($_GET['delete']);
    header("Location: ../view/front/competences.php");
    exit();
}

// UPDATE
if (isset($_POST['update'])) {
    $comp->update($_POST['id'], $_POST['nom'], $_POST['niveau']);
    header("Location: ../view/front/competences.php");
    exit();
}