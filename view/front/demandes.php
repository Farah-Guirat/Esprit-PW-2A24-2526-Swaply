<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swaply - Demandes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body class="bg-slate-100 min-h-screen text-slate-900">

<main class="max-w-7xl mx-auto px-6 py-8">
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200">
        <h1 class="text-3xl font-bold mb-4">Demandes</h1>
        <p class="text-slate-600">Cette page est en cours de développement.</p>
    </div>
</main>

</body>
</html>
