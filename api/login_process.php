<?php
/**
 * Login Process — api/login_process.php
 */
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    redirectWith('../login.php', 'error', 'Please fill in all fields');
}

$stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirectWith('../login.php', 'error', 'Invalid email or password');
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    redirectWith('../login.php', 'error', 'Invalid email or password');
}

// Set session
$_SESSION['user_id']   = $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['role']      = $user['role'];

// Redirect based on role
if ($user['role'] === 'admin') {
    header('Location: ../admin/admin_dashboard.php');
} else {
    header('Location: ../dashboard.php');
}
exit;
