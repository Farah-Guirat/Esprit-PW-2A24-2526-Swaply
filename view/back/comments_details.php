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

// Récupérer tous les commentaires de cette publication
$query = "SELECT c.*, u.nom, u.prenom, u.photo
          FROM commentaires c
          JOIN utilisateurs u ON c.id_client = u.id_u
          WHERE c.id_pub = ?
          ORDER BY c.date_com DESC";
$stmt = $conn->prepare($query);
$stmt->execute([$pubId]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails des Commentaires - Publication #<?= $pubId ?></title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .comment-animation {
            animation: slideInLeft 0.5s ease-out;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .comment:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50">

<div class="min-h-screen py-8">
    <div class="max-w-6xl mx-auto px-4">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button onclick="window.close()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </button>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">💬 Détails des Commentaires</h1>
                        <p class="text-gray-600">Publication de <?= htmlspecialchars($publication['nom']) ?> <?= htmlspecialchars($publication['prenom']) ?></p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold text-blue-600"><?= count($comments) ?></div>
                    <div class="text-sm text-gray-500">Total des commentaires</div>
                </div>
            </div>
        </div>

        <!-- Publication Preview -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">📝 Aperçu de la Publication</h3>
            <div class="border-l-4 border-teal-500 pl-4">
                <h4 class="font-semibold text-gray-800"><?= htmlspecialchars($publication['titre']) ?></h4>
                <p class="text-gray-600 text-sm mt-2"><?= htmlspecialchars(substr($publication['contenu'], 0, 300)) ?>...</p>
                <div class="text-xs text-gray-500 mt-2">
                    Publiée le <?= date('d/m/Y à H:i', strtotime($publication['date_pub'])) ?>
                </div>
            </div>
        </div>

        <!-- Comments List -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">🗣️ Commentaires (<?= count($comments) ?>)</h3>
                <p class="text-gray-600 text-sm mt-1">Tous les commentaires de cette publication</p>
            </div>

            <?php if (empty($comments)): ?>
                <div class="p-12 text-center">
                    <i class="fas fa-comments text-6xl text-gray-300 mb-4"></i>
                    <h4 class="text-xl font-semibold text-gray-600 mb-2">Aucun commentaire</h4>
                    <p class="text-gray-500">Cette publication n'a pas encore reçu de commentaires.</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($comments as $index => $comment): ?>
                        <div class="comment comment-animation p-6 hover:bg-gray-50 transition-all duration-300" style="animation-delay: <?= $index * 0.1 ?>s">
                            <div class="flex gap-4">
                                <div class="flex-shrink-0">
                                    <?php
                                    $photoPath = '../../uploads/profiles/' . ($comment['photo'] ?? 'default.png');
                                    $photoExists = file_exists(__DIR__ . '/../../uploads/profiles/' . ($comment['photo'] ?? ''));
                                    if (!$photoExists || empty($comment['photo'])) {
                                        $photoPath = 'https://i.pravatar.cc/40?u=' . urlencode($comment['nom'] . $comment['prenom']);
                                    }
                                    ?>
                                    <img src="<?= $photoPath ?>"
                                         alt="<?= htmlspecialchars($comment['nom']) ?>"
                                         class="w-12 h-12 rounded-full object-cover border-2 border-blue-200"
                                         onerror="this.src='https://i.pravatar.cc/40?u=<?= urlencode($comment['nom'] . $comment['prenom']) ?>'">
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <h4 class="font-semibold text-gray-800">
                                            <?= htmlspecialchars($comment['nom']) ?> <?= htmlspecialchars($comment['prenom']) ?>
                                        </h4>
                                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                            <?= date('d/m/Y H:i', strtotime($comment['date_com'])) ?>
                                        </span>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-blue-400">
                                        <p class="text-gray-800 leading-relaxed"><?= htmlspecialchars($comment['contenu']) ?></p>
                                    </div>
                                    <div class="flex gap-4 mt-3 text-sm text-gray-500">
                                        <span><i class="fas fa-thumbs-up"></i> 0 likes</span>
                                        <span><i class="fas fa-reply"></i> 0 réponses</span>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <button onclick="editComment(<?= $comment['id_com'] ?>, '<?= addslashes($comment['contenu']) ?>')"
                                            class="text-blue-500 hover:text-blue-700 text-sm">
                                        <i class="fas fa-edit"></i> Modifier
                                    </button>
                                    <button onclick="deleteComment(<?= $comment['id_com'] ?>)"
                                            class="text-red-500 hover:text-red-700 text-sm">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
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

function editComment(id, currentContent) {
    const newContent = prompt('Modifier le commentaire:', currentContent);
    if (newContent !== null && newContent !== currentContent) {
        // Envoyer la modification au parent window
        if (window.opener) {
            window.opener.postMessage({
                action: 'edit_comment',
                id: id,
                content: newContent
            }, '*');
        }
        // Fermer cette fenêtre
        window.close();
    }
}

function deleteComment(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?')) {
        // Envoyer la suppression au parent window
        if (window.opener) {
            window.opener.postMessage({
                action: 'delete_comment',
                id: id
            }, '*');
        }
        // Fermer cette fenêtre
        window.close();
    }
}

// Écouter les messages du parent window pour les mises à jour
window.addEventListener('message', function(event) {
    if (event.data.action === 'comment_updated') {
        // Recharger la page pour voir les changements
        location.reload();
    }
});
</script>

</body>
</html>