/**
 * ErrorHandler - Gère les erreurs JSON et détecte les réponses HTML
 * Inclut un détecteur d'IA pour les mots invalides
 */

class ErrorHandler {
    /**
     * Liste de mots invalides à ignorer/nettoyer
     */
    static INVALID_WORDS = [
        'undefined', 'null', '[object Object]', 'NaN', 'function', 'script'
    ];

    /**
     * Nettoyer une chaîne des balises HTML
     */
    static stripHTML(html) {
        const tmp = document.createElement('DIV');
        tmp.innerHTML = html;
        return tmp.textContent || tmp.innerText || '';
    }

    /**
     * Vérifier si une chaîne est du HTML
     */
    static isHTML(str) {
        const htmlPattern = /<[^>]*>/g;
        return htmlPattern.test(str);
    }

    /**
     * Parser une réponse qui pourrait être HTML ou JSON
     */
    static async parseResponse(response) {
        const text = await response.text();
        
        console.log('[ErrorHandler] Réponse brute:', text.substring(0, 100));
        
        // Si c'est du HTML (erreur PHP), le nettoyer et logger
        if (this.isHTML(text)) {
            console.error('[ErrorHandler] ⚠️ Erreur HTML reçue:', text.substring(0, 200));
            const cleanedHTML = this.stripHTML(text);
            
            return {
                success: false,
                message: 'Erreur serveur: ' + cleanedHTML.substring(0, 100),
                originalError: text
            };
        }
        
        // Essayer de parser le JSON
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('[ErrorHandler] ❌ JSON invalid:', e.message);
            console.error('[ErrorHandler] Texte reçu:', text);
            
            return {
                success: false,
                message: 'Erreur de parsing JSON: ' + e.message,
                originalResponse: text
            };
        }
    }

    /**
     * Détecter et nettoyer les mots invalides dans une chaîne
     */
    static cleanString(str) {
        if (typeof str !== 'string') return '';
        
        let cleaned = str;
        
        // Supprimer les mots invalides
        this.INVALID_WORDS.forEach(word => {
            const regex = new RegExp(`\\b${word}\\b`, 'gi');
            cleaned = cleaned.replace(regex, '');
        });
        
        // Nettoyer les espaces multiples
        cleaned = cleaned.replace(/\s+/g, ' ').trim();
        
        return cleaned;
    }

    /**
     * Valider un objet JSON retourné
     */
    static validateJSON(obj) {
        if (!obj || typeof obj !== 'object') {
            return {
                valid: false,
                message: 'Réponse invalide: pas un objet'
            };
        }
        
        // Vérifier les champs courants
        if (obj.success === undefined && !obj.message) {
            return {
                valid: false,
                message: 'Structure JSON invalide'
            };
        }
        
        return {
            valid: true
        };
    }

    /**
     * Afficher une notification d'erreur nettoyée
     */
    static showError(error) {
        let message = error.message || error;
        message = this.cleanString(message);
        
        console.error('[ErrorHandler] Erreur:', message);
        
        // Afficher dans les notifications vidéo si disponible
        if (window.videoCallUI && window.videoCallUI.showNotification) {
            window.videoCallUI.showNotification('❌ ' + message, 'error');
        } else {
            alert('Erreur: ' + message);
        }
    }
}

/**
 * Wrapper pour fetch qui gère les erreurs automatiquement
 */
async function secureFetch(url, options = {}) {
    try {
        console.log('[secureFetch] Appel:', url);
        
        const response = await fetch(url, {
            credentials: 'same-origin',
            ...options
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        return await ErrorHandler.parseResponse(response);
    } catch (error) {
        console.error('[secureFetch] Erreur:', error);
        ErrorHandler.showError(error);
        throw error;
    }
}
