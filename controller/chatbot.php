<?php
require_once __DIR__ . "/../model/projet.php";
require_once __DIR__ . "/../config/config.php";

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$message = $input['message'] ?? '';
$history = $input['history'] ?? [];

// Get all projects and their competences from DB
$p = new Projet();
$projets = $p->getAll();

$projetsContext = "";
foreach ($projets as $proj) {
    $comps = $p->getCompetences($proj['id_projet']);
    $compList = implode(', ', array_column($comps, 'nom_competence'));
    $projetsContext .= "- Projet: {$proj['nom_projet']} | Description: {$proj['description']} | Statut: {$proj['statut']} | Compétences: {$compList}\n";
}

// Build conversation history for Gemini
$contents = [];

// Add history
foreach ($history as $h) {
    $contents[] = [
        "role" => $h['role'],
        "parts" => [["text" => $h['text']]]
    ];
}

// Add current message with context
$fullMessage = "Tu es un assistant intelligent pour la plateforme Swaply qui gère des projets et compétences. 
Voici les données actuelles des projets:

$projetsContext

Réponds en français de manière claire et utile.
Question de l'utilisateur: $message";

$contents[] = [
    "role" => "user",
    "parts" => [["text" => $fullMessage]]
];

// Call Gemini API
$apiKey = ""; // 👈 mets ta clé ici
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=$apiKey";

$data = json_encode(["contents" => $contents]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

error_log("Gemini Response: " . $response);
error_log("HTTP Code: " . $httpCode);

$result = json_decode($response, true);
$reply = "Désolé, je n'ai pas pu répondre.";
if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    $reply = $result['candidates'][0]['content']['parts'][0]['text'];
} elseif (isset($result['error']['message'])) {
    $reply = "Erreur API Gemini: " . $result['error']['message'];
}

echo json_encode(['reply' => $reply]);