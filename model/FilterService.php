<?php
require_once __DIR__ . '/../config/database.php';

class FilterService {
    private PDO $pdo;
    private array $cachedWords = [];
    private bool $cacheLoaded = false;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    /**
     * Charge les mots filtrés depuis la base de données
     * Utilise un cache pour améliorer les performances
     */
    private function loadFilteredWords(): void {
        if ($this->cacheLoaded) return;

        $stmt = $this->pdo->prepare("
            SELECT word, replacement_count 
            FROM filtered_words 
            WHERE is_active = TRUE
            ORDER BY LENGTH(word) DESC
        ");
        $stmt->execute();
        
        $this->cachedWords = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->cacheLoaded = true;
    }

    /**
     * Filtre le contenu en remplaçant les mots interdits par des astérisques
     * Préserve la casse originale du mot
     * 
     * @param string $content Le contenu à filtrer
     * @return array ['filtered' => string, 'replacements' => int]
     */
    public function filterContent(string $content): array {
        $this->loadFilteredWords();
        
        $filteredContent = $content;
        $replacementCount = 0;

        // Parcourir les mots filtrés (du plus long au plus court pour éviter les collisions)
        foreach ($this->cachedWords as $row) {
            $word = $row['word'];
            $wordLength = strlen($word);
            
            // Remplacer le mot avec les frontières de mots (respecter la casse)
            // Utiliser \b pour les limites de mots
            $pattern = '/\b' . preg_quote($word, '/') . '\b/i';
            
            // Compter les correspondances avant le remplacement
            $matchCount = preg_match_all($pattern, $filteredContent);
            
            if ($matchCount > 0) {
                // Remplacer avec des astérisques de la même longueur que le mot
                $replacement = str_repeat('*', $wordLength);
                $filteredContent = preg_replace($pattern, $replacement, $filteredContent);
                $replacementCount += $matchCount;
            }
        }

        return [
            'filtered' => $filteredContent,
            'replacements' => $replacementCount
        ];
    }

    /**
     * Vérifie si le contenu contient des mots interdits
     * 
     * @param string $content Le contenu à vérifier
     * @return bool true si au moins un mot interdit est trouvé
     */
    public function containsForbiddenWords(string $content): bool {
        $result = $this->filterContent($content);
        return $result['replacements'] > 0;
    }

    /**
     * Ajoute un mot à la liste des mots interdits
     * 
     * @param string $word Le mot à ajouter
     * @param string $category La catégorie du mot
     * @return bool true si succès, false sinon
     */
    public function addFilteredWord(string $word, string $category = 'general'): bool {
        $word = trim(strtolower($word));
        
        if (empty($word) || strlen($word) < 2) {
            return false;
        }

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO filtered_words (word, category) 
                VALUES (:word, :category)
                ON DUPLICATE KEY UPDATE category = :category
            ");
            
            $result = $stmt->execute([
                ':word' => $word,
                ':category' => $category
            ]);
            
            if ($result) {
                $this->clearCache();
                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Supprime un mot de la liste des mots interdits
     * 
     * @param string $word Le mot à supprimer
     * @return bool true si succès, false sinon
     */
    public function removeFilteredWord(string $word): bool {
        $word = trim(strtolower($word));

        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM filtered_words 
                WHERE word = :word
            ");
            
            $result = $stmt->execute([':word' => $word]);
            
            if ($result) {
                $this->clearCache();
                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Récupère tous les mots filtrés avec leurs catégories
     * 
     * @param bool $onlyActive Récupérer uniquement les mots actifs
     * @return array Liste des mots filtrés
     */
    public function getAllFilteredWords(bool $onlyActive = true): array {
        $query = "
            SELECT id_word, word, category, replacement_count, is_active, created_at 
            FROM filtered_words
        ";
        
        if ($onlyActive) {
            $query .= " WHERE is_active = TRUE";
        }
        
        $query .= " ORDER BY category ASC, word ASC";
        
        $stmt = $this->pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les mots filtrés par catégorie
     * 
     * @param string $category La catégorie
     * @return array Liste des mots de cette catégorie
     */
    public function getFilteredWordsByCategory(string $category): array {
        $stmt = $this->pdo->prepare("
            SELECT id_word, word, replacement_count, is_active 
            FROM filtered_words 
            WHERE category = :category AND is_active = TRUE
            ORDER BY word ASC
        ");
        $stmt->execute([':category' => $category]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Met à jour le statut actif/inactif d'un mot
     * 
     * @param int $wordId L'ID du mot
     * @param bool $isActive true pour activer, false pour désactiver
     * @return bool true si succès
     */
    public function setWordActive(int $wordId, bool $isActive): bool {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE filtered_words 
                SET is_active = :is_active 
                WHERE id_word = :id
            ");
            
            $result = $stmt->execute([
                ':id' => $wordId,
                ':is_active' => $isActive ? 1 : 0
            ]);
            
            if ($result) {
                $this->clearCache();
                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Recherche un mot spécifique
     * 
     * @param string $word Le mot à rechercher
     * @return array|null Le mot trouvé ou null
     */
    public function searchWord(string $word): ?array {
        $stmt = $this->pdo->prepare("
            SELECT id_word, word, category, replacement_count, is_active, created_at 
            FROM filtered_words 
            WHERE word = :word
        ");
        $stmt->execute([':word' => strtolower(trim($word))]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Enregistre le remplacement d'un mot dans les statistiques
     * 
     * @param string $word Le mot remplacé
     * @return bool true si succès
     */
    public function incrementReplacementCount(string $word): bool {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE filtered_words 
                SET replacement_count = replacement_count + 1 
                WHERE word = :word
            ");
            return $stmt->execute([':word' => strtolower(trim($word))]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Obtient les statistiques de filtrage
     * 
     * @return array Statistiques globales
     */
    public function getFilteringStats(): array {
        $stmt = $this->pdo->query("
            SELECT 
                COUNT(*) as total_words,
                SUM(CASE WHEN is_active = TRUE THEN 1 ELSE 0 END) as active_words,
                SUM(replacement_count) as total_replacements,
                MAX(replacement_count) as max_replacements
            FROM filtered_words
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC) ?? [];
    }

    /**
     * Obtient les mots les plus remplacés
     * 
     * @param int $limit Nombre maximum de résultats
     * @return array Liste des mots les plus remplacés
     */
    public function getTopFilteredWords(int $limit = 10): array {
        $stmt = $this->pdo->prepare("
            SELECT word, category, replacement_count 
            FROM filtered_words 
            WHERE replacement_count > 0
            ORDER BY replacement_count DESC
            LIMIT :limit
        ");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Vide le cache des mots filtrés
     */
    private function clearCache(): void {
        $this->cachedWords = [];
        $this->cacheLoaded = false;
    }
}
?>
