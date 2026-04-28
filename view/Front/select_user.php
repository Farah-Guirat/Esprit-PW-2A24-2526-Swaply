<?php
// ── Sélection d'utilisateur pour tester ──────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../config/database.php';

// Si un utilisateur est sélectionné via formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $_SESSION['id_user'] = (int)$_POST['user_id'];
    
    // Récupérer les infos de l'utilisateur depuis la DB
    $pdo = Database::getInstance()->getConnection();
    $stmt = $pdo->prepare("SELECT id_u, prenom, nom FROM utilisateurs WHERE id_u = ?");
    $stmt->execute([$_SESSION['id_user']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $_SESSION['prenom'] = $user['prenom'];
        $_SESSION['nom'] = $user['nom'];
        header('Location: messagerie.php');
        exit;
    }
}

// Récupérer tous les utilisateurs disponibles
$pdo = Database::getInstance()->getConnection();
$stmt = $pdo->query("SELECT id_u, CONCAT(prenom, ' ', nom) as nom_complet FROM utilisateurs ORDER BY prenom");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sélectionner un utilisateur</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 500px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        label {
            font-weight: 600;
            color: #555;
            margin-bottom: 5px;
        }
        select {
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: border-color 0.3s;
        }
        select:focus {
            outline: none;
            border-color: #667eea;
        }
        button {
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        button:hover {
            transform: translateY(-2px);
        }
        .current-user {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .current-user p {
            margin: 0;
            color: #666;
        }
        .current-user strong {
            color: #333;
        }
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
            font-size: 14px;
            color: #1565c0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>👤 Sélectionner un utilisateur</h1>
        
        <?php if (isset($_SESSION['id_user'])): ?>
            <div class="current-user">
                <p>Utilisateur actuel: <strong><?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']); ?></strong></p>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <label for="user_id">Choisir un utilisateur:</label>
            <select name="user_id" id="user_id" required>
                <option value="">-- Sélectionner un utilisateur --</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['id_u']; ?>" 
                            <?php echo (isset($_SESSION['id_user']) && $_SESSION['id_user'] == $user['id_u']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($user['nom_complet']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Se connecter</button>
        </form>
        
        <div class="info-box">
            💡 <strong>Astuce:</strong> Ouvrez cette page dans 2 onglets et sélectionnez des utilisateurs différents pour tester les conversations!
        </div>
    </div>
</body>
</html>
