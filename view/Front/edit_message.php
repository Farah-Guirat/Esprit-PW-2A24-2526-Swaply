<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier message – Swaply</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fa; }

        .navbar {
            display: flex; align-items: center; padding: 0 32px;
            height: 64px; background: #fff;
            border-bottom: 1px solid #e5e7eb;
            position: sticky; top: 0; z-index: 100;
        }
        .navbar-brand { display: flex; align-items: center; gap: 8px; font-size: 18px; font-weight: 700; color: #1a1a1a; text-decoration: none; margin-right: auto; }
        .navbar-logo { width: 34px; height: 34px; border-radius: 50%; background: #1D9E75; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 15px; }
        .navbar-links { display: flex; gap: 28px; }
        .navbar-links a { font-size: 14px; color: #4b5563; text-decoration: none; }
        .navbar-links a:hover { color: #1D9E75; }
        .navbar-links a.active { color: #1D9E75; font-weight: 600; border-bottom: 2px solid #1D9E75; padding-bottom: 2px; }
        .navbar-avatar { width: 34px; height: 34px; border-radius: 50%; background: #d1d5db; display: flex; align-items: center; justify-content: center; font-size: 13px; color: #6b7280; margin-left: 24px; }

        .page-content { max-width: 560px; margin: 48px auto; padding: 0 16px; }

        .card {
            background: #fff; border-radius: 12px;
            border: 1px solid #e5e7eb;
            padding: 32px;
        }
        .card-title { font-size: 18px; font-weight: 700; color: #111827; margin-bottom: 24px; }

        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
        .form-control {
            width: 100%; padding: 10px 14px;
            border: 1px solid #e5e7eb; border-radius: 8px;
            font-size: 13px; font-family: inherit;
            outline: none; background: #f9fafb; color: #111827;
        }
        .form-control:focus { border-color: #1D9E75; background: #fff; }
        .form-control.error-field { border-color: #dc2626; }
        textarea.form-control { resize: vertical; min-height: 100px; }

        .error-list { margin-bottom: 16px; padding: 10px 14px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; }
        .error-list p { color: #dc2626; font-size: 12px; margin-bottom: 2px; }

        .char-info { font-size: 11px; color: #9ca3af; text-align: right; margin-top: 4px; }
        .char-info.warn { color: #f59e0b; }
        .char-info.over { color: #dc2626; }

        .form-actions { display: flex; gap: 10px; margin-top: 24px; }
        .btn-primary {
            flex: 1; padding: 11px; background: #1D9E75; color: #fff;
            border: none; border-radius: 8px; font-size: 14px;
            font-weight: 600; cursor: pointer;
        }
        .btn-primary:hover { background: #178a64; }
        .btn-secondary {
            padding: 11px 20px; background: none;
            border: 1px solid #e5e7eb; border-radius: 8px;
            font-size: 14px; color: #6b7280; cursor: pointer;
            text-decoration: none; display: inline-block; text-align: center;
        }
        .btn-secondary:hover { background: #f9fafb; }
    </style>
</head>
<body>

<nav class="navbar">
    <a class="navbar-brand" href="indexf.php">
        <div class="navbar-logo">S</div>
        Swaply
    </a>
    <div class="navbar-links">
        <a href="indexf.php">Accueil</a>
        <a href="#">Profils</a>
        <a href="#">Projets</a>
        <a href="#">Offres</a>
        <a href="#">Demandes</a>
        <a href="#">Publications</a>
        <a href="Messages.php" class="active">Messages</a>
        <a href="#">Réclamations</a>
    </div>
    <div class="navbar-avatar"><?= strtoupper(substr($_SESSION['prenom'] ?? 'U', 0, 1)) ?></div>
</nav>

<div class="page-content">
    <div class="card">
        <h1 class="card-title">✏️ Modifier le message</h1>

        <?php if (!empty($errors)): ?>
            <div class="error-list">
                <?php foreach ($errors as $e): ?>
                    <p>⚠ <?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/swaply/controller/MessageController.php?action=editMessage&id=<?= $message['id_message'] ?>" novalidate>
            <div class="form-group">
                <label class="form-label" for="contenu">Contenu *</label>
                <textarea name="contenu" id="contenu" class="form-control"
                    placeholder="Écrivez votre message..."
                    maxlength="2000"
                    oninput="updateCharInfo()"><?= htmlspecialchars($message['contenu'] ?? '') ?></textarea>
                <div class="char-info" id="charInfo">0 / 2000</div>
            </div>

            <div class="form-actions">
                <a href="/swaply/view/Front/messagerie.php" class="btn-secondary">Annuler</a>
                <button type="submit" class="btn-primary">Modifier</button>
            </div>
        </form>
    </div>
</div>

<script>
function updateCharInfo() {
    const ta = document.getElementById('contenu');
    const el = document.getElementById('charInfo');
    const len = ta.value.length;
    el.textContent = len + ' / 2000';
    el.className = 'char-info' + (len > 1800 ? ' warn' : '') + (len >= 2000 ? ' over' : '');
}
updateCharInfo();
</script>
</body>
</html>