<?php

class AIService
{
   
    private static string $apiKey = "";

    /* =========================
        1. GET EMBEDDING
    ========================== */
    public static function getEmbedding(string $text): array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-embedding-001:embedContent?key=" . self::$apiKey;

        $payload = [
            "content" => [
                "parts" => [
                    ["text" => $text]
                ]
            ]
        ];

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode($payload)
        ]);

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
            $normA += $a[$i] ** 2;
            $normB += $b[$i] ** 2;
        }

        if ($normA == 0 || $normB == 0) return 0;

        return $dot / (sqrt($normA) * sqrt($normB));
    }

    /* =========================
        3. CLEAN TEXT
    ========================== */
    public static function cleanText(string $text): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $text)));
    }

    /* =========================
        4. BUILD TEXT
    ========================== */
    public static function buildText(array $data): string
    {
        return self::cleanText(
            ($data['titre'] ?? '') . ' ' .
            ($data['description'] ?? '') . ' ' .
            ($data['categorie'] ?? '') . ' ' .
            ($data['niveau'] ?? '')
        );
    }

    /* =========================
        5. MATCH SCORE (IMPORTANT FIX)
    ========================== */
    public static function matchScore(string $text1, string $text2): float
    {
        $vec1 = self::getEmbedding(self::cleanText($text1));
        $vec2 = self::getEmbedding(self::cleanText($text2));

        return self::cosineSimilarity($vec1, $vec2);
    }

    /* =========================
        6. MATCH OFFRES / DEMANDES (FIXED)
    ========================== */
    public static function matchOffre(array $offre, array $demandes, float $threshold = 0.75): array
    {
        $matches = [];

        // 🔥 FIX: build text + correct embedding call
        $offreVec = self::getEmbedding(self::buildText($offre));

        foreach ($demandes as $d) {

            $demandeVec = self::getEmbedding(self::buildText($d));

            $score = self::cosineSimilarity($offreVec, $demandeVec);

            if ($score >= $threshold) {
                $matches[] = [
                    "id" => $d["id_demande"] ?? $d["id"] ?? null,
                    "score" => round($score, 3)
                ];
            }
        }

        usort($matches, fn($a, $b) => $b["score"] <=> $a["score"]);

        return $matches;
    }
}