#!/bin/bash
# Script d'installation du système de messagerie Swaply

echo "============================================"
echo "🚀 Installation Système de Messagerie Swaply"
echo "============================================"
echo ""

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 1. Créer les dossiers
echo -e "${YELLOW}[1/3]${NC} Création des dossiers..."

if mkdir -p uploads/messages; then
    echo -e "${GREEN}✓${NC} Dossier uploads/messages créé"
else
    echo -e "${RED}✗${NC} Erreur création uploads/messages"
    exit 1
fi

if mkdir -p tmp; then
    echo -e "${GREEN}✓${NC} Dossier tmp créé"
else
    echo -e "${RED}✗${NC} Erreur création tmp"
    exit 1
fi

# 2. Définir les permissions
echo ""
echo -e "${YELLOW}[2/3]${NC} Configuration des permissions..."

chmod 755 uploads/messages
echo -e "${GREEN}✓${NC} Permissions uploads/messages: 755"

chmod 755 tmp
echo -e "${GREEN}✓${NC} Permissions tmp: 755"

# 3. Migration base de données
echo ""
echo -e "${YELLOW}[3/3]${NC} Exécution de la migration base de données..."

echo -e "${YELLOW}[3.1/3]${NC} Migration des fichiers..."
if php config/migrate_files.php 2>/dev/null; then
    echo -e "${GREEN}✓${NC} Migration des fichiers complétée"
else
    echo -e "${YELLOW}⚠${NC} Migration des fichiers (vérifier manuellement)"
fi

echo -e "${YELLOW}[3.2/3]${NC} Migration des réactions..."
if php config/migrate_reactions.php 2>/dev/null; then
    echo -e "${GREEN}✓${NC} Migration des réactions complétée"
else
    echo -e "${YELLOW}⚠${NC} Migration des réactions (vérifier manuellement)"
fi

echo ""
echo "============================================"
echo -e "${GREEN}✓ Installation terminée!${NC}"
echo "============================================"
echo ""
echo "📋 Prochaines étapes:"
echo "  1. Vérifier que les fichiers sont correctement créés:"
echo "     - ls -la uploads/messages/"
echo "     - ls -la tmp/"
echo "  2. Accéder à la messagerie:"
echo "     - http://localhost/xampp/htdocs/swaply/view/Front/Messages.php"
echo "  3. Voir les statistiques:"
echo "     - http://localhost/xampp/htdocs/swaply/view/Front/back_office_stats.php"
echo ""
