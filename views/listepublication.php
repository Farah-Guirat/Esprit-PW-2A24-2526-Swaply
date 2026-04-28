<?php
require_once '../config/Database.php';
require_once '../model/Publication.php';
require_once '../model/Commentaire.php';
require_once '../controllers/PublicationController.php';

$database = new Database();
$db = $database->getConnection();
$pubModel = new Publication($db);
$comModel = new Commentaire($db);

$controller = new PublicationController();
$controller->handleRequest();

if (isset($_POST['add_comment'])) {
    $com_nom = trim($_POST['com_nom'] ?? '');
    $com_text = trim($_POST['com_text'] ?? '');
    if (empty($com_nom) || empty($com_text)) {
        header("Location: listepublication.php?error=comment");
        exit();
    }
    $clientId = $pubModel->getOrCreateClient($com_nom);
    $comModel->id_pub = $_POST['id_pub'];
    $comModel->id_client = $clientId;
    $comModel->contenu = $com_text;
    $comModel->create();
    header("Location: listepublication.php");
    exit();
}

if (isset($_POST['del_comment'])) {
    $comModel->delete($_POST['id_com']);
    header("Location: listepublication.php");
    exit();
}

if (isset($_POST['like'])) {
    $controller->handleLike($_POST['id_pub']);
    exit();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$stmt = $pubModel->readAll($search);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Swaply - Communauté</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="../Assets/style.css">
</head>
<body class="bg-slate-50 min-h-screen">

  <div class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-8 py-5 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 bg-teal-500 rounded-2xl flex items-center justify-center text-white font-bold text-2xl">S</div>
        <h1 class="text-2xl font-bold text-gray-800">Swaply</h1>
      </div>

      <div class="flex items-center gap-8 text-sm font-medium">
        <a href="index.html" class="nav-link active">Accueil</a>
        <a href="profils.html" class="nav-link">Profils</a>
        <a href="projets.html" class="nav-link">Projets</a>
        <a href="offres.html" class="nav-link">Offres</a>
        <a href="demandes.html" class="nav-link">Demandes</a>
        <a href="listepublication.php" class="nav-link">Publications</a>
        <a href="messages.html" class="nav-link">Messages</a>
        <a href="reclamations.html" class="nav-link">Réclamations</a>
      </div>

      <div class="flex items-center gap-4">
        <form method="GET" action="listepublication.php" class="flex items-center gap-2">
          <input type="text" name="search" placeholder="Rechercher par titre..." value="<?= htmlspecialchars($search) ?>" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
          <button type="submit" class="bg-teal-500 text-white px-4 py-2 rounded-lg hover:bg-teal-600">Rechercher</button>
        </form>
        <div class="w-10 h-10 bg-teal-100 rounded-2xl overflow-hidden border-2 border-white shadow">
          <img src="https://i.pravatar.cc/150?img=68" alt="Profil" class="w-full h-full object-cover">
        </div>
      </div>
    </div>
  </div>

  <div class="flex min-h-screen">
    <div class="w-64 bg-white shadow-lg min-h-screen">
        <div class="p-4">
            <h2 class="text-xl font-bold text-teal-600">Menu</h2>
            <ul class="mt-4 space-y-2">
                <li class="bg-teal-100 text-teal-800 px-3 py-2 rounded font-bold">Publications</li>
                <li><a href="index.php" class="block text-teal-600 hover:text-teal-800 px-3 py-2 rounded hover:bg-teal-50 transition-colors">Accueil</a></li>
            </ul>
        </div>
    </div>

    <div class="flex-1 p-4 md:p-8 bg-slate-50">
            <div class="max-w-7xl mx-auto">
                <?php if (isset($_GET['error']) && $_GET['error'] == 'comment'): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        Le nom et le commentaire sont requis.
                    </div>
                <?php elseif (isset($_GET['error']) && $_GET['error'] == 'edit_comment'): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        Le contenu du commentaire ne peut pas être vide.
                    </div>
                <?php endif; ?>
                <div class="grid gap-8 xl:grid-cols-[1.7fr_0.95fr]">
                    <div class="space-y-8">
                        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between mb-10">
                            <h1 class="text-3xl font-bold text-gray-800">Fil d'actualité</h1>
                            <a href="ajouterpublication.php" class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-2.5 rounded-2xl font-bold transition shadow-lg">+ Publier</a>
                        </div>

                        <div class="space-y-8">
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="bg-white rounded-[2rem] shadow-lg hover:shadow-xl transition-transform duration-200 border border-gray-100 overflow-hidden">
                    
                    <div class="p-6 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center font-bold">
                                <?= strtoupper(substr($row['nom'], 0, 1)) ?>
                            </div>
                            <span class="font-bold text-gray-900"><?= htmlspecialchars($row['nom']) ?></span>
                        </div>
                        <div class="flex gap-2">
                            <a href="ajouterpublication.php?id=<?= $row['id_pub'] ?>" class="p-2 text-amber-500 hover:bg-amber-50 rounded-lg transition-colors" title="Modifier">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                            </a>
                            <form method="POST" action="" onsubmit="return confirm('Supprimer ce poste ?');">
                                <input type="hidden" name="delete_id" value="<?= $row['id_pub'] ?>">
                                <button type="submit" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="px-6 pb-4">
                        <h2 class="text-xl font-bold mb-2"><?= htmlspecialchars($row['titre']) ?></h2>
                        <p class="text-gray-600"><?= nl2br(htmlspecialchars($row['contenu'])) ?></p>
                        <div class="mt-4 flex items-center gap-4">
                            <form method="POST" class="inline">
                                <input type="hidden" name="id_pub" value="<?= $row['id_pub'] ?>">
                                <button name="like" class="flex items-center gap-2 bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">
                                    <i class="fas fa-heart"></i> J'aime (<?= $row['likes'] ?? 0 ?>)
                                </button>
                            </form>
                        </div>
                    </div>

                    <?php if (!empty($row['image'])): ?>
                        <div class="px-4 pb-4">
                            <div class="grid grid-cols-2 gap-2 rounded-2xl overflow-hidden shadow-inner">
                                <?php foreach (explode(',', $row['image']) as $img): ?>
                                    <img src="../<?= trim($img) ?>" class="w-full h-auto object-contain">
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="bg-gray-50 p-6 border-t border-gray-100">
                        <div class="space-y-3 mb-6">
                            <?php 
                            $coms = $comModel->readByPub($row['id_pub']);
                            while ($c = $coms->fetch(PDO::FETCH_ASSOC)): 
                            ?>
                                <div class="flex justify-between items-start bg-white p-3 rounded-2xl shadow-sm border border-gray-100" id="comment-<?= $c['id_com'] ?>">
                                    <div class="text-sm flex-1">
                                        <span class="font-bold text-teal-700"><?= htmlspecialchars($c['nom']) ?></span>
                                        <p class="text-gray-600 mt-1 comment-text" id="text-<?= $c['id_com'] ?>"><?= htmlspecialchars($c['contenu']) ?></p>
                                        <form method="POST" class="edit-form hidden mt-2" id="form-<?= $c['id_com'] ?>">
                                            <input type="hidden" name="id_com" value="<?= $c['id_com'] ?>">
                                            <input type="text" name="edit_contenu" value="<?= htmlspecialchars($c['contenu']) ?>" class="w-full p-1 text-sm border rounded">
                                            <div class="mt-1 flex gap-1">
                                                <button type="submit" name="edit_comment" class="bg-teal-500 text-white px-2 py-1 rounded text-xs">Sauver</button>
                                                <button type="button" onclick="cancelEdit(<?= $c['id_com'] ?>)" class="bg-gray-500 text-white px-2 py-1 rounded text-xs">Annuler</button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="flex gap-1">
                                        <button onclick="editComment(<?= $c['id_com'] ?>)" class="text-blue-500 hover:text-blue-700 text-xs">Modifier</button>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="id_com" value="<?= $c['id_com'] ?>">
                                            <button name="del_comment" class="text-rose-300 hover:text-rose-500 text-xs" onclick="return confirm('Supprimer ce commentaire ?')">Supprimer</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>

                        <form method="POST" class="flex flex-col gap-2" onsubmit="return validateComment(this)">
                            <input type="hidden" name="id_pub" value="<?= $row['id_pub'] ?>">
                            <div class="flex gap-2">
                                <input type="text" name="com_nom" placeholder="Nom" class="w-1/4 p-2 text-sm bg-white border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-teal-500">
                                <input type="text" name="com_text" placeholder="Ajouter un commentaire..." class="w-full p-2 text-sm bg-white border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-teal-500">
                                <button name="add_comment" class="bg-teal-500 text-white px-4 rounded-xl text-sm font-bold hover:bg-teal-600 transition-colors">Envoyer</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6">
                            <h2 class="text-xl font-semibold text-gray-800 mb-3">À propos du fil</h2>
                            <p class="text-gray-600 leading-relaxed">Ici vous pouvez consulter les dernières publications, ajouter un nouveau post et voir les commentaires de la communauté.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<script>
function editComment(id) {
    document.getElementById('text-' + id).classList.add('hidden');
    document.getElementById('form-' + id).classList.remove('hidden');
}

function cancelEdit(id) {
    document.getElementById('text-' + id).classList.remove('hidden');
    document.getElementById('form-' + id).classList.add('hidden');
}

function validateComment(form) {
    const nomInput = form.querySelector('input[name="com_nom"]');
    const textInput = form.querySelector('input[name="com_text"]');
    const nom = nomInput.value.trim();
    const text = textInput.value.trim();
    
    if (!nom) {
        alert('Le nom est requis.');
        nomInput.focus();
        return false;
    }
    
    if (!text) {
        alert('Le commentaire ne peut pas être vide.');
        textInput.focus();
        return false;
    }
    
    return true;
}
</script>
</html>