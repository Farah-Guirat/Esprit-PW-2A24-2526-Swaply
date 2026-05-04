<?php

require_once "Config/Database.php";
require_once "Services/AIService.php";

$pdo = Database::getInstance();

echo "START EMBEDDINGS...\n";

/* ================= OFFRES ================= */
$offres = $pdo->query("SELECT * FROM offres")->fetchAll();

foreach ($offres as $o) {

    $text = trim(
        ($o['titre'] ?? '') . " " .
        ($o['description'] ?? '') . " " .
        ($o['categorie'] ?? '') . " " .
        ($o['niveau'] ?? '')
    );

    if ($text == "") continue;

    $embedding = AIService::getEmbedding($text);

    if (!empty($embedding)) {
        $stmt = $pdo->prepare("UPDATE offres SET embedding=? WHERE id_offre=?");
        $stmt->execute([json_encode($embedding), $o['id_offre']]);

        echo "OK OFFRE " . $o['id_offre'] . "\n";
    } else {
        echo "FAIL OFFRE " . $o['id_offre'] . "\n";
    }
}

/* ================= DEMANDES ================= */
$demandes = $pdo->query("SELECT * FROM demandes")->fetchAll();

foreach ($demandes as $d) {

    $text = trim(
        ($d['titre'] ?? '') . " " .
        ($d['description'] ?? '') . " " .
        ($d['categorie'] ?? '') . " " .
        ($d['niveau'] ?? '')
    );

    if ($text == "") continue;

    $embedding = AIService::getEmbedding($text);

    if (!empty($embedding)) {
        $stmt = $pdo->prepare("UPDATE demandes SET embedding=? WHERE id_demande=?");
        $stmt->execute([json_encode($embedding), $d['id_demande']]);

        echo "OK DEMANDE " . $d['id_demande'] . "\n";
    } else {
        echo "FAIL DEMANDE " . $d['id_demande'] . "\n";
    }
}

echo "DONE\n";