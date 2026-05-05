@echo off
REM ============================================================
REM  Swaply - Méthode la plus simple et fiable
REM  Crée une tâche planifiée Windows qui démarre le serveur
REM  automatiquement au démarrage de Windows
REM
REM  Lance ce fichier UNE SEULE FOIS en Administrateur
REM ============================================================

echo.
echo =====================================================
echo   Swaply Video - Demarrage automatique au boot
echo =====================================================
echo.

REM Vérifier les droits admin
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo ERREUR: Lance ce fichier en tant qu'Administrateur!
    echo Clic droit - "Executer en tant qu'administrateur"
    pause
    exit /b 1
)

REM Chemin du projet
set PROJECT_DIR=%~dp0
REM Supprimer le \ final
if "%PROJECT_DIR:~-1%"=="\" set PROJECT_DIR=%PROJECT_DIR:~0,-1%
set VIDEO_DIR=%PROJECT_DIR%\video_server
set SERVER_FILE=%VIDEO_DIR%\server.js

echo Dossier: %PROJECT_DIR%
echo Serveur: %SERVER_FILE%
echo.

REM Vérifier Node.js
where node >nul 2>&1
if %errorlevel% neq 0 (
    echo ERREUR: Node.js non trouve!
    echo Installe Node.js depuis https://nodejs.org
    pause
    exit /b 1
)

for /f "tokens=*" %%i in ('where node') do set NODE_PATH=%%i
echo Node.js: %NODE_PATH%
echo.

REM Supprimer l'ancienne tâche si elle existe
schtasks /delete /tn "SwaplyVideoServer" /f >nul 2>&1

REM Créer la tâche planifiée
echo [1/2] Creation de la tache planifiee...
schtasks /create ^
  /tn "SwaplyVideoServer" ^
  /tr "\"%NODE_PATH%\" \"%SERVER_FILE%\"" ^
  /sc ONSTART ^
  /ru SYSTEM ^
  /rl HIGHEST ^
  /f ^
  /delay 0000:30

if %errorlevel% equ 0 (
    echo [OK] Tache planifiee creee!
) else (
    echo [WARN] Echec methode 1, tentative methode 2...
    REM Méthode alternative: démarrage avec l'utilisateur courant
    schtasks /create ^
      /tn "SwaplyVideoServer" ^
      /tr "\"%NODE_PATH%\" \"%SERVER_FILE%\"" ^
      /sc ONLOGON ^
      /f ^
      /delay 0000:15
    echo [OK] Tache au login creee!
)

REM Démarrer immédiatement
echo.
echo [2/2] Demarrage immediat du serveur...
schtasks /run /tn "SwaplyVideoServer" >nul 2>&1

REM Attendre 3 secondes et vérifier
timeout /t 3 /nobreak >nul
curl -s http://localhost:3000/health >nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] Serveur demarre et repond sur http://localhost:3000
) else (
    echo [INFO] Serveur en cours de demarrage...
    REM Démarrer manuellement en arrière-plan aussi
    start "SwaplyVideo" /min cmd /c "cd /d "%VIDEO_DIR%" && node server.js"
    echo [OK] Serveur lance en fenetre minimisee
)

echo.
echo =====================================================
echo  INSTALLATION TERMINEE !
echo =====================================================
echo.
echo  Le serveur video demarrera automatiquement a chaque
echo  demarrage de Windows. Plus besoin de rien faire!
echo.
echo  Verification: http://localhost:3000/health
echo  Gestion:      Gestionnaire des taches planifiees
echo                ^> SwaplyVideoServer
echo =====================================================
echo.
pause
