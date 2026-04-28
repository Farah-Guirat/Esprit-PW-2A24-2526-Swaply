<?php
// ── DEBUG: Voir ce qui se passe à l'envoi ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>DEBUG: Données POST reçues</h2>";
    echo "<pre>";
    echo "POST: " . print_r($_POST, true) . "\n";
    echo "FILES: " . print_r($_FILES, true) . "\n";
    echo "</pre>";
    
    echo "<h2>DEBUG: Contenu du message</h2>";
    $contenu = isset($_POST['contenu']) ? trim($_POST['contenu']) : '';
    $id_conv = isset($_POST['id_conversation']) ? (int)$_POST['id_conversation'] : 0;
    
    echo "Contenu: '" . htmlspecialchars($contenu) . "' (longueur: " . strlen($contenu) . ")<br>";
    echo "ID Conversation: " . $id_conv . "<br>";
    echo "Fichier présent: " . (!empty($_FILES['fichier']) ? "OUI" : "NON") . "<br>";
    
    if (!empty($_FILES['fichier'])) {
        echo "Fichier error: " . $_FILES['fichier']['error'] . "<br>";
        echo "Fichier size: " . $_FILES['fichier']['size'] . "<br>";
        echo "Fichier tmp_name: " . $_FILES['fichier']['tmp_name'] . "<br>";
    }
    
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>DEBUG: Test d'envoi de message</title>
</head>
<body>
    <h1>Test d'envoi de message</h1>
    
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id_conversation" value="1">
        
        <label>Message:</label>
        <textarea name="contenu" required>Bonjour, c'est un test</textarea>
        
        <label>Fichier (optionnel):</label>
        <input type="file" name="fichier">
        
        <button type="submit">Envoyer</button>
    </form>
</body>
</html>
