/**
 * VideoCallManager - Gère les appels vidéo WebRTC avec signalisation Socket.io
 * FIXED VERSION - Corrige tous les bugs de signalisation et WebRTC
 */

class VideoCallManager {
    constructor(socketUrl = 'http://localhost:3000', userId = null) {
        this.socketUrl = socketUrl;
        this.userId = parseInt(userId);
        this.socket = null;
        this.socketReady = false;  // FLAG: Socket connecté et enregistré
        this.peerConnections = new Map();
        this.localStream = null;
        this.remoteStreams = new Map();
        this.currentCallId = null;
        this.audioEnabled = true;
        this.videoEnabled = true;
        this.isInitiator = false;  // FIX: track who is the caller

        this.rtcConfig = {
            iceServers: [
                { urls: 'stun:stun.l.google.com:19302' },
                { urls: 'stun:stun1.l.google.com:19302' },
                { urls: 'stun:stun2.l.google.com:19302' }
            ]
        };

        this.onRemoteStream = null;
        this.onRemoteStreamRemoved = null;
        this.onCallIncoming = null;
        this.onCallAccepted = null;
        this.onCallRejected = null;
        this.onCallEnded = null;
        this.onUserJoined = null;
        this.onUserLeft = null;
        this.onError = null;

        // FIX: Socket.io est déjà chargé via <script> dans Messages.php
        // On l'initialise directement sans charger un second script
        this._initSocketWhenReady();
    }

    /**
     * Attendre que le socket soit prêt avant d'émettre
     */
    _waitForSocketReady(timeout = 5000) {
        return new Promise((resolve, reject) => {
            if (this.socketReady) {
                resolve();
                return;
            }
            const start = Date.now();
            const check = setInterval(() => {
                if (this.socketReady) {
                    clearInterval(check);
                    resolve();
                } else if (Date.now() - start > timeout) {
                    clearInterval(check);
                    reject(new Error('Socket.io n\'a pas pu se connecter après ' + timeout + 'ms'));
                }
            }, 100);
        });
    }

    _initSocketWhenReady() {
        // Si io() est déjà disponible, on se connecte directement
        if (typeof io !== 'undefined') {
            this._connectSocket();
        } else {
            // Attendre que socket.io soit chargé
            let attempts = 0;
            const wait = setInterval(() => {
                attempts++;
                if (typeof io !== 'undefined') {
                    clearInterval(wait);
                    this._connectSocket();
                } else if (attempts > 20) {
                    clearInterval(wait);
                    console.error('[VideoCall] socket.io non disponible après 2 secondes');
                }
            }, 100);
        }
    }

    _connectSocket() {
        try {
            console.log('[VideoCall] Tentative de connexion à', this.socketUrl);
            this.socket = io(this.socketUrl, {
                transports: ['websocket', 'polling'],
                reconnection: true,
                reconnectionAttempts: 5,
                reconnectionDelay: 1000
            });
            this.setupSocketListeners();

            this.socket.on('connect', () => {
                console.log('[VideoCall] ✓ Connecté au serveur de signalisation');
                if (this.userId) {
                    console.log('[VideoCall] Enregistrement de l\'utilisateur', this.userId);
                    this.socket.emit('register_user', { id_user: this.userId });
                } else {
                    console.warn('[VideoCall] userId non disponible - pas d\'enregistrement');
                }
            });

            this.socket.on('user_registered', (data) => {
                console.log('[VideoCall] ✓ User enregistré sur le serveur:', data);
                this.socketReady = true;  // FLAG: Socket est maintenant prêt
            });

            this.socket.on('disconnect', (reason) => {
                console.warn('[VideoCall] Déconnecté du serveur:', reason);
                this.socketReady = false;  // Reset flag
            });

            this.socket.on('connect_error', (error) => {
                console.error('[VideoCall] Erreur de connexion Socket.io:', error);
            });
        } catch (e) {
            console.error('[VideoCall] Erreur de connexion socket:', e);
        }
    }

    setupSocketListeners() {
        // Appel entrant → afficher le widget
        this.socket.on('incoming_call', (data) => {
            console.log('[VideoCall] 📞 Appel entrant de', data.id_initiateur, data);
            if (this.onCallIncoming) this.onCallIncoming(data);
        });

        // L'autre a accepté → l'initiateur crée la WebRTC offer
        this.socket.on('call_accepted', (data) => {
            console.log('[VideoCall] ✓ Appel accepté par', data.id_user, '| je suis', this.userId);
            if (this.onCallAccepted) this.onCallAccepted(data);

            // FIX: SEULEMENT l'initiateur crée l'offer WebRTC
            if (this.isInitiator && data.id_user !== this.userId) {
                console.log('[VideoCall] Je suis l\'initiateur → je crée l\'offer vers', data.id_user);
                this._createOfferTo(parseInt(data.id_user));
            }
        });

        // Appel rejeté
        this.socket.on('call_rejected', (data) => {
            console.log('[VideoCall] ✗ Appel rejeté');
            if (this.onCallRejected) this.onCallRejected(data);
            this.cleanup();
        });

        // Appel démarré (reçu par l'accepteur)
        // FIX: l'accepteur ne doit PAS créer d'offer ici — il attend l'offer de l'initiateur
        this.socket.on('call_started', (data) => {
            console.log('[VideoCall] Appel démarré (je suis accepteur, j\'attends l\'offer)');
            this.currentCallId = parseInt(data.id_video_call);
            // Ne rien faire — on attend le webrtc_offer de l'initiateur
        });

        this.socket.on('user_joined', (data) => {
            console.log('[VideoCall] Utilisateur rejoint:', data.id_user);
            if (this.onUserJoined) this.onUserJoined(data);
        });

        this.socket.on('user_left', (data) => {
            console.log('[VideoCall] Utilisateur parti:', data.id_user);
            if (this.onUserLeft) this.onUserLeft(data);
            this.removePeerConnection(parseInt(data.id_user));
        });

        this.socket.on('call_ended', (data) => {
            console.log('[VideoCall] Appel terminé');
            if (this.onCallEnded) this.onCallEnded(data);
            this.cleanup();
        });

        // WebRTC Signalisation
        this.socket.on('webrtc_offer', async (data) => {
            console.log('[VideoCall] Reçu offer de', data.from_user);
            await this.handleWebRTCOffer(data);
        });

        this.socket.on('webrtc_answer', async (data) => {
            console.log('[VideoCall] Reçu answer de', data.from_user);
            await this.handleWebRTCAnswer(data);
        });

        this.socket.on('ice_candidate', async (data) => {
            await this.handleICECandidate(data);
        });

        this.socket.on('audio_toggled', (data) => {
            console.log(`[VideoCall] Audio user ${data.id_user}: ${data.enabled}`);
        });

        this.socket.on('video_toggled', (data) => {
            console.log(`[VideoCall] Video user ${data.id_user}: ${data.enabled}`);
        });
    }

    /**
     * Lancer un appel vidéo
     * FIX: on passe id_interlocuteur ET les noms au serveur socket
     */
    async initiateCall(conversationId, callType = '1to1', interlocuteurId = null, nom = '', prenom = '', forceNew = false) {
        try {
            this.isInitiator = true;
            
            // ATTENDRE que le socket soit prêt avant de faire quoi que ce soit
            console.log('[VideoCall] En attente de connexion socket...');
            await this._waitForSocketReady();

            await this.getLocalStream();

            const body = new URLSearchParams();
            body.append('id_conversation', conversationId);
            body.append('type_appel', callType);
            body.append('force_new', forceNew ? '1' : '0');

            const result = await secureFetch("../../controller/VideoCallController.php?action=initiate", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: body.toString()
            });
            
            console.log('[VideoCall] Réponse PHP initiate:', result);

            if (!result.success) {
                if (result.message && result.message.includes('déjà en cours')) {
                    this.currentCallId = parseInt(result.id_video_call);
                    return;
                }
                throw new Error(result.message || 'Erreur inconnue');
            }

            this.currentCallId = parseInt(result.id_video_call);

            // VÉRIFIER que socket est connecté avant d'émettre
            if (!this.socket || !this.socket.connected) {
                throw new Error('Socket.io n\'est pas connecté');
            }

            // FIX CRITIQUE: envoyer id_interlocuteur au serveur socket
            // pour qu'il sache qui notifier
            this.socket.emit('initiate_call', {
                id_video_call:    this.currentCallId,
                id_conversation:  parseInt(conversationId),
                id_initiateur:    this.userId,
                id_interlocuteur: parseInt(interlocuteurId),
                initiateur_nom:   nom,
                initiateur_prenom: prenom
            });

            console.log('[VideoCall] Appel initié:', this.currentCallId, '→', interlocuteurId);
        } catch (error) {
            console.error('[VideoCall] Erreur initiateCall:', error);
            if (this.onError) this.onError(error);
            throw error;
        }
    }

    /**
     * Accepter un appel
     */
    async acceptCall(callId) {
        try {
            this.isInitiator = false;
            this.currentCallId = parseInt(callId);
            await this.getLocalStream();

            // Attendre que socket soit prêt
            await this._waitForSocketReady();

            const result = await secureFetch('../../controller/VideoCallController.php?action=accept', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id_video_call=${callId}`
            });

            console.log('[VideoCall] Réponse PHP accept:', result);

            if (result.success) {
                // Vérifier socket avant d'émettre
                if (!this.socket || !this.socket.connected) {
                    throw new Error('Socket.io n\'est pas connecté');
                }
                
                this.socket.emit('accept_call', {
                    id_video_call: this.currentCallId,
                    id_user: this.userId
                });
                console.log('[VideoCall] accept_call envoyé');
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error('[VideoCall] Erreur acceptCall:', error);
            if (this.onError) this.onError(error);
            throw error;
        }
    }

    /**
     * Rejeter un appel
     */
    async rejectCall(callId) {
        try {
            await secureFetch('../../controller/VideoCallController.php?action=reject', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id_video_call=${callId}`
            });
            
            // Vérifier socket avant d'émettre
            if (this.socket && this.socket.connected) {
                this.socket.emit('reject_call', { 
                    id_video_call: parseInt(callId), 
                    id_user: this.userId 
                });
            }
        } catch (error) {
            console.error('[VideoCall] Erreur rejectCall:', error);
        }
    }

    /**
     * Obtenir le flux local (microphone + caméra)
     * FIX: Meilleure gestion des erreurs et fallback
     */
    async getLocalStream() {
        if (this.localStream) return this.localStream;

        try {
            console.log('[VideoCall] Demande d\'accès caméra + micro...');
            this.localStream = await navigator.mediaDevices.getUserMedia({
                audio: { echoCancellation: true, noiseSuppression: true },
                video: { width: { ideal: 1280 }, height: { ideal: 720 } }
            });
            console.log('[VideoCall] ✓ Flux local obtenu (caméra + micro)');
            this.videoEnabled = true;
            return this.localStream;
        } catch (error) {
            console.warn('[VideoCall] Caméra non disponible, tentative audio seul:', error.message);
            
            // Fallback: essayer audio seul
            try {
                this.localStream = await navigator.mediaDevices.getUserMedia({ 
                    audio: { echoCancellation: true, noiseSuppression: true }, 
                    video: false 
                });
                this.videoEnabled = false;
                console.warn('[VideoCall] ⚠️ Accès audio seul - caméra non disponible');
                if (this.onError) {
                    this.onError(new Error('Caméra non disponible. Appel en audio seul.'));
                }
                return this.localStream;
            } catch (audioError) {
                console.error('[VideoCall] ✗ Impossible d\'accéder au micro:', audioError.message);
                // Arrêter les pistes qui auraient pu être ouvertes partiellement
                try {
                    if (this.localStream) {
                        this.localStream.getTracks().forEach(t => t.stop());
                        this.localStream = null;
                    }
                } catch (e) {}
                
                if (this.onError) this.onError(audioError);
                throw new Error('Impossible d\'accéder à la caméra ou au microphone. Vérifiez les permissions.');
            }
        }
    }

    /**
     * Créer une connexion WebRTC SANS envoyer d'offer (juste préparer)
     * FIX: l'offer est envoyée séparément par _createOfferTo()
     */
    async _createPeerConnectionBase(remotePeerId) {
        if (this.peerConnections.has(remotePeerId)) {
            return this.peerConnections.get(remotePeerId);
        }

        const pc = new RTCPeerConnection(this.rtcConfig);

        if (this.localStream) {
            this.localStream.getTracks().forEach(track => pc.addTrack(track, this.localStream));
        }

        pc.onicecandidate = (event) => {
            if (event.candidate && this.currentCallId) {
                // Vérifier socket avant d'émettre
                if (this.socket && this.socket.connected) {
                    this.socket.emit('ice_candidate', {
                        id_video_call: this.currentCallId,
                        from_user: this.userId,
                        to_user: remotePeerId,
                        candidate: event.candidate
                    });
                }
            }
        };

        pc.ontrack = (event) => {
            console.log('[VideoCall] ✓ Track reçu de', remotePeerId, event.streams);
            const stream = event.streams[0];
            this.remoteStreams.set(remotePeerId, stream);
            if (this.onRemoteStream) this.onRemoteStream({ userId: remotePeerId, stream });
        };

        pc.onconnectionstatechange = () => {
            console.log('[VideoCall] État connexion P2P:', pc.connectionState, 'avec', remotePeerId);
            if (pc.connectionState === 'failed') {
                console.error('[VideoCall] Connexion P2P échouée avec', remotePeerId);
            }
        };

        pc.oniceconnectionstatechange = () => {
            console.log('[VideoCall] ICE state:', pc.iceConnectionState, 'avec', remotePeerId);
        };

        this.peerConnections.set(remotePeerId, pc);
        return pc;
    }

    /**
     * FIX: Créer l'offer et l'envoyer (appelé seulement par l'initiateur)
     */
    async _createOfferTo(remotePeerId) {
        try {
            const pc = await this._createPeerConnectionBase(remotePeerId);
            const offer = await pc.createOffer();
            await pc.setLocalDescription(offer);

            // Vérifier socket avant d'émettre
            if (!this.socket || !this.socket.connected) {
                console.error('[VideoCall] Socket n\'est pas connecté pour envoyer l\'offer');
                return;
            }

            this.socket.emit('webrtc_offer', {
                id_video_call: this.currentCallId,
                from_user: this.userId,
                to_user: remotePeerId,
                offer: offer
            });
            console.log('[VideoCall] ✓ Offer envoyée à', remotePeerId);
        } catch (error) {
            console.error('[VideoCall] Erreur création offer:', error);
        }
    }

    /**
     * FIX: Gérer une WebRTC Offer (reçue par l'accepteur)
     * On crée la PC, on setRemoteDescription, on crée l'answer — SANS recréer d'offer
     */
    async handleWebRTCOffer(data) {
        const { from_user, offer } = data;
        const remotePeerId = parseInt(from_user);

        try {
            // Créer la PC sans générer d'offer
            const pc = await this._createPeerConnectionBase(remotePeerId);

            await pc.setRemoteDescription(new RTCSessionDescription(offer));
            console.log('[VideoCall] Remote description définie');

            const answer = await pc.createAnswer();
            await pc.setLocalDescription(answer);

            // Vérifier socket avant d'émettre
            if (!this.socket || !this.socket.connected) {
                console.error('[VideoCall] Socket n\'est pas connecté pour envoyer l\'answer');
                return;
            }

            this.socket.emit('webrtc_answer', {
                id_video_call: this.currentCallId,
                from_user: this.userId,
                to_user: remotePeerId,
                answer: answer
            });
            console.log('[VideoCall] ✓ Answer envoyée à', remotePeerId);
        } catch (error) {
            console.error('[VideoCall] Erreur handleWebRTCOffer:', error);
        }
    }

    /**
     * Gérer une WebRTC Answer (reçue par l'initiateur)
     */
    async handleWebRTCAnswer(data) {
        const { from_user, answer } = data;
        const remotePeerId = parseInt(from_user);
        const pc = this.peerConnections.get(remotePeerId);

        if (pc) {
            try {
                await pc.setRemoteDescription(new RTCSessionDescription(answer));
                console.log('[VideoCall] ✓ Answer traitée de', remotePeerId);
            } catch (error) {
                console.error('[VideoCall] Erreur handleWebRTCAnswer:', error);
            }
        } else {
            console.warn('[VideoCall] Pas de PC pour traiter l\'answer de', remotePeerId);
        }
    }

    /**
     * Gérer un candidat ICE
     */
    async handleICECandidate(data) {
        const { from_user, candidate } = data;
        const remotePeerId = parseInt(from_user);
        const pc = this.peerConnections.get(remotePeerId);

        if (pc && candidate) {
            try {
                await pc.addIceCandidate(new RTCIceCandidate(candidate));
            } catch (error) {
                console.warn('[VideoCall] Erreur ICE candidate:', error);
            }
        }
    }

    toggleAudio() {
        if (!this.localStream) return false;
        this.audioEnabled = !this.audioEnabled;
        this.localStream.getAudioTracks().forEach(t => t.enabled = this.audioEnabled);
        if (this.socket && this.socket.connected && this.currentCallId) {
            this.socket.emit('toggle_audio', { id_video_call: this.currentCallId, id_user: this.userId, enabled: this.audioEnabled });
        }
        return this.audioEnabled;
    }

    toggleVideo() {
        if (!this.localStream) return false;
        this.videoEnabled = !this.videoEnabled;
        this.localStream.getVideoTracks().forEach(t => t.enabled = this.videoEnabled);
        if (this.socket && this.socket.connected && this.currentCallId) {
            this.socket.emit('toggle_video', { id_video_call: this.currentCallId, id_user: this.userId, enabled: this.videoEnabled });
        }
        return this.videoEnabled;
    }

    removePeerConnection(userId) {
        const pc = this.peerConnections.get(userId);
        if (pc) { pc.close(); this.peerConnections.delete(userId); }
        const stream = this.remoteStreams.get(userId);
        if (stream) {
            stream.getTracks().forEach(t => t.stop());
            this.remoteStreams.delete(userId);
            if (this.onRemoteStreamRemoved) this.onRemoteStreamRemoved({ userId });
        }
    }

    async endCall() {
        try {
            if (this.currentCallId) {
                await fetch('../../controller/VideoCallController.php?action=end', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id_video_call=${this.currentCallId}`,
                    credentials: 'same-origin'
                });
                
                // Vérifier socket avant d'émettre
                if (this.socket && this.socket.connected) {
                    this.socket.emit('end_call', { 
                        id_video_call: this.currentCallId, 
                        id_user: this.userId 
                    });
                }
            }
        } catch (e) { 
            console.error('[VideoCall] Erreur endCall:', e); 
        }
        this.cleanup();
    }

    cleanup() {
        console.log('[VideoCall] Nettoyage des ressources');
        
        // Fermer toutes les connexions P2P
        this.peerConnections.forEach((pc, userId) => {
            try {
                pc.close();
            } catch (e) {
                console.error('[VideoCall] Erreur fermeture PC pour user', userId, ':', e);
            }
        });
        this.peerConnections.clear();
        
        // Arrêter tous les streams distants
        this.remoteStreams.forEach((stream, userId) => {
            try {
                stream.getTracks().forEach(t => {
                    t.stop();
                    console.log('[VideoCall] Track arrêté:', t.kind, 'de user', userId);
                });
            } catch (e) {
                console.error('[VideoCall] Erreur arrêt tracks distants de', userId, ':', e);
            }
        });
        this.remoteStreams.clear();
        
        // Arrêter le stream local (TRÈS IMPORTANT pour libérer caméra/micro)
        if (this.localStream) {
            try {
                this.localStream.getTracks().forEach(t => {
                    console.log('[VideoCall] 🛑 Arrêt du track local:', t.kind);
                    t.stop();
                });
                this.localStream = null;
            } catch (e) {
                console.error('[VideoCall] Erreur arrêt stream local:', e);
                this.localStream = null;
            }
        }
        
        this.currentCallId = null;
        this.isInitiator = false;
        console.log('[VideoCall] ✓ Ressources nettoyées');
    }
}

if (typeof module !== 'undefined' && module.exports) module.exports = VideoCallManager;