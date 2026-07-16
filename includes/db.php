<?php
/**
 * Database Connection File
 * NexMart E-Commerce
 * 
 * This file handles the database connection using PDO
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'nexmart');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

try {
    // Create PDO connection
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => false
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch (PDOException $e) {
    // Log error and display user-friendly message
    error_log("Database Connection Error: " . $e->getMessage());
    
    // Show error in development
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        die("Database Connection Failed: " . $e->getMessage());
    } else {
        die("Unable to connect to database. Please try again later.");
    }
}

// Set timezone
date_default_timezone_set('UTC');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
