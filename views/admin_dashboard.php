<?php
// views/admin_dashboard.php
require_once '../controllers/AdminController.php';
$adminCtrl = new AdminController();

// Handle actions
if (isset($_POST['delete_pub'])) {
    $adminCtrl->deleteAction($_POST['delete_pub']);
    header("Location: admin_dashboard.php");
    exit();
}

if (isset($_POST['delete_comment'])) {
    $adminCtrl->deleteComment($_POST['delete_comment']);
    header("Location: admin_dashboard.php");
    exit();
}

if (isset($_POST['edit_comment'])) {
    $adminCtrl->updateComment($_POST['edit_comment'], $_POST['edit_contenu']);
    header("Location: admin_dashboard.php");
    exit();
}

if (isset($_POST['delete_selected_comments'])) {
    foreach ($_POST['selected_comments'] as $id) {
        $adminCtrl->deleteComment($id);
    }
    header("Location: admin_dashboard.php");
    exit();
}

$data = $adminCtrl->getDashboardData();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Swaply Admin - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-gray-800 min-h-screen flex">
    <!-- Sidebar -->
    <div class="w-64 bg-white shadow-lg min-h-screen">
        <div class="p-4">
            <h2 class="text-xl font-bold text-teal-600">Menu Admin</h2>
            <ul class="mt-4 space-y-2">
                <li class="bg-teal-100 text-teal-800 px-3 py-2 rounded font-bold">Dashboard</li>
                <li><a href="listepublication.php" class="block text-teal-600 hover:text-teal-800 px-3 py-2 rounded hover:bg-teal-50 transition-colors">Publications</a></li>
                <li><a href="index.php" class="block text-teal-600 hover:text-teal-800 px-3 py-2 rounded hover:bg-teal-50 transition-colors">Accueil</a></li>
            </ul>
        </div>
    </div>

    <!-- Main content -->
    <div class="flex-1 p-4 md:p-8">
        <div class="max-w-6xl mx-auto">
            <h1 class="text-3xl font-bold mb-8 text-teal-600">Tableau de Bord Admin</h1>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
                <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-sm">
                    <p class="text-gray-600 font-medium">Total Publications</p>
                    <h2 class="text-5xl font-bold text-gray-800"><?= $data['total_pubs'] ?></h2>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-sm">
                    <p class="text-gray-600 font-medium">Total Commentaires</p>
                    <h2 class="text-5xl font-bold text-gray-800"><?= $data['total_coms'] ?></h2>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-gray-200 overflow-hidden mb-10">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-xl font-bold">Gestion des Publications</h3>
                </div>
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-gray-600 text-sm">
                        <tr>
                            <th class="p-4">Auteur</th>
                            <th class="p-4">Titre</th>
                            <th class="p-4">Images</th>
                            <th class="p-4">Date</th>
                            <th class="p-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($data['all_publications'] as $pub): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-4 font-bold text-teal-600"><?= htmlspecialchars($pub['nom']) ?></td>
                            <td class="p-4"><?= htmlspecialchars($pub['titre']) ?></td>
                            <td class="p-4">
                                <?php if (!empty($pub['image'])): ?>
                                    <div class="flex gap-1">
                                        <?php foreach (explode(',', $pub['image']) as $img): ?>
                                            <img src="../<?= trim($img) ?>" class="w-12 h-12 object-cover rounded border">
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-400">Aucune</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-gray-600 text-sm"><?= $pub['date_pub'] ?></td>
                            <td class="p-4 text-right">
                                <form method="POST" class="inline" onsubmit="return confirm('Supprimer cette publication ?');">
                                    <input type="hidden" name="delete_pub" value="<?= $pub['id_pub'] ?>">
                                    <button type="submit" class="text-rose-500 hover:underline">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-3xl border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-xl font-bold">Gestion des Commentaires</h3>
                </div>
                <form method="POST">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-gray-600 text-sm">
                            <tr>
                                <th class="p-4"><input type="checkbox" id="selectAllComments" onchange="toggleAll(this)"></th>
                                <th class="p-4">Auteur</th>
                                <th class="p-4">Commentaire</th>
                                <th class="p-4">Publication</th>
                                <th class="p-4">Date</th>
                                <th class="p-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($data['all_commentaires'] as $com): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-4"><input type="checkbox" name="selected_comments[]" value="<?= $com['id_com'] ?>" class="comment-checkbox"></td>
                                <td class="p-4 font-bold text-teal-600"><?= htmlspecialchars($com['nom']) ?></td>
                                <td class="p-4"><?= htmlspecialchars($com['contenu']) ?></td>
                                <td class="p-4"><?= htmlspecialchars($com['titre']) ?></td>
                                <td class="p-4 text-gray-600 text-sm"><?= $com['date_com'] ?></td>
                                <td class="p-4 text-right">
                                    <button type="button" onclick="editComment(<?= $com['id_com'] ?>, '<?= addslashes($com['contenu']) ?>')" class="text-blue-500 hover:underline mr-2">Modifier</button>
                                    <form method="POST" class="inline" onsubmit="return confirm('Supprimer ce commentaire ?');">
                                        <input type="hidden" name="delete_comment" value="<?= $com['id_com'] ?>">
                                        <button type="submit" class="text-rose-500 hover:underline">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="p-4 border-t border-gray-200 flex gap-2">
                        <button type="submit" name="delete_selected_comments" class="bg-rose-600 text-white px-4 py-2 rounded hover:bg-rose-700" onclick="return confirm('Supprimer les commentaires sélectionnés ?')">Supprimer Sélectionnés</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
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
</script>
</html>