<?php
/**
 * QR Verification — api/verify_qr.php
 */
session_start();
require_once '../config/db.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/admin_dashboard.php?tab=qr');
    exit;
}

$qrCode = trim($_POST['qr_code'] ?? '');

if (empty($qrCode)) {
    redirectWith('../admin/admin_dashboard.php?tab=qr', 'error', 'Please enter a QR code');
}

$stmt = $conn->prepare("SELECT c.*, i.title AS item_title FROM claims c JOIN items i ON c.item_id=i.id WHERE c.qr_code=?");
$stmt->bind_param('s', $qrCode);
$stmt->execute();
$claim = $stmt->get_result()->fetch_assoc();

if (!$claim) {
    redirectWith('../admin/admin_dashboard.php?tab=qr', 'error', 'Invalid QR code — no matching claim found');
}

if ($claim['status'] === 'collected') {
    redirectWith('../admin/admin_dashboard.php?tab=qr', 'error', 'This item has already been collected');
}

if ($claim['status'] !== 'approved') {
    redirectWith('../admin/admin_dashboard.php?tab=qr', 'error', 'This claim has not been approved yet (status: ' . $claim['status'] . ')');
}

// Mark as collected
$conn->query("UPDATE claims SET status='collected' WHERE id={$claim['id']}");
$conn->query("UPDATE items SET status='returned' WHERE id={$claim['item_id']}");

// Notify claimant
$notif = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
$msg = "Item '{$claim['item_title']}' has been successfully returned via QR verification. Thank you!";
$notif->bind_param('is', $claim['claimer_id'], $msg);
$notif->execute();

redirectWith('../admin/admin_dashboard.php?tab=qr', 'success', "✅ Verified! Item '{$claim['item_title']}' claimed by {$claim['student_name']} — marked as RETURNED.");
