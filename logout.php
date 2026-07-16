<?php
/**
 * Logout Page
 * NexMart E-Commerce
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Clear remember me cookie
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Destroy session
session_unset();
session_destroy();

// Redirect to home
header('Location: index.php');
exit;
?>
