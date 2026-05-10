function showPage(page) {
  document.querySelectorAll('.menu-item').forEach(item => {
    item.classList.remove('active');
  });

  const activeMenu = document.getElementById('menu-' + page);
  if (activeMenu) activeMenu.classList.add('active');

  const pageTitle = document.getElementById('page-title');
  if (pageTitle) {
    pageTitle.textContent = page === 'dashboard' ? 'Dashboard' : page.charAt(0).toUpperCase() + page.slice(1);
  }

  const header = document.getElementById('main-header');
  const dashboardPage = document.getElementById('dashboard-page');
  const otherPages = document.getElementById('other-pages');

  if (page === 'dashboard' && dashboardPage && otherPages) {
    dashboardPage.classList.remove('hidden');
    otherPages.classList.add('hidden');
    if (header) header.classList.remove('hidden');
  } else if (otherPages) {
    if (dashboardPage) dashboardPage.classList.add('hidden');
    otherPages.classList.remove('hidden');
    if (header) header.classList.add('hidden');
    otherPages.innerHTML = "";
  }
}

document.addEventListener('DOMContentLoaded', () => {
  if (document.getElementById('dashboard-page')) {
    showPage('dashboard');
  }
});