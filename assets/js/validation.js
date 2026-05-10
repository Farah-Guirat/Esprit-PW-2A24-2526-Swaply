// validation.js – Contrôles de saisie côté client pour Swaply Messagerie
// (Les contrôles HTML5 ne sont PAS utilisés – validation manuelle JS uniquement)

/**
 * Valide qu'un champ textarea ou input n'est pas vide et respecte la longueur max.
 * @param {string} fieldId - ID de l'élément
 * @param {string} errorId - ID du span d'erreur
 * @param {number} maxLen  - Longueur maximale
 * @returns {boolean}
 */
function validateTextRequired(fieldId, errorId, maxLen = 2000) {
    const field = document.getElementById(fieldId);
    const errEl = document.getElementById(errorId);
    if (!field || !errEl) return true;

    const val = field.value.trim();
    if (val.length === 0) {
        showError(field, errEl, 'Ce champ est obligatoire.');
        return false;
    }
    if (val.length > maxLen) {
        showError(field, errEl, `Ce champ ne peut pas dépasser ${maxLen} caractères.`);
        return false;
    }
    clearError(field, errEl);
    return true;
}

/**
 * Valide qu'un select a une valeur sélectionnée (non vide).
 * @param {string} fieldId
 * @param {string} errorId
 * @returns {boolean}
 */
function validateSelectRequired(fieldId, errorId) {
    const field = document.getElementById(fieldId);
    const errEl = document.getElementById(errorId);
    if (!field || !errEl) return true;

    if (!field.value || field.value === '') {
        showError(field, errEl, 'Veuillez sélectionner une option.');
        return false;
    }
    clearError(field, errEl);
    return true;
}

/**
 * Valide un champ email (format basique).
 * @param {string} fieldId
 * @param {string} errorId
 * @returns {boolean}
 */
function validateEmail(fieldId, errorId) {
    const field = document.getElementById(fieldId);
    const errEl = document.getElementById(errorId);
    if (!field || !errEl) return true;

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(field.value.trim())) {
        showError(field, errEl, 'Veuillez saisir une adresse email valide.');
        return false;
    }
    clearError(field, errEl);
    return true;
}

/**
 * Affiche une erreur sur un champ.
 */
function showError(field, errEl, message) {
    field.classList.add('error-field');
    errEl.textContent = message;
    errEl.classList.add('visible');
}

/**
 * Efface l'erreur d'un champ.
 */
function clearError(field, errEl) {
    field.classList.remove('error-field');
    errEl.classList.remove('visible');
    errEl.textContent = '';
}
