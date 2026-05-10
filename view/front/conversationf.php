<?php
require_once "../../controller/MessageC.php";

$controller = new MessageC();
$messages = $controller->list($_GET['id']);
?>

<h2>Conversation</h2>

<!-- messages -->
<?php while($m = $messages->fetch(PDO::FETCH_ASSOC)) { ?>
    <div>
        <p><?= $m['contenu'] ?></p>
        <small><?= $m['date_envoi'] ?></small>
    </div>
<?php } ?>

<hr>

<!-- send message -->
<form action="../../controller/MessageC.php" method="POST" onsubmit="return validateMessageForm()">

    <input type="hidden" name="id_conversation" value="<?= $_GET['id'] ?>">
    <input type="hidden" name="id_expediteur" value="1">

    <textarea name="contenu" id="contenu"></textarea>

    <button type="submit">Send</button>
</form>

<script src="../../assets/js/validation.js"></script>