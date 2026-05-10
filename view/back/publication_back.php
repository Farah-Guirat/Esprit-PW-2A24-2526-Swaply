<?php
session_start();

require_once __DIR__ . "/../../config/Database.php";
require_once __DIR__ . "/../../model/Publication.php";
require_once __DIR__ . "/../../model/Commentaire.php";
require_once __DIR__ . "/../../controller/AdminController.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['email'] !== 'klai.aziz@admin.tn') {
    header("Location: ../front/login.php");
    exit();
}

$database = new Database();
$conn = $database->connect();
$adminController = new AdminController();
$adminPhoto = $_SESSION['user']['photo'] ?? null;

// Gestion des actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_pub'])) {
        $adminController->deletePublication($_POST['delete_pub']);
        header("Location: publication_back.php");
        exit();
    }

    if (isset($_POST['delete_comment'])) {
        $adminController->deleteComment($_POST['delete_comment']);
        header("Location: publication_back.php");
        exit();
    }

    if (isset($_POST['edit_comment'])) {
        $adminController->updateComment($_POST['edit_comment'], $_POST['edit_contenu']);
        header("Location: publication_back.php");
        exit();
    }

    if (isset($_POST['delete_selected_comments'])) {
        foreach ($_POST['selected_comments'] as $id) {
            $adminController->deleteComment($id);
        }
        header("Location: publication_back.php");
        exit();
    }

    if (isset($_POST['delete_report'])) {
        $adminController->deleteReport($_POST['delete_report']);
        header("Location: publication_back.php");
        exit();
    }
}

$sortLikes = isset($_GET['sort_likes']) ? $_GET['sort_likes'] : 'date';
$data = $adminController->getDashboardData($sortLikes);

// Handle stats request
if (isset($_GET['action']) && $_GET['action'] == 'stats' && isset($_GET['id'])) {
    $pubId = $_GET['id'];
    $likesStmt = $conn->prepare("SELECT COUNT(*) as likes FROM publication_likes WHERE id_pub = ?");
    $likesStmt->execute([$pubId]);
    $likes = $likesStmt->fetch(PDO::FETCH_ASSOC)['likes'] ?? 0;
    
    $commentsStmt = $conn->prepare("SELECT COUNT(*) as comments FROM commentaires WHERE id_pub = ?");
    $commentsStmt->execute([$pubId]);
    $comments = $commentsStmt->fetch(PDO::FETCH_ASSOC)['comments'];
    
    $engagement = $likes + $comments;
    
    header('Content-Type: application/json');
    echo json_encode(['likes' => $likes, 'comments' => $comments, 'engagement' => $engagement]);
    exit();
}

// Calculate monthly publications data for chart
$monthlyData = [];
for ($i = 11; $i >= 0; $i--) {
    $date = date('Y-m', strtotime("-$i months"));
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM publications WHERE DATE_FORMAT(date_pub, '%Y-%m') = ?");
    $stmt->execute([$date]);
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    $monthlyData[] = $count;
}

// Calculate engagement data (top 10 publications by engagement)
$engagementData = [];
$stmt = $conn->prepare("
    SELECT p.id_pub, p.titre, 
           (COALESCE(likes_count.likes, 0) + COALESCE(comments_count.comments, 0)) as engagement
    FROM publications p
    LEFT JOIN (SELECT id_pub, COUNT(*) as likes FROM publication_likes GROUP BY id_pub) likes_count ON p.id_pub = likes_count.id_pub
    LEFT JOIN (SELECT id_pub, COUNT(*) as comments FROM commentaires GROUP BY id_pub) comments_count ON p.id_pub = comments_count.id_pub
    ORDER BY engagement DESC LIMIT 10
");
$stmt->execute();
$engagementData = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Publications</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="flex h-screen overflow-hidden">

<?php $currentPage = 'publications'; require __DIR__ . '/sidebar.php'; ?>

  <!-- MAIN CONTENT -->
  <div class="main-content">
    <!-- HEADER -->
<header class="header" id="main-header">      <h2 id="page-title">Publications</h2>
      
      <div class="header-right">
        <div class="search-box">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" placeholder="Rechercher...">
        </div>
        
        <div class="notifications">
          <i class="fa-solid fa-bell"></i>
          <span class="badge">7</span>
        </div>
        
        <div class="user">
          <?php if ($adminPhoto): ?>
            <img src="../../uploads/profiles/<?= htmlspecialchars($adminPhoto) ?>" alt="Admin" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
          <?php else: ?>
            <img src="https://i.pravatar.cc/40?img=12" alt="Admin">
          <?php endif; ?>
          <div>
            <p class="name">Admin</p>
            <p class="role">Super Admin</p>
          </div>
        </div>
        
        <a href="../../controller/logout.php" class="logout-btn" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?');">
          <i class="fa-solid fa-sign-out-alt"></i> Déconnexion
        </a>
      </div>
    </header>

    <!-- PAGE CONTENT -->
    <div class="page-content" id="main-content">
      <div class="p-6 md:p-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Gestion des Publications</h1>
                <p class="text-gray-600">Gérez toutes les publications, commentaires et likes du système</p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Total Publications</p>
                            <p class="text-3xl font-bold text-teal-600"><?= $data['total_pubs'] ?></p>
                        </div>
                        <i class="fas fa-newspaper text-3xl text-teal-200"></i>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Total Commentaires</p>
                            <p class="text-3xl font-bold text-blue-600"><?= $data['total_coms'] ?></p>
                        </div>
                        <i class="fas fa-comments text-3xl text-blue-200"></i>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Total Likes</p>
                            <p class="text-3xl font-bold text-red-600"><?= $data['total_likes'] ?></p>
                        </div>
                        <i class="fas fa-heart text-3xl text-red-200"></i>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Total Stories</p>
                            <p class="text-3xl font-bold text-purple-600"><?= $data['total_stories'] ?></p>
                        </div>
                        <i class="fas fa-camera text-3xl text-purple-200"></i>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Total Signalements</p>
                            <p class="text-3xl font-bold text-orange-600"><?= $data['total_reports'] ?? 0 ?></p>
                        </div>
                        <i class="fas fa-flag text-3xl text-orange-200"></i>
                    </div>
                </div>
            </div>

            <!-- Advanced Statistics Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Répartition des Publications par Mois</h3>
                    <canvas id="publicationsChart" width="400" height="200"></canvas>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Engagement par Publication</h3>
                    <canvas id="engagementChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Additional Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Taux d'Engagement Moyen</p>
                            <p class="text-3xl font-bold text-green-600">
                                <?php
                                $totalEngagement = $data['total_likes'] + $data['total_coms'];
                                $avgEngagement = $data['total_pubs'] > 0 ? round(($totalEngagement / $data['total_pubs']), 1) : 0;
                                echo $avgEngagement;
                                ?>
                            </p>
                            <p class="text-xs text-gray-500">likes + commentaires par pub</p>
                        </div>
                        <i class="fas fa-chart-line text-3xl text-green-200"></i>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Publications Actives</p>
                            <p class="text-3xl font-bold text-orange-600">
                                <?php
                                $activePubs = 0;
                                foreach ($data['all_publications'] as $pub) {
                                    if (($pub['likes'] ?? 0) + ($pub['comments_count'] ?? 0) > 0) $activePubs++;
                                }
                                echo $activePubs;
                                ?>
                            </p>
                            <p class="text-xs text-gray-500">avec engagement > 0</p>
                        </div>
                        <i class="fas fa-fire text-3xl text-orange-200"></i>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Ratio Commentaires/Likes</p>
                            <p class="text-3xl font-bold text-indigo-600">
                                <?php
                                $ratio = $data['total_likes'] > 0 ? round(($data['total_coms'] / $data['total_likes']), 2) : 0;
                                echo $ratio;
                                ?>
                            </p>
                            <p class="text-xs text-gray-500">commentaires par like</p>
                        </div>
                        <i class="fas fa-balance-scale text-3xl text-indigo-200"></i>
                    </div>
                </div>
            </div>

            <!-- Publications Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-8">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">Publications Récentes</h2>
                            <p class="text-sm text-gray-600 mt-1">Affichage de toutes les publications avec statistiques d'engagement</p>
                        </div>
                        <div class="flex gap-3">
                            <select id="sortSelect" onchange="changeSort(this.value)" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                                <option value="date" <?= $sortLikes === 'date' ? 'selected' : '' ?>>Trier par Date</option>
                                <option value="likes" <?= $sortLikes === 'likes' ? 'selected' : '' ?>>Trier par Likes</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Auteur</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Titre</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Image</th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">
                                    <i class="fas fa-heart text-red-500"></i> Likes
                                </th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">
                                    <i class="fas fa-comments text-blue-500"></i> Commentaires
                                </th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">
                                    <i class="fas fa-camera text-purple-500"></i> Stories
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($data['all_publications'] as $pub): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <?php
                                        $photoPath = '../../uploads/profiles/' . ($pub['photo'] ?? 'default.png');
                                        $photoExists = file_exists(__DIR__ . '/../../uploads/profiles/' . ($pub['photo'] ?? ''));
                                        if (!$photoExists || empty($pub['photo'])) {
                                            $photoPath = 'https://i.pravatar.cc/40?u=' . urlencode($pub['nom'] . $pub['prenom']);
                                        }
                                        ?>
                                        <img src="<?= $photoPath ?>"
                                             alt="<?= htmlspecialchars($pub['nom']) ?>"
                                             class="w-10 h-10 rounded-full object-cover border-2 border-gray-200"
                                             onerror="this.src='https://i.pravatar.cc/40?u=<?= urlencode($pub['nom'] . $pub['prenom']) ?>'">
                                        <div>
                                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($pub['nom']) ?></p>
                                            <p class="text-xs text-gray-500">@<?= htmlspecialchars($pub['prenom']) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-800 font-medium"><?= htmlspecialchars(substr($pub['titre'], 0, 50)) ?>...</p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if (!empty($pub['image'])): ?>
                                        <div class="flex gap-2 justify-center">
                                            <?php $images = array_slice(explode(',', $pub['image']), 0, 2); ?>
                                            <?php foreach ($images as $img): ?>
                                                <img src="../../<?= trim($img) ?>" 
                                                     alt="publication" 
                                                     class="w-10 h-10 rounded object-cover cursor-pointer hover:scale-150 transition"
                                                     title="Cliquez pour voir">
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400">Aucune image</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center gap-1">
                                        <span class="inline-flex items-center gap-2 bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-semibold hover:bg-red-200 transition-colors cursor-pointer"
                                              onclick="showLikesDetails(<?= $pub['id_pub'] ?>)">
                                            <i class="fas fa-heart text-red-500"></i>
                                            <span id="likes-<?= $pub['id_pub'] ?>"><?= $pub['likes'] ?? 0 ?></span>
                                        </span>
                                        <span class="text-xs text-gray-500">likes</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center gap-1">
                                        <span class="inline-flex items-center gap-2 bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold hover:bg-blue-200 transition-colors cursor-pointer"
                                              onclick="showCommentsDetails(<?= $pub['id_pub'] ?>)">
                                            <i class="fas fa-comments text-blue-500"></i>
                                            <span id="comments-<?= $pub['id_pub'] ?>"><?= $pub['comments_count'] ?? 0 ?></span>
                                        </span>
                                        <span class="text-xs text-gray-500">commentaires</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center gap-1">
                                        <span class="inline-flex items-center gap-2 bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm font-semibold">
                                            <i class="fas fa-camera text-purple-500"></i>
                                            <span id="stories-<?= $pub['id_pub'] ?>"><?= $pub['stories_count'] ?? 0 ?></span>
                                        </span>
                                        <span class="text-xs text-gray-500">stories</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?= date('d/m/Y H:i', strtotime($pub['date_pub'])) ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex gap-2 justify-end">
                                        <button onclick="viewStats(<?= $pub['id_pub'] ?>)" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition">
                                            <i class="fas fa-chart-bar"></i> Stats
                                        </button>
                                        <form method="POST" class="inline" onsubmit="return confirm('Supprimer cette publication ?');">
                                            <input type="hidden" name="delete_pub" value="<?= $pub['id_pub'] ?>">
                                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Comments Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-2xl font-bold text-gray-800">Gestion des Commentaires</h2>
                    <p class="text-sm text-gray-600 mt-1">Modifiez ou supprimez les commentaires des utilisateurs</p>
                </div>

                <form method="POST">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-6 py-3 text-left"><input type="checkbox" id="selectAllComments" onchange="toggleAll(this)"></th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Auteur</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Commentaire</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Publication</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($data['all_commentaires'] as $com): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4"><input type="checkbox" name="selected_comments[]" value="<?= $com['id_com'] ?>" class="comment-checkbox"></td>
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-teal-600"><?= htmlspecialchars($com['nom']) ?></p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-800"><?= htmlspecialchars(substr($com['contenu'], 0, 80)) ?>...</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-600"><?= htmlspecialchars(substr($com['titre'], 0, 50)) ?>...</p>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <?= date('d/m/Y H:i', strtotime($com['date_com'])) ?>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex gap-2 justify-end">
                                            <button type="button" onclick="editComment(<?= $com['id_com'] ?>, '<?= addslashes($com['contenu']) ?>')" class="text-blue-500 hover:text-blue-700 text-sm">
                                                <i class="fas fa-edit"></i> Modifier
                                            </button>
                                            <form method="POST" class="inline" onsubmit="return confirm('Supprimer ce commentaire ?');">
                                                <input type="hidden" name="delete_comment" value="<?= $com['id_com'] ?>">
                                                <button type="submit" class="text-red-500 hover:text-red-700 text-sm">
                                                    <i class="fas fa-trash"></i> Supprimer
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-6 border-t border-gray-200 flex gap-3">
                        <button type="submit" name="delete_selected_comments" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded transition" 
                                onclick="return confirm('Supprimer les commentaires sélectionnés ?')">
                            <i class="fas fa-trash"></i> Supprimer Sélectionnés
                        </button>
                    </div>
                </form>
            </div>

            <!-- Reports Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mt-8">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-2xl font-bold text-gray-800">Historique des Signalements</h2>
                    <p class="text-sm text-gray-600 mt-1">Consultez et gérez tous les signalements soumis par les utilisateurs</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Signalé par</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Type</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Raison</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Description</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Statut</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($data['all_reports'] as $report): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($report['nom'] . ' ' . $report['prenom']) ?></p>
                                    <p class="text-xs text-gray-500">@<?= htmlspecialchars($report['email']) ?></p>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($report['id_pub']): ?>
                                        <span class="inline-flex items-center gap-2 bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                                            <i class="fas fa-newspaper"></i> Publication
                                        </span>
                                    <?php elseif ($report['id_com']): ?>
                                        <span class="inline-flex items-center gap-2 bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">
                                            <i class="fas fa-comment"></i> Commentaire
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-800 font-medium"><?= htmlspecialchars($report['reason']) ?></p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-600"><?= htmlspecialchars(substr($report['description'] ?? '', 0, 50)) ?>...</p>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $statusColors = [
                                        'pending' => 'yellow',
                                        'in_review' => 'blue',
                                        'resolved' => 'green',
                                        'rejected' => 'red'
                                    ];
                                    $statusLabels = [
                                        'pending' => 'En attente',
                                        'in_review' => 'En revue',
                                        'resolved' => 'Résolu',
                                        'rejected' => 'Rejeté'
                                    ];
                                    $color = $statusColors[$report['status']] ?? 'gray';
                                    $label = $statusLabels[$report['status']] ?? $report['status'];
                                    ?>
                                    <span class="inline-flex items-center gap-2 bg-<?= $color ?>-100 text-<?= $color ?>-800 px-3 py-1 rounded-full text-sm">
                                        <i class="fas fa-circle text-<?= $color ?>-500"></i> <?= $label ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?= date('d/m/Y H:i', strtotime($report['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex gap-2 justify-end">
                                        <button onclick="viewReportDetails(<?= $report['id_report'] ?>)" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition">
                                            <i class="fas fa-eye"></i> Détails
                                        </button>
                                        <form method="POST" class="inline" onsubmit="return confirm('Supprimer ce signalement ?');">
                                            <input type="hidden" name="delete_report" value="<?= $report['id_report'] ?>">
                                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
  </div>
</div>

<script src="script.js"></script>
<script src="../../assets/recl/script.js"></script>
<script>
function toggleAll(source) {
    const checkboxes = document.querySelectorAll('.comment-checkbox');
    checkboxes.forEach(cb => cb.checked = source.checked);
}

function editComment(id, currentContent) {
    const newContent = prompt('Modifier le commentaire:', currentContent);
    if (newContent !== null && newContent !== currentContent) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="edit_comment" value="${id}">
            <input type="hidden" name="edit_contenu" value="${newContent.replace(/"/g, '&quot;')}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function changeSort(sortType) {
    window.location.href = `publication_back.php?sort_likes=${sortType}`;
}

function viewStats(pubId) {
    fetch(`publication_back.php?action=stats&id=${pubId}`)
        .then(response => response.json())
        .then(data => {
            alert(`📊 Statistiques de la publication #${pubId}:\n\n❤️ Likes: ${data.likes}\n💬 Commentaires: ${data.comments}\n🎯 Engagement total: ${data.engagement}`);
        })
        .catch(error => {
            console.error('Erreur lors de la récupération des stats:', error);
            alert('Erreur lors de la récupération des statistiques');
        });
}

function showLikesDetails(pubId) {
    // Ouvrir une modal ou rediriger vers une page détaillée des likes
    window.open(`likes_details.php?pub_id=${pubId}`, '_blank', 'width=800,height=600');
}

function showCommentsDetails(pubId) {
    // Ouvrir une modal ou rediriger vers une page détaillée des commentaires
    window.open(`comments_details.php?pub_id=${pubId}`, '_blank', 'width=1000,height=700');
}

function viewReportDetails(reportId) {
    // Ouvrir une modal ou rediriger vers une page détaillée du signalement
    window.open(`report_details.php?report_id=${reportId}`, '_blank', 'width=800,height=600');
}

// Rafraîchir automatiquement les statistiques toutes les 30 secondes
setInterval(() => {
    refreshStats();
}, 30000);

function refreshStats() {
    const pubRows = document.querySelectorAll('tbody tr');
    pubRows.forEach((row, index) => {
        const pubId = row.querySelector('[onclick*="viewStats"]').getAttribute('onclick').match(/viewStats\((\d+)\)/)?.[1];
        if (pubId) {
            fetch(`publication_back.php?action=stats&id=${pubId}`)
                .then(response => response.json())
                .then(data => {
                    const likesElement = document.getElementById(`likes-${pubId}`);
                    const commentsElement = document.getElementById(`comments-${pubId}`);
                    const storiesElement = document.getElementById(`stories-${pubId}`);

                    if (likesElement && likesElement.textContent !== data.likes.toString()) {
                        likesElement.textContent = data.likes;
                        likesElement.style.animation = 'none';
                        setTimeout(() => likesElement.style.animation = 'heartPulse 0.6s ease-in-out', 10);
                    }
                    if (commentsElement && commentsElement.textContent !== data.comments.toString()) {
                        commentsElement.textContent = data.comments;
                        commentsElement.style.animation = 'none';
                        setTimeout(() => commentsElement.style.animation = 'commentPulse 0.6s ease-in-out', 10);
                    }
                    if (storiesElement && data.stories !== undefined) {
                        storiesElement.textContent = data.stories;
                    }
                })
                .catch(error => console.error('Erreur de rafraîchissement:', error));
        }
    });
}

document.head.appendChild(style);

// Initialize Charts
document.addEventListener('DOMContentLoaded', function() {
    // Publications Chart
    const publicationsCtx = document.getElementById('publicationsChart').getContext('2d');
    new Chart(publicationsCtx, {
        type: 'line',
        data: {
            labels: [
                <?php for ($i = 11; $i >= 0; $i--): ?>
                    '<?= date('M Y', strtotime("-$i months")) ?>',
                <?php endfor; ?>
            ],
            datasets: [{
                label: 'Publications',
                data: <?= json_encode($monthlyData) ?>,
                borderColor: 'rgb(20, 184, 166)',
                backgroundColor: 'rgba(20, 184, 166, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Engagement Chart
    const engagementCtx = document.getElementById('engagementChart').getContext('2d');
    new Chart(engagementCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_map(function($item) { return substr($item['titre'], 0, 20) . '...'; }, $engagementData)) ?>,
            datasets: [{
                label: 'Engagement',
                data: <?= json_encode(array_column($engagementData, 'engagement')) ?>,
                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Animate stat elements
    const statElements = document.querySelectorAll('[id^="likes-"], [id^="comments-"], [id^="stories-"]');
    statElements.forEach((el, index) => {
        el.style.animation = `fadeInUp 0.6s ease-out ${index * 0.1}s both`;
    });
});

// Animation CSS pour les statistiques
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes heartPulse {
        0%, 100% { transform: scale(1); color: #ef4444; }
        50% { transform: scale(1.3); color: #dc2626; }
    }

    @keyframes commentPulse {
        0%, 100% { transform: scale(1); color: #3b82f6; }
        50% { transform: scale(1.2); color: #2563eb; }
    }

    .stat-updated {
        animation: statUpdate 0.8s ease-in-out;
    }

    @keyframes statUpdate {
        0% { background-color: rgba(34, 197, 94, 0.1); }
        50% { background-color: rgba(34, 197, 94, 0.3); }
        100% { background-color: transparent; }
    }
`;
document.head.appendChild(style);
</script>
</body>
</html>
