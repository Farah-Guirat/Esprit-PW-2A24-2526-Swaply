let allOffers = [];
let currentFilter = "all";

let deleteId = null;
let blockId = null;

// ================= TOASTR CONFIG =================
toastr.options = {
  closeButton: true,
  progressBar: true,
  positionClass: "toast-top-right",
  timeOut: 3000
};

// ================= LOAD =================
async function loadAnnonces() {
  try {
    const res = await fetch("get_offres.php");
    allOffers = await res.json();

    renderTable();
    updateStatistics();

  } catch (error) {
    console.error("Erreur fetch:", error);
  }
}

// ================= STATUS =================
function getStatusClass(statut) {

  const s = (statut || "").toLowerCase().trim();

  if (s === "active")
    return "bg-green-500 text-white";

  if (s === "bloquée" || s === "bloquee" || s === "blocked")
    return "bg-gray-500 text-white";

  if (s === "fermée" || s === "fermee")
    return "bg-orange-500 text-white";

  if (s === "expirée" || s === "expiree")
    return "bg-red-500 text-white";

  if (s === "banned")
    return "bg-purple-600 text-white";

  return "bg-gray-200 text-gray-800";
}

function getStatusLabel(statut) {

  const s = (statut || "").toLowerCase().trim();

  if (s === "banned") return "Bloqué";
  if (s === "active") return "Active";
  if (s === "bloquée" || s === "bloquee" || s === "blocked") return "Bloquée";
  if (s === "fermée" || s === "fermee") return "Fermée";
  if (s === "expirée" || s === "expiree") return "Expirée";

  return statut;
}

// ================= RENDER =================
function renderTable() {

  const tbody = document.getElementById("table-body");
  tbody.innerHTML = "";

  let filtered = allOffers;

  if (currentFilter === "offres") {
    filtered = allOffers.filter(a => a.type === "Offre");
  } else if (currentFilter === "demandes") {
    filtered = allOffers.filter(a => a.type === "Demande");
  } else if (currentFilter === "expirees") {
    filtered = allOffers.filter(a =>
      a.statut === "Expirée" || a.statut === "Fermée"
    );
  }

  filtered.forEach((a) => {

    const statusClass = getStatusClass(a.statut);
    const statusLabel = getStatusLabel(a.statut);

    const tr = document.createElement("tr");
    tr.className = "hover:bg-gray-50 transition";

    tr.innerHTML = `
      <td class="px-8 py-5 font-medium">${a.titre}</td>

      <td class="px-8 py-5">
        <span class="${getRandomLevelClass(a.niveau)} px-3 py-1 rounded-full text-xs">
          ${a.niveau}
        </span>
      </td>

      <td class="px-8 py-5">
        ${a.date_limite || a.date_creation || a.date}
      </td>

      <td class="px-8 py-5 text-center">
        <span class="${statusClass} px-3 py-1 rounded-full text-xs font-medium">
          ${statusLabel}
        </span>
      </td>

      <td class="px-8 py-5 text-center flex gap-2 justify-center">


<button onclick="viewDetails(${a.id_offre})"
    class="text-blue-500 hover:text-blue-700">
    <i class="fa-solid fa-eye"></i>
  </button>

        <button onclick="openDeleteModal(${a.id_offre})"
          class="text-red-500 hover:text-red-700">
          <i class="fa-solid fa-trash"></i>
        </button>

        <button onclick="openBlockModal(${a.id_offre})"
          class="text-purple-600 hover:text-purple-800">
          <i class="fa-solid fa-lock"></i>
        </button>

      </td>
    `;

    tbody.appendChild(tr);
  });
}

// ================= STATISTICS =================
function updateStatistics() {

  // TOTAL
  document.getElementById("total-annonces").textContent = allOffers.length;

  // OFFRES ACTIVES
  const offresActives = allOffers.filter(a =>
  a.statut === "active" &&
  (a.statut || "").toLowerCase().trim() === "active"
);

document.getElementById("offres-actives").textContent = offresActives.length;


  // DEMANDES ACTIVES
  document.getElementById("demandes-actives").textContent =
    allOffers.filter(a =>
      a.type === "Demande" &&
      (a.statut || "").toLowerCase() === "active"
    ).length;

  // EXPIRÉES + FERMÉES + BLOQUÉES
  document.getElementById("expirees-fermees").textContent =
    allOffers.filter(a => {
      const s = (a.statut || "").toLowerCase();
      return s === "expirée"
          || s === "expiree"
          || s === "fermée"
          || s === "fermee"
          || s === "bloquée"
          || s === "bloquee"
          || s === "banned";
    }).length;

  // 🔥 BONUS (si tu veux afficher dans console)
  console.log("Offres actives =", offresActives.length);
}
// ================= FILTER =================
function filterByType(type) {
  currentFilter = type;
  renderTable();
}

// ================= DELETE =================
function openDeleteModal(id) {
  deleteId = id;
  document.getElementById("deleteConfirmModal").classList.remove("hidden");
}

function cancelDelete() {
  deleteId = null;
  document.getElementById("deleteConfirmModal").classList.add("hidden");
}

async function confirmDelete() {

  if (!deleteId) return;

  try {
    const res = await fetch("delete_offre.php?id_offre=" + deleteId);
    const data = await res.json();

    if (data.success) {
      toastr.success("Annonce supprimée avec succès");
      await loadAnnonces();
    } else {
      toastr.error("Erreur suppression");
    }

  } catch (error) {
    console.error(error);
    toastr.error("Erreur serveur");
  }

  cancelDelete();
}

// ================= BLOCK =================
function openBlockModal(id) {
  blockId = id;
  document.getElementById("blockConfirmModal").classList.remove("hidden");
}

function cancelBlock() {
  blockId = null;
  document.getElementById("blockConfirmModal").classList.add("hidden");
}

async function confirmBlock() {

  if (!blockId) return;

  try {
    const res = await fetch("block_offre.php?id_offre=" + blockId);
    const data = await res.json();

    if (data.success) {
      toastr.warning("Annonce bloquée");

      const offer = allOffers.find(a => a.id_offre == blockId);
      if (offer) offer.statut = "Bloquée";

      renderTable();
      updateStatistics();

    } else {
      toastr.error("Erreur blocage");
    }

  } catch (error) {
    console.error(error);
    toastr.error("Erreur serveur");
  }

  cancelBlock();
}

// ================= SEARCH =================
function filterTable() {
  const value = document.getElementById("search").value.toLowerCase().trim();

  const filtered = allOffers.filter(a =>
    (a.titre || "").toLowerCase().includes(value)
  );

  renderFilteredTable(filtered);
}









function renderFilteredTable(list) {

  const tbody = document.getElementById("table-body");
  tbody.innerHTML = "";

  list.forEach((a) => {

    const statusClass = getStatusClass(a.statut);
    const statusLabel = getStatusLabel(a.statut);

    const tr = document.createElement("tr");
    tr.className = "hover:bg-gray-50 transition";

    tr.innerHTML = `
      <td class="px-8 py-5 font-medium">${a.titre}</td>

      <td class="px-8 py-5">
        <span class="${getRandomLevelClass(a.niveau)} px-3 py-1 rounded-full text-xs">
          ${a.niveau}
        </span>
      </td>

      <td class="px-8 py-5">
        ${a.date_limite || a.date_creation || a.date}
      </td>

      <td class="px-8 py-5 text-center">
        <span class="${statusClass} px-3 py-1 rounded-full text-xs font-medium">
          ${statusLabel}
        </span>
      </td>

      <td class="px-8 py-5 text-center flex gap-2 justify-center">
      <button onclick="viewDetails(${a.id_offre})"
    class="text-blue-500 hover:text-blue-700">
    <i class="fa-solid fa-eye"></i>
  </button>
        <button onclick="openDeleteModal(${a.id_offre})" class="text-red-500">
          <i class="fa-solid fa-trash"></i>
        </button>

        <button onclick="openBlockModal(${a.id_offre})" class="text-purple-600">
          <i class="fa-solid fa-lock"></i>
        </button>
      </td>
    `;

    tbody.appendChild(tr);
  });
}



// ================= LEVEL COLORS =================
function getRandomLevelClass(text) {

  const colors = [
    "bg-blue-100 text-blue-700",
    "bg-green-100 text-green-700",
    "bg-amber-100 text-amber-700",
    "bg-purple-100 text-purple-700",
    "bg-pink-100 text-pink-700",
    "bg-indigo-100 text-indigo-700",
    "bg-red-100 text-red-700"
  ];

  let hash = 0;
  for (let i = 0; i < (text || "").length; i++) {
    hash = text.charCodeAt(i) + ((hash << 5) - hash);
  }

  return colors[Math.abs(hash) % colors.length];
}

// ================= INIT =================
loadAnnonces();












// ================= VIEW DETAILS =================
function viewDetails(id) {
    if (!id) return;
    window.location.href = `offreDetails.html?id_offre=${id}`;
}