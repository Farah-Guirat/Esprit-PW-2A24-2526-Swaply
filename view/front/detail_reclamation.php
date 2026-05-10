<?php
require_once '../../controller/ReclamationController.php';
require_once '../../controller/ReponseController.php';

session_start();
$photo = $_SESSION['user']['photo'] ?? null;
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: reclamations.php");
    exit;
}

$id = (int)$_GET['id'];

$recController = new ReclamationController();
$repController = new ReponseController();

$reclamation = $recController->getById($id);
if (!$reclamation) {
    header("Location: reclamations.php");
    exit;
}

$reponsesStmt = $repController->afficher($id);
$reponsesAll  = $reponsesStmt->fetchAll(PDO::FETCH_ASSOC);

$reponses = array_filter($reponsesAll, fn($r) => trim($r['contenu'] ?? '') !== '');
$reponses = array_values($reponses);

// ── Métier 6 : marquer cette réclamation comme vue ──
if (!isset($_SESSION['seen_reponses'])) {
    $_SESSION['seen_reponses'] = [];
}
if (!empty($reponses)) {
    $_SESSION['seen_reponses'][] = $id;
    $_SESSION['seen_reponses'] = array_unique($_SESSION['seen_reponses']);
}

function stars(int $n): string {
    return str_repeat('⭐', max(0, min(5, $n)));
}

function normalizeStatut(string $s): string {
    $s = strtolower(trim($s));
    return str_replace(
        ['é','è','ê','ë','à','â','î','ï','ô','û','ù','ç'],
        ['e','e','e','e','a','a','i','i','o','u','u','c'],
        $s
    );
}

$statutOptions = ['en attente', 'en cours', 'traité'];
$statut        = normalizeStatut($reclamation['statut'] ?? 'en attente');

$stepIndex = match(true) {
    str_contains($statut, 'traite') => 2,
    str_contains($statut, 'cours')  => 1,
    default                         => 0,
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Swaply – Détail Réclamation #<?= $id ?></title>

<script src="https://cdn.tailwindcss.com"></script>
<!-- Métier 8 : jsPDF pour export PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<link rel="stylesheet" href="../../assets/css/style.css">

<style>
.step-done   { background:#14b8a6; color:#fff; border-color:#14b8a6; }
.step-active { background:#fff; color:#14b8a6; border-color:#14b8a6; box-shadow:0 0 0 3px #99f6e4; }
.step-idle   { background:#fff; color:#9ca3af; border-color:#d1d5db; }
.line-done   { background:#14b8a6; }
.line-idle   { background:#d1d5db; }
.rep-card    { border-left: 4px solid #14b8a6; }
.s-attente   { background:#fef2f2; color:#ef4444; }
.s-encours   { background:#fff7ed; color:#f97316; }
.s-traite    { background:#f0fdf4; color:#16a34a; }

.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.4);
    backdrop-filter: blur(4px);
    z-index: 100;
    display: flex; align-items: center; justify-content: center;
    opacity: 0; pointer-events: none;
    transition: opacity 0.25s ease;
}
.modal-overlay.open { opacity: 1; pointer-events: all; }
.modal-box {
    background: white;
    border-radius: 24px;
    padding: 36px;
    width: 100%; max-width: 520px;
    transform: translateY(20px);
    transition: transform 0.25s ease;
    box-shadow: 0 24px 48px rgba(0,0,0,0.15);
}
.modal-overlay.open .modal-box { transform: translateY(0); }

@keyframes fadeUp {
    from { opacity:0; transform:translateY(14px); }
    to   { opacity:1; transform:translateY(0); }
}
.fade-up { animation: fadeUp .35s ease both; }

/* Toast PDF */
#pdfToast {
    position: fixed; bottom: 28px; right: 28px; z-index: 999;
    background: #14b8a6; color: white;
    padding: 14px 22px; border-radius: 16px;
    font-size: 14px; font-weight: 600;
    box-shadow: 0 8px 24px rgba(20,184,166,.4);
    display: flex; align-items: center; gap-10px;
    opacity: 0; transform: translateY(16px);
    transition: all .3s ease;
    pointer-events: none;
    gap: 10px;
}
#pdfToast.show { opacity: 1; transform: translateY(0); }
</style>
</head>

<body class="bg-gray-50">

<!-- ── NAVBAR ── -->
 <header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-8 py-5 flex items-center justify-between">
      <div class="flex items-center gap-3">
          <span class="text-gray-700 font-medium">
              <?php echo $_SESSION['user']['nom']; ?>
              <?php echo $_SESSION['user']['prenom']; ?>
          </span>
        <div class="w-9 h-9 bg-teal-500 rounded-2xl flex items-center justify-center text-white font-bold text-2xl">S</div>
        <h1 class="text-2xl font-bold text-gray-800">Swaply</h1>
      </div>

     
      <nav class="flex items-center gap-8 text-sm font-medium">
        <a href="/swaply/view/front/swaplyf.php" class="nav-link ">Accueil</a>
        <a href="/swaply/view/front/Profil.php" class="nav-link">Profils</a>
        <a href="swaply/view/front/projets.php" class="nav-link">Projets</a>
       <a href="/swaply/public/index.php?action=choice"  >Demandes</a>
        <a href="/swaply/public/index.php?action=choicee">Offres</a>
        <a href="publications.html" class="nav-link">Publications</a>
        <a href="messages.html" class="nav-link">Messages</a>
        <a href="/swaply/view/front/reclamations.php" class="nav-link active">Réclamations</a>
      </nav>

      <div onclick="window.location.href='/swaply/view/front/Profil.php'" class="w-10 h-10 bg-teal-100 rounded-2xl overflow-hidden border-2 border-white shadow cursor-pointer relative" onclick="togglePhotoMenu()">
        <?php if ($photo): ?>
         <img src="/swaply/uploads/profiles/<?= htmlspecialchars($photo) ?>" alt="Profil" class="w-full h-full object-cover" style="width: 100%; height: 100%; object-fit: cover; display: block;">
        <?php else: ?>
          <div class="w-full h-full flex items-center justify-center text-teal-600 font-bold text-lg">
            <?= strtoupper(substr($_SESSION['user']['nom'], 0, 1) . substr($_SESSION['user']['prenom'], 0, 1)) ?>
          </div>
        <?php endif; ?>
        <div id="photo-menu" class="absolute top-full right-0 bg-white border border-gray-300 rounded-lg shadow-lg p-2 hidden z-50">
          <button onclick="uploadFile(event)" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Télécharger un fichier</button>
          <button onclick="takePhoto(event)" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Prendre une photo</button>
          <?php if ($photo): ?>
            <button onclick="deletePhoto(event)" class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-100">Supprimer la photo</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </header>

<!-- ── MAIN ── -->
<main class="max-w-3xl mx-auto px-6 py-12 space-y-8">

  <!-- Carte réclamation -->
  <div class="fade-up bg-white rounded-3xl shadow p-8 space-y-5" id="reclamation-card">

    <div class="flex items-start justify-between gap-4">
      <div>
        <p class="text-xs text-gray-400 mb-1">Réclamation #<?= $id ?></p>
        <h2 class="text-xl font-bold text-gray-800 leading-snug" id="desc-display">
          <?= htmlspecialchars($reclamation['description']) ?>
        </h2>
      </div>

      <?php
        $badgeClass = match($stepIndex) {
            2       => 's-traite',
            1       => 's-encours',
            default => 's-attente',
        };
      ?>
      <span class="<?= $badgeClass ?> text-xs font-semibold px-3 py-1 rounded-full whitespace-nowrap self-start">
        <?= htmlspecialchars($reclamation['statut'] ?? 'en attente') ?>
      </span>
    </div>

    <div class="flex flex-wrap gap-4 text-sm text-gray-500">
      <span>🏷️ Type : <strong class="text-gray-700" id="type-display"><?= htmlspecialchars($reclamation['type'] ?? '—') ?></strong></span>
      <span>📅 <strong class="text-gray-700"><?= htmlspecialchars($reclamation['date_creation'] ?? '') ?></strong></span>
    </div>

    <?php if (!empty($reclamation['username_cible'])): ?>
    <div class="flex items-center gap-2 text-sm">
      <span class="text-gray-500">👤 Utilisateur concerné :</span>
      <span class="inline-flex items-center gap-1 bg-teal-50 text-teal-700 font-semibold px-3 py-1 rounded-full border border-teal-200">
        @<?= htmlspecialchars($reclamation['username_cible']) ?>
      </span>
    </div>
    <?php endif; ?>

    <p class="text-lg" id="rating-display"><?= stars((int)($reclamation['rating'] ?? 0)) ?></p>

    <!-- Actions -->
    <div class="flex justify-between items-center pt-4 border-t border-gray-100">
      <a href="reclamations.php" class="text-teal-500 hover:underline text-sm">← Retour à la liste</a>

      <div class="flex gap-2 flex-wrap justify-end">
        <!-- ── Métier 8 : Bouton Export PDF ── -->
        <button onclick="exportPDF()"
                class="bg-gray-700 text-white text-sm px-4 py-2 rounded-xl hover:bg-gray-800 transition flex items-center gap-1.5">
          📄 Export PDF
        </button>

        <!-- Modifier -->
        <button onclick="openModal()"
                class="bg-teal-500 text-white text-sm px-4 py-2 rounded-xl hover:bg-teal-600 transition">
          ✏️ Modifier
        </button>

        <!-- Supprimer -->
        <a href="delete_reclamation.php?id=<?= $reclamation['id_reclamation'] ?>"
           onclick="return confirm('Supprimer cette réclamation ?');"
           class="bg-red-500 text-white text-sm px-4 py-2 rounded-xl hover:bg-red-600 transition">
          🗑 Supprimer
        </a>
      </div>
    </div>

  </div>

  <!-- Stepper -->
  <div class="fade-up bg-white rounded-3xl shadow p-8" style="animation-delay:.07s">
    <h3 class="font-semibold text-gray-700 mb-6">📊 Progression du traitement</h3>
    <div class="flex items-center">
      <?php foreach ($statutOptions as $i => $s): ?>
        <div class="flex flex-col items-center">
          <div class="w-10 h-10 rounded-full border-2 flex items-center justify-center font-bold text-sm
            <?= $i < $stepIndex ? 'step-done' : ($i === $stepIndex ? 'step-active' : 'step-idle') ?>">
            <?= $i < $stepIndex ? '✓' : ($i + 1) ?>
          </div>
          <span class="text-xs mt-2 font-medium <?= $i === $stepIndex ? 'text-teal-600' : 'text-gray-400' ?>">
            <?= ucfirst($s) ?>
          </span>
        </div>
        <?php if ($i < count($statutOptions) - 1): ?>
          <div class="flex-1 h-1 mx-2 rounded <?= $i < $stepIndex ? 'line-done' : 'line-idle' ?>"></div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Réponses -->
  <div class="fade-up" style="animation-delay:.12s">
    <h3 class="font-semibold text-gray-700 mb-4">💬 Réponses de l'administration</h3>

    <?php if (empty($reponses)): ?>
      <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 flex items-center gap-4">
        <span class="text-3xl">⏳</span>
        <div>
          <p class="font-semibold text-amber-700">En cours de traitement</p>
          <p class="text-sm text-amber-600 mt-1">Votre réclamation a bien été reçue. Notre équipe vous répondra dans les plus brefs délais.</p>
        </div>
      </div>
    <?php else: ?>
      <div class="space-y-4">
        <?php foreach ($reponses as $i => $rep): ?>
          <div class="rep-card bg-white rounded-2xl shadow-sm p-6 fade-up" style="animation-delay:<?= ($i + 1) * 0.08 ?>s">
            <p class="text-gray-700 leading-relaxed"><?= nl2br(htmlspecialchars($rep['contenu'])) ?></p>
            <div class="flex items-center justify-between mt-4 text-xs text-gray-400">
              <span>📅 <?= htmlspecialchars($rep['date_reponse'] ?? '') ?></span>
              <?php
                $sRaw  = $rep['status'] ?? 'en cours';
                $sNorm = normalizeStatut($sRaw);
                $rb = match(true) {
                    str_contains($sNorm, 'traite') || str_contains($sNorm, 'resolu') => 'bg-green-100 text-green-600',
                    str_contains($sNorm, 'rejete') || str_contains($sNorm, 'rejet')  => 'bg-red-100 text-red-500',
                    default => 'bg-blue-100 text-blue-500',
                };
              ?>
              <span class="<?= $rb ?> px-3 py-1 rounded-full font-medium"><?= htmlspecialchars($sRaw) ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</main>

<!-- ── Toast PDF ── -->
<div id="pdfToast">📄 PDF généré avec succès !</div>

<!-- ── MODAL MODIFIER ── -->
<div class="modal-overlay" id="editModal">
  <div class="modal-box">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-xl font-bold text-gray-800">✏️ Modifier la réclamation</h3>
      <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
    </div>

    <form method="POST" action="update_reclamation.php" class="space-y-4">
      <input type="hidden" name="id" value="<?= $id ?>">

      <div>
        <label class="block text-sm font-medium text-gray-600 mb-1">Type</label>
        <select name="type" class="w-full p-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
          <option value="person"  <?= ($reclamation['type'] ?? '') === 'person'  ? 'selected' : '' ?>>Personne</option>
          <option value="company" <?= ($reclamation['type'] ?? '') === 'company' ? 'selected' : '' ?>>Entreprise</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-600 mb-1">Description</label>
        <textarea name="description" required rows="4"
                  class="w-full p-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 resize-none"
        ><?= htmlspecialchars($reclamation['description']) ?></textarea>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-600 mb-1">Note</label>
        <select name="rating" class="w-full p-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
          <?php for ($s = 1; $s <= 5; $s++): ?>
            <option value="<?= $s ?>" <?= (int)($reclamation['rating'] ?? 0) === $s ? 'selected' : '' ?>>
              <?= str_repeat('⭐', $s) ?>
            </option>
          <?php endfor; ?>
        </select>
      </div>

      <div class="flex gap-3 pt-2">
        <button type="submit"
                class="flex-1 bg-teal-500 text-white py-3 rounded-xl hover:bg-teal-600 transition font-medium text-sm">
          💾 Enregistrer
        </button>
        <button type="button" onclick="closeModal()"
                class="flex-1 bg-gray-100 text-gray-600 py-3 rounded-xl hover:bg-gray-200 transition font-medium text-sm">
          Annuler
        </button>
      </div>
    </form>
  </div>
</div>

<script src="../../assets/js/main.js"></script>
<script>
// ── Modal ──
function openModal()  { document.getElementById('editModal').classList.add('open'); }
function closeModal() { document.getElementById('editModal').classList.remove('open'); }
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// ── Métier 8 : Export PDF ──
async function exportPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });

    const margin   = 20;
    const pageW    = 210;
    const contentW = pageW - margin * 2;
    let y          = margin;

    // ── En-tête ──
    doc.setFillColor(20, 184, 166); // teal-500
    doc.roundedRect(margin, y, contentW, 22, 4, 4, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(16);
    doc.setFont('helvetica', 'bold');
    doc.text('Swaply – Reclamation #<?= $id ?>', margin + 6, y + 14);
    y += 30;

    // ── Info générale ──
    doc.setTextColor(30, 30, 30);
    doc.setFontSize(11);
    doc.setFont('helvetica', 'bold');
    doc.text('Description :', margin, y);
    doc.setFont('helvetica', 'normal');
    const descLines = doc.splitTextToSize(<?= json_encode($reclamation['description']) ?>, contentW - 30);
    doc.text(descLines, margin + 32, y);
    y += descLines.length * 6 + 4;

    doc.setFont('helvetica', 'bold');
    doc.text('Type :', margin, y);
    doc.setFont('helvetica', 'normal');
    doc.text(<?= json_encode($reclamation['type'] ?? '—') ?>, margin + 16, y);
    y += 8;

    doc.setFont('helvetica', 'bold');
    doc.text('Statut :', margin, y);
    doc.setFont('helvetica', 'normal');
    doc.text(<?= json_encode($reclamation['statut'] ?? 'en attente') ?>, margin + 20, y);
    y += 8;

    doc.setFont('helvetica', 'bold');
    doc.text('Date :', margin, y);
    doc.setFont('helvetica', 'normal');
    doc.text(<?= json_encode($reclamation['date_creation'] ?? '') ?>, margin + 16, y);
    y += 8;

    <?php if (!empty($reclamation['username_cible'])): ?>
    doc.setFont('helvetica', 'bold');
    doc.text('Utilisateur cible :', margin, y);
    doc.setFont('helvetica', 'normal');
    doc.text('@<?= htmlspecialchars($reclamation['username_cible']) ?>', margin + 44, y);
    y += 8;
    <?php endif; ?>

    doc.setFont('helvetica', 'bold');
    doc.text('Note :', margin, y);
    doc.setFont('helvetica', 'normal');
    doc.text('<?= (int)($reclamation['rating'] ?? 0) ?> / 5', margin + 16, y);
    y += 14;

    // ── Ligne séparatrice ──
    doc.setDrawColor(20, 184, 166);
    doc.setLineWidth(0.5);
    doc.line(margin, y, margin + contentW, y);
    y += 10;

    // ── Réponses ──
    <?php if (!empty($reponses)): ?>
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(12);
    doc.setTextColor(20, 184, 166);
    doc.text('Reponses de l\'administration :', margin, y);
    y += 8;

    doc.setTextColor(30, 30, 30);
    doc.setFontSize(10);

    <?php foreach ($reponses as $idx => $rep): ?>
    doc.setFillColor(240, 253, 250);
    doc.roundedRect(margin, y, contentW, 4, 1, 1, 'F'); // top accent
    doc.setFillColor(248, 250, 252);
    doc.roundedRect(margin, y + 3, contentW, 20, 2, 2, 'F');

    doc.setFont('helvetica', 'normal');
    const rep<?= $idx ?>Lines = doc.splitTextToSize(<?= json_encode($rep['contenu']) ?>, contentW - 6);
    doc.text(rep<?= $idx ?>Lines, margin + 3, y + 10);

    doc.setFontSize(8);
    doc.setTextColor(150, 150, 150);
    doc.text(<?= json_encode($rep['date_reponse'] ?? '') ?>, margin + 3, y + 20);
    doc.text('Statut: <?= htmlspecialchars($rep['status'] ?? '') ?>', margin + contentW - 30, y + 20);
    doc.setTextColor(30, 30, 30);
    doc.setFontSize(10);

    y += 28;
    <?php endforeach; ?>
    <?php else: ?>
    doc.setFont('helvetica', 'italic');
    doc.setTextColor(150, 150, 150);
    doc.text('Aucune reponse pour l\'instant.', margin, y);
    y += 10;
    <?php endif; ?>

    // ── Pied de page ──
    const footerY = 290;
    doc.setDrawColor(230, 230, 230);
    doc.setLineWidth(0.3);
    doc.line(margin, footerY - 6, margin + contentW, footerY - 6);
    doc.setFontSize(8);
    doc.setTextColor(160, 160, 160);
    doc.setFont('helvetica', 'normal');
    doc.text('Swaply – Document généré le ' + new Date().toLocaleDateString('fr-FR'), margin, footerY);
    doc.text('Page 1/1', margin + contentW, footerY, { align: 'right' });

    // ── Téléchargement ──
    doc.save('reclamation-<?= $id ?>-swaply.pdf');

    // Toast
    const toast = document.getElementById('pdfToast');
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}
</script>
</body>
</html>