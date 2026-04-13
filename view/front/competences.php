<?php
require_once "../../model/competence.php";
$id_projet = isset($_GET['id_projet']) ? $_GET['id_projet'] : null;
if (!$id_projet) {
    $id_projet = 0; // prevent crash, keep page alive
}
$c = new Competence();
$data = $c->getAll();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Compétences</title>
  <link rel="stylesheet" href="../../assets/competences.css">
</head>
<body>



<div class="container">

<h2>Compétences</h2>

<div class="card">
<form method="POST" action="../../controller/CompetenceController.php">
  <input type="hidden" name="id_projet" value="<?= $id_projet ?>">
  <input type="text" name="nom" placeholder="Nom compétence">
  <input type="text" name="niveau" placeholder="Niveau">
  <button name="add">Ajouter</button>
</form>
</div>

<div class="card">


<!-- READ -->
<table border="1">
<tr>
  <th>Nom</th>
  <th>Niveau</th>
  <th>Actions</th>
</tr>

<?php while($row = $data->fetch_assoc()) { ?>
<tr>
  <td><?= $row['nom_competence'] ?></td>
  <td><?= $row['niveau'] ?></td>
  <td class="actions">

  <a href="../../controller/CompetenceController.php?delete=<?= $row['id_competence'] ?>">
    Delete
  </a>

  <form class="update-form" method="POST" action="../../controller/CompetenceController.php">
    <input type="hidden" name="id" value="<?= $row['id_competence'] ?>">
    <input type="text" name="nom" value="<?= $row['nom_competence'] ?>">
    <input type="text" name="niveau" value="<?= $row['niveau'] ?>">
    <button name="update">Modifier</button>
  </form>

</td>
  
</tr>
<?php } ?>
</table>
</div>

</div>   
</body>
</html>