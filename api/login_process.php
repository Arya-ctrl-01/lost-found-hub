<?php
session_start();
require_once '../config/db.php';
require_once '../app/controllers/AuthController.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

// Sanitize input
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    redirectWith('../login.php', 'error', 'Please fill in all fields');
}

// Prepare statement
$stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirectWith('../login.php', 'error', 'Invalid email or password');
}

$user = $result->fetch_assoc();

// Verify password
if (!password_verify($password, $user['password'])) {
    redirectWith('../login.php', 'error', 'Invalid email or password');
}

// Security: regenerate session ID
session_regenerate_id(true);

// Set session
$_SESSION['user_id']   = $user['id'];
$_SESSION['user_name'] = htmlspecialchars($user['name']);
$_SESSION['role']      = $user['role'];

// Redirect
if ($user['role'] === 'admin') {
    header('Location: ../admin/admin_dashboard.php');
} else {
    header('Location: ../dashboard.php');
}
exit;
?>
