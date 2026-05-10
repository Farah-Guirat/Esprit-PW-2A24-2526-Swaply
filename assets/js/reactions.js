/**
 * Système de réactions aux messages
 * Gère l'affichage et l'interaction des emojis de réaction
 */

class ReactionManager {
    constructor(currentUserId) {
        this.currentUserId = currentUserId;
        this.apiUrl = '../../controller/ReactionController.php';
        this.reactions = {};
        this.init();
    }

    /**
     * Initialiser le gestionnaire
     */
    init() {
        this.loadAllowedEmojis();
        this.attachEventListeners();
    }

    /**
     * Charger la liste des emojis autorisés
     */
    loadAllowedEmojis() {
        fetch(`${this.apiUrl}?action=getEmojis`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.allowedEmojis = data.emojis;
                }
            })
            .catch(error => console.error('Erreur lors du chargement des emojis:', error));
    }

    /**
     * Attacher les écouteurs d'événements aux messages
     */
    attachEventListeners() {
        document.addEventListener('click', (e) => {
            // Clic sur le bouton de réaction
            if (e.target.closest('.reaction-btn')) {
                const btn = e.target.closest('.reaction-btn');
                const idMessage = btn.dataset.idMessage;
                this.showReactionPicker(btn, idMessage);
            }

            // Clic sur une réaction existante
            if (e.target.closest('.reaction-badge')) {
                const badge = e.target.closest('.reaction-badge');
                const idMessage = badge.dataset.idMessage;
                const emoji = badge.dataset.emoji;
                this.toggleReaction(idMessage, emoji);
            }
        });
    }

    /**
     * Afficher le picker d'emojis
     */
    showReactionPicker(btn, idMessage) {
        // Empêcher la propagation du clic
        event.stopPropagation();
        
        // Vérifier si un picker existe déjà
        const existingPicker = document.querySelector('.emoji-picker-active');
        if (existingPicker) {
            existingPicker.remove();
        }

        const picker = document.createElement('div');
        picker.className = 'emoji-picker emoji-picker-active';
        picker.style.position = 'absolute';
        picker.style.backgroundColor = '#fff';
        picker.style.border = '1px solid #ddd';
        picker.style.borderRadius = '8px';
        picker.style.padding = '8px';
        picker.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
        picker.style.zIndex = '1000';
        picker.style.display = 'flex';
        picker.style.gap = '4px';
        picker.style.flexWrap = 'wrap';
        picker.style.maxWidth = '280px';

        this.allowedEmojis.forEach(emoji => {
            const emojiBtn = document.createElement('button');
            emojiBtn.textContent = emoji;
            emojiBtn.style.fontSize = '20px';
            emojiBtn.style.border = 'none';
            emojiBtn.style.backgroundColor = '#f0f0f0';
            emojiBtn.style.borderRadius = '4px';
            emojiBtn.style.padding = '4px 8px';
            emojiBtn.style.cursor = 'pointer';
            emojiBtn.style.transition = 'background-color 0.2s';
            
            emojiBtn.addEventListener('mouseover', () => {
                emojiBtn.style.backgroundColor = '#e0e0e0';
            });
            emojiBtn.addEventListener('mouseout', () => {
                emojiBtn.style.backgroundColor = '#f0f0f0';
            });
            
            emojiBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.addReaction(idMessage, emoji);
                picker.remove();
            });

            picker.appendChild(emojiBtn);
        });

        // Positionner le picker près du bouton
        document.body.appendChild(picker);
        const rect = btn.getBoundingClientRect();
        picker.style.top = (rect.bottom + 5) + 'px';
        picker.style.left = (rect.left - 50) + 'px';

        // Fermer le picker en cliquant en dehors
        setTimeout(() => {
            document.addEventListener('click', function closeOnClickOutside(e) {
                if (!picker.contains(e.target) && !btn.contains(e.target)) {
                    picker.remove();
                    document.removeEventListener('click', closeOnClickOutside);
                }
            });
        }, 0);
    }

    /**
     * Ajouter une réaction
     */
    addReaction(idMessage, emoji) {
        fetch(this.apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'add',
                id_message: idMessage,
                emoji: emoji,
                id_user: this.currentUserId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.updateReactionDisplay(idMessage, data.reactions);
            } else {
                alert('Erreur: ' + data.error);
            }
        })
        .catch(error => console.error('Erreur lors de l\'ajout de la réaction:', error));
    }

    /**
     * Basculer une réaction (ajouter ou supprimer)
     */
    toggleReaction(idMessage, emoji) {
        fetch(this.apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'remove',
                id_message: idMessage,
                emoji: emoji,
                id_user: this.currentUserId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.updateReactionDisplay(idMessage, data.reactions);
            } else {
                alert('Erreur: ' + data.error);
            }
        })
        .catch(error => console.error('Erreur lors de la suppression de la réaction:', error));
    }

    /**
     * Mettre à jour l'affichage des réactions pour un message
     */
    updateReactionDisplay(idMessage, reactions) {
        const messageElement = document.querySelector(`[data-message-id="${idMessage}"]`);
        if (!messageElement) return;

        const reactionsContainer = messageElement.querySelector('.reactions-container');
        if (!reactionsContainer) return;

        // Effacer les réactions existantes
        reactionsContainer.innerHTML = '';

        // Afficher les nouvelles réactions
        reactions.forEach(reaction => {
            const badge = document.createElement('span');
            badge.className = 'reaction-badge';
            badge.dataset.idMessage = idMessage;
            badge.dataset.emoji = reaction.emoji;
            badge.style.display = 'inline-block';
            badge.style.backgroundColor = '#f0f0f0';
            badge.style.borderRadius = '12px';
            badge.style.padding = '4px 8px';
            badge.style.marginRight = '4px';
            badge.style.marginBottom = '4px';
            badge.style.cursor = 'pointer';
            badge.style.fontSize = '14px';
            badge.style.fontWeight = '500';
            badge.style.transition = 'background-color 0.2s';

            // Vérifier si l'utilisateur actuel a réagi avec cet emoji
            const userIds = reaction.users.split(',').map(id => parseInt(id));
            const userHasReacted = userIds.includes(this.currentUserId);

            if (userHasReacted) {
                badge.style.backgroundColor = '#e3f2fd';
                badge.style.color = '#1976d2';
            }

            badge.addEventListener('mouseover', () => {
                badge.style.backgroundColor = userHasReacted ? '#bbdefb' : '#e0e0e0';
            });
            badge.addEventListener('mouseout', () => {
                badge.style.backgroundColor = userHasReacted ? '#e3f2fd' : '#f0f0f0';
            });

            badge.textContent = `${reaction.emoji} ${reaction.count}`;
            reactionsContainer.appendChild(badge);
        });
    }

    /**
     * Charger et afficher les réactions d'un message
     */
    loadReactions(idMessage) {
        fetch(`${this.apiUrl}?action=get&id_message=${idMessage}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.updateReactionDisplay(idMessage, data.reactions);
                }
            })
            .catch(error => console.error('Erreur lors du chargement des réactions:', error));
    }
}

// Initialiser le gestionnaire quand le DOM est prêt
document.addEventListener('DOMContentLoaded', () => {
    // Le currentUserId doit être défini dans votre vue
    if (typeof currentUserId !== 'undefined') {
        window.reactionManager = new ReactionManager(currentUserId);
    }
});
