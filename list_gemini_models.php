<?php
require_once __DIR__ . "/config/config.php";

header('Content-Type: text/html; charset=utf-8');
echo "<pre style='background: #1e1e1e; color: #00ff00; padding: 20px; font-family: monospace; font-size: 14px;'>";
echo "=== MODÈLES GEMINI DISPONIBLES ===\n\n";

$apiKey = GEMINI_API_KEY;
echo "Clé API: " . substr($apiKey, 0, 15) . "...\n\n";

$url = "https://generativelanguage.googleapis.com/v1/models?key=" . urlencode($apiKey);
echo "URL: $url\n\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n\n";

if ($httpCode === 200) {
    $models = json_decode($response, true);
    
    if (isset($models['models']) && is_array($models['models'])) {
        echo "✅ MODÈLES TROUVÉS: " . count($models['models']) . "\n";
        echo str_repeat("-", 70) . "\n\n";
        
        foreach ($models['models'] as $model) {
            $name = $model['name'];
            $displayName = $model['displayName'] ?? '';
            $methods = implode(', ', $model['supportedGenerationMethods'] ?? []);
            
            echo "📌 $name\n";
            if ($displayName) echo "   Nom: $displayName\n";
            echo "   Méthodes: $methods\n";
            echo "\n";
        }
    } else {
        echo "❌ Format inattendu:\n";
        print_r($models);
    }
} else {
    echo "❌ Erreur HTTP $httpCode\n\n";
    echo "Réponse:\n";
    echo $response . "\n";
}

echo "</pre>";
?>
