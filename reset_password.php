<?php
session_start();
require_once 'config/db.php';

$error = "";
$success = "";

$email = $_GET['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $error = "Passwords do not match";
    } else {

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password=?, reset_code=NULL, reset_expiry=NULL WHERE email=?");
        $stmt->bind_param("ss", $hashed, $email);

        if ($stmt->execute()) {
            $success = "Password updated successfully!";
        } else {
            $error = "Something went wrong";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Reset Password</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex align-items-center justify-content-center vh-100">

<div class="card shadow p-4" style="width: 400px; border-radius: 15px;">

  <div class="text-center mb-3">
    <h4 class="fw-bold">Reset Password</h4>
    <p class="text-muted small"><?= htmlspecialchars($email) ?></p>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php endif; ?>

  <form method="POST">

    <div class="mb-3">
      <input type="password" name="password" class="form-control"
             placeholder="New Password" required>
    </div>

    <div class="mb-3">
      <input type="password" name="confirm_password" class="form-control"
             placeholder="Confirm Password" required>
    </div>

    <button class="btn btn-success w-100">
      Reset Password
    </button>

  </form>

  <div class="text-center mt-3">
    <a href="login.php" class="small text-decoration-none">Back to Login</a>
  </div>

</div>

</body>
</html>