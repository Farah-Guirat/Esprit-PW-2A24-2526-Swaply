<?php
require_once "../../model/projet.php";

$p = new Projet();
$data = $p->getAll();
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../../assets/projets.css">
</head>

<body>
  <div style="background:white; padding:15px 30px; box-shadow:0 2px 10px rgba(0,0,0,0.05); display:flex; justify-content:space-between; align-items:center;">

  <!-- LOGO -->
  <div style="display:flex; align-items:center; gap:10px;">
    <div style="width:35px; height:35px; background:#14b8a6; color:white; border-radius:10px; display:flex; align-items:center; justify-content:center; font-weight:bold;">
      S
    </div>
    <h2 style="margin:0; color:#333;">Swaply</h2>
  </div>

  <!-- MENU -->
  <div style="display:flex; gap:20px;">
    <a href="index.php">Accueil</a>
    <a href="profils.html">Profils</a>
    <a href="projets.php" style="color:#14b8a6; font-weight:bold;">Projets</a>
    <a href="offres.html">Offres</a>
    <a href="demandes.html">Demandes</a>
    <a href="publications.html">Publications</a>
    <a href="messages.html">Messages</a>
    <a href="reclamations.html">Réclamations</a>
  </div>

</div>

<div class="container">

<h2 class="title">Gestion des Projets</h2>

<!-- FORM AJOUT -->
<div class="card">
<h3>Ajouter un projet</h3>

<form method="POST" action="../../controller/ProjetController.php">

  <label>Nom du projet</label>
  <input type="text" name="nom">

  <label>Description</label>
  <textarea name="desc"></textarea>

  <label>Statut</label>
  <input type="text" name="statut">

  

  <button name="add">Ajouter</button>
</form>
</div>

<!-- TABLE -->
<div class="card">

<table>
<tr>
  <th>Nom projet</th>
  <th>Description</th>
  <th>Statut</th>
  <th>Date création</th>
  <th>Compétences</th>


  <th>Actions</th>

</tr>

<?php while($row = $data->fetch_assoc()) { ?>

<tr>
  <td><b><?= $row['nom_projet'] ?></b></td>
  <td><?= $row['description'] ?></td>
  <td><?= $row['statut'] ?></td>
  <td><?= $row['date_creation'] ?></td>

  <!-- COMPETENCES -->
  <td>
    <?php 
      $comps = $p->getCompetences($row['id_projet']);
      while($c = $comps->fetch_assoc()) { ?>
        <span class="badge"><?= $c['nom_competence'] ?></span>
    <?php } ?>
  </td>

  <!-- ACTIONS -->
 <td class="actions">

  <div class="action-buttons">
    
    <a class="btn red" href="../../controller/ProjetController.php?delete=<?= $row['id_projet'] ?>">
      Delete
    </a>

    <a class="btn green" href="competences.php?id_projet=<?= $row['id_projet'] ?>">
      ➕ Compétences
    </a>

    <!-- ✅ BOUTON MODIFIER -->
    <button class="btn orange" onclick="toggleForm(<?= $row['id_projet'] ?>)">
      Modifier
    </button>

  </div>

  <!-- FORM UPDATE CACHÉ -->
  <form id="form-<?= $row['id_projet'] ?>" class="update-form" method="POST" action="../../controller/ProjetController.php">

    <input type="hidden" name="id" value="<?= $row['id_projet'] ?>">

    <input type="text" name="nom" value="<?= $row['nom_projet'] ?>">
    <input type="text" name="desc" value="<?= $row['description'] ?>">
    <input type="text" name="statut" value="<?= $row['statut'] ?>">

    <button name="update">✔</button>

  </form>

</td>

<?php } ?>

</table>

</div>

</div>

<script>
function toggleForm(id) {
  let form = document.getElementById("form-" + id);
  form.style.display = form.style.display === "block" ? "none" : "block";
}
</script>

</body>
</html>