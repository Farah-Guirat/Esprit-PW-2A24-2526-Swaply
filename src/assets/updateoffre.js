document.addEventListener("DOMContentLoaded", () => {

    // ================= ELEMENTS =================
    const titre = document.getElementById('titre');
    const description = document.getElementById('description');
    const categorie = document.getElementById('categorie');
    const niveau = document.getElementById('niveau');
    const dateLimite = document.getElementById('dateLimite');
    const form = document.getElementById('offreForm');
    const submitBtn = document.getElementById('submitBtn');

    const loader = document.getElementById("loader");
    const toastContainer = document.getElementById("toast-container");

    // ================= TOAST =================
    function showToast(message, type = "success") {

        if (!toastContainer) return;

        const colors = {
            success: "bg-green-500",
            error: "bg-red-500",
            warning: "bg-yellow-500",
            info: "bg-teal-500"
        };

        const toast = document.createElement("div");

        toast.className = `${colors[type]} text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-2`;

        toast.innerHTML = `<span>${message}</span>`;

        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = "0";
            toast.style.transform = "translateX(50px)";
            toast.style.transition = "0.4s";
            setTimeout(() => toast.remove(), 400);
        }, 3000);
    }

    // ================= LOADER =================
    if (form) {
        form.addEventListener("submit", (e) => {

            // si invalide → stop submit
            if (submitBtn.disabled) {
                e.preventDefault();
                showToast("Veuillez corriger le formulaire", "error");
                return;
            }

            loader.classList.remove("hidden");
            submitBtn.disabled = true;
            submitBtn.textContent = "Enregistrement...";
        });
    }

    // ================= VALIDATION =================
    function validateForm() {

        let valid = true;

        if (titre.value.trim().length < 5) valid = false;
        if (description.value.trim().length < 10) valid = false;
        if (!categorie.value) valid = false;
        if (!niveau.value) valid = false;

        const today = new Date().toISOString().split('T')[0];
        if (!dateLimite.value || dateLimite.value < today) valid = false;

        submitBtn.disabled = !valid;
        submitBtn.style.opacity = valid ? "1" : "0.5";
    }

    // ================= EVENTS =================
    titre.addEventListener('input', () => {
        document.getElementById('error-titre').textContent =
            titre.value.length < 5 ? "Min 5 caractères" : "";
        validateForm();
    });

    description.addEventListener('input', () => {
        document.getElementById('error-description').textContent =
            description.value.length < 10 ? "Min 10 caractères" : "";
        validateForm();
    });

    categorie.addEventListener('change', () => {
        document.getElementById('error-categorie').textContent =
            !categorie.value ? "Choisir catégorie" : "";
        validateForm();
    });

    niveau.addEventListener('change', () => {
        document.getElementById('error-niveau').textContent =
            !niveau.value ? "Choisir niveau" : "";
        validateForm();
    });

    dateLimite.addEventListener('change', () => {

        const today = new Date().toISOString().split('T')[0];

        document.getElementById('error-date').textContent =
            (!dateLimite.value || dateLimite.value < today)
                ? "Date invalide"
                : "";

        validateForm();
    });

    // ================= INIT =================
    dateLimite.setAttribute('min', new Date().toISOString().split('T')[0]);
    validateForm();
});