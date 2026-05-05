<?php
/**
 * Exemple d'intégration des appels vidéo dans la messagerie
 * À adapter selon votre template Messages.php existant
 */
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messagerie - Swaply</title>
    
    <!-- Styles existants de la messagerie -->
    <link rel="stylesheet" href="/asset/css/messages.css">
    
    <!-- NOUVEAU: Styles des appels vidéo -->
    <link rel="stylesheet" href="/asset/css/videocall.css">
</head>
<body>
    <div class="messagerie-container">
        <!-- Sidebar des conversations -->
        <aside class="conversations-sidebar">
            <!-- Contenu existant -->
        </aside>

        <!-- Area principale de chat -->
        <main class="chat-area">
            <!-- Header avec infos de la conversation -->
            <div class="chat-header">
                <h2><?php echo htmlspecialchars($conversation['interlocuteur_nom'] ?? ''); ?></h2>
                
                <!-- NOUVEAU: Boutons de contrôle incluant l'appel vidéo -->
                <div class="chat-controls">
                    <button id="videoCallBtn" class="btn-video-call" title="Appel vidéo">
                        <i class="icon-video"></i>
                    </button>
                    <button class="btn-info" title="Infos">
                        <i class="icon-info"></i>
                    </button>
                </div>
            </div>

            <!-- Zone de messages -->
            <div class="messages-area">
                <!-- Messages affichés ici -->
            </div>

            <!-- Input des messages -->
            <div class="message-input-area">
                <textarea id="messageInput" placeholder="Écrivez votre message..."></textarea>
                <button id="sendBtn" class="btn-send">Envoyer</button>
            </div>
        </main>
    </div>

    <!-- NOUVEAU: Container pour les appels vidéo -->
    <div id="videoCallContainer" class="video-call-container hidden">
        <div class="video-call-header">
            <div class="call-info">
                <h3 id="callParticipant" class="participant-name">Appel vidéo</h3>
                <span id="callDuration" class="call-duration">00:00</span>
            </div>
            <div class="call-controls">
                <button id="toggleAudioBtn" class="control-btn audio-btn" title="Microphone">
                    <i class="icon-mic"></i>
                </button>
                <button id="toggleVideoBtn" class="control-btn video-btn" title="Caméra">
                    <i class="icon-video"></i>
                </button>
                <button id="endCallBtn" class="control-btn end-btn" title="Terminer">
                    <i class="icon-phone-off"></i>
                </button>
            </div>
        </div>

        <div class="video-call-main">
            <div class="video-grid">
                <div id="localVideoContainer" class="video-container local">
                    <video id="localVideo" autoplay muted playsinline></video>
                    <div class="video-label">Vous</div>
                </div>
                <div id="remoteVideoGrid" class="remote-grid"></div>
            </div>
        </div>

        <div class="video-call-footer">
            <div id="connectionStatus" class="connection-status">
                <span class="status-indicator"></span>
                <span class="status-text">Connecté</span>
            </div>
        </div>
    </div>

    <!-- NOUVEAU: Widget d'appel entrant -->
    <div id="incomingCallWidget" class="incoming-call-widget hidden">
        <div class="incoming-call-content">
            <div class="incoming-avatar">
                <i class="icon-phone"></i>
            </div>
            <h2 id="incomingCallerName" class="caller-name">Appel entrant</h2>
            <p class="calling-text">Appel vidéo en cours...</p>
            
            <div class="incoming-call-actions">
                <button id="acceptIncomingBtn" class="action-btn accept-btn">
                    <i class="icon-phone"></i> Accepter
                </button>
                <button id="rejectIncomingBtn" class="action-btn reject-btn">
                    <i class="icon-phone-off"></i> Rejeter
                </button>
            </div>
        </div>
    </div>

    <!-- NOUVEAU: Scripts des appels vidéo -->
    
    <!-- Socket.io -->
    <script src="http://localhost:3000/socket.io/socket.io.js"></script>
    
    <!-- Manager d'appels vidéo -->
    <script src="/asset/js/VideoCallManager.js"></script>
    
    <!-- UI des appels vidéo -->
    <script src="/asset/js/VideoCallUI.js"></script>

    <!-- Script de la messagerie -->
    <script src="/asset/js/messages.js"></script>

    <!-- NOUVEAU: Script d'initialisation -->
    <script>
        // Passer l'ID utilisateur à JavaScript
        window.currentUserId = <?php echo (int)($_SESSION['id_user'] ?? 0); ?>;
        
        // ID de la conversation actuelle
        const currentConversationId = <?php echo (int)($id_conversation ?? 0); ?>;

        // Initialiser l'interface vidéo après le chargement du DOM
        document.addEventListener('DOMContentLoaded', function() {
            // Créer une instance de VideoCallUI
            if (window.currentUserId) {
                window.videoCallUI = new VideoCallUI(
                    window.currentUserId,
                    'http://localhost:3000'
                );

                // Ajouter un écouteur au bouton vidéo
                const videoCallBtn = document.getElementById('videoCallBtn');
                if (videoCallBtn) {
                    videoCallBtn.addEventListener('click', function() {
                        window.videoCallUI.initiateCall(currentConversationId);
                    });
                }
            }
        });
    </script>
</body>
</html>
