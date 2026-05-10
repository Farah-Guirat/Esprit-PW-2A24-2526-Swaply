<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../../controller/PublicationController.php';
require_once __DIR__ . '/../../controller/StoryController.php';
require_once __DIR__ . '/helpers.php';

$publicationController = new PublicationController();
$storyController = new StoryController();

$error = null;
$success = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = $publicationController->handleRequest();
    if ($error === null) {
        $error = $storyController->handleRequest();
    }
}

$search = trim($_GET['search'] ?? '');
$userId = (int) ($_SESSION['user']['id_u'] ?? $_SESSION['id_user'] ?? 0);
$publications = $publicationController->getPublications($search);
$userLikedPublications = $publicationController->getUserLikedPublicationIds($userId);
$userLikedComments = $publicationController->getUserLikedCommentIds($userId);
$activeStoriesStatement = $storyController->getActiveStories();
$activeStories = $activeStoriesStatement->fetchAll(PDO::FETCH_ASSOC);
$userLikedStories = $storyController->getUserLikedStoryIds($userId);
$userLikedStoryComments = $storyController->getUserLikedStoryCommentIds($userId);

$photo = $_SESSION['user']['photo'] ?? null;
$getSuccess = $_GET['success'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Swaply - Publications</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .file-input-custom {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }
        .file-input-custom input[type="file"] {
            position: absolute;
            left: -9999px;
        }
        .file-input-label {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background-color: #14b8a6;
            color: white;
            border-radius: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        .file-input-label:hover {
            background-color: #0d9488;
        }
        .file-name {
            font-size: 0.875rem;
            color: #64748b;
            margin-top: 0.5rem;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background-color: white;
            padding: 2rem;
            border-radius: 1.5rem;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }
        .story-avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            border-radius: 50%;
            border: 4px solid #16a34a;
            background: #22c55e;
            color: white;
            font-weight: bold;
            font-size: 22px;
            cursor: pointer;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.18);
        }
        .story-avatar:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 14px 28px rgba(34, 197, 94, 0.18);
        }
        .story-avatar.has-image {
            background-color: transparent;
            background-size: cover;
            background-position: center;
            color: transparent;
        }
        .story-avatar-label {
            max-width: 72px;
            text-align: center;
            font-size: 0.75rem;
            color: #334155;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .story-meta {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            color: #475569;
            font-size: 0.75rem;
        }
        .story-meta i {
            color: #16a34a;
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-900">
  <!-- Header -->
  <header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-8 py-5 flex items-center justify-between">
      <div class="flex items-center gap-3">
          <span class="text-gray-700 font-medium">
              <?= htmlspecialchars($_SESSION['user']['nom'] ?? '') ?>
              <?= htmlspecialchars($_SESSION['user']['prenom'] ?? '') ?>
          </span>
        <div class="w-9 h-9 bg-teal-500 rounded-2xl flex items-center justify-center text-white font-bold text-2xl">S</div>
        <h1 class="text-2xl font-bold text-gray-800">Swaply</h1>
      </div>

      <nav class="flex items-center gap-8 text-sm font-medium">
        <a href="swaplyf.php" class="nav-link">Accueil</a>
        <a href="Profil.php" class="nav-link">Profils</a>
        <a href="projets.php" class="nav-link">Projets</a>
        <a href="/swaply/public/index.php?action=choice" class="nav-link">Demandes</a>
        <a href="/swaply/public/index.php?action=choicee" class="nav-link">Offres</a>
        <a href="listepublication.php" class="nav-link">Publications</a>
        <a href="Messages.php" class="nav-link">Messages</a>
        <a href="reclamations.php" class="nav-link">Réclamations</a>
      </nav>

      <div onclick="window.location.href='/swaply/view/front/Profil.php'" class="w-10 h-10 bg-teal-100 rounded-2xl overflow-hidden border-2 border-white shadow cursor-pointer relative">
        <?php if ($photo): ?>
          <img src="/swaply/uploads/profiles/<?= htmlspecialchars($photo) ?>" alt="Profil" class="w-full h-full object-cover" style="width: 100%; height: 100%; object-fit: cover; display: block;">
        <?php else: ?>
          <div class="w-full h-full flex items-center justify-center text-teal-600 font-bold text-lg">
            <?= strtoupper(substr($_SESSION['user']['nom'] ?? '', 0, 1) . substr($_SESSION['user']['prenom'] ?? '', 0, 1)) ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </header>

<main class="max-w-7xl mx-auto px-6 py-8">
    <?php if ($getSuccess === 'publication_created'): ?>
        <div class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-800">Publication créée avec succès.</div>
    <?php elseif ($getSuccess === 'publication_updated'): ?>
        <div class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-800">Publication modifiée avec succès.</div>
    <?php elseif ($getSuccess === 'publication_deleted'): ?>
        <div class="mb-6 rounded-2xl bg-orange-50 border border-orange-200 p-4 text-orange-800">Publication supprimée.</div>
    <?php elseif ($getSuccess === 'comment_deleted'): ?>
        <div class="mb-6 rounded-2xl bg-orange-50 border border-orange-200 p-4 text-orange-800">Commentaire supprimé.</div>
    <?php elseif ($getSuccess === 'story_created'): ?>
        <div class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-800">Story publiée.</div>
    <?php elseif ($getSuccess === 'story_deleted'): ?>
        <div class="mb-6 rounded-2xl bg-orange-50 border border-orange-200 p-4 text-orange-800">Story supprimée.</div>
    <?php elseif ($getSuccess === 'story_comment_deleted'): ?>
        <div class="mb-6 rounded-2xl bg-orange-50 border border-orange-200 p-4 text-orange-800">Commentaire de story supprimé.</div>
    <?php elseif ($getSuccess === 'publication_reported'): ?>
        <div class="mb-6 rounded-2xl bg-blue-50 border border-blue-200 p-4 text-blue-800">Publication signalée. Le signalement a bien été enregistré.</div>
    <?php elseif ($getSuccess === 'publication_auto_deleted'): ?>
        <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 p-4 text-red-800">Publication supprimée automatiquement suite à plusieurs signalements.</div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="mb-6 rounded-2xl bg-rose-50 border border-rose-200 p-4 text-rose-800"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="grid gap-6 lg:grid-cols-[1.4fr_0.85fr]">
        <section class="space-y-6">
            <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-3xl font-bold">Fil d'actualité</h1>
                        <p class="mt-1 text-sm text-slate-500">Publiez, commentez et aimez les publications de la communauté.</p>
                    </div>
                    <form method="GET" action="listepublication.php" class="flex gap-2">
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Rechercher..." class="rounded-2xl border border-slate-200 px-4 py-2 outline-none focus:border-teal-500">
                        <button type="submit" class="rounded-2xl bg-teal-600 px-4 py-2 text-white">Rechercher</button>
                    </form>
                </div>
            </div>

            <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                <h2 class="text-xl font-semibold mb-4">Créer une publication</h2>
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="form_type" value="publication">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Titre</label>
                        <input type="text" name="titre" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-teal-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Contenu</label>
                        <textarea name="contenu" rows="4" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-teal-500" required></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Image (optionnel)</label>
                        <div class="file-input-custom mt-2">
                            <input type="file" id="pub_image_input" name="pub_image" accept="image/*" onchange="updateFileName(this, 'pub_image_name')">
                            <label for="pub_image_input" class="file-input-label">
                                <i class="fas fa-image"></i> Choisir une image
                            </label>
                            <div id="pub_image_name" class="file-name"></div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Musique (optionnel)</label>
                        <div class="file-input-custom mt-2">
                            <input type="file" id="pub_musique_input" name="pub_musique" accept="audio/*" onchange="updateFileName(this, 'pub_musique_name')">
                            <label for="pub_musique_input" class="file-input-label">
                                <i class="fas fa-music"></i> Choisir une musique
                            </label>
                            <div id="pub_musique_name" class="file-name"></div>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="rounded-2xl bg-teal-600 px-6 py-3 text-white font-semibold hover:bg-teal-700">Publier</button>
                        <button type="reset" class="rounded-2xl border border-slate-200 px-6 py-3 text-slate-700 hover:bg-slate-50">Réinitialiser</button>
                    </div>
                </form>
            </div>

            <?php while ($publication = $publications->fetch(PDO::FETCH_ASSOC)): ?>
                <article class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                    <div class="flex items-center justify-between gap-4 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center">
                                <?php 
                                $publicationUserName = htmlspecialchars($publication['prenom'] . ' ' . $publication['nom']);
                                echo renderProfileAvatar($publication['photo'], $publication['nom'], 'h-12 w-12');
                                ?>
                            </div>
                            <div>
                                <div class="text-base font-semibold"><?= htmlspecialchars($publication['nom'] . ' ' . $publication['prenom']) ?></div>
                                <div class="text-sm text-slate-500"><?= htmlspecialchars($publication['date_pub']) ?></div>
                            </div>
                        </div>
                        <?php if ($publication['id_client'] === $userId): ?>
                            <div class="flex items-center gap-2">
                                <button onclick="toggleEditForm(<?= (int) $publication['id_pub'] ?>)" class="rounded-2xl border border-blue-200 px-3 py-2 text-blue-700 hover:bg-blue-50"><i class="fas fa-edit"></i></button>
                                <form method="POST" class="inline-block" onsubmit="return confirm('Êtes-vous sûr?');">
                                    <input type="hidden" name="form_type" value="delete_publication">
                                    <input type="hidden" name="id_pub" value="<?= (int) $publication['id_pub'] ?>">
                                    <button type="submit" class="rounded-2xl border border-rose-200 px-3 py-2 text-rose-700 hover:bg-rose-50"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Edit Form (hidden by default) -->
                    <div id="edit-form-<?= (int) $publication['id_pub'] ?>" class="hidden mb-4 rounded-2xl border-2 border-blue-300 bg-blue-50 p-4">
                        <form method="POST" enctype="multipart/form-data" class="space-y-3">
                            <input type="hidden" name="form_type" value="update_publication">
                            <input type="hidden" name="id_pub" value="<?= (int) $publication['id_pub'] ?>">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Titre</label>
                                <input type="text" name="titre" value="<?= htmlspecialchars($publication['titre']) ?>" class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 outline-none focus:border-teal-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Contenu</label>
                                <textarea name="contenu" rows="3" class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 outline-none focus:border-teal-500" required><?= htmlspecialchars($publication['contenu']) ?></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Image</label>
                                <div class="file-input-custom mt-1">
                                    <input type="file" id="pub_image_edit_<?= (int) $publication['id_pub'] ?>" name="pub_image" accept="image/*" onchange="updateFileName(this, 'pub_image_edit_name_<?= (int) $publication['id_pub'] ?>')">
                                    <label for="pub_image_edit_<?= (int) $publication['id_pub'] ?>" class="file-input-label">
                                        <i class="fas fa-image"></i> Modifier l'image
                                    </label>
                                    <div id="pub_image_edit_name_<?= (int) $publication['id_pub'] ?>" class="file-name"></div>
                                </div>
                                <?php if (!empty($publication['image'])): ?>
                                    <p class="text-xs text-slate-500 mt-1">Image actuelle: <?= basename($publication['image']) ?></p>
                                <?php endif; ?>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Musique</label>
                                <div class="file-input-custom mt-1">
                                    <input type="file" id="pub_musique_edit_<?= (int) $publication['id_pub'] ?>" name="pub_musique" accept="audio/*" onchange="updateFileName(this, 'pub_musique_edit_name_<?= (int) $publication['id_pub'] ?>')">
                                    <label for="pub_musique_edit_<?= (int) $publication['id_pub'] ?>" class="file-input-label">
                                        <i class="fas fa-music"></i> Modifier la musique
                                    </label>
                                    <div id="pub_musique_edit_name_<?= (int) $publication['id_pub'] ?>" class="file-name"></div>
                                </div>
                                <?php if (!empty($publication['musique'])): ?>
                                    <p class="text-xs text-slate-500 mt-1">Musique actuelle: <?= basename($publication['musique']) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="rounded-2xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Enregistrer</button>
                                <button type="button" onclick="toggleEditForm(<?= (int) $publication['id_pub'] ?>)" class="rounded-2xl border border-slate-200 px-4 py-2 text-slate-700 hover:bg-slate-50">Annuler</button>
                            </div>
                        </form>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <h3 class="text-xl font-bold"><?= htmlspecialchars($publication['titre']) ?></h3>
                            <p class="mt-2 text-slate-700 whitespace-pre-line"><?= htmlspecialchars($publication['contenu']) ?></p>
                        </div>
                        <?php if (!empty($publication['image'])): ?>
                            <img src="/swaply/<?= ltrim($publication['image'], '/') ?>" alt="Publication" class="mt-4 w-full rounded-3xl object-contain max-h-[560px]">
                        <?php endif; ?>
                        <?php if (!empty($publication['musique'])): ?>
                            <div class="mt-4 rounded-3xl bg-slate-50 p-4 border border-slate-200">
                                <div class="music-player" data-src="/swaply/<?= ltrim($publication['musique'], '/') ?>">
                                    <div class="flex items-center gap-3 mb-3">
                                        <button class="play-btn w-12 h-12 rounded-full bg-teal-500 text-white flex items-center justify-center hover:bg-teal-600 transition-colors">
                                            <i class="fas fa-play text-lg"></i>
                                        </button>
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-slate-700">Musique</div>
                                            <div class="text-xs text-slate-500"><?= basename($publication['musique']) ?></div>
                                        </div>
                                        <div class="text-xs text-slate-500 current-time">0:00</div>
                                    </div>
                                    <div class="progress-bar bg-slate-200 rounded-full h-2 cursor-pointer relative">
                                        <div class="progress-fill bg-teal-500 h-full rounded-full transition-all duration-300" style="width: 0%"></div>
                                    </div>
                                    <audio preload="none">
                                        <source src="/swaply/<?= ltrim($publication['musique'], '/') ?>" type="audio/mpeg">
                                        <source src="/swaply/<?= ltrim($publication['musique'], '/') ?>" type="audio/ogg">
                                        <source src="/swaply/<?= ltrim($publication['musique'], '/') ?>" type="audio/wav">
                                        Votre navigateur ne supporte pas l'élément audio.
                                    </audio>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="flex flex-wrap items-center gap-3 text-sm text-slate-600">
                            <span><?= (int) $publication['likes'] ?> j'aime</span>
                        </div>
                        <div class="flex flex-wrap gap-3 pt-2">
                            <form method="POST" class="inline-block">
                                <input type="hidden" name="form_type" value="toggle_pub_like">
                                <input type="hidden" name="id_pub" value="<?= (int) $publication['id_pub'] ?>">
                                <button type="submit" class="rounded-2xl border px-4 py-2 <?= in_array($publication['id_pub'], $userLikedPublications, true) ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-700' ?>">
                                    <?= in_array($publication['id_pub'], $userLikedPublications, true) ? 'Retirer le like' : 'J’aime' ?>
                                </button>
                            </form>
                            <button class="rounded-2xl border border-slate-200 px-4 py-2 bg-slate-50 text-slate-700" onclick="document.getElementById('report-<?= $publication['id_pub'] ?>').classList.toggle('hidden');">Signaler</button>
                        </div>
                        <div id="report-<?= $publication['id_pub'] ?>" class="hidden mt-4 rounded-3xl border border-orange-200 bg-orange-50 p-4">
                            <form method="POST" class="space-y-3">
                                <input type="hidden" name="form_type" value="report_publication">
                                <input type="hidden" name="id_pub" value="<?= (int) $publication['id_pub'] ?>">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Raison</label>
                                    <select name="reason" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none">
                                        <option value="Contenu inapproprié">Contenu inapproprié</option>
                                        <option value="Spam">Spam</option>
                                        <option value="Autre">Autre</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Description (optionnel)</label>
                                    <textarea name="description" rows="2" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none"></textarea>
                                </div>
                                <button type="submit" class="rounded-2xl bg-orange-500 px-4 py-2 text-white hover:bg-orange-600">Envoyer le signalement</button>
                            </form>
                        </div>
                    </div>
                    <div class="mt-6 border-t border-slate-200 pt-6 space-y-4">
                        <div class="text-sm font-semibold text-slate-700">Commentaires</div>
                        <?php $comments = $publicationController->getCommentsByPublication($publication['id_pub']); ?>
                        <?php while ($comment = $comments->fetch(PDO::FETCH_ASSOC)): ?>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 comment-item" id="comment-<?= (int) $comment['id_com'] ?>">
                                <div class="flex items-start justify-between gap-3 mb-2">
                                    <div class="flex items-center gap-3">
                                        <?php
                                        $commentPhotoPath = '../../uploads/profiles/' . ($comment['photo'] ?? 'default.png');
                                        $commentPhotoExists = file_exists(__DIR__ . '/../../uploads/profiles/' . ($comment['photo'] ?? ''));
                                        if (!$commentPhotoExists || empty($comment['photo'])) {
                                            $commentPhotoPath = 'https://i.pravatar.cc/40?u=' . urlencode($comment['nom'] . $comment['prenom']);
                                        }
                                        ?>
                                        <img src="<?= $commentPhotoPath ?>"
                                             alt="<?= htmlspecialchars($comment['nom']) ?>"
                                             class="w-10 h-10 rounded-full object-cover border-2 border-blue-200"
                                             onerror="this.src='https://i.pravatar.cc/40?u=<?= urlencode($comment['nom'] . $comment['prenom']) ?>'">
                                        <div>
                                            <div class="text-sm font-semibold text-slate-800">
                                                <?= htmlspecialchars($comment['nom'] . ' ' . $comment['prenom']) ?>
                                                <?php if ($comment['id_client'] === $userId): ?>
                                                    <span class="text-xs text-blue-600 font-normal">(Vous)</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                <?= date('d/m/Y à H:i', strtotime($comment['date_com'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <?php if ($comment['id_client'] === $userId): ?>
                                            <button onclick="toggleCommentEdit(<?= (int) $comment['id_com'] ?>)" class="text-blue-600 hover:text-blue-700 text-xs transition-colors">
                                                <i class="fas fa-edit"></i> Modifier
                                            </button>
                                            <form method="POST" class="inline-block" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?');">
                                                <input type="hidden" name="form_type" value="delete_comment">
                                                <input type="hidden" name="id_com" value="<?= (int) $comment['id_com'] ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-700 text-xs transition-colors">
                                                    <i class="fas fa-trash"></i> Supprimer
                                                </button>
                                            </form>
                                        <?php elseif ($publication['id_client'] === $userId): ?>
                                            <form method="POST" class="inline-block" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?');">
                                                <input type="hidden" name="form_type" value="delete_comment">
                                                <input type="hidden" name="id_com" value="<?= (int) $comment['id_com'] ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-700 text-xs transition-colors">
                                                    <i class="fas fa-trash"></i> Supprimer
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div id="comment-content-<?= (int) $comment['id_com'] ?>" class="comment-content">
                                    <p class="text-slate-700 leading-relaxed"><?= htmlspecialchars($comment['contenu']) ?></p>
                                    <div class="flex items-center gap-4 mt-2 text-xs text-slate-500">
                                        <span class="flex items-center gap-1">
                                            <i class="fas fa-heart text-red-400"></i>
                                            <?= (int) $comment['likes'] ?> j'aime
                                        </span>
                                    </div>
                                </div>

                                <div id="comment-edit-<?= (int) $comment['id_com'] ?>" class="comment-edit hidden mt-3">
                                    <form method="POST" class="space-y-2">
                                        <input type="hidden" name="form_type" value="update_comment">
                                        <input type="hidden" name="id_com" value="<?= (int) $comment['id_com'] ?>">
                                        <textarea name="comment_text" rows="3" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500 resize-none" required><?= htmlspecialchars($comment['contenu']) ?></textarea>
                                        <div class="flex gap-2">
                                            <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-white text-sm hover:bg-blue-700 transition-colors">
                                                <i class="fas fa-save"></i> Enregistrer
                                            </button>
                                            <button type="button" onclick="toggleCommentEdit(<?= (int) $comment['id_com'] ?>)" class="rounded-xl border border-slate-200 px-4 py-2 text-slate-700 text-sm hover:bg-slate-50 transition-colors">
                                                <i class="fas fa-times"></i> Annuler
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        <form method="POST" class="space-y-3">
                            <input type="hidden" name="form_type" value="comment">
                            <input type="hidden" name="id_pub" value="<?= (int) $publication['id_pub'] ?>">
                            <textarea name="comment_text" rows="2" class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none" placeholder="Écrire un commentaire..."></textarea>
                            <button type="submit" class="rounded-2xl bg-slate-900 px-4 py-2 text-white hover:bg-slate-700">Commenter</button>
                        </form>
                    </div>
                </article>
            <?php endwhile; ?>
        </section>

        <aside class="space-y-6 lg:sticky lg:top-24">
            <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-semibold">Stories</h2>
                        <p class="text-sm text-slate-500">Les stories disparaissent au bout de 24 heures.</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-4">
                    <?php if (count($activeStories) === 0): ?>
                        <div class="w-full rounded-3xl bg-slate-50 p-4 text-center text-slate-600">Aucune story active pour le moment.</div>
                    <?php else: ?>
                        <?php foreach ($activeStories as $story): ?>
                        <div class="flex flex-col items-center gap-2">
                            <button onclick="openStoryModal(<?= (int) $story['id_story'] ?>)" class="story-avatar <?= !empty($story['image']) ? 'has-image' : '' ?>" style="<?= !empty($story['image']) ? "background-image: url('/swaply/" . ltrim($story['image'], '/') . "');" : '' ?>">
                                <?php if (empty($story['image'])): ?>
                                    <?= htmlspecialchars(substr($story['nom'], 0, 1)) ?>
                                <?php endif; ?>
                            </button>
                            <div class="story-avatar-label"><?= htmlspecialchars($story['prenom']) ?></div>
                            <div class="flex flex-wrap justify-center gap-2">
                                <span class="story-meta"><i class="fas fa-heart"></i> <?= (int) $story['like_count'] ?></span>
                                <span class="story-meta"><i class="fas fa-comment"></i> <?= (int) $story['comment_count'] ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                <h2 class="text-xl font-semibold mb-4">Partager une story</h2>
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="form_type" value="story">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Texte</label>
                        <textarea name="story_contenu" rows="3" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Image (optionnel)</label>
                        <div class="file-input-custom mt-2">
                            <input type="file" id="story_image_input" name="story_image" accept="image/*" onchange="updateFileName(this, 'story_image_name')">
                            <label for="story_image_input" class="file-input-label">
                                <i class="fas fa-image"></i> Choisir une image
                            </label>
                            <div id="story_image_name" class="file-name"></div>
                        </div>
                    </div>
                    <button type="submit" class="w-full rounded-2xl bg-teal-600 px-4 py-3 text-white font-semibold hover:bg-teal-700">Publier ma story</button>
                </form>
            </div>
        </aside>
    </div>
</main>

<!-- Story Modal -->
<div id="storyModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <button onclick="closeStoryModal()" class="absolute right-4 top-4 text-2xl text-slate-400 hover:text-slate-600">×</button>
        
        <div id="storyModalContent">
            <!-- Content will be loaded via AJAX -->
        </div>
    </div>
</div>

<script>
function updateFileName(input, nameElementId) {
    const nameElement = document.getElementById(nameElementId);
    if (input.files && input.files[0]) {
        nameElement.textContent = '✓ ' + input.files[0].name;
        nameElement.style.color = '#059669';
    } else {
        nameElement.textContent = '';
    }
}

function toggleEditForm(publicationId) {
    const form = document.getElementById('edit-form-' + publicationId);
    form.classList.toggle('hidden');
}

async function openStoryModal(storyId) {
    const modal = document.getElementById('storyModal');
    const content = document.getElementById('storyModalContent');
    
    try {
        const response = await fetch('get_story.php?id=' + storyId);
        const html = await response.text();
        content.innerHTML = html;
        modal.classList.add('active');
    } catch (error) {
        console.error('Error loading story:', error);
    }
}

function closeStoryModal() {
    document.getElementById('storyModal').classList.remove('active');
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('storyModal');
    modal.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeStoryModal();
        }
    });

    // Initialize music players
    initializeMusicPlayers();
});

function initializeMusicPlayers() {
    document.querySelectorAll('.music-player').forEach(player => {
        const audio = player.querySelector('audio');
        const playBtn = player.querySelector('.play-btn');
        const playIcon = playBtn.querySelector('i');
        const progressBar = player.querySelector('.progress-bar');
        const progressFill = player.querySelector('.progress-fill');
        const currentTimeDisplay = player.querySelector('.current-time');

        playBtn.addEventListener('click', () => {
            if (audio.paused) {
                // Pause all other players
                document.querySelectorAll('.music-player audio').forEach(otherAudio => {
                    if (otherAudio !== audio) {
                        otherAudio.pause();
                        const otherPlayIcon = otherAudio.closest('.music-player').querySelector('.play-btn i');
                        otherPlayIcon.className = 'fas fa-play text-lg';
                    }
                });
                audio.play();
                playIcon.className = 'fas fa-pause text-lg';
            } else {
                audio.pause();
                playIcon.className = 'fas fa-play text-lg';
            }
        });

        audio.addEventListener('timeupdate', () => {
            const progress = (audio.currentTime / audio.duration) * 100;
            progressFill.style.width = progress + '%';
            currentTimeDisplay.textContent = formatTime(audio.currentTime);
        });

        audio.addEventListener('ended', () => {
            playIcon.className = 'fas fa-play text-lg';
            progressFill.style.width = '0%';
            currentTimeDisplay.textContent = '0:00';
        });

        progressBar.addEventListener('click', (e) => {
            const rect = progressBar.getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            const percentage = clickX / rect.width;
            audio.currentTime = percentage * audio.duration;
        });
    });
}

function formatTime(seconds) {
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return mins + ':' + (secs < 10 ? '0' : '') + secs;
}

function toggleCommentEdit(commentId) {
    const contentDiv = document.getElementById('comment-content-' + commentId);
    const editDiv = document.getElementById('comment-edit-' + commentId);
    
    contentDiv.classList.toggle('hidden');
    editDiv.classList.toggle('hidden');
}
</script>
</body>
</html>
