<?php
require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer/src/SMTP.php';
require __DIR__ . '/../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendMail($to, $subject, $body) {

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'universitylostfoundhub@gmail.com';   // 🔥 YOUR EMAIL
        $mail->Password = 'lqmz qnsz sjhj bsqz';      // 🔥 APP PASSWORD
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('universitylostfoundhub@gmail.com', 'Lost & Found Hub');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();

        return true; // ✅ ONLY return true

    } catch (Exception $e) {
        return false; // ❌ NO echo here
    }
}