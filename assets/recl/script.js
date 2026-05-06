// Mapping page key → fichier PHP à charger
const PAGE_MAP = {
  dashboard:     null,
  users:         'users_admin.php',
  profiles:      'profiles_admin.php',
  offres:        'offres_admin.php',
  publications:  'publications_admin.php',
  conversations: 'conversations_admin.php',
  reclamations:  'reclamations_admin.php',
  stats:         'stats_admin.php',
  settings:      'settings_admin.php',
};

const PAGE_TITLES = {
  dashboard:     'Dashboard',
  users:         'Utilisateurs',
  profiles:      'Profils & Portfolios',
  offres:        'Offres & Demandes',
  publications:  'Publications',
  conversations: 'Conversations',
  reclamations:  'Réclamations',
  stats:         'Statistiques',
  settings:      'Paramètres',
};

function showPage(page) {
  // 1. Menu actif
  document.querySelectorAll('.menu-item').forEach(item => item.classList.remove('active'));
  const activeMenu = document.getElementById('menu-' + page);
  if (activeMenu) activeMenu.classList.add('active');

  // 2. Titre header
  document.getElementById('page-title').textContent = PAGE_TITLES[page] || page;

  const dashboardPage = document.getElementById('dashboard-page');
  const otherPages    = document.getElementById('other-pages');

  // 3. Dashboard → afficher la div locale
  if (page === 'dashboard') {
    dashboardPage.classList.remove('hidden');
    otherPages.classList.add('hidden');
    otherPages.innerHTML = '';
    return;
  }

  // 4. Autre page → cacher dashboard, afficher #other-pages
  dashboardPage.classList.add('hidden');
  otherPages.classList.remove('hidden');

  const file = PAGE_MAP[page];

  if (!file) {
    otherPages.innerHTML = `
      <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;
                  height:60vh;color:#94a3b8;gap:16px;">
        <div style="font-size:56px;">🚧</div>
        <p style="font-size:20px;font-weight:600;color:#64748b;">Page en construction</p>
        <p style="font-size:14px;">Cette section sera bientôt disponible.</p>
      </div>`;
    return;
  }

  // 5. Spinner
  otherPages.innerHTML = `
    <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;
                height:60vh;gap:20px;">
      <div style="width:44px;height:44px;border:4px solid #e2e8f0;
                  border-top-color:#14b8a6;border-radius:50%;
                  animation:adm-spin .7s linear infinite;"></div>
      <p style="font-size:14px;color:#94a3b8;">Chargement...</p>
    </div>
    <style>@keyframes adm-spin { to { transform: rotate(360deg); } }</style>`;

  // 6. Fetch — inject only the body content, never replace the whole page
  fetch(file)
    .then(r => {
      if (!r.ok) throw new Error('Erreur HTTP ' + r.status);
      return r.text();
    })
    .then(html => {
      // If the response is a full HTML page, extract only <body> content
      // Otherwise inject as-is (partial HTML from PHP)
      let content = html;
      const bodyMatch = html.match(/<body[^>]*>([\s\S]*?)<\/body>/i);
      if (bodyMatch) {
        content = bodyMatch[1];
      }

      otherPages.innerHTML = content;

      // Re-execute any <script> tags inside the injected content
      otherPages.querySelectorAll('script').forEach(oldScript => {
        const newScript = document.createElement('script');
        newScript.textContent = oldScript.textContent;
        document.body.appendChild(newScript);
        oldScript.remove();
      });
    })
    .catch(err => {
      otherPages.innerHTML = `
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;
                    height:60vh;gap:16px;">
          <div style="font-size:56px;">⚠️</div>
          <p style="font-size:18px;font-weight:600;color:#ef4444;">Erreur de chargement</p>
          <p style="font-size:13px;color:#94a3b8;">${err.message}</p>
          <button onclick="showPage('${page}')"
                  style="margin-top:8px;padding:10px 24px;background:#14b8a6;color:#fff;
                         border:none;border-radius:12px;cursor:pointer;font-size:14px;">
            🔄 Réessayer
          </button>
        </div>`;
    });
}

