<?php
/**
 * Classe de validation des formulaires - Contrôle de saisie fonctionnel
 * Respecte les principes POO et MVC
 */

class FormValidator {
    private array $errors = [];
    private array $data = [];
    private array $rules = [];

    public function __construct(array $data = []) {
        $this->data = array_map('trim', $data);
    }

    /**
     * Ajouter une règle de validation
     */
    public function addRule(string $field, string $rule, string $message = ''): self {
        if (!isset($this->rules[$field])) {
            $this->rules[$field] = [];
        }
        $this->rules[$field][] = [
            'rule' => $rule,
            'message' => $message ?: "Le champ '$field' est invalide"
        ];
        return $this;
    }

    /**
     * Valider les données
     */
    public function validate(): bool {
        $this->errors = [];

        foreach ($this->rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? '';

            foreach ($fieldRules as $ruleData) {
                $rule = $ruleData['rule'];
                $message = $ruleData['message'];

                if (!$this->checkRule($field, $value, $rule)) {
                    $this->errors[$field][] = $message;
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Vérifier une règle
     */
    private function checkRule(string $field, string $value, string $rule): bool {
        // Parse rule format: "rule:param1,param2"
        $parts = explode(':', $rule);
        $ruleName = $parts[0];
        $params = isset($parts[1]) ? explode(',', $parts[1]) : [];

        switch ($ruleName) {
            case 'required':
                return !empty($value);

            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;

            case 'min_length':
                $min = $params[0] ?? 1;
                return strlen($value) >= (int)$min;

            case 'max_length':
                $max = $params[0] ?? 255;
                return strlen($value) <= (int)$max;

            case 'numeric':
                return is_numeric($value);

            case 'integer':
                return filter_var($value, FILTER_VALIDATE_INT) !== false;

            case 'positive':
                return is_numeric($value) && (int)$value > 0;

            case 'regex':
                $pattern = $params[0] ?? '';
                return !empty($pattern) && preg_match($pattern, $value);

            case 'phone':
                return preg_match('/^[0-9\s\-\+\(\)]{10,}$/', $value);

            case 'url':
                return filter_var($value, FILTER_VALIDATE_URL) !== false;

            case 'alphanumeric':
                return preg_match('/^[a-zA-Z0-9_\-\.]*$/', $value);

            case 'unique':
                // Pour la base de données - implémenter dans le contrôleur
                return true;

            default:
                return true;
        }
    }

    /**
     * Obtenir les erreurs
     */
    public function getErrors(): array {
        return $this->errors;
    }

    /**
     * Obtenir une erreur spécifique
     */
    public function getError(string $field): ?string {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Vérifier s'il y a une erreur pour un champ
     */
    public function hasError(string $field): bool {
        return isset($this->errors[$field]) && !empty($this->errors[$field]);
    }

    /**
     * Afficher les erreurs en JSON
     */
    public function getErrorsJson(): string {
        return json_encode($this->errors);
    }

    /**
     * Obtenir les données validées
     */
    public function getData(): array {
        return $this->data;
    }

    /**
     * Obtenir une valeur spécifique
     */
    public function get(string $field, $default = null) {
        return $this->data[$field] ?? $default;
    }
}

// EXEMPLE D'UTILISATION:
/*
$validator = new FormValidator($_POST);

$validator
    ->addRule('email', 'required', 'L\'email est obligatoire')
    ->addRule('email', 'email', 'L\'email doit être valide')
    ->addRule('password', 'required', 'Le mot de passe est obligatoire')
    ->addRule('password', 'min_length:8', 'Le mot de passe doit avoir au moins 8 caractères')
    ->addRule('phone', 'phone', 'Le téléphone doit être valide');

if ($validator->validate()) {
    // Les données sont valides
    $email = $validator->get('email');
} else {
    // Afficher les erreurs
    echo json_encode(['success' => false, 'errors' => $validator->getErrors()]);
}
*/
?>
