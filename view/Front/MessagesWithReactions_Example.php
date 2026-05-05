<?php
/**
 * Exemple d'intégration du système de réactions
 * À adapter dans votre fichier view/Front/Messages.php
 */
session_start();

require_once __DIR__ . '/../../model/Message.php';
require_once __DIR__ . '/../../model/Reaction.php';

// Vérifier que l'utilisateur est connecté
$currentUserId = $_SESSION['id_u'] ?? null;
if (!$currentUserId) {
    header('Location: index.php');
    exit;
}

$message = new Message();
$reaction = new Reaction();

// Exemple: Récupérer les messages avec les réactions
$conversationId = $_GET['id'] ?? 1;
$messages = $message->getByConversationWithReactions($conversationId);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages avec Réactions</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: #1976d2;
            color: white;
            padding: 20px;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .messages-container {
            padding: 20px;
            max-height: 600px;
            overflow-y: auto;
        }

        .message-item {
            margin-bottom: 20px;
            padding: 12px;
            background: #f9f9f9;
            border-left: 3px solid #1976d2;
            border-radius: 4px;
        }

        .message-item:hover {
            background: #f0f0f0;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .message-author {
            font-weight: 600;
            color: #333;
        }

        .message-time {
            font-size: 12px;
            color: #999;
        }

        .message-content {
            color: #555;
            line-height: 1.5;
            margin-bottom: 8px;
            word-wrap: break-word;
        }

        .message-actions {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }

        .reaction-btn {
            background: none;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 4px 12px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.2s;
            color: #555;
        }

        .reaction-btn:hover {
            background-color: #e3f2fd;
            border-color: #1976d2;
            color: #1976d2;
        }

        .reactions-container {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            margin-top: 8px;
        }

        .reaction-badge {
            display: inline-block;
            background-color: #f0f0f0;
            border-radius: 12px;
            padding: 4px 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .reaction-badge:hover {
            background-color: #e0e0e0;
            transform: scale(1.05);
        }

        .reaction-badge.user-reacted {
            background-color: #e3f2fd;
            color: #1976d2;
            border-color: #1976d2;
        }

        .reaction-badge.user-reacted:hover {
            background-color: #bbdefb;
        }

        .emoji-picker {
            position: fixed;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            max-width: 300px;
            justify-content: center;
        }

        .emoji-picker button {
            background: #f0f0f0;
            border: none;
            border-radius: 4px;
            padding: 6px 10px;
            cursor: pointer;
            font-size: 20px;
            transition: background-color 0.2s;
        }

        .emoji-picker button:hover {
            background: #e0e0e0;
        }

        .empty-message {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        @media (max-width: 600px) {
            .container {
                border-radius: 0;
            }

            .messages-container {
                max-height: 500px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💬 Messages</h1>
            <p>Conversation #<?php echo htmlspecialchars($conversationId); ?></p>
        </div>

        <div class="messages-container">
            <?php if (empty($messages)): ?>
                <div class="empty-message">
                    <p>Aucun message pour le moment</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="message-item" data-message-id="<?php echo $msg['id_message']; ?>">
                        <div class="message-header">
                            <span class="message-author">
                                <?php echo htmlspecialchars($msg['prenom'] . ' ' . $msg['nom']); ?>
                            </span>
                            <span class="message-time">
                                <?php echo date('H:i', strtotime($msg['date_envoi'])); ?>
                            </span>
                        </div>

                        <div class="message-content">
                            <?php echo htmlspecialchars($msg['contenu']); ?>
                        </div>

                        <div class="message-actions">
                            <button class="reaction-btn" data-id-message="<?php echo $msg['id_message']; ?>" title="Ajouter une réaction">
                                😊 Réagir
                            </button>
                        </div>

                        <div class="reactions-container">
                            <!-- Les réactions seront ajoutées ici par JavaScript -->
                            <?php 
                            // Affichage initial des réactions
                            if (!empty($msg['reactions'])):
                                foreach ($msg['reactions'] as $rxn):
                                    $users = array_map('intval', explode(',', $rxn['users']));
                                    $userHasReacted = in_array($currentUserId, $users);
                                    $class = $userHasReacted ? 'user-reacted' : '';
                            ?>
                                <span class="reaction-badge <?php echo $class; ?>" data-id-message="<?php echo $msg['id_message']; ?>" data-emoji="<?php echo $rxn['emoji']; ?>">
                                    <?php echo $rxn['emoji']; ?> <span><?php echo $rxn['count']; ?></span>
                                </span>
                            <?php 
                                endforeach;
                            endif;
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Inclusion du script de gestion des réactions -->
    <script src="../../asset/js/reactions.js"></script>

    <script>
        // Définir l'utilisateur actuel (IMPORTANT!)
        var currentUserId = <?php echo (int)$currentUserId; ?>;

        // Initialiser après le chargement
        document.addEventListener('DOMContentLoaded', function() {
            // Le gestionnaire est initialisé automatiquement par reactions.js
            console.log('✓ Système de réactions initialisé');
        });
    </script>
</body>
</html>
