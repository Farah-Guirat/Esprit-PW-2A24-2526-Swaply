<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$context = $input['context'] ?? '';

//

$data = json_encode([
    "contents" => [[
        "role" => "user",
        "parts" => [["text" => $context]]
    ]],
    "generationConfig" => [
        "temperature" => 0.3,
        "responseMimeType" => "application/json"
    ]
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
$text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

// Clean JSON
$text = preg_replace('/```json|```/', '', $text);
$text = trim($text);

$parsed = json_decode($text, true);

if (!$parsed) {
    echo json_encode(['error' => 'Erreur de parsing JSON']);
    exit;
}

echo json_encode($parsed);