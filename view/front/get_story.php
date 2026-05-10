<?php
session_start();

require_once __DIR__ . '/../../controller/StoryController.php';

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    exit();
}

$storyId = (int) ($_GET['id'] ?? 0);
if ($storyId <= 0) {
    http_response_code(400);
    exit();
}

$storyController = new StoryController();
$userId = (int) ($_SESSION['user']['id_u'] ?? $_SESSION['id_user'] ?? 0);
$story = $storyController->getStoryById($storyId);

if (!$story) {
    http_response_code(404);
    exit();
}

$userLikedStory = $storyController->isLikedByUser($storyId, $userId);
$comments = $storyController->getStoryComments($storyId)->fetchAll(PDO::FETCH_ASSOC);
$userLikedComments = $storyController->getUserLikedStoryCommentIds($userId);
?>

<div class="space-y-6">
    <div class="flex items-center gap-3 pb-4 border-b border-slate-200">
        <?php if (!empty($story['photo']) && file_exists(__DIR__ . '/../../' . trim($story['photo']))): ?>
            <img src="/swaply/<?= ltrim($story['photo'], '/') ?>" alt="Profil" class="h-12 w-12 rounded-full object-cover border border-teal-200" onerror="this.style.display='none'; this.nextElementSibling?.style.display='flex';">
            <div class="h-12 w-12 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-bold text-lg hidden">
                <?= htmlspecialchars(substr($story['nom'], 0, 1)) ?>
            </div>
        <?php else: ?>
            <div class="h-12 w-12 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-bold text-lg border border-emerald-200">
                <?= htmlspecialchars(substr($story['nom'], 0, 1)) ?>
            </div>
        <?php endif; ?>
        <div class="flex-1">
            <div class="text-base font-semibold"><?= htmlspecialchars($story['nom'] . ' ' . $story['prenom']) ?></div>
            <div class="text-sm text-slate-500">Publié le <?= htmlspecialchars($story['date_creation']) ?></div>
        </div>
        <div class="inline-flex items-center gap-3 text-sm text-slate-600">
            <span class="inline-flex items-center gap-2"><i class="fas fa-heart text-emerald-500"></i> <?= (int) $story['like_count'] ?></span>
            <span class="inline-flex items-center gap-2"><i class="fas fa-comment text-slate-500"></i> <?= (int) $story['comment_count'] ?></span>
        </div>
        <?php if ($story['id_client'] === $userId): ?>
            <form method="POST" action="listepublication.php" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette story ?');" class="inline-block">
                <input type="hidden" name="form_type" value="delete_story">
                <input type="hidden" name="id_story" value="<?= (int) $story['id_story'] ?>">
                <button type="submit" class="text-rose-600 hover:text-rose-700"><i class="fas fa-trash"></i></button>
            </form>
        <?php endif; ?>
    </div>

    <div class="space-y-4">
        <?php if (!empty($story['image'])): ?>
            <img src="/swaply/<?= ltrim($story['image'], '/') ?>" alt="Story" class="w-full rounded-3xl object-cover max-h-96">
        <?php endif; ?>

        <?php if (!empty($story['contenu'])): ?>
            <div class="rounded-3xl bg-slate-50 p-4 text-slate-700 whitespace-pre-line"><?= nl2br(htmlspecialchars($story['contenu'])) ?></div>
        <?php endif; ?>
    </div>

    <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-slate-200">
        <form method="POST" action="listepublication.php" class="inline-block">
            <input type="hidden" name="form_type" value="toggle_story_like">
            <input type="hidden" name="id_story" value="<?= (int) $story['id_story'] ?>">
            <button type="submit" class="rounded-full px-4 py-2 text-sm <?= $userLikedStory ? 'bg-teal-600 text-white' : 'bg-slate-200 text-slate-700' ?>">
                <i class="fas fa-heart"></i> <?= $userLikedStory ? 'Retirer le like' : 'J’aime' ?>
            </button>
        </form>
        <div class="text-sm text-slate-600">Cette story a <?= (int) $story['like_count'] ?> j'aime<?= $story['like_count'] > 1 ? 's' : '' ?> et <?= (int) $story['comment_count'] ?> commentaire<?= $story['comment_count'] > 1 ? 's' : '' ?>.</div>
    </div>

    <div class="border-t border-slate-200 pt-4">
        <div class="flex items-center justify-between mb-3">
            <div class="text-sm font-semibold">Commentaires</div>
            <div class="text-xs text-slate-500"><?= (int) $story['comment_count'] ?> commentaire<?= $story['comment_count'] > 1 ? 's' : '' ?></div>
        </div>
        <div class="space-y-3 max-h-72 overflow-y-auto mb-4">
            <?php if (count($comments) === 0): ?>
                <div class="rounded-3xl bg-slate-50 p-4 text-sm text-slate-500">Aucun commentaire pour le moment.</div>
            <?php endif; ?>

            <?php foreach ($comments as $comment): ?>
                <div class="rounded-3xl bg-slate-50 p-4 border border-slate-200">
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <div class="flex items-center gap-3">
                            <?php if (!empty($comment['photo']) && file_exists(__DIR__ . '/../../' . trim($comment['photo']))): ?>
                                <img src="/swaply/<?= ltrim($comment['photo'], '/') ?>" alt="Profil" class="h-8 w-8 rounded-full object-cover border border-emerald-100" onerror="this.style.display='none'; this.nextElementSibling?.style.display='flex';">
                                <div class="h-8 w-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-bold text-xs hidden"><?= htmlspecialchars(substr($comment['nom'], 0, 1)) ?></div>
                            <?php else: ?>
                                <div class="h-8 w-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-bold text-xs border border-emerald-100"><?= htmlspecialchars(substr($comment['nom'], 0, 1)) ?></div>
                            <?php endif; ?>
                            <div>
                                <div class="text-sm font-semibold"><?= htmlspecialchars($comment['nom'] . ' ' . $comment['prenom']) ?></div>
                                <div class="text-xs text-slate-500"><?= htmlspecialchars($comment['date_com']) ?></div>
                            </div>
                        </div>
                        <?php if ($comment['id_client'] === $userId): ?>
                            <form method="POST" action="listepublication.php" class="inline-block" onsubmit="return confirm('Supprimer ce commentaire ?');">
                                <input type="hidden" name="form_type" value="delete_story_comment">
                                <input type="hidden" name="id_com" value="<?= (int) $comment['id_com'] ?>">
                                <button type="submit" class="text-rose-600 hover:text-rose-700 text-sm"><i class="fas fa-trash"></i></button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <p class="text-sm text-slate-700 mb-3"><?= htmlspecialchars($comment['contenu']) ?></p>
                    <form method="POST" action="listepublication.php" class="inline-flex items-center gap-2">
                        <input type="hidden" name="form_type" value="toggle_story_comment_like">
                        <input type="hidden" name="id_com" value="<?= (int) $comment['id_com'] ?>">
                        <button type="submit" class="rounded-full px-3 py-1 text-xs <?= in_array($comment['id_com'], $userLikedComments, true) ? 'bg-teal-600 text-white' : 'bg-slate-200 text-slate-700' ?>">
                            <i class="fas fa-heart"></i> <?= (int) $comment['likes'] ?></button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <form method="POST" action="listepublication.php" class="space-y-3">
            <input type="hidden" name="form_type" value="story_comment">
            <input type="hidden" name="id_story" value="<?= (int) $story['id_story'] ?>">
            <textarea name="story_comment_text" rows="3" class="w-full rounded-3xl border border-slate-200 px-4 py-3 text-sm outline-none" placeholder="Écrire un commentaire..."></textarea>
            <button type="submit" class="rounded-3xl bg-teal-600 px-4 py-3 text-sm font-semibold text-white hover:bg-teal-700">Commenter</button>
        </form>
    </div>
</div>
