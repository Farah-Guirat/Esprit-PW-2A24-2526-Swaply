<?php
require_once __DIR__ . "/config/config.php";

echo "<pre style='background: #1e1e1e; color: #00ff00; padding: 15px; border-radius: 5px; font-family: monospace;'>";
echo "=== TEST API GEMINI ===\n\n";

$apiKey = GEMINI_API_KEY;
echo "✓ Clé API chargée: " . substr($apiKey, 0, 20) . "...\n";
echo "✓ Longueur clé: " . strlen($apiKey) . " caractères\n\n";

// Test 1: Lister les modèles
echo "--- TEST 1: Lister les modèles ---\n";
$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . urlencode($apiKey);
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($httpCode === 200) {
    $models = json_decode($response, true);
    if (isset($models['models'])) {
        echo "✓ Modèles trouvés: " . count($models['models']) . "\n";
        echo "Premiers modèles:\n";
        foreach (array_slice($models['models'], 0, 3) as $model) {
            echo "  - " . $model['name'] . "\n";
        }
    }
} else {
    echo "✗ Erreur HTTP $httpCode\n";
    echo $response . "\n";
}

echo "\n--- TEST 2: Test avec gemini-pro ---\n";
$url2 = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . urlencode($apiKey);
$data = json_encode([
    "contents" => [[
        "role" => "user",
        "parts" => [["text" => "Réponds avec 'OK' seulement."]]
    ]]
]);

$ch = curl_init($url2);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($httpCode === 200) {
    $result = json_decode($response, true);
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $text = $result['candidates'][0]['content']['parts'][0]['text'];
        echo "✓ Réponse reçue: " . $text . "\n";
    } else {
        echo "✗ Format de réponse inattendu\n";
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }
} else {
    echo "✗ Erreur HTTP $httpCode\n";
    echo $response . "\n";
}

echo "\n--- TEST 3: Afficher tous les modèles disponibles ---\n";
$models_url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . urlencode($apiKey);
$ch = curl_init($models_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $models = json_decode($response, true);
    if (isset($models['models'])) {
        echo "Tous les modèles disponibles:\n";
        foreach ($models['models'] as $model) {
            echo "  - " . $model['name'] . "\n";
        }
    }
}

echo "\n</pre>";
?>
