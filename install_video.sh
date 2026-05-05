#!/bin/bash
# Installation script for Swaply Video Call System
# Usage: bash install.sh

set -e

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║     Installation - Système d'Appel Vidéo Swaply       ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Step 1: Vérifier les prérequis
echo -e "${BLUE}[1/4] Vérification des prérequis...${NC}"

command -v php &> /dev/null || { echo -e "${RED}✗ PHP non trouvé${NC}"; exit 1; }
echo -e "${GREEN}✓ PHP trouvé${NC}"

command -v node &> /dev/null || { echo -e "${RED}✗ Node.js non trouvé${NC}"; exit 1; }
echo -e "${GREEN}✓ Node.js trouvé${NC}"

command -v npm &> /dev/null || { echo -e "${RED}✗ npm non trouvé${NC}"; exit 1; }
echo -e "${GREEN}✓ npm trouvé${NC}"

echo ""

# Step 2: Créer la structure des dossiers
echo -e "${BLUE}[2/4] Création de la structure des dossiers...${NC}"

mkdir -p migrations
mkdir -p asset/js
mkdir -p asset/css
mkdir -p view/Front
mkdir -p video_server

echo -e "${GREEN}✓ Dossiers créés${NC}"
echo ""

# Step 3: Installer les dépendances Node.js
echo -e "${BLUE}[3/4] Installation des dépendances Node.js...${NC}"

cd video_server

if [ ! -f "node_modules/.package-lock.json" ]; then
    echo "Installation des packages npm..."
    npm install
    echo -e "${GREEN}✓ Dépendances Node.js installées${NC}"
else
    echo -e "${GREEN}✓ Dépendances Node.js déjà installées${NC}"
fi

cd ..
echo ""

# Step 4: Migration de la base de données
echo -e "${BLUE}[4/4] Migration de la base de données...${NC}"

read -p "Exécuter la migration SQL maintenant ? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php -r "
        require_once __DIR__ . '/config/database.php';
        try {
            \$pdo = Database::getInstance()->getConnection();
            \$sql = file_get_contents(__DIR__ . '/migrations/003_create_video_calls.sql');
            \$pdo->exec(\$sql);
            echo \"Migration exécutée avec succès.\n\";
        } catch (Exception \$e) {
            die(\"Erreur: \" . \$e->getMessage());
        }
    "
else
    echo "Ignoré. À exécuter plus tard manuellement."
fi

echo -e "${GREEN}✓ Configuration terminée${NC}"
echo ""

# Afficher les prochaines étapes
echo "╔════════════════════════════════════════════════════════════════╗"
echo "║                  Installation terminée!                        ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""
echo -e "${BLUE}Prochaines étapes:${NC}"
echo "1. Démarrer le serveur Node.js:"
echo "   cd video_server && npm start"
echo ""
echo "2. Intégrer dans la messagerie (voir VIDEO_CALL_README.md)"
echo ""
echo "3. Vérifier les permissions:"
echo "   chmod 755 asset/js asset/css view/Front migrations"
echo ""
echo -e "${BLUE}Configuration:${NC}"
echo "- Port serveur: 3000 (configurable dans .env)"
echo "- CORS: localhost:8080, localhost:3306, localhost"
echo ""
echo -e "${GREEN}Pour plus de détails, consultez VIDEO_CALL_README.md${NC}"
