<?php
require_once "../../model/projet.php";
$p = new Projet();
$projets = $p->getAll();

// Préparer les compétences de chaque projet
foreach ($projets as &$proj) {
    $comps = $p->getCompetences($proj['id_projet']);
    $proj['competences'] = array_column($comps, 'nom_competence');
}
unset($proj);
?>

<div class="bg-white rounded-3xl p-8 shadow-sm mb-8">

  <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
    <span class="w-8 h-8 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center text-sm">🔗</span>
    Matching Intelligent de Projets
  </h3>

  <!-- SELECT PROJET -->
  <div class="mb-6">
    <label class="block text-sm font-medium text-gray-600 mb-2">Choisir un projet à analyser</label>
    <div class="flex gap-3">
      <select id="matchSelect"
        class="flex-1 px-4 py-3 rounded-2xl border border-gray-200 focus:outline-none focus:border-teal-400 text-sm bg-white">
        <option value="">-- Sélectionner un projet --</option>
        <?php foreach($projets as $proj): ?>
          <option value="<?= $proj['id_projet'] ?>"
            data-nom="<?= htmlspecialchars($proj['nom_projet']) ?>"
            data-comps="<?= htmlspecialchars(implode(', ', $proj['competences'])) ?>">
            <?= htmlspecialchars($proj['nom_projet']) ?>
            <?= !empty($proj['competences']) ? '(' . implode(', ', $proj['competences']) . ')' : '(aucune compétence)' ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button onclick="lancerMatching()"
        style="background:#14b8a6;color:white;border:none;padding:12px 24px;border-radius:16px;font-size:13px;font-weight:600;cursor:pointer;transition:background 0.2s;"
        onmouseover="this.style.background='#0d9488'"
        onmouseout="this.style.background='#14b8a6'">
        🔍 Analyser
      </button>
    </div>
  </div>

  <!-- LOADING -->
  <div id="matchLoading" style="display:none;text-align:center;padding:30px;">
    <div style="display:inline-flex;gap:6px;align-items:center;">
      <span style="width:8px;height:8px;background:#14b8a6;border-radius:50%;animation:bounce 0.8s infinite;display:inline-block;"></span>
      <span style="width:8px;height:8px;background:#14b8a6;border-radius:50%;animation:bounce 0.8s 0.2s infinite;display:inline-block;"></span>
      <span style="width:8px;height:8px;background:#14b8a6;border-radius:50%;animation:bounce 0.8s 0.4s infinite;display:inline-block;"></span>
      <span style="font-size:13px;color:#6b7280;margin-left:8px;">L'IA analyse les projets...</span>
    </div>
  </div>

  <!-- RESULTATS -->
  <div id="matchResults" style="display:none;">

    <!-- PROJET SELECTIONNE -->
    <div id="matchSelected" style="background:#f0fdf9;border:1px solid #99f6e4;border-radius:16px;padding:16px;margin-bottom:20px;">
    </div>

    <!-- LISTE DES MATCHES -->
    <h4 style="font-size:14px;font-weight:600;color:#374151;margin-bottom:12px;">Projets similaires trouvés:</h4>
    <div id="matchList" style="display:flex;flex-direction:column;gap:12px;"></div>

    <!-- CONSEIL IA -->
    <div id="matchAdvice" style="margin-top:20px;background:#faf5ff;border:1px solid #e9d5ff;border-radius:16px;padding:16px;display:none;">
      <p style="font-size:12px;font-weight:600;color:#7c3aed;margin-bottom:6px;">💡 Conseil de l'IA</p>
      <p id="matchAdviceText" style="font-size:13px;color:#6b7280;line-height:1.6;margin:0;"></p>
    </div>

  </div>

  <!-- EMPTY STATE -->
  <div id="matchEmpty" style="text-align:center;padding:30px;color:#9ca3af;">
    <p style="font-size:32px;margin-bottom:8px;">🔗</p>
    <p style="font-size:13px;">Sélectionne un projet pour trouver les projets similaires</p>
  </div>

</div>

<script>
// Données projets depuis PHP
const projetsData = <?= json_encode(array_map(function($p) {
    return [
        'id'          => $p['id_projet'],
        'nom'         => $p['nom_projet'],
        'description' => $p['description'],
        'statut'      => $p['statut'],
        'competences' => $p['competences']
    ];
}, $projets)) ?>;

async function lancerMatching() {
    let select = document.getElementById('matchSelect');
    let selectedId = parseInt(select.value);

    if (!selectedId) {
        alert('Veuillez sélectionner un projet!');
        return;
    }

    let selectedProjet = projetsData.find(p => p.id === selectedId);
    if (!selectedProjet) return;

    // Show loading
    document.getElementById('matchEmpty').style.display = 'none';
    document.getElementById('matchResults').style.display = 'none';
    document.getElementById('matchLoading').style.display = 'block';

    // Build context for AI
    let autresProjets = projetsData.filter(p => p.id !== selectedId);

    let context = `Tu es un expert en gestion de projets. 
Analyse ce projet et trouve les projets similaires basé sur les compétences communes.

PROJET SÉLECTIONNÉ:
- Nom: ${selectedProjet.nom}
- Description: ${selectedProjet.description}
- Statut: ${selectedProjet.statut}
- Compétences: ${selectedProjet.competences.join(', ') || 'aucune'}

AUTRES PROJETS:
${autresProjets.map(p => `- ID:${p.id} | Nom: ${p.nom} | Compétences: ${p.competences.join(', ') || 'aucune'}`).join('\n')}

Réponds UNIQUEMENT en JSON valide avec ce format exact:
{
  "matches": [
    {
      "id": 1,
      "score": 85,
      "competences_communes": ["React", "Node.js"],
      "raison": "explication courte en français"
    }
  ],
  "conseil": "un conseil général en français sur ce projet et ses similarités"
}

Trie les matches par score décroissant. Inclure seulement les projets avec score > 0.`;

    try {
        let response = await fetch('../../controller/matching.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ context: context })
        });

        let data = await response.json();
        document.getElementById('matchLoading').style.display = 'none';

        if (data.error) {
            alert('Erreur: ' + data.error);
            document.getElementById('matchEmpty').style.display = 'block';
            return;
        }

        afficherResultats(selectedProjet, data);

    } catch(err) {
        document.getElementById('matchLoading').style.display = 'none';
        document.getElementById('matchEmpty').style.display = 'block';
        alert('Erreur de connexion. Réessaie!');
    }
}

function afficherResultats(selectedProjet, data) {
    // Afficher projet sélectionné
    let selectedDiv = document.getElementById('matchSelected');
    selectedDiv.innerHTML = `
        <p style="font-size:12px;font-weight:600;color:#0d9488;margin-bottom:8px;">📁 Projet analysé</p>
        <p style="font-size:15px;font-weight:600;color:#1f2937;margin-bottom:4px;">${selectedProjet.nom}</p>
        <p style="font-size:13px;color:#6b7280;margin-bottom:8px;">${selectedProjet.description}</p>
        <div style="display:flex;flex-wrap:wrap;gap:6px;">
            ${selectedProjet.competences.map(c => `
                <span style="background:#ccfbf1;color:#0d9488;font-size:11px;padding:3px 10px;border-radius:20px;font-weight:500;">${c}</span>
            `).join('')}
        </div>`;

    // Afficher matches
    let matchList = document.getElementById('matchList');
    matchList.innerHTML = '';

    if (!data.matches || data.matches.length === 0) {
        matchList.innerHTML = `
            <div style="text-align:center;padding:20px;color:#9ca3af;">
                <p style="font-size:13px;">Aucun projet similaire trouvé pour ce projet.</p>
            </div>`;
    } else {
        data.matches.forEach(match => {
            let projet = projetsData.find(p => p.id === match.id);
            if (!projet) return;

            let scoreColor = match.score >= 70 ? '#10b981' : match.score >= 40 ? '#f59e0b' : '#ef4444';
            let scoreBg    = match.score >= 70 ? '#ecfdf5' : match.score >= 40 ? '#fffbeb' : '#fef2f2';

            let card = document.createElement('div');
            card.style.cssText = `background:white;border:1px solid #e5e7eb;border-radius:16px;padding:16px;display:flex;align-items:center;gap:16px;`;
            card.innerHTML = `
                <!-- SCORE -->
                <div style="width:60px;height:60px;border-radius:50%;background:${scoreBg};border:2px solid ${scoreColor};
                            display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <span style="font-size:14px;font-weight:700;color:${scoreColor};">${match.score}%</span>
                </div>

                <!-- INFO -->
                <div style="flex:1;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                        <p style="font-size:14px;font-weight:600;color:#1f2937;margin:0;">${projet.nom}</p>
                        <span style="font-size:11px;padding:2px 8px;border-radius:20px;
                            ${projet.statut === 'Terminé' ? 'background:#d1fae5;color:#059669;' : 'background:#dbeafe;color:#2563eb;'}">
                            ${projet.statut}
                        </span>
                    </div>
                    <p style="font-size:12px;color:#6b7280;margin:0 0 8px;">${match.raison}</p>
                    <div style="display:flex;flex-wrap:wrap;gap:4px;">
                        ${(match.competences_communes || []).map(c => `
                            <span style="background:#ccfbf1;color:#0d9488;font-size:11px;padding:2px 8px;border-radius:20px;">✓ ${c}</span>
                        `).join('')}
                        ${projet.competences.filter(c => !(match.competences_communes || []).includes(c)).map(c => `
                            <span style="background:#f3f4f6;color:#9ca3af;font-size:11px;padding:2px 8px;border-radius:20px;">${c}</span>
                        `).join('')}
                    </div>
                </div>

                <!-- BARRE SCORE -->
                <div style="width:80px;">
                    <div style="height:4px;background:#f0f0f0;border-radius:2px;overflow:hidden;">
                        <div style="width:${match.score}%;height:100%;background:${scoreColor};border-radius:2px;transition:width 1s ease;"></div>
                    </div>
                </div>`;

            matchList.appendChild(card);
        });
    }

    // Conseil IA
    if (data.conseil) {
        document.getElementById('matchAdviceText').textContent = data.conseil;
        document.getElementById('matchAdvice').style.display = 'block';
    }

    document.getElementById('matchResults').style.display = 'block';
}
</script>