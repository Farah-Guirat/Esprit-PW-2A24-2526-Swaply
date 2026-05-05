@echo off
REM Installation script for Swaply Video Call System (Windows)
REM Usage: install_video.bat

setlocal enabledelayedexpansion

echo.
echo ╔════════════════════════════════════════════════════════════════╗
echo ║     Installation - Système d'Appel Vidéo Swaply       ║
echo ╚════════════════════════════════════════════════════════════════╝
echo.

REM Step 1: Check prerequisites
echo [1/4] Verification des prerequisites...

where php >nul 2>&1
if errorlevel 1 (
    echo X PHP non trouve
    exit /b 1
)
echo [OK] PHP trouve

where node >nul 2>&1
if errorlevel 1 (
    echo X Node.js non trouve
    exit /b 1
)
echo [OK] Node.js trouve

where npm >nul 2>&1
if errorlevel 1 (
    echo X npm non trouve
    exit /b 1
)
echo [OK] npm trouve

echo.

REM Step 2: Create folder structure
echo [2/4] Creation de la structure des dossiers...

if not exist "migrations" mkdir migrations
if not exist "asset\js" mkdir asset\js
if not exist "asset\css" mkdir asset\css
if not exist "view\Front" mkdir view\Front
if not exist "video_server" mkdir video_server

echo [OK] Dossiers crees
echo.

REM Step 3: Install Node.js dependencies
echo [3/4] Installation des dependances Node.js...

cd video_server

if not exist "node_modules" (
    echo Installation des packages npm...
    call npm install
    if errorlevel 1 (
        echo X Erreur lors de l'installation npm
        cd ..
        exit /b 1
    )
    echo [OK] Dependances Node.js installees
) else (
    echo [OK] Dependances Node.js deja installees
)

cd ..
echo.

REM Step 4: Database migration (optional)
echo [4/4] Migration de la base de donnees...

set /p MIGRATE="Executer la migration SQL maintenant ? (o/n): "
if /i "%MIGRATE%"=="o" (
    php -r "
        require_once __DIR__ . '/config/database.php';
        try {
            \$pdo = Database::getInstance()->getConnection();
            \$sql = file_get_contents(__DIR__ . '/migrations/003_create_video_calls.sql');
            \$pdo->exec(\$sql);
            echo \"Migration executee avec succes.\n\";
        } catch (Exception \$e) {
            die(\"Erreur: \" . \$e->getMessage());
        }
    "
) else (
    echo Ignore. A executer plus tard manuellement.
)

echo.
echo [OK] Configuration terminee
echo.

REM Display next steps
echo ╔════════════════════════════════════════════════════════════════╗
echo ║                  Installation terminee!                        ║
echo ╚════════════════════════════════════════════════════════════════╝
echo.

echo Prochaines etapes:
echo 1. Demarrer le serveur Node.js (dans un nouveau terminal):
echo    cd video_server
echo    npm start
echo.
echo 2. Integrer dans la messagerie (voir VIDEO_CALL_README.md)
echo.
echo 3. Configuration:
echo    - Port serveur: 3000 (configurable dans .env)
echo    - CORS: localhost:8080, localhost:3306, localhost
echo.
echo Pour plus de details, consultez VIDEO_CALL_README.md
echo.

pause
