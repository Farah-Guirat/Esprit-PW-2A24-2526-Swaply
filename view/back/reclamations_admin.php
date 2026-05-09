<?php
require_once '../../controller/ReclamationController.php';
require_once '../../controller/ReponseController.php';

$controller    = new ReclamationController();
$repController = new ReponseController();

// Récupérer les réclamations avec les infos de l'expéditeur
require_once '../../config/database.php';
$stmtRecs = $conn->query("
    SELECT r.*, 
           u.prenom, u.nom, u.email, u.telephone,
           CONCAT(u.prenom, ' ', u.nom) AS full_name
    FROM reclamations r
    LEFT JOIN utilisateurs u ON r.id_user = u.id_u
    ORDER BY r.date_creation DESC
");
$recs = $stmtRecs->fetchAll(PDO::FETCH_ASSOC);

function normalizeStatut(string $s): string {
    $s = strtolower(trim($s));
    return str_replace(
        ['é','è','ê','ë','à','â','î','ï','ô','û','ù','ç'],
        ['e','e','e','e','a','a','i','i','o','u','u','c'],
        $s
    );
}

// ── Stats rapides pour KPIs ──
$total     = count($recs);
$enAttente = 0; $enCours = 0; $traite = 0;
foreach ($recs as $r) {
    $sn = normalizeStatut($r['statut'] ?? '');
    if (str_contains($sn, 'traite'))    $traite++;
    elseif (str_contains($sn, 'cours')) $enCours++;
    else                                $enAttente++;
}
?>
<style>
/* ── Variables ── */
:root {
  --teal:   #14b8a6;
  --teal-d: #0f766e;
  --red:    #ef4444;
  --orange: #f97316;
  --green:  #22c55e;
}

/* ── KPI mini cards ── */
.adm-kpi-row { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:24px; }
.adm-kpi {
  background:white; border-radius:16px; padding:18px 20px;
  box-shadow:0 2px 10px rgba(0,0,0,.06);
  border-top:3px solid transparent;
}
.adm-kpi .val { font-size:28px; font-weight:700; margin:4px 0 2px; }
.adm-kpi .lbl { font-size:12px; color:#64748b; }

/* ── Filter bar ── */
.adm-filters {
  background:white; border-radius:16px; padding:14px 18px;
  box-shadow:0 2px 10px rgba(0,0,0,.06);
  display:flex; flex-wrap:wrap; gap:10px; align-items:center;
  margin-bottom:20px;
}
.adm-filters input,
.adm-filters select {
  border:1.5px solid #e2e8f0; border-radius:10px;
  padding:7px 12px; font-size:13px; outline:none;
  background:#f8fafc; transition:border-color .2s;
}
.adm-filters input:focus,
.adm-filters select:focus { border-color:var(--teal); background:white; }
.adm-filters input { min-width:200px; flex:1; }

/* ── Table ── */
.adm-table-wrap {
  background:white; border-radius:16px;
  box-shadow:0 2px 10px rgba(0,0,0,.06);
  overflow:hidden;
}
.adm-table { width:100%; border-collapse:collapse; font-size:13px; }
.adm-table thead tr { background:#f0fdfa; }
.adm-table th {
  padding:13px 14px; text-align:left;
  font-size:11px; font-weight:700; color:#0f766e;
  text-transform:uppercase; letter-spacing:.05em;
  border-bottom:1px solid #e2e8f0;
}
.adm-table td {
  padding:13px 14px; border-bottom:1px solid #f1f5f9;
  vertical-align:middle;
}
.adm-table tbody tr { transition:background .15s; }
.adm-table tbody tr:hover { background:#f8fffe; }
.adm-table tbody tr:last-child td { border-bottom:none; }

/* ── Badges statut ── */
.badge-st {
  display:inline-flex; align-items:center; gap:5px;
  font-size:11px; font-weight:600; padding:3px 10px; border-radius:20px;
}
.st-attente { background:#fef2f2; color:#ef4444; }
.st-cours   { background:#fff7ed; color:#f97316; }
.st-traite  { background:#f0fdf4; color:#16a34a; }

/* ── Type badge ── */
.badge-type {
  font-size:11px; font-weight:500; padding:2px 8px;
  border-radius:20px; background:#f1f5f9; color:#475569;
}

/* ── Stars ── */
.stars { color:#f59e0b; letter-spacing:-1px; }

/* ── Action buttons ── */
.btn-rep {
  background:var(--teal); color:white;
  border:none; border-radius:10px; padding:6px 12px;
  font-size:12px; font-weight:600; cursor:pointer;
  display:inline-flex; align-items:center; gap:5px;
  transition:background .2s;
}
.btn-rep:hover { background:var(--teal-d); }

.btn-st {
  background:#f1f5f9; color:#475569;
  border:none; border-radius:10px; padding:6px 10px;
  font-size:12px; font-weight:600; cursor:pointer;
  display:inline-flex; align-items:center; gap:4px;
  transition:all .2s;
}
.btn-st:hover { background:#e2e8f0; }

.btn-del {
  background:#fef2f2; color:#ef4444;
  border:none; border-radius:10px; padding:6px 10px;
  font-size:12px; cursor:pointer;
  display:inline-flex; align-items:center;
  transition:all .2s;
}
.btn-del:hover { background:#fee2e2; }

.actions-cell { display:flex; gap:6px; align-items:center; }

/* ── Empty state ── */
.adm-empty {
  text-align:center; padding:60px 20px; color:#94a3b8;
}

/* ── Modal ── */
.adm-overlay {
  position:fixed; inset:0; background:rgba(0,0,0,.45);
  backdrop-filter:blur(4px); z-index:9999;
  display:flex; align-items:center; justify-content:center;
  opacity:0; pointer-events:none;
  transition:opacity .25s;
}
.adm-overlay.open { opacity:1; pointer-events:all; }
.adm-modal {
  background:white; border-radius:20px; padding:30px;
  width:100%; max-width:480px;
  transform:translateY(18px); transition:transform .25s;
  box-shadow:0 24px 48px rgba(0,0,0,.18);
}
.adm-overlay.open .adm-modal { transform:translateY(0); }
.adm-modal h3 { font-size:17px; font-weight:700; margin-bottom:18px; color:#1e293b; }
.adm-modal label { font-size:12px; font-weight:600; color:#64748b; display:block; margin-bottom:5px; }
.adm-modal textarea,
.adm-modal select {
  width:100%; border:1.5px solid #e2e8f0; border-radius:12px;
  padding:10px 13px; font-size:13px; outline:none;
  font-family:inherit; transition:border-color .2s;
  background:#f8fafc;
}
.adm-modal textarea:focus,
.adm-modal select:focus { border-color:var(--teal); background:white; }
.adm-modal textarea { resize:none; }
.modal-btns { display:flex; gap:10px; margin-top:18px; }
.btn-save {
  flex:1; background:var(--teal); color:white;
  border:none; border-radius:12px; padding:11px;
  font-size:13px; font-weight:600; cursor:pointer; transition:background .2s;
}
.btn-save:hover { background:var(--teal-d); }
.btn-cancel {
  flex:1; background:#f1f5f9; color:#64748b;
  border:none; border-radius:12px; padding:11px;
  font-size:13px; font-weight:600; cursor:pointer; transition:background .2s;
}
.btn-cancel:hover { background:#e2e8f0; }

/* ── Statut modal ── */
.st-options { display:flex; flex-direction:column; gap:8px; margin-top:4px; }
.st-opt {
  display:flex; align-items:center; gap:10px;
  border:1.5px solid #e2e8f0; border-radius:12px;
  padding:10px 14px; cursor:pointer; transition:all .2s;
}
.st-opt:hover { border-color:var(--teal); background:#f0fdfa; }
.st-opt input[type=radio] { accent-color:var(--teal); width:16px; height:16px; }
.st-opt span { font-size:13px; font-weight:500; }

/* ── Description Modal ── */
.desc-modal-overlay {
  position:fixed; inset:0; background:rgba(0,0,0,.45);
  backdrop-filter:blur(4px); z-index:9999;
  display:flex; align-items:center; justify-content:center;
  opacity:0; pointer-events:none;
  transition:opacity .25s;
}
.desc-modal-overlay.open { opacity:1; pointer-events:all; }
.desc-modal-box {
  background:white; border-radius:20px; padding:32px;
  width:100%; max-width:520px;
  transform:translateY(20px) scale(.97);
  transition:transform .25s;
  box-shadow:0 24px 48px rgba(0,0,0,.18);
  position:relative;
}
.desc-modal-overlay.open .desc-modal-box { transform:translateY(0) scale(1); }
.desc-modal-close {
  position:absolute; top:16px; right:16px;
  background:#f1f5f9; border:none; border-radius:50%;
  width:32px; height:32px; cursor:pointer;
  display:flex; align-items:center; justify-content:center;
  font-size:16px; color:#64748b; transition:background .2s;
}
.desc-modal-close:hover { background:#e2e8f0; }
.desc-modal-header {
  display:flex; align-items:center; gap:10px;
  margin-bottom:18px; padding-bottom:14px;
  border-bottom:1px solid #f1f5f9;
}
.desc-modal-id {
  font-size:12px; font-weight:700; color:var(--teal-d);
  background:#f0fdfa; padding:3px 10px; border-radius:20px;
}
.desc-modal-meta {
  display:flex; flex-wrap:wrap; gap:8px; margin-bottom:16px;
}
.desc-modal-meta span {
  font-size:12px; padding:3px 10px; border-radius:20px;
  background:#f1f5f9; color:#475569; font-weight:500;
}
.desc-modal-body {
  font-size:14px; line-height:1.75; color:#1e293b;
  background:#f8fafc; border-radius:12px; padding:16px;
  white-space:pre-wrap; word-break:break-word;
  max-height:280px; overflow-y:auto;
}
.desc-modal-stars { color:#f59e0b; font-size:18px; }

/* Clickable row hint */
.adm-table tbody tr { cursor:pointer; }
.adm-table tbody tr:hover td { background:#f0fdfa; }

/* ── Toast ── */
.adm-toast {
  position:fixed; bottom:24px; right:24px; z-index:99999;
  background:#1e293b; color:white;
  padding:12px 20px; border-radius:14px; font-size:13px;
  font-weight:600; box-shadow:0 8px 24px rgba(0,0,0,.25);
  opacity:0; transform:translateY(12px);
  transition:all .3s ease; pointer-events:none;
  display:flex; align-items:center; gap:8px;
}
.adm-toast.show { opacity:1; transform:translateY(0); }
.adm-toast.ok  { border-left:4px solid var(--teal); }
.adm-toast.err { border-left:4px solid var(--red); }

/* ── Pagination ── */
.adm-pagination {
  display:flex; align-items:center; justify-content:space-between;
  padding:14px 18px; border-top:1px solid #f1f5f9;
  font-size:13px; color:#64748b;
}
.pg-btns { display:flex; gap:6px; }
.pg-btn {
  border:1.5px solid #e2e8f0; background:white; border-radius:8px;
  padding:5px 12px; cursor:pointer; font-size:12px; transition:all .2s;
}
.pg-btn:hover:not(:disabled) { border-color:var(--teal); color:var(--teal); }
.pg-btn.active { background:var(--teal); color:white; border-color:var(--teal); }
.pg-btn:disabled { opacity:.4; cursor:not-allowed; }

@keyframes fadeRow {
  from { opacity:0; transform:translateX(-6px); }
  to   { opacity:1; transform:translateX(0); }
}
.adm-table tbody tr { animation:fadeRow .25s ease both; }
</style>

<!-- ── KPI MINI ── -->
<div class="adm-kpi-row">
  <div class="adm-kpi" style="border-top-color:var(--teal)">
    <p class="lbl">Total réclamations</p>
    <p class="val" style="color:var(--teal-d)"><?= $total ?></p>
  </div>
  <div class="adm-kpi" style="border-top-color:var(--red)">
    <p class="lbl">En attente</p>
    <p class="val" style="color:var(--red)"><?= $enAttente ?></p>
  </div>
  <div class="adm-kpi" style="border-top-color:var(--orange)">
    <p class="lbl">En cours</p>
    <p class="val" style="color:var(--orange)"><?= $enCours ?></p>
  </div>
  <div class="adm-kpi" style="border-top-color:var(--green)">
    <p class="lbl">Traités</p>
    <p class="val" style="color:var(--green)"><?= $traite ?></p>
  </div>
</div>

<!-- ── FILTER BAR ── -->
<div class="adm-filters">
  <input type="text" id="admSearch" placeholder="🔍  Rechercher description, @username…" oninput="admFilter()">

  <select id="admStatut" onchange="admFilter()">
    <option value="">Tous les statuts</option>
    <option value="en attente">⏳ En attente</option>
    <option value="en cours">🔄 En cours</option>
    <option value="traité">✅ Traité</option>
  </select>

  <select id="admType" onchange="admFilter()">
    <option value="">Tous les types</option>
    <option value="person">👤 Personne</option>
    <option value="company">🏢 Entreprise</option>
  </select>

  <button onclick="admReset()"
          style="border:1.5px solid #e2e8f0;background:white;border-radius:10px;
                 padding:7px 14px;font-size:13px;cursor:pointer;color:#64748b;">
    ✕ Reset
  </button>

  <span id="admCount" style="margin-left:auto;font-size:12px;color:#94a3b8;">
    <?= $total ?> réclamation<?= $total>1?'s':'' ?>
  </span>
</div>

<!-- ── TABLE ── -->
<div class="adm-table-wrap">
  <table class="adm-table" id="admTable">
    <thead>
      <tr>
        <th>#ID</th>
        <th>Description</th>
        <th>Type</th>
        <th>Expéditeur</th>
        <th>Utilisateur ciblé</th>
        <th>Note</th>
        <th>Statut</th>
        <th>Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody id="admTbody">
    <?php foreach ($recs as $i => $rec):
      $sNorm = normalizeStatut($rec['statut'] ?? '');
      if      (str_contains($sNorm, 'traite')) { $stClass = 'st-traite';  $stLabel = 'traité'; }
      elseif  (str_contains($sNorm, 'cours'))  { $stClass = 'st-cours';   $stLabel = 'en cours'; }
      else                                      { $stClass = 'st-attente'; $stLabel = 'en attente'; }

      $stars = str_repeat('★', (int)($rec['rating'] ?? 0)) . str_repeat('☆', 5 - (int)($rec['rating'] ?? 0));
      $desc  = htmlspecialchars(mb_substr($rec['description'], 0, 55)) . (mb_strlen($rec['description']) > 55 ? '…' : '');
      $date  = htmlspecialchars(substr($rec['date_creation'] ?? '', 0, 10));
      $recId = (int)$rec['id_reclamation'];
    ?>
    <tr data-desc="<?= strtolower(htmlspecialchars($rec['description'])) ?>"
        data-user="<?= strtolower(htmlspecialchars($rec['username_cible'] ?? '')) ?>"
        data-sender="<?= strtolower(htmlspecialchars($rec['full_name'] ?? '')) ?>"
        data-statut="<?= $stLabel ?>"
        data-type="<?= strtolower($rec['type'] ?? '') ?>"
        data-fulldesc="<?= htmlspecialchars($rec['description'], ENT_QUOTES) ?>"
        data-recid="<?= $recId ?>"
        data-rating="<?= (int)($rec['rating'] ?? 0) ?>"
        data-typelabel="<?= ($rec['type'] ?? '') === 'company' ? 'Entreprise' : 'Personne' ?>"
        data-cible="<?= htmlspecialchars($rec['username_cible'] ?? '', ENT_QUOTES) ?>"
        data-stlabel="<?= $stLabel ?>"
        onclick="openDescModal(this, event)"
        style="animation-delay:<?= $i * 0.03 ?>s">
      <td style="font-weight:700;color:#64748b">#<?= $recId ?></td>
      <td style="max-width:200px;color:#1e293b"><?= $desc ?></td>
      <td>
        <span class="badge-type">
          <?= ($rec['type'] ?? '') === 'company' ? '🏢 Entreprise' : '👤 Personne' ?>
        </span>
      </td>
      <td>
        <?php if (!empty($rec['full_name'])): ?>
          <div style="display:flex;flex-direction:column;gap:2px;">
            <span style="font-weight:600;color:#1e293b"><?= htmlspecialchars($rec['full_name']) ?></span>
            <span style="font-size:11px;color:#94a3b8"><?= htmlspecialchars($rec['email'] ?? '') ?></span>
          </div>
        <?php else: ?>
          <span style="color:#cbd5e1;font-size:12px">Utilisateur #<?= (int)$rec['id_user'] ?></span>
        <?php endif; ?>
      </td>
      <td>
        <?php if (!empty($rec['username_cible'])): ?>
        <span style="color:var(--teal);font-weight:600">@<?= htmlspecialchars($rec['username_cible']) ?></span>
        <?php else: ?><span style="color:#cbd5e1">—</span><?php endif; ?>
      </td>
      <td><span class="stars"><?= $stars ?></span></td>
      <td>
        <span class="badge-st <?= $stClass ?>">
          <?= $stLabel === 'traité' ? '✅' : ($stLabel === 'en cours' ? '🔄' : '⏳') ?>
          <?= $stLabel ?>
        </span>
      </td>
      <td style="color:#94a3b8"><?= $date ?></td>
      <td>
        <div class="actions-cell">
          <button class="btn-rep" onclick="openReponseModal(<?= $recId ?>)">
            💬 Répondre
          </button>
          <button class="btn-st" onclick="openStatutModal(<?= $recId ?>, '<?= $stLabel ?>')">
            📋 Statut
          </button>
          <button class="btn-del" onclick="deleteRec(<?= $recId ?>, this)"
                  title="Supprimer">
            🗑
          </button>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Empty state -->
  <div class="adm-empty" id="admEmpty" style="display:none">
    <div style="font-size:48px;margin-bottom:12px">🔍</div>
    <p style="font-size:16px;font-weight:600;color:#64748b">Aucune réclamation trouvée</p>
    <p style="font-size:13px;margin-top:4px">Modifiez vos filtres</p>
  </div>

  <!-- Pagination -->
  <div class="adm-pagination">
    <span id="pgInfo">Affichage de <?= $total ?> résultats</span>
    <div class="pg-btns" id="pgBtns"></div>
  </div>
</div>

<!-- ── Modal Description ── -->
<div class="desc-modal-overlay" id="modalDesc">
  <div class="desc-modal-box">
    <button class="desc-modal-close" onclick="closeDescModal()">✕</button>
    <div class="desc-modal-header">
      <span class="desc-modal-id" id="descModalId">#00</span>
      <span style="font-size:14px;font-weight:600;color:#1e293b">Réclamation</span>
    </div>
    <div class="desc-modal-meta" id="descModalMeta"></div>
    <p style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">Description</p>
    <div class="desc-modal-body" id="descModalBody"></div>
  </div>
</div>

<!-- ── Toast ── -->
<div class="adm-toast" id="admToast"></div>

<!-- ── Modal Répondre ── -->
<div class="adm-overlay" id="modalRep">
  <div class="adm-modal">
    <h3>💬 Répondre à la réclamation</h3>
    <input type="hidden" id="repRecId">

    <label>Votre réponse</label>
    <textarea id="repContenu" rows="4" placeholder="Rédigez votre réponse…"></textarea>

    <div style="margin-top:14px">
      <label>Statut de la réponse</label>
      <select id="repStatus">
        <option value="en cours">🔄 En cours de traitement</option>
        <option value="traité">✅ Traité</option>
        <option value="rejeté">❌ Rejeté</option>
      </select>
    </div>

    <div class="modal-btns">
      <button class="btn-save" onclick="submitReponse()">✅ Envoyer</button>
      <button class="btn-cancel" onclick="closeModals()">Annuler</button>
    </div>
  </div>
</div>

<!-- ── Modal Statut ── -->
<div class="adm-overlay" id="modalSt">
  <div class="adm-modal">
    <h3>📋 Changer le statut</h3>
    <input type="hidden" id="stRecId">

    <div class="st-options">
      <label class="st-opt">
        <input type="radio" name="adm_statut" value="en attente">
        <span>⏳ En attente</span>
      </label>
      <label class="st-opt">
        <input type="radio" name="adm_statut" value="en cours">
        <span>🔄 En cours</span>
      </label>
      <label class="st-opt">
        <input type="radio" name="adm_statut" value="traité">
        <span>✅ Traité</span>
      </label>
    </div>

    <div class="modal-btns">
      <button class="btn-save" onclick="submitStatut()">💾 Enregistrer</button>
      <button class="btn-cancel" onclick="closeModals()">Annuler</button>
    </div>
  </div>
</div>

<script>
// ══════════════════════════════════════════
//  FILTER + SEARCH (Métier 5 back)
// ══════════════════════════════════════════
const PER_PAGE = 8;
let currentPage = 1;
let visibleRows = [];

function admFilter() {
    const q   = document.getElementById('admSearch').value.toLowerCase().trim();
    const st  = document.getElementById('admStatut').value.toLowerCase();
    const tp  = document.getElementById('admType').value.toLowerCase();
    const rows = document.querySelectorAll('#admTbody tr');

    visibleRows = [];
    rows.forEach(row => {
        const desc   = row.dataset.desc   || '';
        const user   = row.dataset.user   || '';
        const sender = row.dataset.sender || '';
        const statut = row.dataset.statut || '';
        const type   = row.dataset.type   || '';

        const matchQ  = !q  || desc.includes(q) || user.includes(q) || sender.includes(q);
        const matchSt = !st || statut.includes(st.replace('é','e').replace('î','i'));
        const matchTp = !tp || type === tp;

        if (matchQ && matchSt && matchTp) {
            visibleRows.push(row);
        }
        row.style.display = 'none';
    });

    document.getElementById('admCount').textContent =
        visibleRows.length + ' réclamation' + (visibleRows.length > 1 ? 's' : '');
    document.getElementById('admEmpty').style.display =
        visibleRows.length === 0 ? 'block' : 'none';

    currentPage = 1;
    renderPage();
}

function renderPage() {
    const start = (currentPage - 1) * PER_PAGE;
    const end   = start + PER_PAGE;
    visibleRows.forEach((row, i) => {
        row.style.display = (i >= start && i < end) ? '' : 'none';
        row.style.animationDelay = ((i - start) * 0.03) + 's';
    });

    // Pagination buttons
    const total  = visibleRows.length;
    const pages  = Math.ceil(total / PER_PAGE);
    const pgBtns = document.getElementById('pgBtns');
    const pgInfo = document.getElementById('pgInfo');

    pgInfo.textContent = `Affichage de ${Math.min(end, total)} / ${total} résultats`;

    pgBtns.innerHTML = '';
    if (pages <= 1) return;

    // Prev
    const prev = document.createElement('button');
    prev.className = 'pg-btn'; prev.textContent = '‹';
    prev.disabled = currentPage === 1;
    prev.onclick = () => { currentPage--; renderPage(); };
    pgBtns.appendChild(prev);

    // Pages
    for (let p = 1; p <= pages; p++) {
        const btn = document.createElement('button');
        btn.className = 'pg-btn' + (p === currentPage ? ' active' : '');
        btn.textContent = p;
        btn.onclick = () => { currentPage = p; renderPage(); };
        pgBtns.appendChild(btn);
    }

    // Next
    const next = document.createElement('button');
    next.className = 'pg-btn'; next.textContent = '›';
    next.disabled = currentPage === pages;
    next.onclick = () => { currentPage++; renderPage(); };
    pgBtns.appendChild(next);
}

function admReset() {
    document.getElementById('admSearch').value = '';
    document.getElementById('admStatut').value = '';
    document.getElementById('admType').value   = '';
    admFilter();
}

// Init
document.addEventListener('DOMContentLoaded', () => {
    admFilter();
});
// Si déjà injecté (SPA)
admFilter();

// ══════════════════════════════════════════
//  TOAST
// ══════════════════════════════════════════
function showToast(msg, ok = true) {
    const t = document.getElementById('admToast');
    t.textContent = (ok ? '✅ ' : '❌ ') + msg;
    t.className = 'adm-toast show ' + (ok ? 'ok' : 'err');
    clearTimeout(t._timer);
    t._timer = setTimeout(() => t.classList.remove('show'), 3500);
}

// ══════════════════════════════════════════
//  MODALS
// ══════════════════════════════════════════
function closeModals() {
    document.getElementById('modalRep').classList.remove('open');
    document.getElementById('modalSt').classList.remove('open');
}

['modalRep','modalSt'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) closeModals();
    });
});

// ── Répondre ──
function openReponseModal(id) {
    document.getElementById('repRecId').value   = id;
    document.getElementById('repContenu').value = '';
    document.getElementById('repStatus').value  = 'en cours';
    document.getElementById('modalRep').classList.add('open');
    setTimeout(() => document.getElementById('repContenu').focus(), 300);
}

function submitReponse() {
    const id      = document.getElementById('repRecId').value;
    const contenu = document.getElementById('repContenu').value.trim();
    const status  = document.getElementById('repStatus').value;

    if (!contenu) { showToast('Veuillez écrire une réponse.', false); return; }

    const fd = new FormData();
    fd.append('id_reclamation', id);
    fd.append('contenu', contenu);
    fd.append('status', status);

    fetch('reponse_admin.php', { method:'POST', body:fd })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                showToast('Réponse envoyée avec succès !');
                closeModals();
                // Mettre à jour le badge statut dans la table si "traité"
                updateRowStatut(id, status.includes('traite') || status.includes('traité') ? 'traité' : 'en cours');
            } else {
                showToast(data.msg || 'Erreur lors de l\'envoi.', false);
            }
        })
        .catch(() => showToast('Erreur réseau.', false));
}

// ── Statut ──
function openStatutModal(id, currentStatut) {
    document.getElementById('stRecId').value = id;
    const radios = document.querySelectorAll('input[name="adm_statut"]');
    radios.forEach(r => {
        r.checked = (r.value === currentStatut);
    });
    document.getElementById('modalSt').classList.add('open');
}

function submitStatut() {
    const id     = document.getElementById('stRecId').value;
    const statut = document.querySelector('input[name="adm_statut"]:checked')?.value;

    if (!statut) { showToast('Choisissez un statut.', false); return; }

    const fd = new FormData();
    fd.append('id_reclamation', id);
    fd.append('statut', statut);

    fetch('statut_admin.php', { method:'POST', body:fd })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                showToast('Statut mis à jour !');
                updateRowStatut(id, statut);
                closeModals();
            } else {
                showToast(data.msg || 'Erreur.', false);
            }
        })
        .catch(() => showToast('Erreur réseau.', false));
}

// Mise à jour visuelle du badge dans la ligne
function updateRowStatut(id, statut) {
    const rows = document.querySelectorAll('#admTbody tr');
    rows.forEach(row => {
        const btn = row.querySelector('.btn-st');
        if (!btn) return;
        const delBtn = row.querySelector('.btn-del');
        if (!delBtn) return;
        // Repérer la bonne ligne via l'onclick du bouton delete
        if (btn.getAttribute('onclick')?.includes('(' + id + ',') ||
            delBtn.getAttribute('onclick')?.includes('(' + id + ',')) {

            // Update dataset
            row.dataset.statut = statut;

            // Update badge
            const badge = row.querySelector('.badge-st');
            if (badge) {
                badge.className = 'badge-st ';
                let cls='', icon='', label=statut;
                if (statut === 'traité')     { cls='st-traite'; icon='✅'; }
                else if (statut === 'en cours') { cls='st-cours'; icon='🔄'; }
                else                         { cls='st-attente'; icon='⏳'; label='en attente'; }
                badge.classList.add(cls);
                badge.textContent = icon + ' ' + label;
            }

            // Update onclick du btn statut
            btn.setAttribute('onclick', `openStatutModal(${id}, '${statut}')`);
        }
    });
    // Re-filter pour mettre à jour les résultats
    admFilter();
}

// ── Supprimer ──
function deleteRec(id, btn) {
    if (!confirm('Supprimer définitivement cette réclamation ?')) return;

    btn.disabled = true;
    btn.textContent = '⏳';

    fetch('delete_reclamation_admin.php?id=' + id)
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                const row = btn.closest('tr');
                row.style.transition = 'all .3s ease';
                row.style.opacity    = '0';
                row.style.transform  = 'translateX(20px)';
                setTimeout(() => {
                    row.remove();
                    admFilter();
                    showToast('Réclamation supprimée.');
                }, 300);
            } else {
                btn.disabled = false;
                btn.textContent = '🗑';
                showToast(data.msg || 'Erreur suppression.', false);
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.textContent = '🗑';
            showToast('Erreur réseau.', false);
        });
}

// ══════════════════════════════════════════
//  DESCRIPTION MODAL
// ══════════════════════════════════════════
function openDescModal(row, event) {
    // Ne pas ouvrir si on clique sur un bouton d'action
    if (event.target.closest('button')) return;

    const id       = row.dataset.recid;
    const desc     = row.dataset.fulldesc;
    const rating   = parseInt(row.dataset.rating) || 0;
    const type     = row.dataset.typelabel;
    const cible    = row.dataset.cible;
    const stLabel  = row.dataset.stlabel;

    document.getElementById('descModalId').textContent = '#' + id;

    const stars = '★'.repeat(rating) + '☆'.repeat(5 - rating);
    let stClass = 'st-attente', stIcon = '⏳';
    if (stLabel === 'traité')   { stClass = 'st-traite'; stIcon = '✅'; }
    else if (stLabel === 'en cours') { stClass = 'st-cours'; stIcon = '🔄'; }

    document.getElementById('descModalMeta').innerHTML = `
        <span class="badge-st ${stClass}">${stIcon} ${stLabel}</span>
        <span>${type === 'Entreprise' ? '🏢' : '👤'} ${type}</span>
        ${cible ? `<span style="color:var(--teal);font-weight:600">@${cible}</span>` : ''}
        <span class="desc-modal-stars">${stars}</span>
        <span style="color:#94a3b8">${rating}/5</span>
    `;

    document.getElementById('descModalBody').textContent = desc;
    document.getElementById('modalDesc').classList.add('open');
}

function closeDescModal() {
    document.getElementById('modalDesc').classList.remove('open');
}

document.getElementById('modalDesc').addEventListener('click', function(e) {
    if (e.target === this) closeDescModal();
});

// Debounce search
let _sTimer;
document.getElementById('admSearch').addEventListener('input', function() {
    clearTimeout(_sTimer);
    _sTimer = setTimeout(() => admFilter(), 350);
});
</script>