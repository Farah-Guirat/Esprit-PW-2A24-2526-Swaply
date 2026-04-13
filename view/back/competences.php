<?php
require_once "../../model/competence.php";

$c = new Competence();
$data = $c->getAll();
?>

<h1>Admin - Compétences</h1>

<table border="1">
<tr>
  <th>ID</th>
  <th>Nom</th>
  <th>Niveau</th>
  <th>Action</th>
</tr>

<?php while($row = $data->fetch_assoc()) { ?>
<tr>
  <td><?= $row['id_competence'] ?></td>
  <td><?= $row['nom_competence'] ?></td>
  <td><?= $row['niveau'] ?></td>
  <td>
    <a href="../../controller/CompetenceController.php?delete=<?= $row['id_competence'] ?>">
      🗑 Supprimer
    </a>
  </td>
</tr>
<?php } ?>

</table>