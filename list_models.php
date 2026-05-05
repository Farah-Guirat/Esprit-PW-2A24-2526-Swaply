<?php
/**
 * Liste tous les modèles Gemini disponibles
 */

echo "<pre style='background: #f4f4f4; padding: 15px; border-radius: 5px;'>";
echo "=== MODÈLES GEMINI DISPONIBLES ===\n\n";

$apiKey = "AIzaSyBpd2WZuvb61d2zKYfXTiegZd8mYbTOzBY";
$url = "https://generativelanguage.googleapis.com/v1beta/models?key=$apiKey";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Code HTTP: $httpCode\n\n";

$models = json_decode($response, true);

if (isset($models['models']) && is_array($models['models'])) {
    echo "✅ Modèles disponibles:\n";
    echo str_repeat("-", 60) . "\n";
    
    foreach ($models['models'] as $model) {
        echo "\n📌 " . $model['name'] . "\n";
        echo "   Display Name: " . ($model['displayName'] ?? 'N/A') . "\n";
        echo "   Description: " . substr($model['description'] ?? 'N/A', 0, 50) . "...\n";
        
        if (isset($model['supportedGenerationMethods'])) {
            echo "   Méthodes: " . implode(', ', $model['supportedGenerationMethods']) . "\n";
        }
    }
    
    echo "\n" . str_repeat("-", 60) . "\n";
} else {
    echo "❌ Erreur:\n";
    echo json_encode($models, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

echo "</pre>";
?>
