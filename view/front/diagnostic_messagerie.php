<?php
session_start();

if (!isset($_SESSION['user'])) {
    die("Vous n'êtes pas connecté. <a href='login.php'>Se connecter</a>");
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../model/Conversation.php';

$id_user = (int)$_SESSION['user']['id_u'];

echo "════════════════════════════════════════════════════════════════<br>";
echo "🔍 DIAGNOSTIC MESSAGERIE<br>";
echo "════════════════════════════════════════════════════════════════<br><br>";

echo "✅ Utilisateur connecté :<br>";
echo "- ID : " . htmlspecialchars($id_user) . "<br>";
echo "- Nom : " . htmlspecialchars($_SESSION['user']['nom'] ?? 'N/A') . "<br>";
echo "- Prénom : " . htmlspecialchars($_SESSION['user']['prenom'] ?? 'N/A') . "<br>";
echo "- Email : " . htmlspecialchars($_SESSION['user']['email'] ?? 'N/A') . "<br><br>";

$convModel = new Conversation();
$conversations = $convModel->getByUser($id_user);

echo "📋 Conversations de cet utilisateur :<br>";
if (!empty($conversations)) {
    foreach ($conversations as $conv) {
        echo "- Conv ID: " . htmlspecialchars($conv['id_conversation']) . " | ";
        echo "Interlocuteur: " . htmlspecialchars($conv['interlocuteur_prenom'] . ' ' . $conv['interlocuteur_nom']) . " | ";
        echo "Messages non lus: " . htmlspecialchars($conv['non_lus']) . "<br>";
    }
} else {
    echo "⚠️ Aucune conversation trouvée<br>";
}

echo "<br>════════════════════════════════════════════════════════════════<br>";
echo "<a href='messagerie.php'>↩️ Retour à la messagerie</a><br>";
echo "════════════════════════════════════════════════════════════════<br>";
?>
