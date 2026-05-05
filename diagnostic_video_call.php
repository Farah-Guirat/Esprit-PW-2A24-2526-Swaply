<?php
/**
 * Diagnostic Appel Vidéo - Page de vérification
 */

session_start();
$id_user = $_SESSION['id_user'] ?? 0;

// Vérifier les tables
$tablesExist = false;
try {
    require_once __DIR__ . '/config/database.php';
    $pdo = Database::getInstance()->getConnection();
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'video_calls'");
    $tablesExist = $stmt->rowCount() > 0;
    
    // Récupérer le statut des appels
    $stmt = $pdo->query("SELECT statut, COUNT(*) as count FROM video_calls GROUP BY statut");
    $callStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic Appel Vidéo - Swaply</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container { 
            max-width: 800px; 
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 { color: #333; margin-bottom: 10px; }
        .subtitle { color: #666; margin-bottom: 30px; font-size: 14px; }
        
        .check-item { 
            display: flex;
            align-items: center;
            padding: 12px;
            margin: 8px 0;
            border-radius: 5px;
            border-left: 4px solid #ddd;
        }
        .check-item.success { 
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        .check-item.error { 
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        .check-item.warning { 
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        .check-icon { 
            font-size: 20px;
            margin-right: 10px;
            font-weight: bold;
        }
        
        .section { margin: 25px 0; }
        .section-title { 
            font-size: 16px; 
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #667eea;
        }
        
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; font-weight: 600; color: #333; }
        
        .action-btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 8px 8px 8px 0;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .action-btn:hover { background: #764ba2; }
        .action-btn.secondary {
            background: #6c757d;
        }
        .action-btn.secondary:hover { background: #5a6268; }
        
        .code-block {
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            font-family: monospace;
            font-size: 12px;
            margin: 10px 0;
            overflow-x: auto;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>🔧 Diagnostic Appel Vidéo Swaply</h1>
    <p class="subtitle">Vérification de la configuration et du statut du système</p>
    
    <!-- Utilisateur -->
    <div class="section">
        <div class="section-title">👤 Utilisateur Connecté</div>
        <?php if ($id_user > 0): ?>
            <div class="check-item success">
                <span class="check-icon">✓</span>
                <span>Utilisateur #<?php echo $id_user; ?> connecté</span>
            </div>
        <?php else: ?>
            <div class="check-item error">
                <span class="check-icon">❌</span>
                <span>Pas d'utilisateur connecté - <a href="view/Front/login.php">Se connecter</a></span>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Base de données -->
    <div class="section">
        <div class="section-title">🗄️ Base de Données</div>
        <?php if ($tablesExist): ?>
            <div class="check-item success">
                <span class="check-icon">✓</span>
                <span>Tables vidéo existantes</span>
            </div>
            <?php if (!empty($callStats)): ?>
                <table>
                    <tr>
                        <th>Statut</th>
                        <th>Nombre d'appels</th>
                    </tr>
                    <?php foreach ($callStats as $stat): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($stat['statut']); ?></td>
                            <td><?php echo (int)$stat['count']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        <?php else: ?>
            <div class="check-item error">
                <span class="check-icon">❌</span>
                <span>Tables vidéo manquantes</span>
            </div>
            <a href="install_video_tables.php" class="action-btn">Créer les tables</a>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="check-item error">
                <span class="check-icon">❌</span>
                <span>Erreur base de données: <?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Configuration serveur -->
    <div class="section">
        <div class="section-title">🌐 Configuration Serveur</div>
        
        <div class="check-item success">
            <span class="check-icon">✓</span>
            <span>Serveur PHP: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Apache'; ?></span>
        </div>
        
        <div id="socketStatus" class="check-item warning">
            <span class="check-icon">⏳</span>
            <span>Vérification du serveur Socket.io...</span>
        </div>
        
        <script>
            fetch('http://localhost:3000/health')
                .then(r => r.json())
                .then(data => {
                    document.getElementById('socketStatus').className = 'check-item success';
                    document.getElementById('socketStatus').innerHTML = `
                        <span class="check-icon">✓</span>
                        <span>Serveur Socket.io actif (${Object.keys(data.users).length} utilisateurs, ${data.calls} appels)</span>
                    `;
                })
                .catch(e => {
                    document.getElementById('socketStatus').className = 'check-item error';
                    document.getElementById('socketStatus').innerHTML = `
                        <span class="check-icon">❌</span>
                        <span>Serveur Socket.io indisponible - Vérifiez que: <code>npm start</code> est lancé dans video_server/</span>
                    `;
                });
        </script>
    </div>
    
    <!-- Fichiers critiques -->
    <div class="section">
        <div class="section-title">📁 Fichiers Critiques</div>
        
        <?php
        $files = [
            'asset/js/VideoCallManager.js' => 'Manager WebRTC',
            'asset/js/VideoCallUI.js' => 'Interface utilisateur',
            'controller/VideoCallController.php' => 'API PHP',
            'model/VideoCall.php' => 'Modèle base de données',
            'video_server/server.js' => 'Serveur Node.js'
        ];
        
        foreach ($files as $file => $desc):
            $path = __DIR__ . '/' . $file;
            $exists = file_exists($path);
            $class = $exists ? 'success' : 'error';
            $icon = $exists ? '✓' : '❌';
        ?>
            <div class="check-item <?php echo $class; ?>">
                <span class="check-icon"><?php echo $icon; ?></span>
                <span><?php echo htmlspecialchars($desc); ?> (<code><?php echo htmlspecialchars($file); ?></code>)</span>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Actions rapides -->
    <div class="section">
        <div class="section-title">🚀 Actions</div>
        
        <a href="view/Front/Messages.php" class="action-btn">Aller à la messagerie</a>
        <a href="debug_video_call.html" class="action-btn">Outils de debug</a>
        <a href="install_video_tables.php" class="action-btn">Vérifier les tables</a>
        <a href="VIDEO_CALL_TROUBLESHOOT.md" class="action-btn secondary">Guide de dépannage</a>
    </div>
    
    <!-- Instructions -->
    <div class="section">
        <div class="section-title">📋 Premiers pas</div>
        
        <h4 style="margin-top: 15px; margin-bottom: 8px;">1. Démarrer le serveur Socket.io</h4>
        <div class="code-block">
cd c:\xampp\htdocs\swaply\video_server<br>
npm start
        </div>
        
        <h4 style="margin-top: 15px; margin-bottom: 8px;">2. Ouvrir deux onglets</h4>
        <p style="font-size: 14px; color: #666;">
            Ouvrez deux navigateurs (ou deux onglets incognito) avec deux utilisateurs différents et allez à la messagerie.
        </p>
        
        <h4 style="margin-top: 15px; margin-bottom: 8px;">3. Lancer un appel vidéo</h4>
        <p style="font-size: 14px; color: #666;">
            Cliquez sur le bouton "📞 Appel vidéo" pour démarrer un appel.
        </p>
    </div>
</div>

</body>
</html>
