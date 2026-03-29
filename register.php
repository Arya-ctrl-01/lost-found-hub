<?php
/**
 * Register Page — register.php
 */
session_start();
require_once 'config/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include 'includes/head.php'; ?>
  <title>Register — Lost &amp; Found Hub</title>
</head>
<body style="background:var(--clr-muted);">

<div class="min-vh-100 d-flex align-items-center justify-content-center p-4">
  <div class="card auth-card" style="max-width:420px;width:100%;">
    <div class="card-body p-4 p-md-5">

      <div class="text-center mb-4">
        <a href="index.php" class="brand-icon-lg d-inline-flex align-items-center justify-content-center mb-3">
          <i class="bi bi-search"></i>
        </a>
        <h2 class="font-heading fw-bold mb-1">Create Account</h2>
        <p class="text-muted">Join the University Lost &amp; Found Hub</p>
      </div>

      <?php include 'includes/alerts.php'; ?>

      <form action="api/register_process.php" method="POST">
        <div class="mb-3">
          <label class="form-label fw-medium">Full Name</label>
          <input type="text" name="full_name" class="form-control" placeholder="Your Name" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-medium">Student ID</label>
          <input type="text" name="student_id" class="form-control" placeholder="STU-2024-001" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-medium">Email</label>
          <input type="email" name="email" class="form-control" placeholder="you@university.edu" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-medium">Password</label>
          <input type="password" name="password" class="form-control" placeholder="••••••••" required minlength="6">
        </div>
        <div class="mb-4">
          <label class="form-label fw-medium">Confirm Password</label>
          <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required minlength="6">
        </div>
        <button type="submit" class="btn btn-primary btn-lg w-100">Register Account</button>
      </form>

      <p class="text-center text-muted mt-4 mb-0">
        Already have an account? <a href="login.php" class="fw-semibold">Sign in</a>
      </p>
    </div>
  </div>
</div>

<?php include 'includes/scripts.php'; ?>
</body>
</html>
