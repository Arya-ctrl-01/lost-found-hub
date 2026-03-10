<?php
/**
 * Login Page — login.php
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
  <title>Login — Lost &amp; Found Hub</title>
</head>
<body style="background:var(--clr-muted);">

<div class="min-vh-100 d-flex align-items-center justify-content-center p-4">
  <div class="card auth-card" style="max-width:420px;width:100%;">
    <div class="card-body p-4 p-md-5">

      <!-- Logo + Heading -->
      <div class="text-center mb-4">
        <a href="index.php" class="brand-icon-lg d-inline-flex align-items-center justify-content-center mb-3">
          <i class="bi bi-search"></i>
        </a>
        <h2 class="font-heading fw-bold mb-1">Welcome Back</h2>
        <p class="text-muted">Sign in to your Lost &amp; Found account</p>
      </div>

      <?php include 'includes/alerts.php'; ?>

      <form action="api/login_process.php" method="POST">
        <div class="mb-3">
          <label class="form-label fw-medium">Email</label>
          <input type="email" name="email" class="form-control form-control-lg" placeholder="you@university.edu" required>
        </div>
        <div class="mb-4">
          <label class="form-label fw-medium">Password</label>
          <input type="password" name="password" class="form-control form-control-lg" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-primary btn-lg w-100">Sign In</button>
      </form>

      <p class="text-center text-muted mt-4 mb-0">
        Don't have an account? <a href="register.php" class="fw-semibold">Register</a>
      </p>
    </div>
  </div>
</div>

<?php include 'includes/scripts.php'; ?>
</body>
</html>
