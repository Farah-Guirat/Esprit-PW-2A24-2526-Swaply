<?php
require_once '../../controller/ReclamationController.php';

session_start(); 

// Nthabtou elli l-utilisateur mahloul bih el compte
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['submit'])) {
    $controller = new ReclamationController();

    // Nesta'mlou l-ID mel session f'blast el '1' el static[cite: 11, 14]
    $userId = $_SESSION['user']['id_u']; 

    $controller->ajouter(
        $userId, 
        $_POST['description'],
        $_POST['rating'],
        $_POST['type'],
        $_POST['username_cible'] ?? ''
    );

    header("Location: reclamations.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Swaply - Nouvelle Réclamation</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body class="bg-gray-50">

<main class="max-w-3xl mx-auto py-16 px-8">

    <div class="bg-white p-10 rounded-3xl shadow">

        <h2 class="text-2xl font-bold mb-6 text-gray-800">Nouvelle Réclamation</h2>

        <form method="POST">

            <!-- Type -->
            <label class="block mb-2 font-medium text-gray-700">Type</label>
            <select name="type" class="w-full p-3 border rounded-xl mb-4 focus:outline-none focus:ring-2 focus:ring-teal-400">
                <option value="person">Personne</option>
                <option value="company">Entreprise</option>
            </select>

            <!-- Username ciblé -->
            <label class="block mb-2 font-medium text-gray-700">Nom d'utilisateur concerné</label>
            <div class="relative mb-6">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-teal-500 font-bold select-none text-base">@</span>
                <input
                    type="text"
                    name="username_cible"
                    required
                    class="w-full pl-9 pr-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-400"
                    placeholder="username"
                    oninput="this.value = this.value.replace(/[@\s]/g, '')"
                    title="Sans @ ni espace"
                >
            </div>

            <!-- Description -->
            <label class="block mb-2 font-medium text-gray-700">Description</label>
            <textarea
                name="description"
                required
                class="w-full p-4 border rounded-xl mb-6 focus:outline-none focus:ring-2 focus:ring-teal-400"
                placeholder="Décrivez votre problème..."
                rows="4"
            ></textarea>

            <!-- Note -->
            <label class="block mb-2 font-medium text-gray-700">Note</label>
            <select name="rating" class="w-full p-3 border rounded-xl mb-8 focus:outline-none focus:ring-2 focus:ring-teal-400">
                <option value="1">⭐</option>
                <option value="2">⭐⭐</option>
                <option value="3">⭐⭐⭐</option>
                <option value="4">⭐⭐⭐⭐</option>
                <option value="5">⭐⭐⭐⭐⭐</option>
            </select>

            <!-- Actions -->
            <div class="flex items-center gap-4">
                <button
                    type="submit"
                    name="submit"
                    class="bg-teal-500 text-white px-8 py-3 rounded-xl hover:bg-teal-600 transition font-medium shadow-sm"
                >
                    Envoyer
                </button>
                
                <a 
                    href="reclamations.php" 
                    class="bg-gray-100 text-gray-600 px-8 py-3 rounded-xl hover:bg-gray-200 transition font-medium text-center"
                >
                    Annuler et retour
                </a>
            </div>

        </form>
        
    </div>

</main>

</body>
</html>