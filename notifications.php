<?php
/**
 * Notifications — notifications.php
 */
session_start();
require_once 'config/db.php';
requireLogin();

$userId = $_SESSION['user_id'];

// Mark all as read if requested
if (isset($_GET['mark_all_read'])) {
    $conn->query("UPDATE notifications SET is_read=1 WHERE user_id=$userId");
    header('Location: notifications.php');
    exit;
}

// Mark single as read
if (isset($_GET['read'])) {
    $rid = intval($_GET['read']);
    $conn->query("UPDATE notifications SET is_read=1 WHERE id=$rid AND user_id=$userId");
    header('Location: notifications.php');
    exit;
}

$notifs = $conn->query("SELECT * FROM notifications WHERE user_id=$userId ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$unreadCount = count(array_filter($notifs, fn($n) => !$n['is_read']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include 'includes/head.php'; ?>
  <title>Notifications — Lost &amp; Found Hub</title>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container py-5" style="max-width:700px;">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="font-heading fw-bold mb-0"><i class="bi bi-bell me-2"></i>Notifications</h2>
    <?php if ($unreadCount > 0): ?>
      <a href="notifications.php?mark_all_read=1" class="btn btn-sm btn-outline-primary">Mark all read</a>
    <?php endif; ?>
  </div>

  <?php if (count($notifs) === 0): ?>
    <div class="text-center py-5">
      <i class="bi bi-bell-slash text-muted" style="font-size:3rem;"></i>
      <p class="text-muted mt-3">No notifications yet.</p>
    </div>
  <?php else: ?>
    <div class="d-flex flex-column gap-3">
      <?php foreach ($notifs as $n): ?>
        <div class="card shadow-card <?= !$n['is_read'] ? 'notif-unread' : '' ?>">
          <div class="card-body d-flex align-items-center gap-3 p-3">
            <i class="bi bi-bell fs-5 <?= $n['is_read'] ? 'text-muted' : 'text-primary-custom' ?>"></i>
            <div class="flex-grow-1">
              <p class="mb-0 small"><?= e($n['message']) ?></p>
              <small class="text-muted"><?= date('M d, Y · h:i A', strtotime($n['created_at'])) ?></small>
            </div>
            <?php if (!$n['is_read']): ?>
              <a href="notifications.php?read=<?= $n['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Mark read">
                <i class="bi bi-check"></i>
              </a>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include 'includes/scripts.php'; ?>
</body>
</html>
