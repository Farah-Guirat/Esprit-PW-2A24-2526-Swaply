<?php
require_once '../controllers/AdminController.php';

if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$id_pub = $_GET['id'];
$adminCtrl = new AdminController();
$stats = $adminCtrl->getPublicationStats($id_pub);

if (!$stats) {
    header("Location: admin_dashboard.php");
    exit();
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Statistiques Publication - Swaply Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-50 min-h-screen">
    <div class="max-w-4xl mx-auto p-8">
        <div class="bg-white rounded-3xl shadow-lg p-8">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-3xl font-bold text-teal-600">Statistiques de la Publication</h1>
                <a href="admin_dashboard.php" class="bg-teal-500 text-white px-4 py-2 rounded hover:bg-teal-600">Retour</a>
            </div>

            <div class="mb-8">
                <h2 class="text-xl font-bold mb-4">Publication : <?= htmlspecialchars($stats['titre']) ?></h2>
                <p class="text-gray-600">Par : <?= htmlspecialchars($stats['auteur']) ?> - Date : <?= $stats['date_pub'] ?></p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-red-50 p-6 rounded-2xl border border-red-200">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-heart text-white text-xl"></i>
                        </div>
                        <div>
                            <p class="text-red-600 font-bold text-2xl"><?= $stats['likes'] ?></p>
                            <p class="text-red-700">Likes</p>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 p-6 rounded-2xl border border-blue-200">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-comments text-white text-xl"></i>
                        </div>
                        <div>
                            <p class="text-blue-600 font-bold text-2xl"><?= $stats['commentaires'] ?></p>
                            <p class="text-blue-700">Commentaires</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-gray-200">
                <h3 class="text-lg font-bold mb-4">Répartition</h3>
                <canvas id="statsChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('statsChart').getContext('2d');
        const statsChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Likes', 'Commentaires'],
                datasets: [{
                    data: [<?= $stats['likes'] ?>, <?= $stats['commentaires'] ?>],
                    backgroundColor: ['#ef4444', '#3b82f6'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    </script>
</body>
</html>