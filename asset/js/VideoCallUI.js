/**
 * VideoCallUI - Interface utilisateur des appels vidéo Swaply
 * FIXED VERSION
 */

class VideoCallUI {
    constructor(userId, socketUrl = null) {
        this.userId = parseInt(userId);
        // Utiliser socketUrl passé, ou la variable globale, ou par défaut localhost:3000
        this.socketUrl = socketUrl || window.socketUrl || 'http://localhost:3000';
        this.videoManager = null;
        this.currentCallId = null;
        this.currentConversationId = null;
        this.callStartTime = null;
        this.callDurationInterval = null;
        this.participants = new Map();
        this.isMinimized = false;
        this._callTimeoutId = null; // Pour le timeout d'appel sans réponse
        this.incomingCallerInfo = null; // Infos du correspondant entrant
        this.outgoingCalleeInfo = null;  // Infos du destinataire pour appel sortant

        this.initUI();
    }

    initUI() {
        // FIX: Le HTML est déjà inclus via <?php include_once 'VideoCallUI.html' ?>
        // On initialise directement le VideoManager
        try {
            this.videoManager = new VideoCallManager(this.socketUrl, this.userId);
            this.setupVideoManagerCallbacks();
            console.log('[VideoCallUI] ✓ VideoCallManager initialisé avec succès pour user', this.userId);
        } catch (e) {
            console.error('[VideoCallUI] Erreur lors de l\'initialisation VideoCallManager:', e);
            this.showNotification('⚠️ Impossible de démarrer le système d\'appel vidéo', 'error');
        }

        // Attendre que le DOM soit prêt pour les listeners
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupEventListeners());
        } else {
            this.setupEventListeners();
        }
    }

    setupVideoManagerCallbacks() {
        this.videoManager.onRemoteStream        = (d) => this.handleRemoteStream(d);
        this.videoManager.onRemoteStreamRemoved = (d) => this.handleRemoteStreamRemoved(d);
        this.videoManager.onCallIncoming        = (d) => this.handleIncomingCall(d);
        this.videoManager.onCallAccepted        = (d) => this.handleCallAccepted(d);
        this.videoManager.onCallRejected        = (d) => this.handleCallRejected(d);
        this.videoManager.onCallEnded           = (d) => this.handleCallEnded(d);
        this.videoManager.onUserJoined          = (d) => this.handleUserJoined(d);
        this.videoManager.onUserLeft            = (d) => this.handleUserLeft(d);
        this.videoManager.onError               = (e) => this.handleError(e);
    }

    setupEventListeners() {
        const toggleAudioBtn  = document.getElementById('toggleAudioBtn');
        const toggleVideoBtn  = document.getElementById('toggleVideoBtn');
        const toggleScreenBtn = document.getElementById('toggleScreenBtn');
        const endCallBtn      = document.getElementById('endCallBtn');
        const acceptBtn       = document.getElementById('acceptIncomingBtn');
        const rejectBtn       = document.getElementById('rejectIncomingBtn');

        if (toggleAudioBtn)  toggleAudioBtn.addEventListener('click', () => this.toggleAudio());
        if (toggleVideoBtn)  toggleVideoBtn.addEventListener('click', () => this.toggleVideo());
        if (toggleScreenBtn) toggleScreenBtn.addEventListener('click', () => this.toggleScreenShare());
        if (endCallBtn)      endCallBtn.addEventListener('click', () => this.endCall());
        if (acceptBtn)       acceptBtn.addEventListener('click', () => this.acceptIncomingCall());
        if (rejectBtn)       rejectBtn.addEventListener('click', () => this.rejectIncomingCall());

        window.addEventListener('beforeunload', (e) => {
            if (this.videoManager?.currentCallId) {
                e.preventDefault();
                e.returnValue = 'Un appel vidéo est en cours.';
            }
        });
    }

    /**
     * FIX: Lancer un appel vidéo avec les infos de l'interlocuteur
     * AMÉLIORATION: Envoyer la demande d'appel AVANT de demander les permissions
     * pour un meilleur UX - l'autre utilisateur est notifié rapidement
     */
    async initiateCall(conversationId, interlocuteurId = null, nom = '', prenom = '', forceNew = false) {
        try {
            // Utiliser les variables globales si non passées
            const interlId = interlocuteurId || window.currentInterlocuteurId || 0;
            const interlNom    = nom    || window.currentInterlocuteurNom    || '';
            const interlPrenom = prenom || window.currentInterlocuteurPrenom || '';

            console.log('[VideoCallUI] Lancement appel vers user', interlId, `(${interlPrenom} ${interlNom})`);

            if (!interlId) {
                throw new Error('ID de l\'interlocuteur manquant');
            }

            this.currentConversationId = parseInt(conversationId);
            
            // Stocker les infos du destinataire pour enregistrer les événements
            this.outgoingCalleeInfo = {
                id_user: interlId,
                nom: interlNom,
                prenom: interlPrenom
            };

            // ÉTAPE 1: Envoyer la demande d'appel IMMÉDIATEMENT
            console.log('[VideoCallUI] Envoi de la demande d\'appel' + (forceNew ? ' (force)' : '') + '...');
            this.showNotification('📞 Demande d\'appel envoyée...', 'info');

            // Initier via le manager (passe l'interlocuteur)
            await this.videoManager.initiateCall(
                conversationId,
                '1to1',
                interlId,
                interlNom,
                interlPrenom,
                forceNew
            );

            // ÉTAPE 2: Afficher le conteneur vidéo
            this.showVideoCallContainer('Appel en attente...');

            // ÉTAPE 3: Demander les permissions
            try {
                console.log('[VideoCallUI] Demande d\'accès aux médias...');
                const stream = await this.videoManager.getLocalStream();
                this.displayLocalVideo(stream);
                this.showNotification('📞 Appel en cours... En attente de réponse', 'info');
            } catch (mediaError) {
                console.warn('[VideoCallUI] Impossible d\'accéder aux médias:', mediaError);
                this.showNotification('⚠️ Caméra/Microphone non disponible. L\'appel continue quand même...', 'warning');
            }

            // ÉTAPE 4: Ajouter un timeout - raccrocher après 30s sans réponse
            this._callTimeoutId = setTimeout(() => {
                console.log('[VideoCallUI] Timeout d\'appel - pas de réponse');
                this.showNotification('Pas de réponse après 30 secondes', 'info');
                this.endCall();
            }, 30000);

        } catch (error) {
            console.error('[VideoCallUI] Erreur initiateCall:', error);
            
            // Si c'est une erreur 409 (Conflict), proposer une option de retry
            if (error.message && error.message.includes('409')) {
                this.showNotification('❌ Un appel précédent est encore en cours...', 'error');
                this.showRetryButton();
            } else {
                this.showNotification('Erreur: ' + error.message, 'error');
            }
            
            this.hideVideoCallContainer();
        }
    }

    showRetryButton() {
        const container = document.getElementById('videoCallContainer');
        if (container) {
            // Créer un bouton de retry
            let btn = document.getElementById('retryCallBtn');
            if (!btn) {
                btn = document.createElement('button');
                btn.id = 'retryCallBtn';
                btn.className = 'action-btn';
                btn.innerHTML = '🔄 Réessayer (Force)';
                btn.style.cssText = `
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    padding: 12px 20px;
                    background: #ff9800;
                    color: white;
                    border: none;
                    border-radius: 25px;
                    cursor: pointer;
                    z-index: 1000;
                    font-weight: 600;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                `;
                btn.onclick = () => {
                    btn.remove();
                    // Réessayer avec force_new = true
                    this.initiateCall(
                        window.currentConversationId,
                        window.currentInterlocuteurId,
                        window.currentInterlocuteurNom,
                        window.currentInterlocuteurPrenom,
                        true // force = true
                    );
                };
                document.body.appendChild(btn);
            }
        }
    }

    showVideoCallContainer(participantName = '') {
        const container = document.getElementById('videoCallContainer');
        if (container) {
            container.classList.remove('hidden');
            if (participantName) {
                const el = document.getElementById('callParticipant');
                if (el) el.textContent = participantName;
            }
            this.startCallDurationTimer();
        }
    }

    hideVideoCallContainer() {
        const container = document.getElementById('videoCallContainer');
        if (container) {
            container.classList.add('hidden');
            this.stopCallDurationTimer();
        }
    }

    displayLocalVideo(stream) {
        const localVideo = document.getElementById('localVideo');
        if (localVideo) {
            localVideo.srcObject = stream;
            localVideo.play().catch(e => console.warn('[VideoCallUI] Autoplay bloqué:', e));
        }
    }

    handleRemoteStream(data) {
        const { userId, stream } = data;
        console.log('[VideoCallUI] Flux distant reçu de', userId);

        const remoteGrid = document.getElementById('remoteVideoGrid');
        if (!remoteGrid) return;

        let container = document.getElementById(`remote-video-${userId}`);
        if (!container) {
            container = document.createElement('div');
            container.id = `remote-video-${userId}`;
            container.className = 'video-container';

            const video = document.createElement('video');
            video.autoplay = true;
            video.playsinline = true;

            const label = document.createElement('div');
            label.className = 'video-label';
            label.textContent = `Participant`;

            container.appendChild(video);
            container.appendChild(label);
            remoteGrid.appendChild(container);
        }

        const video = container.querySelector('video');
        video.srcObject = stream;
        video.play().catch(e => console.warn('[VideoCallUI] Autoplay distant:', e));

        this.participants.set(userId, { stream, container });
        this.updateParticipantList();
    }

    handleRemoteStreamRemoved(data) {
        const container = document.getElementById(`remote-video-${data.userId}`);
        if (container) container.remove();
        this.participants.delete(data.userId);
        this.updateParticipantList();
    }

    /**
     * FIX: Afficher le widget d'appel entrant avec le nom de l'appelant
     */
    handleIncomingCall(data) {
        const { id_video_call, id_initiateur, initiateur_nom, initiateur_prenom } = data;
        console.log('[VideoCallUI] Appel entrant de', initiateur_nom, initiateur_prenom);

        this.currentCallId = parseInt(id_video_call);
        // Stocker les infos de l'appelant pour l'enregistrement
        this.incomingCallerInfo = {
            id_initiateur,
            initiateur_nom,
            initiateur_prenom
        };

        const widget = document.getElementById('incomingCallWidget');
        if (widget) {
            const callerNameEl = document.getElementById('incomingCallerName');
            if (callerNameEl) {
                const name = [initiateur_prenom, initiateur_nom].filter(Boolean).join(' ') || `Utilisateur #${id_initiateur}`;
                callerNameEl.textContent = name;
            }
            widget.classList.remove('hidden');
        }

        this.playRingtone();
        const fullName = [initiateur_prenom, initiateur_nom].filter(Boolean).join(' ') || 'Quelqu\'un';
        this.showNotification(`📞 Appel entrant de ${fullName}`, 'info');
    }

    async acceptIncomingCall() {
        if (!this.currentCallId) return;

        try {
            const widget = document.getElementById('incomingCallWidget');
            if (widget) widget.classList.add('hidden');

            this.stopRingtone();
            this.showVideoCallContainer('Connexion...');
            this.showNotification('✓ Appel accepté — accès aux médias...', 'success');

            try {
                const stream = await this.videoManager.getLocalStream();
                this.displayLocalVideo(stream);
            } catch (mediaError) {
                console.warn('[VideoCallUI] Impossible d\'accéder aux médias:', mediaError);
                this.showNotification('⚠️ Caméra/Microphone non disponible', 'warning');
                // On continue quand même
            }

            await this.videoManager.acceptCall(this.currentCallId);
            
            // Enregistrer l'événement dans la conversation
            await this.logCallEvent('accepted');
            
            this.showNotification('✓ Connexion WebRTC en cours...', 'success');
        } catch (error) {
            console.error('[VideoCallUI] Erreur acceptIncomingCall:', error);
            this.showNotification('Erreur: ' + error.message, 'error');
            this.hideVideoCallContainer();
        }
    }

    async rejectIncomingCall() {
        if (!this.currentCallId) return;

        const widget = document.getElementById('incomingCallWidget');
        if (widget) widget.classList.add('hidden');
        this.stopRingtone();

        await this.videoManager.rejectCall(this.currentCallId);
        
        // Enregistrer l'événement dans la conversation
        await this.logCallEvent('rejected');
        
        this.currentCallId = null;
        this.showNotification('Appel rejeté', 'info');
    }

    handleCallAccepted(data) {
        console.log('[VideoCallUI] Appel accepté par', data.id_user);
        
        // Annuler le timeout si l'appel a été rejeté
        if (this._callTimeoutId) {
            clearTimeout(this._callTimeoutId);
            this._callTimeoutId = null;
        }
        
        // Enregistrer l'événement pour l'initiateur
        if (this.outgoingCalleeInfo) {
            this.logCallEventForOutgoing('accepted');
        }
        
        this.showNotification('✓ Appel accepté — connexion WebRTC en cours...', 'success');
        const el = document.getElementById('callParticipant');
        if (el) el.textContent = 'En communication';
    }

    handleCallRejected(data) {
        console.log('[VideoCallUI] Appel rejeté');
        
        // Annuler le timeout
        if (this._callTimeoutId) {
            clearTimeout(this._callTimeoutId);
            this._callTimeoutId = null;
        }
        
        // Enregistrer l'événement pour l'initiateur
        if (this.outgoingCalleeInfo) {
            this.logCallEventForOutgoing('rejected');
        }
        
        this.hideVideoCallContainer();
        this.showNotification('Appel refusé', 'warning');
        this.cleanupUI();
    }

    handleCallEnded(data) {
        console.log('[VideoCallUI] Appel terminé');
        this.hideVideoCallContainer();
        this.showNotification('Appel terminé', 'info');
        this.cleanupUI();
    }

    handleUserJoined(data) {
        this.showNotification('Participant rejoint', 'info');
    }

    handleUserLeft(data) {
        this.showNotification('Participant parti', 'info');
        if (!data.remaining_participants || data.remaining_participants.length <= 1) {
            setTimeout(() => this.endCall(), 2000);
        }
    }

    handleError(error) {
        console.error('[VideoCallUI] Erreur:', error);
        this.showNotification('Erreur: ' + (error.message || error), 'error');
    }

    /**
     * Enregistrer un événement d'appel vidéo dans la conversation
     * @param {string} event - Type d'événement: 'accepted', 'rejected', 'missed', 'ended'
     */
    async logCallEvent(event) {
        try {
            if (!this.currentCallId || !this.incomingCallerInfo) return;

            const body = new URLSearchParams();
            body.append('id_video_call', this.currentCallId);
            body.append('event', event);
            body.append('initiateur_nom', this.incomingCallerInfo.initiateur_nom);
            body.append('initiateur_prenom', this.incomingCallerInfo.initiateur_prenom);

            const result = await secureFetch('../../controller/VideoCallController.php?action=logCallEvent', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            });

            if (result.success) {
                console.log('[VideoCallUI] ✓ Événement enregistré:', event);
            } else {
                console.warn('[VideoCallUI] Erreur enregistrement événement:', result.message);
            }
        } catch (error) {
            console.error('[VideoCallUI] Erreur logCallEvent:', error);
        }
    }

    /**
     * Enregistrer un événement d'appel vidéo pour l'initiateur (appel sortant)
     * @param {string} event - Type d'événement: 'accepted', 'rejected'
     */
    async logCallEventForOutgoing(event) {
        try {
            if (!this.currentCallId || !this.outgoingCalleeInfo) return;

            const body = new URLSearchParams();
            body.append('id_video_call', this.currentCallId);
            body.append('event', event);
            body.append('initiateur_nom', this.outgoingCalleeInfo.nom);
            body.append('initiateur_prenom', this.outgoingCalleeInfo.prenom);

            const result = await secureFetch('../../controller/VideoCallController.php?action=logCallEvent', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            });

            if (result.success) {
                console.log('[VideoCallUI] ✓ Événement enregistré (sortant):', event);
            } else {
                console.warn('[VideoCallUI] Erreur enregistrement événement sortant:', result.message);
            }
        } catch (error) {
            console.error('[VideoCallUI] Erreur logCallEventForOutgoing:', error);
        }
    }

    toggleAudio() {
        const enabled = this.videoManager.toggleAudio();
        const btn = document.getElementById('toggleAudioBtn');
        if (btn) {
            btn.classList.toggle('muted', !enabled);
            btn.title = enabled ? 'Microphone (activé)' : 'Microphone (désactivé)';
        }
        this.showNotification(`Microphone ${enabled ? 'activé' : 'désactivé'}`, 'info');
    }

    toggleVideo() {
        const enabled = this.videoManager.toggleVideo();
        const btn = document.getElementById('toggleVideoBtn');
        if (btn) {
            btn.classList.toggle('disabled', !enabled);
            btn.title = enabled ? 'Caméra (activée)' : 'Caméra (désactivée)';
        }
        this.showNotification(`Caméra ${enabled ? 'activée' : 'désactivée'}`, 'info');
    }

    async toggleScreenShare() {
        this.showNotification('Partage d\'écran non disponible', 'warning');
    }

    async endCall() {
        try {
            // Annuler le timeout s'il existe
            if (this._callTimeoutId) {
                clearTimeout(this._callTimeoutId);
                this._callTimeoutId = null;
            }
            
            await this.videoManager.endCall();
        } catch(e) { console.error(e); }
        this.hideVideoCallContainer();
        this.cleanupUI();
    }

    updateParticipantList(participants = null) {
        const list = document.getElementById('participantList');
        if (!list) return;
        const count = participants ? participants.length : this.participants.size + 1;
        list.textContent = `${count} participant(s)`;
    }

    startCallDurationTimer() {
        this.callStartTime = Date.now();
        this.callDurationInterval = setInterval(() => {
            const elapsed = Math.floor((Date.now() - this.callStartTime) / 1000);
            const m = Math.floor(elapsed / 60);
            const s = elapsed % 60;
            const el = document.getElementById('callDuration');
            if (el) el.textContent = `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
        }, 1000);
    }

    stopCallDurationTimer() {
        if (this.callDurationInterval) {
            clearInterval(this.callDurationInterval);
            this.callDurationInterval = null;
        }
    }

    showNotification(message, type = 'info') {
        const container = document.getElementById('videoCallNotifications');
        if (!container) return;
        const n = document.createElement('div');
        n.className = `video-notification ${type}`;
        n.textContent = message;
        container.appendChild(n);
        setTimeout(() => n.remove(), 4000);
    }

    playRingtone() {
        // Sonnerie via Web Audio API
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const playBeep = (time) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.value = 440;
                gain.gain.setValueAtTime(0.3, time);
                gain.gain.exponentialRampToValueAtTime(0.001, time + 0.5);
                osc.start(time);
                osc.stop(time + 0.5);
            };
            playBeep(ctx.currentTime);
            playBeep(ctx.currentTime + 0.8);
            playBeep(ctx.currentTime + 1.6);
            this._ringtoneCtx = ctx;
        } catch(e) { /* silencieux */ }
    }

    stopRingtone() {
        if (this._ringtoneCtx) {
            try { this._ringtoneCtx.close(); } catch(e) {}
            this._ringtoneCtx = null;
        }
    }

    cleanupUI() {
        const remoteGrid = document.getElementById('remoteVideoGrid');
        if (remoteGrid) remoteGrid.innerHTML = '';
        this.participants.clear();
        this.currentCallId = null;
        this.currentConversationId = null;
        this.stopCallDurationTimer();
        this.stopRingtone();
        const widget = document.getElementById('incomingCallWidget');
        if (widget) widget.classList.add('hidden');
    }
}

// Init
document.addEventListener('DOMContentLoaded', () => {
    if (window.currentUserId) {
        window.videoCallUI = new VideoCallUI(window.currentUserId);
        console.log('[VideoCallUI] Initialisé pour user', window.currentUserId);
    } else {
        console.warn('[VideoCallUI] window.currentUserId non défini');
    }
});