function showPage(page) {
  document.querySelectorAll('.menu-item').forEach(item => {
    item.classList.remove('active');
  });

  const activeMenu = document.getElementById('menu-' + page);
  if (activeMenu) activeMenu.classList.add('active');

  document.getElementById('page-title').textContent =
    page === 'dashboard' ? 'Dashboard' :
    page === 'publications' ? 'Publications' :
    page === 'stories' ? 'Stories' :
    page === 'reports' ? 'Signalements' :
    page.charAt(0).toUpperCase() + page.slice(1);

  const header = document.getElementById('main-header');

  if (page === 'dashboard') {
    document.getElementById('dashboard-page').classList.remove('hidden');
    document.getElementById('other-pages').classList.add('hidden');
    header.classList.remove('hidden');
  } else if (page === 'publications') {
    document.getElementById('dashboard-page').classList.add('hidden');
    document.getElementById('other-pages').classList.remove('hidden');
    header.classList.add('hidden');
    loadPublicationsPage();
  } else if (page === 'stories') {
    document.getElementById('dashboard-page').classList.add('hidden');
    document.getElementById('other-pages').classList.remove('hidden');
    header.classList.add('hidden');
    loadStoriesPage();
  } else if (page === 'reports') {
    document.getElementById('dashboard-page').classList.add('hidden');
    document.getElementById('other-pages').classList.remove('hidden');
    header.classList.add('hidden');
    loadReportsPage();
  } else {
    document.getElementById('dashboard-page').classList.add('hidden');
    document.getElementById('other-pages').classList.remove('hidden');
    header.classList.add('hidden');
    document.getElementById('other-pages').innerHTML = ""; // page vide
  }
}

function loadPublicationsPage() {
  const otherPages = document.getElementById('other-pages');
  otherPages.innerHTML = `
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Gestion des Publications</h2>
        <div class="flex space-x-4">
          <input type="text" id="search-publications" placeholder="Rechercher..." class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          <select id="sort-publications" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="date_pub">Trier par date</option>
            <option value="likes">Trier par likes</option>
          </select>
        </div>
      </div>

      <div id="publications-list" class="space-y-4">
        <!-- Les publications seront chargées ici -->
      </div>
    </div>
  `;

  loadPublications();
}

function loadPublications(search = '', sortBy = 'date_pub') {
  fetch('swaplyB.php?action=getPublications&search=' + encodeURIComponent(search) + '&sort=' + sortBy)
    .then(response => response.json())
    .then(data => {
      const publicationsList = document.getElementById('publications-list');
      publicationsList.innerHTML = '';

      if (data.length === 0) {
        publicationsList.innerHTML = '<p class="text-gray-500 text-center py-8">Aucune publication trouvée.</p>';
        return;
      }

      data.forEach(pub => {
        const pubElement = document.createElement('div');
        pubElement.className = 'bg-white rounded-lg shadow-md p-6';
        pubElement.innerHTML = `
          <div class="flex justify-between items-start mb-4">
            <div class="flex items-center space-x-3">
              <img src="${pub.photo || '/swaply/assets/default-avatar.png'}" alt="Avatar" class="w-10 h-10 rounded-full">
              <div>
                <h3 class="font-semibold text-gray-800">${pub.nom} ${pub.prenom}</h3>
                <p class="text-sm text-gray-500">${new Date(pub.date_pub).toLocaleDateString('fr-FR')}</p>
              </div>
            </div>
            <button onclick="deletePublication(${pub.id_pub})" class="text-red-500 hover:text-red-700">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
            </button>
          </div>

          <div class="mb-4">
            <h4 class="font-semibold text-lg mb-2">${pub.titre}</h4>
            <p class="text-gray-700">${pub.contenu}</p>
            ${pub.image ? `<img src="${pub.image}" alt="Publication" class="mt-3 max-w-full h-auto rounded-lg">` : ''}
          </div>

          <div class="flex items-center justify-between text-sm text-gray-500">
            <div class="flex space-x-4">
              <span>❤️ ${pub.likes_count} likes</span>
              <span>💬 ${pub.comments_count} commentaires</span>
            </div>
            <button onclick="showPublicationStats(${pub.id_pub})" class="text-blue-500 hover:text-blue-700">
              Voir statistiques
            </button>
          </div>
        `;
        publicationsList.appendChild(pubElement);
      });
    })
    .catch(error => {
      console.error('Erreur lors du chargement des publications:', error);
      document.getElementById('publications-list').innerHTML = '<p class="text-red-500 text-center py-8">Erreur lors du chargement des publications.</p>';
    });
}

function deletePublication(id) {
  if (confirm('Êtes-vous sûr de vouloir supprimer cette publication ?')) {
    fetch('swaplyB.php?action=deletePublication', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'id=' + id
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        loadPublications();
      } else {
        alert('Erreur lors de la suppression: ' + data.message);
      }
    })
    .catch(error => {
      console.error('Erreur:', error);
      alert('Erreur lors de la suppression');
    });
  }
}

function showPublicationStats(id) {
  // Cette fonction sera implémentée pour afficher les statistiques détaillées
  alert('Fonctionnalité de statistiques à implémenter');
}

function loadStoriesPage() {
  const otherPages = document.getElementById('other-pages');
  otherPages.innerHTML = `
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Gestion des Stories</h2>
      </div>

      <div id="stories-list" class="space-y-4">
        <!-- Les stories seront chargées ici -->
      </div>
    </div>
  `;

  loadStories();
}

function loadStories() {
  fetch('swaplyB.php?action=getStories')
    .then(response => response.json())
    .then(data => {
      const storiesList = document.getElementById('stories-list');
      storiesList.innerHTML = '';

      if (data.length === 0) {
        storiesList.innerHTML = '<p class="text-gray-500 text-center py-8">Aucune story trouvée.</p>';
        return;
      }

      data.forEach(story => {
        const storyElement = document.createElement('div');
        storyElement.className = 'bg-white rounded-lg shadow-md p-6';
        storyElement.innerHTML = `
          <div class="flex justify-between items-start mb-4">
            <div class="flex items-center space-x-3">
              <img src="${story.photo || '/swaply/assets/default-avatar.png'}" alt="Avatar" class="w-10 h-10 rounded-full">
              <div>
                <h3 class="font-semibold text-gray-800">${story.nom} ${story.prenom}</h3>
                <p class="text-sm text-gray-500">${new Date(story.date_creation).toLocaleDateString('fr-FR')}</p>
              </div>
            </div>
            <button onclick="deleteStory(${story.id_story})" class="text-red-500 hover:text-red-700">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
            </button>
          </div>

          <div class="mb-4">
            <p class="text-gray-700">${story.contenu}</p>
            ${story.image ? `<img src="${story.image}" alt="Story" class="mt-3 max-w-full h-auto rounded-lg">` : ''}
          </div>

          <div class="flex items-center justify-between text-sm text-gray-500">
            <div class="flex space-x-4">
              <span>❤️ ${story.likes_count} likes</span>
              <span>💬 ${story.comments_count} commentaires</span>
            </div>
            <button onclick="showStoryStats(${story.id_story})" class="text-blue-500 hover:text-blue-700">
              Voir statistiques
            </button>
          </div>
        `;
        storiesList.appendChild(storyElement);
      });
    })
    .catch(error => {
      console.error('Erreur lors du chargement des stories:', error);
      document.getElementById('stories-list').innerHTML = '<p class="text-red-500 text-center py-8">Erreur lors du chargement des stories.</p>';
    });
}

function deleteStory(id) {
  if (confirm('Êtes-vous sûr de vouloir supprimer cette story ?')) {
    fetch('swaplyB.php?action=deleteStory', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'id_story=' + id
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        loadStories();
      } else {
        alert('Erreur lors de la suppression: ' + data.message);
      }
    })
    .catch(error => {
      console.error('Erreur:', error);
      alert('Erreur lors de la suppression');
    });
  }
}

function showStoryStats(id) {
  alert('Fonctionnalité de statistiques des stories à implémenter');
}

function loadReportsPage() {
  const otherPages = document.getElementById('other-pages');
  otherPages.innerHTML = `
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Historique des Signalements</h2>
      </div>

      <div id="reports-list" class="space-y-4">
        <!-- Les signalements seront chargés ici -->
      </div>
    </div>
  `;

  loadReports();
}

function loadReports() {
  fetch('swaplyB.php?action=getReports')
    .then(response => response.json())
    .then(data => {
      const reportsList = document.getElementById('reports-list');
      reportsList.innerHTML = '';

      if (data.length === 0) {
        reportsList.innerHTML = '<p class="text-gray-500 text-center py-8">Aucun signalement trouvé.</p>';
        return;
      }

      data.forEach(report => {
        const reportElement = document.createElement('div');
        reportElement.className = 'bg-white rounded-lg shadow-md p-6';
        reportElement.innerHTML = `
          <div class="flex justify-between items-start mb-4">
            <div class="flex items-center space-x-3">
              <div>
                <h3 class="font-semibold text-gray-800">${report.nom} ${report.prenom}</h3>
                <p class="text-sm text-gray-500">${new Date(report.created_at).toLocaleDateString('fr-FR')}</p>
                <p class="text-xs text-gray-400">${report.email}</p>
              </div>
            </div>
            <div class="flex space-x-2">
              <span class="px-2 py-1 text-xs rounded-full ${
                report.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                report.status === 'in_review' ? 'bg-blue-100 text-blue-800' :
                report.status === 'resolved' ? 'bg-green-100 text-green-800' :
                'bg-red-100 text-red-800'
              }">
                ${report.status === 'pending' ? 'En attente' :
                  report.status === 'in_review' ? 'En revue' :
                  report.status === 'resolved' ? 'Résolu' : 'Rejeté'}
              </span>
              <button onclick="deleteReport(${report.id_report})" class="text-red-500 hover:text-red-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
              </button>
            </div>
          </div>

          <div class="mb-4">
            <p class="font-medium text-gray-700">Motif: ${report.reason}</p>
            ${report.description ? `<p class="text-gray-600 mt-2">${report.description}</p>` : ''}
          </div>

          ${report.publication_title ? `
          <div class="bg-gray-50 p-3 rounded-lg">
            <p class="text-sm font-medium text-gray-700">Publication signalée:</p>
            <p class="text-sm text-gray-600">"${report.publication_title}"</p>
          </div>
          ` : ''}

          ${report.comment_content ? `
          <div class="bg-gray-50 p-3 rounded-lg mt-2">
            <p class="text-sm font-medium text-gray-700">Commentaire signalé:</p>
            <p class="text-sm text-gray-600">"${report.comment_content}"</p>
          </div>
          ` : ''}
        `;
        reportsList.appendChild(reportElement);
      });
    })
    .catch(error => {
      console.error('Erreur lors du chargement des signalements:', error);
      document.getElementById('reports-list').innerHTML = '<p class="text-red-500 text-center py-8">Erreur lors du chargement des signalements.</p>';
    });
}

function deleteReport(id) {
  if (confirm('Êtes-vous sûr de vouloir supprimer ce signalement ?')) {
    fetch('swaplyB.php?action=deleteReport', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'id=' + id
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        loadReports();
      } else {
        alert('Erreur lors de la suppression: ' + data.message);
      }
    })
    .catch(error => {
      console.error('Erreur:', error);
      alert('Erreur lors de la suppression');
    });
  }
}

// Événements pour la recherche et le tri
document.addEventListener('DOMContentLoaded', () => {
  showPage('dashboard');

  // Délégation d'événements pour les éléments qui seront créés dynamiquement
  document.addEventListener('input', function(e) {
    if (e.target.id === 'search-publications') {
      const searchTerm = e.target.value;
      const sortBy = document.getElementById('sort-publications').value;
      loadPublications(searchTerm, sortBy);
    }
  });

  document.addEventListener('change', function(e) {
    if (e.target.id === 'sort-publications') {
      const searchTerm = document.getElementById('search-publications').value;
      const sortBy = e.target.value;
      loadPublications(searchTerm, sortBy);
    }
  });
});