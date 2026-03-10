<?php
/**
 * Submit Claim — api/claim_item.php
 */
session_start();
require_once '../config/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../dashboard.php');
    exit;
}

$itemId           = intval($_POST['item_id'] ?? 0);
$studentName      = trim($_POST['student_name'] ?? '');
$studentIdNumber  = trim($_POST['student_id_number'] ?? '');
$contactNumber    = trim($_POST['contact_number'] ?? '');
$proofDescription = trim($_POST['proof_description'] ?? '');
$claimerId        = $_SESSION['user_id'];

if (!$itemId || empty($studentName) || empty($proofDescription)) {
    redirectWith("../item_details.php?id=$itemId", 'error', 'Please fill in all fields');
}

// Generate unique QR code
$qrCode = 'CLAIM-' . $itemId . '-' . $claimerId . '-' . time() . '-' . bin2hex(random_bytes(4));

$stmt = $conn->prepare(
    "INSERT INTO claims (item_id, claimer_id, student_name, student_id_number, contact_number, proof_description, qr_code)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param('iisssss', $itemId, $claimerId, $studentName, $studentIdNumber, $contactNumber, $proofDescription, $qrCode);

if ($stmt->execute()) {
    // Notify claimant
    $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $msg = "Your claim request has been submitted. QR Code: $qrCode — Please wait for admin review.";
    $notifStmt->bind_param('is', $claimerId, $msg);
    $notifStmt->execute();

    // Notify item owner
    $itemOwner = $conn->query("SELECT user_id, title FROM items WHERE id=$itemId")->fetch_assoc();
    if ($itemOwner && $itemOwner['user_id'] != $claimerId) {
        $ownerMsg = "Someone submitted a claim for your item: '{$itemOwner['title']}'.";
        $notifStmt->bind_param('is', $itemOwner['user_id'], $ownerMsg);
        $notifStmt->execute();
    }

    redirectWith("../item_details.php?id=$itemId&qr_code=" . urlencode($qrCode), 'success', 'Claim submitted! You will be notified when reviewed.');
} else {
    redirectWith("../item_details.php?id=$itemId", 'error', 'Failed to submit claim');
}
