function showPage(page) {
  document.querySelectorAll('.menu-item').forEach(item => {
    item.classList.remove('active');
  });

  const activeMenu = document.getElementById('menu-' + page);
  if (activeMenu) activeMenu.classList.add('active');

  document.getElementById('page-title').textContent = 
    page === 'dashboard' ? 'Dashboard' : page.charAt(0).toUpperCase() + page.slice(1);

  const header = document.getElementById('main-header');

  if (page === 'dashboard') {
    document.getElementById('dashboard-page').classList.remove('hidden');
    document.getElementById('other-pages').classList.add('hidden');

    header.classList.remove('hidden'); // 
  } else {
    document.getElementById('dashboard-page').classList.add('hidden');
    document.getElementById('other-pages').classList.remove('hidden');

    header.classList.add('hidden'); // 
    document.getElementById('other-pages').innerHTML = ""; // page vide
  }
}

document.addEventListener('DOMContentLoaded', () => {
  showPage('dashboard');
});