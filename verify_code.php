<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config/db.php';

$error = "";
$email = $_GET['email'] ?? '';

if (!$email) {
    die("❌ Email missing in URL");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $code = trim($_POST['code']);

    if (empty($code)) {
        $error = "❌ Please enter OTP";
    } else {

        // Get user data safely
        $stmt = $conn->prepare("SELECT reset_code, reset_expiry FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user) {
            $error = "❌ User not found";
        } elseif (empty($user['reset_code']) || empty($user['reset_expiry'])) {
            $error = "❌ No OTP found. Request again.";
        } else {

            $dbCode = trim($user['reset_code']);
            $expiry = strtotime($user['reset_expiry']);
            $now = time();

            // 🔍 DEBUG (uncomment if needed)
            /*
            echo "Entered: $code<br>";
            echo "DB Code: $dbCode<br>";
            echo "Now: $now<br>";
            echo "Expiry: $expiry<br>";
            exit;
            */

            if ($code !== $dbCode) {
                $error = "❌ Incorrect OTP";
            } elseif (!$expiry || $expiry < $now) {
                $error = "❌ OTP expired";
            } else {

                // Clear OTP after success
                $clear = $conn->prepare("UPDATE users SET reset_code=NULL, reset_expiry=NULL WHERE email=?");
                $clear->bind_param("s", $email);
                $clear->execute();

                header("Location: reset_password.php?email=" . urlencode($email));
                exit();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Verify OTP</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body {
      background: linear-gradient(135deg, #667eea, #764ba2);
      height: 100vh;
    }

    .card-custom {
      border-radius: 18px;
      backdrop-filter: blur(10px);
      background: rgba(255,255,255,0.95);
      box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    }

    .otp-box {
      width: 45px;
      height: 50px;
      text-align: center;
      font-size: 20px;
      border-radius: 10px;
      border: 1px solid #ddd;
      transition: 0.2s;
    }

    .otp-box:focus {
      border-color: #6366f1;
      box-shadow: 0 0 5px rgba(99,102,241,0.5);
      outline: none;
    }

    .btn-primary {
      background: linear-gradient(135deg, #6366f1, #4f46e5);
      border: none;
    }

    .btn-primary:hover {
      opacity: 0.9;
    }
  </style>
</head>

<body class="d-flex align-items-center justify-content-center">

<div class="card card-custom p-4 text-center" style="width: 380px;">

  <!-- Icon -->
  <div class="mb-3">
    <i class="bi bi-shield-lock-fill text-primary" style="font-size: 40px;"></i>
  </div>

  <h4 class="fw-bold mb-2">Verify OTP</h4>

  <?php
    $masked = preg_replace('/(.{2}).*(@.*)/', '$1****$2', $email);
  ?>

  <p class="text-muted small mb-3">
    Code sent to <br><strong><?= $masked ?></strong>
  </p>

  <!-- Error -->
  <?php if (!empty($error)): ?>
    <div class="alert alert-danger py-2 small"><?= $error ?></div>
  <?php endif; ?>

  <!-- OTP FORM -->
  <form method="POST" id="otpForm">

    <div class="d-flex justify-content-between mb-3">
      <?php for ($i = 0; $i < 6; $i++): ?>
        <input type="text" maxlength="1" class="otp-box" required>
      <?php endfor; ?>
    </div>

    <!-- Hidden full OTP -->
    <input type="hidden" name="code" id="fullOtp">

    <button class="btn btn-primary w-100 py-2">Verify Code</button>

  </form>

  <!-- Resend -->
  <div class="mt-3 small">
    <span id="timer">Resend in 30s</span><br>
    <a href="forgot_password.php" id="resendLink" class="text-decoration-none d-none">
      Resend OTP
    </a>
  </div>

</div>

<script>
  const inputs = document.querySelectorAll('.otp-box');
  const fullOtp = document.getElementById('fullOtp');

  inputs.forEach((input, index) => {

    input.addEventListener('input', () => {
      if (input.value.length === 1 && index < inputs.length - 1) {
        inputs[index + 1].focus();
      }

      updateOTP();
    });

    input.addEventListener('keydown', (e) => {
      if (e.key === "Backspace" && input.value === "" && index > 0) {
        inputs[index - 1].focus();
      }
    });
  });

  function updateOTP() {
    let otp = '';
    inputs.forEach(i => otp += i.value);
    fullOtp.value = otp;

    // Auto submit if full
    if (otp.length === 6) {
      document.getElementById('otpForm').submit();
    }
  }

  // TIMER
  let time = 30;
  const timerEl = document.getElementById('timer');
  const resendLink = document.getElementById('resendLink');

  const countdown = setInterval(() => {
    time--;
    timerEl.innerText = "Resend in " + time + "s";

    if (time <= 0) {
      clearInterval(countdown);
      timerEl.classList.add("d-none");
      resendLink.classList.remove("d-none");
    }
  }, 1000);
</script>

</body>
</html>