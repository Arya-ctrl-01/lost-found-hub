<?php
/**
 * Claim Actions — api/claim_action.php
 * approve | reject | collected
 */
session_start();
require_once '../config/db.php';
requireAdmin();

$action = $_GET['action'] ?? '';
$id     = intval($_GET['id'] ?? 0);

if (!$id) {
    redirectWith('../admin/admin_dashboard.php?tab=claims', 'error', 'Invalid claim');
}

$claim = $conn->query("SELECT c.*, i.title AS item_title, i.id AS iid FROM claims c JOIN items i ON c.item_id=i.id WHERE c.id=$id")->fetch_assoc();
if (!$claim) {
    redirectWith('../admin/admin_dashboard.php?tab=claims', 'error', 'Claim not found');
}

$notif = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");

switch ($action) {
    case 'approve':
        $conn->query("UPDATE claims SET status='approved' WHERE id=$id");
        $conn->query("UPDATE items SET status='claimed' WHERE id={$claim['iid']}");
        $msg = "Your claim for '{$claim['item_title']}' has been APPROVED! Visit the Lost & Found office with your QR code to collect.";
        $notif->bind_param('is', $claim['claimer_id'], $msg);
        $notif->execute();
        redirectWith('../admin/admin_dashboard.php?tab=claims', 'success', 'Claim approved');
        break;

    case 'reject':
        $conn->query("UPDATE claims SET status='rejected' WHERE id=$id");
        $msg = "Your claim for '{$claim['item_title']}' has been rejected.";
        $notif->bind_param('is', $claim['claimer_id'], $msg);
        $notif->execute();
        redirectWith('../admin/admin_dashboard.php?tab=claims', 'success', 'Claim rejected');
        break;

    case 'collected':
        $conn->query("UPDATE claims SET status='collected' WHERE id=$id");
        $conn->query("UPDATE items SET status='returned' WHERE id={$claim['iid']}");
        $msg = "Item '{$claim['item_title']}' has been collected and marked as returned. Thank you!";
        $notif->bind_param('is', $claim['claimer_id'], $msg);
        $notif->execute();
        redirectWith('../admin/admin_dashboard.php?tab=claims', 'success', 'Item marked as collected/returned');
        break;

    default:
        redirectWith('../admin/admin_dashboard.php?tab=claims', 'error', 'Invalid action');
}
