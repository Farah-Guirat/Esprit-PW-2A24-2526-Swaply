<?php

class AIService
{
    private static string $apiKey = "AIzaSyCR1ZWgYTrapFEHTn0DFKHe37YhHxaN-Jk";

    /* =========================
        1. GET EMBEDDING (Gemini)
    ========================== */
    public static function getEmbedding(string $text): array
    {
        $payload = [
            "content" => [
                "parts" => [
                    ["text" => $text]
                ]
            ]
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,
"https://generativelanguage.googleapis.com/v1beta/models/gemini-embedding-001:embedContent?key=" . self::$apiKey
);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            return [];
        }

        curl_close($ch);

        $json = json_decode($response, true);

        return $json["embedding"]["values"] ?? [];
    }

    /* =========================
        2. COSINE SIMILARITY
    ========================== */
    public static function cosineSimilarity(array $a, array $b): float
    {
        $dot = 0;
        $normA = 0;
        $normB = 0;

        $count = min(count($a), count($b));

        for ($i = 0; $i < $count; $i++) {
            $dot += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }

        if ($normA == 0 || $normB == 0) return 0;

        return $dot / (sqrt($normA) * sqrt($normB));
    }

    /* =========================
        3. CLEAN TEXT (important)
    ========================== */
    public static function cleanText(string $text): string
    {
        $text = strtolower($text);
        $text = trim($text);
        $text = preg_replace('/\s+/', ' ', $text);

        return $text;
    }

    /* =========================
        4. MATCH SCORE
    ========================== */
    public static function matchScore(string $text1, string $text2): float
    {
        $text1 = self::cleanText($text1);
        $text2 = self::cleanText($text2);

        $vec1 = self::getEmbedding($text1);
        $vec2 = self::getEmbedding($text2);

        return self::cosineSimilarity($vec1, $vec2);
    }

    /* =========================
        5. MATCH LIST (offre vs demandes)
    ========================== */
    public static function matchOffre(array $offreText, array $demandes, float $threshold = 0.75): array
    {
        $matches = [];

        $offreVec = self::getEmbedding(self::cleanText($offreText));

        foreach ($demandes as $d) {

            $demandeVec = self::getEmbedding(self::cleanText($d["text"]));

            $score = self::cosineSimilarity($offreVec, $demandeVec);

            if ($score >= $threshold) {
                $matches[] = [
                    "id" => $d["id"],
                    "score" => round($score, 3)
                ];
            }
        }

        // tri décroissant
        usort($matches, function ($a, $b) {
            return $b["score"] <=> $a["score"];
        });

        return $matches;
    }



    public static function buildText(array $data): string
{
    return strtolower(
        $data['titre'] . ' ' .
        $data['description'] . ' ' .
        $data['categorie'] . ' ' .
        $data['niveau']
    );
}
}