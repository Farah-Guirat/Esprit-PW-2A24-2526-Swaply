<?php
require_once "../../model/projet.php";

$p = new Projet();
$data = $p->getAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Admin - Projets</title>
  <link rel="stylesheet" href="../../assets/projetsb.css">
</head>
<body>

<h1>Administration des projets</h1>

<!-- READ -->
<table border="1">
  <tr>
    <th>ID</th>
    <th>Nom</th>
    <th>Description</th>
    <th>Statut</th>
    <th>Compétences</th>
    <th>Action</th>
  </tr>

  <?php while($row = $data->fetch_assoc()) { ?>
  <tr>
    <td><?= $row['id_projet'] ?></td>
    <td><?= $row['nom_projet'] ?></td>
    <td><?= $row['description'] ?></td>
    <td><?= $row['statut'] ?></td>
    <td>
  <?php 
    $comps = $p->getCompetences($row['id_projet']);
    while($c = $comps->fetch_assoc()) { ?>
      <span>
        <?= $c['nom_competence'] ?> (<?= $c['niveau'] ?>)
      </span><br>
  <?php } ?>
</td>
 <td>
      <!-- DELETE -->
      <a href="../../controller/ProjetController.php?delete=<?= $row['id_projet'] ?>"
         onclick="return confirm('Voulez-vous supprimer ce projet ?')">
         🗑 Supprimer
      </a>
    </td>
  </tr>
  <?php } ?>

</table>

</body>
</html>