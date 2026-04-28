function showPage(page) {

  document.querySelectorAll('.menu-item').forEach(item => {
    item.classList.remove('active');
  });

  document.getElementById('menu-' + page).classList.add('active');

  document.getElementById('page-title').textContent =
    page.charAt(0).toUpperCase() + page.slice(1);

  const home = document.getElementById('home-page');
  const other = document.getElementById('other-pages');

  if (page === 'home') {
    home.classList.remove('hidden');
    other.classList.add('hidden');
    return;
  }

  home.classList.add('hidden');
  other.classList.remove('hidden');

  let content = "";

  switch(page) {

    case "explore":
      content = "<h3>Explorer les compétences</h3><p>Liste des utilisateurs et compétences...</p>";
      break;

   case "publish":
  content = `
    <h3>Publier</h3>

    <button onclick="showForm()" class="btn-primary">
      + Créer une publication
    </button>

    <div id="post-form" class="hidden form-box">

      <textarea placeholder="Exprimez votre besoin..."></textarea>

      <input type="file" id="imageUpload" multiple accept="image/*" onchange="previewImages(event)">

      <div id="preview" class="preview-container"></div>

      <button onclick="submitPost()">Publier</button>

    </div>
  `;
  break;

    case "messages":
      content = "<h3>Messages</h3><p>Conversations avec les utilisateurs...</p>";
      break;

    case "exchanges":
      content = "<h3>Mes échanges</h3><p>Historique des échanges...</p>";
      break;

    case "profile":
      content = "<h3>Mon profil</h3><p>Modifier vos informations...</p>";
      break;

    case "settings":
      content = "<h3>Paramètres</h3><p>Changer mot de passe...</p>";
      break;
  }

  other.innerHTML = content;
}

document.addEventListener('DOMContentLoaded', () => {
  showPage('home');
});
function showForm() {
  document.getElementById("post-form").classList.remove("hidden");
}

function submitPost() {
  const text = document.querySelector("#post-form textarea").value;
  const files = document.getElementById("imageUpload").files;

  console.log("Texte :", text);
  console.log("Images :", files);

  alert("Publication envoyée !");
}
function previewImages(event) {
  const preview = document.getElementById("preview");
  preview.innerHTML = "";

  const files = event.target.files;

  for (let i = 0; i < files.length; i++) {
    const file = files[i];

    const reader = new FileReader();

    reader.onload = function(e) {
      const img = document.createElement("img");
      img.src = e.target.result;
      img.classList.add("preview-img");
      preview.appendChild(img);
    };

    reader.readAsDataURL(file);
  }
}