<?php
session_start();

require_once __DIR__ . "/../../config/Database.php";
require_once __DIR__ . "/../../model/Publication.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['email'] !== 'klai.aziz@admin.tn') {
    header("Location: ../front/login.php");
    exit();
}

if (!isset($_GET['pub_id'])) {
    die("ID de publication manquant");
}

$pubId = $_GET['pub_id'];
$database = new Database();
$conn = $database->connect();

// Récupérer les informations de la publication
$query = "SELECT p.*, u.nom, u.prenom FROM publications p
          JOIN utilisateurs u ON p.id_client = u.id_u
          WHERE p.id_pub = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$pubId]);
$publication = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$publication) {
    die("Publication non trouvée");
}

// Récupérer tous les likes de cette publication
$query = "SELECT pl.*, u.nom, u.prenom, u.photo, pl.date_like
          FROM publication_likes pl
          JOIN utilisateurs u ON pl.id_utilisateur = u.id_u
          WHERE pl.id_pub = ?
          ORDER BY pl.date_like DESC";
$stmt = $conn->prepare($query);
$stmt->execute([$pubId]);
$likes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails des Likes - Publication #<?= $pubId ?></title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .like-animation {
            animation: heartPulse 0.6s ease-in-out;
        }

        @keyframes heartPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
    </style>
</head>
<body class="bg-gray-50">

<div class="min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button onclick="window.close()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </button>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">❤️ Détails des Likes</h1>
                        <p class="text-gray-600">Publication de <?= htmlspecialchars($publication['nom']) ?> <?= htmlspecialchars($publication['prenom']) ?></p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold text-red-600"><?= count($likes) ?></div>
                    <div class="text-sm text-gray-500">Total des likes</div>
                </div>
            </div>
        </div>

        <!-- Publication Preview -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">📝 Aperçu de la Publication</h3>
            <div class="border-l-4 border-teal-500 pl-4">
                <h4 class="font-semibold text-gray-800"><?= htmlspecialchars($publication['titre']) ?></h4>
                <p class="text-gray-600 text-sm mt-2"><?= htmlspecialchars(substr($publication['contenu'], 0, 200)) ?>...</p>
                <div class="text-xs text-gray-500 mt-2">
                    Publiée le <?= date('d/m/Y à H:i', strtotime($publication['date_pub'])) ?>
                </div>
            </div>
        </div>

        <!-- Likes List -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">👥 Utilisateurs qui ont aimé</h3>
                <p class="text-gray-600 text-sm mt-1"><?= count($likes) ?> personne(s) ont aimé cette publication</p>
            </div>

            <?php if (empty($likes)): ?>
                <div class="p-12 text-center">
                    <i class="fas fa-heart-broken text-6xl text-gray-300 mb-4"></i>
                    <h4 class="text-xl font-semibold text-gray-600 mb-2">Aucun like pour le moment</h4>
                    <p class="text-gray-500">Cette publication n'a pas encore reçu de likes.</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($likes as $like): ?>
                        <div class="p-6 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="relative">
                                    <?php
                                    $photoPath = '../../uploads/profiles/' . ($like['photo'] ?? 'default.png');
                                    $photoExists = file_exists(__DIR__ . '/../../uploads/profiles/' . ($like['photo'] ?? ''));
                                    if (!$photoExists || empty($like['photo'])) {
                                        $photoPath = 'https://i.pravatar.cc/40?u=' . urlencode($like['nom'] . $like['prenom']);
                                    }
                                    ?>
                                    <img src="<?= $photoPath ?>"
                                         alt="<?= htmlspecialchars($like['nom']) ?>"
                                         class="w-12 h-12 rounded-full object-cover border-2 border-red-200"
                                         onerror="this.src='https://i.pravatar.cc/40?u=<?= urlencode($like['nom'] . $like['prenom']) ?>'">
                                    <div class="absolute -bottom-1 -right-1 bg-red-500 text-white rounded-full p-1">
                                        <i class="fas fa-heart text-xs"></i>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-800">
                                        <?= htmlspecialchars($like['nom']) ?> <?= htmlspecialchars($like['prenom']) ?>
                                    </h4>
                                    <p class="text-sm text-gray-500">
                                        A aimé le <?= date('d/m/Y à H:i', strtotime($like['date_like'])) ?>
                                    </p>
                                </div>
                                <div class="text-red-500 like-animation">
                                    <i class="fas fa-heart text-xl"></i>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Fermer la fenêtre avec la touche Échap
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        window.close();
    }
});

// Animation des cœurs au chargement
document.addEventListener('DOMContentLoaded', function() {
    const hearts = document.querySelectorAll('.like-animation');
    hearts.forEach((heart, index) => {
        heart.style.animationDelay = `${index * 0.1}s`;
    });
});
</script>

</body>
</html>