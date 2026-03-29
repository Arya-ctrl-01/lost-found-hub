<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.php');
    exit;
}

// Sanitize inputs
$fullName  = htmlspecialchars(trim($_POST['full_name'] ?? ''));
$studentId = htmlspecialchars(trim($_POST['student_id'] ?? ''));
$email     = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$password  = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Validation
if (empty($fullName) || empty($studentId) || empty($email) || empty($password)) {
    redirectWith('../register.php', 'error', 'Please fill in all fields');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectWith('../register.php', 'error', 'Invalid email format');
}

if ($password !== $confirmPassword) {
    redirectWith('../register.php', 'error', 'Passwords do not match');
}

if (strlen($password) < 6) {
    redirectWith('../register.php', 'error', 'Password must be at least 6 characters');
}

// Check existing email
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    redirectWith('../register.php', 'error', 'Email already registered');
}

// Check existing student ID
$stmt = $conn->prepare("SELECT id FROM users WHERE student_id = ?");
$stmt->bind_param('s', $studentId);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    redirectWith('../register.php', 'error', 'Student ID already registered');
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert user
$stmt = $conn->prepare("INSERT INTO users (name, student_id, email, password, role) VALUES (?, ?, ?, ?, 'student')");
$stmt->bind_param('ssss', $fullName, $studentId, $email, $hashedPassword);

if ($stmt->execute()) {
    redirectWith('../login.php', 'success', 'Account created successfully! Please login.');
} else {
    redirectWith('../register.php', 'error', 'Registration failed. Try again.');
}
?>
