
<?php
session_start();
require_once 'config/db.php';
require_once 'includes/mailer.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);

    if (!empty($email)) {

        $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user) {

            $code = rand(100000, 999999);
            $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

            $stmt = $conn->prepare("UPDATE users SET reset_code=?, reset_expiry=? WHERE email=?");
            $stmt->bind_param("sss", $code, $expiry, $email);
            $stmt->execute();

            // 🔥 SEND EMAIL
            if (sendMail($email, "Password Reset Code", "Your OTP is: <b>$code</b>")) {
    header("Location: verify_code.php?email=" . urlencode($email));
    exit();
} else {
    die("❌ Email sending failed");
}

            // ✅ redirect
            header("Location: verify_code.php?email=" . urlencode($email));
            exit();

        } else {
            $error = "❌ Email not found in our system";
        }

    } else {
        $error = "❌ Please enter your email";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <?php include 'includes/head.php'; ?>
  <title>Forgot Password</title>
</head>

<body class="bg-light d-flex align-items-center justify-content-center vh-100">

<div class="card shadow p-4" style="width: 380px; border-radius: 15px;">

  <div class="text-center mb-3">
    <h4 class="fw-bold">Forgot Password</h4>
    <p class="text-muted small">Enter your email to receive OTP</p>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger py-2"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <input type="email" name="email" class="form-control"
             placeholder="Enter your email" required>
    </div>

    <button class="btn btn-primary w-100">
      Send Code
    </button>
  </form>

  <div class="text-center mt-3">
    <a href="login.php" class="small text-decoration-none">Back to Login</a>
  </div>

</div>

</body>
</html>