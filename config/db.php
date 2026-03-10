<?php
/**
 * Database Configuration — XAMPP MySQL
 * 
 * HOW TO USE:
 * 1. Start XAMPP → turn on Apache + MySQL
 * 2. Go to http://localhost/phpmyadmin
 * 3. Create database "lost_found_hub" 
 * 4. Import config/database.sql
 * 5. This file auto-connects to the database
 */

$DB_HOST = 'localhost';
$DB_NAME = 'lost_found_hub';
$DB_USER = 'root';       // default XAMPP user
$DB_PASS = '';            // default XAMPP password (empty)

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    die('<div style="text-align:center;padding:50px;font-family:sans-serif;">
        <h2>❌ Database Connection Failed</h2>
        <p>' . $conn->connect_error . '</p>
        <p>Make sure XAMPP MySQL is running and database <b>lost_found_hub</b> exists.</p>
        <p><a href="http://localhost/phpmyadmin" target="_blank">Open phpMyAdmin →</a></p>
    </div>');
}

$conn->set_charset("utf8mb4");

// Helper: redirect with message
function redirectWith($url, $type, $msg) {
    header("Location: $url?$type=" . urlencode($msg));
    exit;
}

// Helper: check if logged in
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /lost_found_hub/login.php?error=Please login first');
        exit;
    }
}

// Helper: check if admin
function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: /lost_found_hub/index.php?error=Access denied');
        exit;
    }
}

// Helper: sanitize output
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Base URL helper (adjust if your folder name is different)
$BASE_URL = '/lost_found_hub';
?>
