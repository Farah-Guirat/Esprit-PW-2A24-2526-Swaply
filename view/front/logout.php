<?php
// ── Déconnexion ──────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) session_start();

// Détruire la session
session_unset();
session_destroy();

// Redirection vers la page de login
header('Location: login.php');
exit;
?>
