<?php
require_once '../../controller/ReclamationController.php';

$controller = new ReclamationController();
$stmt = $controller->afficher();
$recs = $stmt->fetchAll(PDO::FETCH_ASSOC);

function badgeClass(string $statut): array {
    $s = strtolower($statut);
    $s = str_replace(['é','è','ê','à','â','î','ï','ô','û','ù','ç'],
                     ['e','e','e','a','a','i','i','o','u','u','c'], $s);
    if (str_contains($s, 'traite'))  return ['bg:#dcfce7;color:#16a34a', '✅'];
    if (str_contains($s, 'rejete') || str_contains($s, 'rejet')) return ['bg:#fee2e2;color:#dc2626', '❌'];
    if (str_contains($s, 'cours'))   return ['bg:#fff7ed;color:#ea580c', '🔄'];
    return ['bg:#f1f5f9;color:#64748b', '⏳'];
}


$recsJson = json_encode($recs, JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<style>
  .adm-wrap        { font-family:'Segoe UI',sans-serif; padding:0; }
  .adm-topbar      { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
  .adm-title       { font-size:22px; font-weight:700; color:#0f766e; }
  .adm-filters     { display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom:20px; }
  .adm-filters input,
  .adm-filters select { padding:9px 14px; border:1px solid #e2e8f0; border-radius:12px; font-size:14px; outline:none; background:#fff; }
  .adm-filters input:focus,
  .adm-filters select:focus { border-color:#14b8a6; box-shadow:0 0 0 3px #ccfbf1; }
  .adm-table-wrap  { background:#fff; border-radius:20px; box-shadow:0 4px 20px rgba(0,0,0,.06); overflow:hidden; }
  table            { width:100%; border-collapse:collapse; }
  thead            { background:#f0fdfa; }
  thead th         { padding:14px 18px; text-align:left; font-size:13px; font-weight:600; color:#0f766e; text-transform:uppercase; letter-spacing:.5px; }
  tbody tr         { border-top:1px solid #f1f5f9; transition:background .15s; }
  tbody tr:hover   { background:#f8fafc; }
  td               { padding:14px 18px; font-size:14px; color:#374151; vertical-align:middle; }
  .desc-cell       { max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .badge-statut    { display:inline-block; padding:4px 12px; border-radius:999px; font-size:12px; font-weight:600; }
  .stars           { color:#f59e0b; letter-spacing:-1px; }
  .btn             { display:inline-flex; align-items:center; gap:6px; padding:7px 14px; border-radius:10px; font-size:13px; font-weight:500; cursor:pointer; border:none; transition:all .15s; text-decoration:none; }
  .btn-teal        { background:#14b8a6; color:#fff; }
  .btn-teal:hover  { background:#0f766e; }
  .btn-red         { background:#fee2e2; color:#dc2626; }
  .btn-red:hover   { background:#fecaca; }
  .btn-orange      { background:#fff7ed; color:#ea580c; }
  .btn-orange:hover{ background:#fed7aa; }
  .actions         { display:flex; gap:6px; flex-wrap:wrap; }
  .empty-state     { text-align:center; padding:60px 20px; color:#94a3b8; }
  .empty-state .icon { font-size:48px; margin-bottom:12px; }
  .count-badge     { background:#14b8a6; color:#fff; font-size:12px; padding:2px 10px; border-radius:999px; margin-left:8px; }


  .modal-bg        { display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center; }
  .modal-bg.open   { display:flex; }
  .modal-box       { background:#fff; border-radius:24px; padding:32px; width:100%; max-width:480px; box-shadow:0 24px 60px rgba(0,0,0,.15); animation:slideUp .25s ease; }
  @keyframes slideUp { from{transform:translateY(20px);opacity:0} to{transform:translateY(0);opacity:1} }
  .modal-title     { font-size:18px; font-weight:700; color:#0f766e; margin-bottom:20px; }
  .modal-close     { float:right; background:none; border:none; font-size:22px; cursor:pointer; color:#94a3b8; line-height:1; }
  .modal-close:hover { color:#374151; }
  .form-group      { margin-bottom:16px; }
  .form-group label{ display:block; font-size:13px; font-weight:600; color:#64748b; margin-bottom:6px; }
  .form-group textarea,
  .form-group select{ width:100%; padding:11px 14px; border:1px solid #e2e8f0; border-radius:12px; font-size:14px; outline:none; font-family:inherit; resize:vertical; }
  .form-group textarea:focus,
  .form-group select:focus { border-color:#14b8a6; box-shadow:0 0 0 3px #ccfbf1; }
  .modal-footer    { display:flex; gap:10px; margin-top:20px; }
  .btn-full        { flex:1; padding:12px; justify-content:center; }
  .btn-cancel      { background:#f1f5f9; color:#64748b; }
  .btn-cancel:hover{ background:#e2e8f0; }
  .alert           { padding:10px 16px; border-radius:10px; font-size:13px; margin-bottom:16px; display:none; }
  .alert.success   { background:#dcfce7; color:#16a34a; border:1px solid #bbf7d0; display:block; }
  .alert.error     { background:#fee2e2; color:#dc2626; border:1px solid #fecaca; display:block; }
</style>

<div class="adm-wrap">


  <div class="adm-topbar">
    <div class="adm-title">
      📋 Réclamations
      <span class="count-badge" id="recCountBadge"><?= count($recs) ?></span>
    </div>
  </div>


  <div class="adm-filters">
    <input type="text"   id="fSearch"  placeholder="🔍 Rechercher..." oninput="applyFilters()">
    <select id="fStatut" onchange="applyFilters()">
      <option value="">Tous les statuts</option>
      <option value="en attente">⏳ En attente</option>
      <option value="en cours">🔄 En cours</option>
      <option value="traité">✅ Traité</option>
      <option value="rejeté">❌ Rejeté</option>
    </select>
    <select id="fType" onchange="applyFilters()">
      <option value="">Tous les types</option>
      <option value="person">👤 Personne</option>
      <option value="company">🏢 Entreprise</option>
    </select>
    <button class="btn btn-cancel" onclick="resetFilters()">✕ Reset</button>
  </div>


  <div class="adm-table-wrap">
    <table>
      <thead>
        <tr>
          <th>#ID</th>
          <th>Description</th>
          <th>Type</th>
          <th>Utilisateur ciblé</th>
          <th>Note</th>
          <th>Statut</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="recTableBody">
   
      </tbody>
    </table>
    <div class="empty-state" id="emptyState" style="display:none;">
      <div class="icon">📭</div>
      <p>Aucune réclamation trouvée</p>
    </div>
  </div>

</div>


<div class="modal-bg" id="modalRepondre">
  <div class="modal-box">
    <button class="modal-close" onclick="closeModal('modalRepondre')">&times;</button>
    <div class="modal-title">💬 Envoyer une réponse</div>
    <div class="alert" id="alertRepondre"></div>
    <p style="font-size:13px;color:#64748b;margin-bottom:16px;" id="descRepondre"></p>
    <div>
      <input type="hidden" id="repRecId">
      <div class="form-group">
        <label>Contenu de la réponse</label>
        <textarea id="repContenu" rows="4" placeholder="Votre réponse à l'utilisateur..." required></textarea>
      </div>
      <div class="form-group">
        <label>Statut de la réponse</label>
        <select id="repStatus">
          <option value="en cours">🔄 En cours</option>
          <option value="traité">✅ Traité</option>
          <option value="rejeté">❌ Rejeté</option>
        </select>
      </div>
      <div class="modal-footer">
        <button class="btn btn-teal btn-full" onclick="submitRepondre()">📤 Envoyer</button>
        <button class="btn btn-cancel btn-full" onclick="closeModal('modalRepondre')">Annuler</button>
      </div>
    </div>
  </div>
</div>


<div class="modal-bg" id="modalStatut">
  <div class="modal-box">
    <button class="modal-close" onclick="closeModal('modalStatut')">&times;</button>
    <div class="modal-title">🔄 Changer le statut</div>
    <div class="alert" id="alertStatut"></div>
    <div>
      <input type="hidden" id="statRecId">
      <div class="form-group">
        <label>Nouveau statut</label>
        <select id="statNouv" style="padding:12px 14px;">
          <option value="en attente">⏳ En attente</option>
          <option value="en cours">🔄 En cours</option>
          <option value="traité">✅ Traité</option>
          <option value="rejeté">❌ Rejeté</option>
        </select>
      </div>
      <div class="modal-footer">
        <button class="btn btn-teal btn-full" onclick="submitStatut()">💾 Enregistrer</button>
        <button class="btn btn-cancel btn-full" onclick="closeModal('modalStatut')">Annuler</button>
      </div>
    </div>
  </div>
</div>

<script>

const ALL_RECS = <?= $recsJson ?>;
let currentRecs = [...ALL_RECS];

function badgeHtml(statut) {
  const s = (statut || '').toLowerCase()
    .replace(/[éèê]/g,'e').replace(/[àâ]/g,'a').replace(/[îï]/g,'i')
    .replace(/[ôö]/g,'o').replace(/[ùûü]/g,'u').replace('ç','c');
  if (s.includes('traite'))  return ['bg:#dcfce7;color:#16a34a', '✅'];
  if (s.includes('rejete') || s.includes('rejet')) return ['bg:#fee2e2;color:#dc2626', '❌'];
  if (s.includes('cours'))   return ['bg:#fff7ed;color:#ea580c', '🔄'];
  return ['bg:#f1f5f9;color:#64748b', '⏳'];
}


function renderTable(recs) {
  const tbody = document.getElementById('recTableBody');
  const empty = document.getElementById('emptyState');
  document.getElementById('recCountBadge').textContent = recs.length;

  if (!recs.length) {
    tbody.innerHTML = '';
    empty.style.display = 'block';
    return;
  }
  empty.style.display = 'none';

  tbody.innerHTML = recs.map(r => {
    const [style, icon] = badgeHtml(r.statut || '');
    const stars = '⭐'.repeat(Math.min(5, Math.max(0, parseInt(r.rating) || 0))) || '—';
    const desc  = (r.description || '').replace(/"/g, '&quot;');
    const descEsc = (r.description || '').replace(/'/g, "\\'");
    const statut  = (r.statut || 'en attente');
    const statutEsc = statut.replace(/'/g, "\\'");
    const date = (r.date_creation || '').substring(0, 10);
    const typeLabel = r.type === 'company' ? '🏢 Entreprise' : '👤 Personne';
    const userCible = r.username_cible
      ? `<span style="background:#f0fdfa;color:#0f766e;padding:3px 10px;border-radius:999px;font-size:13px;font-weight:600;">@${r.username_cible}</span>`
      : `<span style="color:#cbd5e1">—</span>`;

    return `<tr id="row-${r.id_reclamation}">
      <td><strong>#${r.id_reclamation}</strong></td>
      <td class="desc-cell" title="${desc}">${desc}</td>
      <td>${typeLabel}</td>
      <td>${userCible}</td>
      <td class="stars">${stars}</td>
      <td><span class="badge-statut" style="${style}">${icon} ${statut}</span></td>
      <td style="color:#94a3b8;font-size:13px;">${date}</td>
      <td>
        <div class="actions">
          <button class="btn btn-teal"   onclick="openRepondre(${r.id_reclamation}, '${descEsc}')">💬 Répondre</button>
          <button class="btn btn-orange" onclick="openStatut(${r.id_reclamation}, '${statutEsc}')">🔄 Statut</button>
          <button class="btn btn-red"    onclick="supprimerRec(${r.id_reclamation})">🗑</button>
        </div>
      </td>
    </tr>`;
  }).join('');
}


function normalize(s) {
  return (s || '').toLowerCase()
    .replace(/[éèê]/g,'e').replace(/[àâ]/g,'a').replace(/[îï]/g,'i')
    .replace(/[ôö]/g,'o').replace(/[ùûü]/g,'u').replace('ç','c');
}

function applyFilters() {
  const search = normalize(document.getElementById('fSearch').value);
  const statut = normalize(document.getElementById('fStatut').value);
  const type   = document.getElementById('fType').value;

  currentRecs = ALL_RECS.filter(r => {
    if (search && !normalize(r.description).includes(search)) return false;
    if (statut && !normalize(r.statut || '').includes(statut)) return false;
    if (type   && r.type !== type) return false;
    return true;
  });
  renderTable(currentRecs);
}

function resetFilters() {
  document.getElementById('fSearch').value = '';
  document.getElementById('fStatut').value = '';
  document.getElementById('fType').value   = '';
  applyFilters();
}


function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

document.querySelectorAll('.modal-bg').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
});

function openRepondre(id, desc) {
  document.getElementById('repRecId').value   = id;
  document.getElementById('repContenu').value = '';
  document.getElementById('repStatus').value  = 'en cours';
  document.getElementById('descRepondre').textContent = '📝 ' + desc.substring(0, 80) + (desc.length > 80 ? '…' : '');
  showAlert('alertRepondre', '', '');
  openModal('modalRepondre');
}

function openStatut(id, currentStatut) {
  document.getElementById('statRecId').value = id;
  const sel = document.getElementById('statNouv');
  for (let opt of sel.options) {
    if (opt.value.toLowerCase() === currentStatut.toLowerCase()) { sel.value = opt.value; break; }
  }
  showAlert('alertStatut', '', '');
  openModal('modalStatut');
}

function showAlert(elemId, type, msg) {
  const el = document.getElementById(elemId);
  el.className = 'alert ' + type;
  el.textContent = msg;
  if (msg) setTimeout(() => el.className = 'alert', 3500);
}


function updateLocalStatut(id, newStatut) {
  const rec = ALL_RECS.find(r => r.id_reclamation == id);
  if (rec) rec.statut = newStatut;
  applyFilters();
}

function removeLocalRec(id) {
  const idx = ALL_RECS.findIndex(r => r.id_reclamation == id);
  if (idx !== -1) ALL_RECS.splice(idx, 1);
  applyFilters();
}


function supprimerRec(id) {
  if (!confirm('Supprimer cette réclamation ?')) return;
  fetch('delete_reclamation_admin.php?id=' + id)
    .then(r => r.json())
    .then(data => {
      if (data.ok) {
        removeLocalRec(id);
      } else {
        alert('Erreur : ' + (data.msg || 'Réessayez'));
      }
    })
    .catch(() => alert('Erreur réseau'));
}


function submitRepondre() {
  const id      = document.getElementById('repRecId').value;
  const contenu = document.getElementById('repContenu').value.trim();
  const status  = document.getElementById('repStatus').value;

  if (!contenu) { showAlert('alertRepondre', 'error', '❌ Le contenu est requis'); return; }

  const fd = new FormData();
  fd.append('id_reclamation', id);
  fd.append('contenu', contenu);
  fd.append('status', status);

  fetch('reponse_admin.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
      if (data.ok) {
    
        const sNorm = status.toLowerCase().replace(/[éèê]/g,'e').replace(/[àâ]/g,'a');
        const newStatut = (sNorm.includes('traite') || sNorm.includes('rejete') || sNorm.includes('rejet'))
          ? 'traité' : 'en cours';
        showAlert('alertRepondre', 'success', '✅ Réponse envoyée avec succès !');
        setTimeout(() => {
          closeModal('modalRepondre');
          updateLocalStatut(id, newStatut);
        }, 1000);
      } else {
        showAlert('alertRepondre', 'error', '❌ Erreur : ' + (data.msg || 'Réessayez'));
      }
    })
    .catch(() => showAlert('alertRepondre', 'error', '❌ Erreur réseau'));
}


function submitStatut() {
  const id     = document.getElementById('statRecId').value;
  const statut = document.getElementById('statNouv').value;

  const fd = new FormData();
  fd.append('id_reclamation', id);
  fd.append('statut', statut);

  fetch('statut_admin.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
      if (data.ok) {
        showAlert('alertStatut', 'success', '✅ Statut mis à jour !');
        setTimeout(() => {
          closeModal('modalStatut');
          updateLocalStatut(id, statut);
        }, 1000);
      } else {
        showAlert('alertStatut', 'error', '❌ Erreur : ' + (data.msg || 'Réessayez'));
      }
    })
    .catch(() => showAlert('alertStatut', 'error', '❌ Erreur réseau'));
}


renderTable(ALL_RECS);
</script>