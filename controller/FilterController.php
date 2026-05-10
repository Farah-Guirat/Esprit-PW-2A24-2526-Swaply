<?php
require_once __DIR__ . '/../model/FilterService.php';
require_once __DIR__ . '/../config/database.php';

class FilterController {

    private FilterService $filterService;

    public function __construct() {
        $this->filterService = new FilterService();
        $this->startSession();
    }

    private function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    /**
     * Afficher le tableau de bord du filtrage (back office)
     */
    public function dashboard(): void {
        // Vérifier que l'utilisateur est admin
        if (!$this->isAdmin()) {
            header('Location: ../view/Front/indexf.php');
            exit;
        }

        $stats = $this->filterService->getFilteringStats();
        $allWords = $this->filterService->getAllFilteredWords(false);
        $topWords = $this->filterService->getTopFilteredWords(10);

        require __DIR__ . '/../view/Back/filter_dashboard.php';
    }

    /**
     * Afficher la liste des mots filtrés avec pagination
     */
    public function listWords(): void {
        if (!$this->isAdmin()) {
            header('Location: ../view/Front/indexf.php');
            exit;
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 50;
        $allWords = $this->filterService->getAllFilteredWords(false);
        
        $totalPages = ceil(count($allWords) / $perPage);
        if ($page < 1) $page = 1;
        if ($page > $totalPages) $page = $totalPages;

        $offset = ($page - 1) * $perPage;
        $paginatedWords = array_slice($allWords, $offset, $perPage);

        require __DIR__ . '/../view/Back/filter_manage_words.php';
    }

    /**
     * Ajouter un mot interdit
     */
    public function addWord(): void {
        if (!$this->isAdmin()) {
            echo json_encode(['success' => false, 'error' => 'Accès refusé']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
            exit;
        }

        $word = isset($_POST['word']) ? trim($_POST['word']) : '';
        $category = isset($_POST['category']) ? trim($_POST['category']) : 'general';

        if (empty($word) || strlen($word) < 2) {
            echo json_encode(['success' => false, 'error' => 'Le mot doit contenir au moins 2 caractères']);
            exit;
        }

        if (strlen($word) > 255) {
            echo json_encode(['success' => false, 'error' => 'Le mot ne peut pas dépasser 255 caractères']);
            exit;
        }

        $result = $this->filterService->addFilteredWord($word, $category);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Mot ajouté avec succès']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Le mot existe déjà ou une erreur est survenue']);
        }
        exit;
    }

    /**
     * Supprimer un mot interdit
     */
    public function deleteWord(): void {
        if (!$this->isAdmin()) {
            echo json_encode(['success' => false, 'error' => 'Accès refusé']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
            exit;
        }

        $word = isset($_POST['word']) ? trim($_POST['word']) : '';

        if (empty($word)) {
            echo json_encode(['success' => false, 'error' => 'Mot invalide']);
            exit;
        }

        $result = $this->filterService->removeFilteredWord($word);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Mot supprimé avec succès']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erreur lors de la suppression']);
        }
        exit;
    }

    /**
     * Basculer l'état actif/inactif d'un mot
     */
    public function toggleWord(): void {
        if (!$this->isAdmin()) {
            echo json_encode(['success' => false, 'error' => 'Accès refusé']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
            exit;
        }

        $wordId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $isActive = isset($_POST['is_active']) ? (bool)$_POST['is_active'] : false;

        if ($wordId <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID invalide']);
            exit;
        }

        $result = $this->filterService->setWordActive($wordId, $isActive);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Mot mis à jour avec succès']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erreur lors de la mise à jour']);
        }
        exit;
    }

    /**
     * Rechercher un mot
     */
    public function searchWord(): void {
        if (!$this->isAdmin()) {
            echo json_encode(['success' => false, 'error' => 'Accès refusé']);
            exit;
        }

        $word = isset($_GET['q']) ? trim($_GET['q']) : '';

        if (empty($word)) {
            echo json_encode(['success' => false, 'error' => 'Recherche vide']);
            exit;
        }

        $result = $this->filterService->searchWord($word);
        
        if ($result) {
            echo json_encode(['success' => true, 'data' => $result]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Mot non trouvé']);
        }
        exit;
    }

    /**
     * Obtenir les statistiques de filtrage
     */
    public function getStats(): void {
        if (!$this->isAdmin()) {
            echo json_encode(['success' => false, 'error' => 'Accès refusé']);
            exit;
        }

        $stats = $this->filterService->getFilteringStats();
        echo json_encode(['success' => true, 'data' => $stats]);
        exit;
    }

    /**
     * Importer des mots depuis un fichier CSV
     */
    public function importWords(): void {
        if (!$this->isAdmin()) {
            echo json_encode(['success' => false, 'error' => 'Accès refusé']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['file'])) {
            echo json_encode(['success' => false, 'error' => 'Aucun fichier envoyé']);
            exit;
        }

        $file = $_FILES['file'];
        $allowedMimes = ['text/csv', 'text/plain', 'application/vnd.ms-excel'];
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimes)) {
            echo json_encode(['success' => false, 'error' => 'Type de fichier non autorisé']);
            exit;
        }

        $importedCount = 0;
        $errors = [];
        $handle = fopen($file['tmp_name'], 'r');

        if ($handle) {
            // Passer la première ligne (en-tête)
            fgetcsv($handle, 1000, ",");

            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                if (count($data) >= 2) {
                    $word = trim($data[0]);
                    $category = trim($data[1]) ?: 'general';

                    if (!empty($word) && strlen($word) >= 2) {
                        if ($this->filterService->addFilteredWord($word, $category)) {
                            $importedCount++;
                        }
                    }
                }
            }
            fclose($handle);
        }

        echo json_encode([
            'success' => true,
            'message' => "$importedCount mots importés avec succès"
        ]);
        exit;
    }

    /**
     * Exporter les mots filtrés en CSV
     */
    public function exportWords(): void {
        if (!$this->isAdmin()) {
            echo json_encode(['success' => false, 'error' => 'Accès refusé']);
            exit;
        }

        $words = $this->filterService->getAllFilteredWords(false);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="filtered_words_' . date('Y-m-d_H-i-s') . '.csv"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

        // En-têtes
        fputcsv($output, ['Mot', 'Catégorie', 'Remplacements', 'Actif', 'Créé le'], ',');

        // Données
        foreach ($words as $word) {
            fputcsv($output, [
                $word['word'],
                $word['category'],
                $word['replacement_count'],
                $word['is_active'] ? 'Oui' : 'Non',
                $word['created_at']
            ], ',');
        }

        fclose($output);
        exit;
    }

    /**
     * Vérifier si l'utilisateur est administrateur
     */
    private function isAdmin(): bool {
        // À adapter selon votre système d'authentification
        // Pour l'instant, vérifier juste que l'utilisateur est connecté
        if (!isset($_SESSION['id_user'])) {
            return false;
        }
        
        // Vous pouvez ajouter ici une vérification plus stricte
        // Si vous avez un rôle admin dans votre système
        return true; // À modifier selon vos besoins
    }
}

// ─── SYSTEM DE ROUTAGE ───────────────────────────────────────────────────────
// Traite les requêtes ?action=xxx pour les appels AJAX et directs
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    $controller = new FilterController();
    $action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';
    
    // Vérifier que la méthode existe
    if (method_exists($controller, $action)) {
        $controller->$action();
    } else {
        // Sinon, afficher le dashboard par défaut
        $controller->dashboard();
    }
}
?>
