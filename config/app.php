<?php
/**
 * CONFIG APPLICATION - Configuration centralisée
 * Fichier: config/app.php
 */

// ========================================================================
// INFORMATIONS DE L'APPLICATION
// ========================================================================

const APP_NAME = 'Swaply';
const APP_VERSION = '1.0.0';
const APP_ENVIRONMENT = 'production'; // production, development

// ========================================================================
// CHEMINS
// ========================================================================

const PATH_ROOT = __DIR__ . '/..';
const PATH_CONFIG = __DIR__;
const PATH_CONTROLLER = PATH_ROOT . '/controller';
const PATH_MODEL = PATH_ROOT . '/model';
const PATH_VIEW = PATH_ROOT . '/view';
const PATH_PUBLIC = PATH_ROOT . '/public';
const PATH_STORAGE = PATH_ROOT . '/storage';
const PATH_UPLOADS = PATH_PUBLIC . '/uploads';
const PATH_LOGS = PATH_STORAGE . '/logs';
const PATH_TMP = PATH_STORAGE . '/tmp';

// ========================================================================
// BASE DE DONNÉES
// ========================================================================

const DB_HOST = 'localhost';
const DB_NAME = 'swaply';
const DB_USER = 'root';
const DB_PASS = '';
const DB_CHARSET = 'utf8mb4';

// ========================================================================
// SESSION
// ========================================================================

const SESSION_TIMEOUT = 3600; // 1 heure
const SESSION_NAME = 'SWAPLY_SESSION';

// ========================================================================
// SÉCURITÉ
// ========================================================================

const BCRYPT_ROUNDS = 10;
const MAX_LOGIN_ATTEMPTS = 5;
const LOGIN_ATTEMPT_TIMEOUT = 900; // 15 minutes

// ========================================================================
// FICHIERS AUTORISÉS
// ========================================================================

const ALLOWED_FILE_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'jpg', 'jpeg', 'png', 'gif', 'mp3', 'wav'];
const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10 MB

// ========================================================================
// FILTRAGE
// ========================================================================

const FILTERING_ENABLED = true;
const FILTERING_REPLACEMENT_CHAR = '*';

// ========================================================================
// MESSAGES
// ========================================================================

const MESSAGES_PER_PAGE = 20;
const MAX_MESSAGE_LENGTH = 2000;

// ========================================================================
// LOGGING
// ========================================================================

const LOG_ERRORS = true;
const LOG_QUERIES = false;
const LOG_FILE = PATH_LOGS . '/app.log';

?>
