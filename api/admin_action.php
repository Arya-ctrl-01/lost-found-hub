<?php
/**
 * Admin Item Actions — api/admin_action.php
 * approve | reject | delete | returned
 */
session_start();
require_once '../config/db.php';
requireAdmin();

$action = $_GET['action'] ?? '';
$id     = intval($_GET['id'] ?? 0);

if (!$id) {
    redirectWith('../admin/admin_dashboard.php', 'error', 'Invalid item');
}

// Get item info
$item = $conn->query("SELECT user_id, title, image FROM items WHERE id=$id")->fetch_assoc();
if (!$item) {
    redirectWith('../admin/admin_dashboard.php', 'error', 'Item not found');
}

switch ($action) {
    case 'approve':
        $conn->query("UPDATE items SET status='approved' WHERE id=$id");
        // Notify owner
        $notif = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $msg = "Your item '{$item['title']}' has been approved and is now visible to everyone.";
        $notif->bind_param('is', $item['user_id'], $msg);
        $notif->execute();
        redirectWith('../admin/admin_dashboard.php?tab=items', 'success', 'Item approved');
        break;

    case 'reject':
        $conn->query("UPDATE items SET status='rejected' WHERE id=$id");
        $notif = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $msg = "Your item '{$item['title']}' has been rejected by admin.";
        $notif->bind_param('is', $item['user_id'], $msg);
        $notif->execute();
        redirectWith('../admin/admin_dashboard.php?tab=items', 'success', 'Item rejected');
        break;

    case 'delete':
        // Delete image file
        if ($item['image'] && file_exists('../uploads/' . $item['image'])) {
            unlink('../uploads/' . $item['image']);
        }
        $conn->query("DELETE FROM items WHERE id=$id");
        redirectWith('../admin/admin_dashboard.php?tab=items', 'success', 'Item deleted');
        break;

    case 'returned':
        $conn->query("UPDATE items SET status='returned' WHERE id=$id");
        $notif = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $msg = "Your item '{$item['title']}' has been marked as returned!";
        $notif->bind_param('is', $item['user_id'], $msg);
        $notif->execute();
        redirectWith('../admin/admin_dashboard.php?tab=items', 'success', 'Item marked as returned');
        break;

    default:
        redirectWith('../admin/admin_dashboard.php', 'error', 'Invalid action');
}
