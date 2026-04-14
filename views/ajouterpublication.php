<?php
require_once '../controllers/PublicationController.php';
$controller = new PublicationController();
$erreur = $controller->handleRequest();

$editMode = false;
$publicationData = null;
if (isset($_GET['id'])) {
    $editMode = true;
    require_once '../config/Database.php';
    $database = new Database();
    $db = $database->getConnection();
    $stmt = $db->prepare("SELECT * FROM publications WHERE id_pub = ?");
    $stmt->execute([$_GET['id']]);
    $publicationData = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Swaply - <?= $editMode ? 'Modifier' : 'Ajouter' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-8 rounded-[2rem] shadow-xl border border-teal-100 w-full max-w-md">
        <h2 class="text-2xl font-bold text-teal-800 mb-6"><?= $editMode ? 'Modifier' : 'Nouvelle' ?> Publication</h2>

        <?php if (!empty($erreur)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($erreur) ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="id_client" value="<?= $editMode ? $publicationData['id_client'] : '1' ?>">
            <?php if($editMode): ?>
                <input type="hidden" name="existing_images" value="<?= $publicationData['image'] ?>">
            <?php endif; ?>

            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Titre</label>
                <input type="text" name="titre" value="<?= $editMode ? htmlspecialchars($publicationData['titre']) : '' ?>" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none">
            </div>
            <div>
    <label class="block text-sm font-medium text-gray-600 mb-1">Votre Nom</label>
    <input type="text" name="nom_utilisateur" 
           placeholder="Entrez votre nom"
           class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none">
</div>

            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Message</label>
                <textarea name="contenu" rows="4" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none"><?= $editMode ? htmlspecialchars($publicationData['contenu']) : '' ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Photos</label>
                <input type="file" name="images[]" multiple id="image-upload" class="hidden" accept="image/*">
                <label for="image-upload" class="flex flex-col items-center justify-center w-full p-6 bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl cursor-pointer hover:bg-teal-50 transition-all">
                    <span class="text-gray-500 text-sm">Ajouter des photos</span>
                </label>
                
                <div id="preview" class="mt-4 grid grid-cols-3 gap-2">
                    <?php if ($editMode && !empty($publicationData['image'])): ?>
                        <?php foreach (explode(',', $publicationData['image']) as $img): ?>
                            <img src="../<?= htmlspecialchars($img) ?>" class="w-full h-24 object-cover rounded-lg border">
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <button type="submit" name="submit_pub" class="w-full bg-teal-500 text-white py-4 rounded-xl font-bold shadow-lg hover:bg-teal-600 transition-all">
                <?= $editMode ? 'Enregistrer les modifications' : 'Publier maintenant' ?>
            </button>
        </form>
        <a href="listepublication.php" class="block text-center mt-4 text-gray-400 text-sm">Annuler</a>
    </div>

    <script>
        document.getElementById('image-upload').addEventListener('change', function(e) {
            const preview = document.getElementById('preview');
            preview.innerHTML = ''; // Efface l'ancien aperçu lors de la sélection de nouveaux fichiers
            Array.from(e.target.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = (event) => {
                    const img = document.createElement('img');
                    img.src = event.target.result;
                    img.className = 'w-full h-24 object-cover rounded-lg border-2 border-teal-100';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });
    </script>
</body>
</html>