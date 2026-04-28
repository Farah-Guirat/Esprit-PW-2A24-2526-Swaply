<?php
$uploadDir = __DIR__ . '/../uploads/messages/';

echo "=== VÉRIFICATION DU DOSSIER D'UPLOAD ===\n\n";

echo "Chemin complet: " . $uploadDir . "\n";
echo "Existe: " . (is_dir($uploadDir) ? "✅ OUI" : "❌ NON") . "\n";

if (!is_dir($uploadDir)) {
    echo "\n⏳ Création du dossier...\n";
    if (mkdir($uploadDir, 0755, true)) {
        echo "✅ Dossier créé avec succès\n";
    } else {
        echo "❌ Erreur lors de la création du dossier\n";
    }
}

// Vérifier les permissions
if (is_dir($uploadDir)) {
    $perms = substr(sprintf('%o', fileperms($uploadDir)), -4);
    echo "Permissions: " . $perms . "\n";
    
    // Essayer d'écrire un fichier de test
    $testFile = $uploadDir . 'test_' . time() . '.txt';
    if (file_put_contents($testFile, 'test')) {
        echo "✅ Lecture/écriture: OK\n";
        unlink($testFile);
    } else {
        echo "❌ Impossible d'écrire dans le dossier\n";
    }
}

echo "\n=== DOSSIERS REQUIS ===\n";
echo "uploads/messages/ → Où stocker les fichiers attachés\n";
echo "tmp/ → Fichiers temporaires (statut 'en ligne')\n";

// Créer aussi le dossier tmp s'il n'existe pas
$tmpDir = __DIR__ . '/../tmp/';
if (!is_dir($tmpDir)) {
    mkdir($tmpDir, 0755, true);
    echo "✅ Dossier tmp créé\n";
}
?>
