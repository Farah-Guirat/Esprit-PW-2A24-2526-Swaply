<?php
/**
 * POINT D'ENTRÉE PRINCIPAL (INDEX.PHP)
 * Architecture: MVC
 * Rôle: Router centralisé qui dirige les requêtes vers les contrôleurs
 */

session_start();

define('ROOT', dirname(__FILE__));
define('CONFIG', ROOT . '/config');
define('CONTROLLER', ROOT . '/controller');
define('MODEL', ROOT . '/model');
define('VIEW', ROOT . '/view');
define('PUBLIC_PATH', ROOT . '/public');

require_once CONFIG . '/database.php';

// ── ROUTER ────────────────────────────────────────────────────────────────────
$requestUri = $_SERVER['REQUEST_URI'];
$baseDir    = '/swaply'; // À adapter si besoin
$route      = str_replace($baseDir, '', $requestUri);
$route      = strtok($route, '?');
$route      = trim($route, '/');

$controller = isset($_GET['controller']) ? $_GET['controller'] : 'message';
$action     = isset($_GET['action'])     ? $_GET['action']     : 'index';

if (empty($route) || $route === 'index.php') {
    // Page d'accueil → sélection d'utilisateur si pas de session
    if (!isset($_SESSION['id_user'])) {
        header('Location: view/Front/select_user.php');
        exit;
    }
    $controller = 'message';
    $action     = 'indexFront';
} elseif (strpos($route, 'select_user') !== false) {
    header('Location: view/Front/select_user.php');
    exit;
} elseif (strpos($route, 'messagerie') !== false) {
    $controller = 'message';
    $action     = 'indexFront';
} elseif (strpos($route, 'admin') !== false) {
    $controller = 'message';
    $action     = 'indexBack';
} elseif (strpos($route, 'filter') !== false) {
    $controller = 'filter';
    $action     = 'dashboard';
}

// ── CHARGER LE CONTRÔLEUR ────────────────────────────────────────────────────
try {
    $controllerFile  = CONTROLLER . '/' . ucfirst($controller) . 'Controller.php';

    if (!file_exists($controllerFile)) {
        throw new Exception("Contrôleur '$controller' non trouvé: $controllerFile");
    }

    require_once $controllerFile;

    $controllerClass = ucfirst($controller) . 'Controller';

    if (!class_exists($controllerClass)) {
        throw new Exception("Classe '$controllerClass' non trouvée");
    }

    $ctrl = new $controllerClass();

    if (!method_exists($ctrl, $action)) {
        throw new Exception("Action '$action' non trouvée dans $controllerClass");
    }

    $ctrl->$action();

} catch (Exception $e) {
    http_response_code(404);
    echo "<h1>Erreur 404</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}
?>
