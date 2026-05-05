@echo off
REM Script d'installation du système de messagerie Swaply (Windows)

echo.
echo ============================================
echo 🚀 Installation Système de Messagerie Swaply
echo ============================================
echo.

setlocal enabledelayedexpansion

REM 1. Créer les dossiers
echo [1/3] Création des dossiers...

if not exist "uploads\messages" (
    mkdir "uploads\messages"
    echo ✓ Dossier uploads\messages créé
) else (
    echo ✓ Dossier uploads\messages existe déjà
)

if not exist "tmp" (
    mkdir "tmp"
    echo ✓ Dossier tmp créé
) else (
    echo ✓ Dossier tmp existe déjà
)

REM 2. Migration base de données
echo.
echo [2/3] Exécution de la migration base de données...

echo [2.1/3] Migration des fichiers...
php config\migrate_files.php
if !errorlevel! equ 0 (
    echo ✓ Migration des fichiers complétée
) else (
    echo ⚠ Migration des fichiers (vérifier manuellement)
)

echo [2.2/3] Migration des réactions...
php config\migrate_reactions.php
if !errorlevel! equ 0 (
    echo ✓ Migration des réactions complétée
) else (
    echo ⚠ Migration des réactions (vérifier manuellement)
)

echo.
echo ============================================
echo ✓ Installation terminée!
echo ============================================
echo.
echo 📋 Prochaines étapes:
echo   1. Vérifier que les fichiers sont correctement créés:
echo      - explorer uploads\messages\
echo      - explorer tmp\
echo   2. Accéder à la messagerie:
echo      - http://localhost/xampp/htdocs/swaply/view/Front/Messages.php
echo   3. Voir les statistiques:
echo      - http://localhost/xampp/htdocs/swaply/view/Front/back_office_stats.php
echo.
pause
