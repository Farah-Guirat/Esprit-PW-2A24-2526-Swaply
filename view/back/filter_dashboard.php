<?php
session_start();

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    header('Location: ../Front/login.php');
    exit;
}

require_once __DIR__ . '/../../controller/FilterController.php';
require_once __DIR__ . '/../../model/FilterService.php';

$filterService = new FilterService();
$stats = $filterService->getFilteringStats();
$allWords = $filterService->getAllFilteredWords(false);
$topWords = $filterService->getTopFilteredWords(10);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion du Filtrage - Back Office</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .header h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
            font-size: 14px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #667eea;
        }

        .stat-card h3 {
            color: #667eea;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .stat-card .value {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }

        .main-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .card h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #667eea;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .btn-danger {
            background: #ff6b6b;
            color: white;
            padding: 6px 12px;
            font-size: 12px;
        }

        .btn-danger:hover {
            background: #ee5a52;
        }

        .btn-success {
            background: #51cf66;
            color: white;
            padding: 6px 12px;
            font-size: 12px;
        }

        .btn-success:hover {
            background: #40c057;
        }

        .word-list {
            max-height: 500px;
            overflow-y: auto;
        }

        .word-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            margin-bottom: 10px;
            background: #f9f9f9;
        }

        .word-info {
            flex: 1;
        }

        .word-text {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .word-meta {
            font-size: 12px;
            color: #999;
        }

        .word-actions {
            display: flex;
            gap: 5px;
        }

        .top-words-list {
            list-style: none;
        }

        .top-words-list li {
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-words-list li:last-child {
            border-bottom: none;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-primary {
            background: #e7f5ff;
            color: #1971c2;
        }

        .badge-warning {
            background: #fff3bf;
            color: #f08c00;
        }

        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            animation: slideIn 0.3s ease;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 10px;
            padding: 30px;
            max-width: 500px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛡️ Gestion du Filtrage des Messages</h1>
            <p>Administrez les mots interdits et les statistiques de filtrage</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Mots Interdits</h3>
                <div class="value"><?php echo $stats['total_words'] ?? 0; ?></div>
            </div>
            <div class="stat-card">
                <h3>Mots Actifs</h3>
                <div class="value"><?php echo $stats['active_words'] ?? 0; ?></div>
            </div>
            <div class="stat-card">
                <h3>Remplacements</h3>
                <div class="value"><?php echo $stats['total_replacements'] ?? 0; ?></div>
            </div>
            <div class="stat-card">
                <h3>Max Remplacements</h3>
                <div class="value"><?php echo $stats['max_replacements'] ?? 0; ?></div>
            </div>
        </div>

        <div class="main-content">
            <div>
                <div class="card">
                    <h2>Ajouter un Mot Interdit</h2>
                    <form id="addWordForm">
                        <div class="form-group">
                            <label>Mot ou phrase</label>
                            <input type="text" id="wordInput" name="word" placeholder="Ex: idiot, spam, etc." required>
                        </div>
                        <div class="form-group">
                            <label>Catégorie</label>
                            <select id="categoryInput" name="category">
                                <option value="general">Général</option>
                                <option value="insulte">Insulte</option>
                                <option value="spam">Spam</option>
                                <option value="harcèlement">Harcèlement</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Ajouter le Mot</button>
                    </form>

                    <hr style="margin: 30px 0; border: 1px solid #e0e0e0;">

                    <h2 style="margin-top: 0;">Actions</h2>
                    <div style="display: grid; gap: 10px;">
                        <button class="btn btn-secondary" onclick="document.getElementById('importFile').click()">
                            📥 Importer depuis CSV
                        </button>
                        <input type="file" id="importFile" accept=".csv" style="display: none;">
                        <button class="btn btn-secondary" onclick="exportWords()">
                            📤 Exporter en CSV
                        </button>
                    </div>
                </div>

                <div class="card" style="margin-top: 30px;">
                    <h2>Tous les Mots (<?php echo count($allWords); ?>)</h2>
                    <div id="messageContainer"></div>
                    <div class="word-list" id="wordList">
                        <?php if (empty($allWords)): ?>
                            <p style="color: #999; text-align: center;">Aucun mot filtré.</p>
                        <?php else: ?>
                            <?php foreach ($allWords as $word): ?>
                                <div class="word-item" data-word-id="<?php echo $word['id_word']; ?>">
                                    <div class="word-info">
                                        <div class="word-text"><?php echo htmlspecialchars($word['word']); ?></div>
                                        <div class="word-meta">
                                            <span class="badge badge-primary"><?php echo htmlspecialchars($word['category']); ?></span>
                                            <span style="margin-left: 10px;">Remplacements: <?php echo $word['replacement_count']; ?></span>
                                            <span style="margin-left: 10px;">
                                                <?php echo $word['is_active'] ? '✓ Actif' : '✗ Inactif'; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="word-actions">
                                        <button class="btn btn-success" onclick="toggleWord(<?php echo $word['id_word']; ?>, <?php echo $word['is_active'] ? 0 : 1; ?>)">
                                            <?php echo $word['is_active'] ? 'Désactiver' : 'Activer'; ?>
                                        </button>
                                        <button class="btn btn-danger" onclick="deleteWord('<?php echo htmlspecialchars($word['word']); ?>')">
                                            Supprimer
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div>
                <div class="card">
                    <h2>Mots les Plus Remplacés</h2>
                    <?php if (empty($topWords)): ?>
                        <p style="color: #999;">Aucune statistique disponible.</p>
                    <?php else: ?>
                        <ul class="top-words-list">
                            <?php foreach ($topWords as $index => $word): ?>
                                <li>
                                    <div>
                                        <strong><?php echo htmlspecialchars($word['word']); ?></strong>
                                        <div style="font-size: 12px; color: #999; margin-top: 5px;">
                                            <?php echo htmlspecialchars($word['category']); ?>
                                        </div>
                                    </div>
                                    <span class="badge badge-warning"><?php echo $word['replacement_count']; ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <div class="card" style="margin-top: 30px;">
                    <h2>Informations</h2>
                    <p style="color: #666; line-height: 1.6;">
                        <strong>📌 Comment ça fonctionne :</strong><br>
                        • Les mots ajoutés sont filtrés automatiquement<br>
                        • La casse est ignorée (minuscules/majuscules)<br>
                        • Les remplacements sont comptabilisés<br>
                        • Vous pouvez activer/désactiver les mots<br>
                        • Importez/exportez en CSV
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Ajouter un mot
        document.getElementById('addWordForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const word = document.getElementById('wordInput').value;
            const category = document.getElementById('categoryInput').value;

            try {
                const response = await fetch('../../controller/FilterController.php?action=addWord', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `word=${encodeURIComponent(word)}&category=${encodeURIComponent(category)}`
                });

                const data = await response.json();
                showMessage(data.success ? 'success' : 'error', data.message || data.error);

                if (data.success) {
                    document.getElementById('addWordForm').reset();
                    location.reload();
                }
            } catch (error) {
                showMessage('error', 'Erreur : ' + error.message);
            }
        });

        // Supprimer un mot
        async function deleteWord(word) {
            if (!confirm(`Êtes-vous sûr de vouloir supprimer le mot "${word}" ?`)) return;

            try {
                const response = await fetch('../../controller/FilterController.php?action=deleteWord', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `word=${encodeURIComponent(word)}`
                });

                const data = await response.json();
                showMessage(data.success ? 'success' : 'error', data.message || data.error);

                if (data.success) {
                    location.reload();
                }
            } catch (error) {
                showMessage('error', 'Erreur : ' + error.message);
            }
        }

        // Basculer un mot
        async function toggleWord(id, isActive) {
            try {
                const response = await fetch('../../controller/FilterController.php?action=toggleWord', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${id}&is_active=${isActive}`
                });

                const data = await response.json();
                showMessage(data.success ? 'success' : 'error', data.message || data.error);

                if (data.success) {
                    location.reload();
                }
            } catch (error) {
                showMessage('error', 'Erreur : ' + error.message);
            }
        }

        // Exporter en CSV
        function exportWords() {
            window.location.href = '../../controller/FilterController.php?action=exportWords';
        }

        // Importer depuis CSV
        document.getElementById('importFile').addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('file', file);

            try {
                const response = await fetch('../../controller/FilterController.php?action=importWords', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                showMessage(data.success ? 'success' : 'error', data.message || data.error);

                if (data.success) {
                    setTimeout(() => location.reload(), 1500);
                }
            } catch (error) {
                showMessage('error', 'Erreur : ' + error.message);
            }
        });

        // Afficher les messages
        function showMessage(type, message) {
            const container = document.getElementById('messageContainer');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${type}`;
            messageDiv.textContent = message;
            container.innerHTML = '';
            container.appendChild(messageDiv);

            setTimeout(() => {
                messageDiv.remove();
            }, 5000);
        }
    </script>
</body>
</html>
