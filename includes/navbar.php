<?php
/**
 * Shared Navbar — updated with Notification System
 */
$currentPage = basename($_SERVER['PHP_SELF']);

// 🔥 FETCH NOTIFICATIONS
$notifications = [];
$unread = 0;

if (isset($_SESSION['user_id'])) {
  $uid = $_SESSION['user_id'];

  // latest 5 notifications
  $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
  $stmt->bind_param("i", $uid);
  $stmt->execute();
  $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

  // unread count
  $stmt2 = $conn->prepare("SELECT COUNT(*) as c FROM notifications WHERE user_id = ? AND is_read = 0");
  $stmt2->bind_param("i", $uid);
  $stmt2->execute();
  $unread = $stmt2->get_result()->fetch_assoc()['c'];
}
?>

<nav class="navbar navbar-expand-lg navbar-main sticky-top shadow-sm">
  <div class="container">

    <!-- Brand -->
    <a class="navbar-brand" href="<?= $BASE_URL ?>/index.php">
      <span class="brand-icon"><i class="bi bi-search"></i></span>
      Lost &amp; Found
    </a>

    <!-- Mobile toggle -->
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <i class="bi bi-list fs-4"></i>
    </button>

    <!-- Nav -->
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-1">

        <li class="nav-item">
          <a class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" href="<?= $BASE_URL ?>/dashboard.php">
            <i class="bi bi-search me-1"></i>Search Items
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link <?= $currentPage === 'report_lost.php' ? 'active' : '' ?>" href="<?= $BASE_URL ?>/report_lost.php">
            Report Lost
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link <?= $currentPage === 'report_found.php' ? 'active' : '' ?>" href="<?= $BASE_URL ?>/report_found.php">
            Report Found
          </a>
        </li>

        <?php if (isset($_SESSION['user_id'])): ?>

          <?php if ($_SESSION['role'] === 'admin'): ?>
            <li class="nav-item">
              <a class="nav-link" href="<?= $BASE_URL ?>/admin/admin_dashboard.php">
                <i class="bi bi-shield-check me-1"></i>Admin
              </a>
            </li>
          <?php endif; ?>

          <!-- 🔔 NOTIFICATION DROPDOWN -->
          <li class="nav-item dropdown">
            <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown">
              <i class="bi bi-bell"></i>

              <?php if ($unread > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem">
                  <?= $unread ?>
                </span>
              <?php endif; ?>
            </a>

         <ul class="dropdown-menu dropdown-menu-end shadow" 
    style="width:320px; max-height:350px; overflow-y:auto;">

  <li class="dropdown-header fw-bold">Notifications</li>

  <?php if (empty($notifications)): ?>
    <li><span class="dropdown-item text-muted">No notifications</span></li>
  <?php else: ?>
    <?php foreach ($notifications as $n): ?>
      <li>
        <div class="dropdown-item small <?= $n['is_read'] ? '' : 'bg-light fw-semibold' ?>"
             style="white-space: normal; word-wrap: break-word;">
          
          <?= e($n['message']) ?><br>

          <small class="text-muted">
            <?= date('M d, H:i', strtotime($n['created_at'])) ?>
          </small>

        </div>
      </li>
    <?php endforeach; ?>
  <?php endif; ?>

  <li><hr class="dropdown-divider"></li>

  <li>
    <a class="dropdown-item text-center small text-primary" href="<?= $BASE_URL ?>/notifications.php">
      View All
    </a>
  </li>
</ul>
          </li>

          <!-- USER -->
          <li class="nav-item">
            <span class="nav-link text-muted">
              <i class="bi bi-person-circle me-1"></i><?= e($_SESSION['user_name']) ?>
            </span>
          </li>

          <li class="nav-item">
            <a class="nav-link text-danger" href="<?= $BASE_URL ?>/api/logout.php">
              <i class="bi bi-box-arrow-right me-1"></i>Logout
            </a>
          </li>

        <?php else: ?>

          <li class="nav-item">
            <a class="nav-link <?= $currentPage === 'login.php' ? 'active' : '' ?>" href="<?= $BASE_URL ?>/login.php">Login</a>
          </li>

          <li class="nav-item ms-lg-2">
            <a class="btn btn-primary btn-sm px-3" href="<?= $BASE_URL ?>/register.php">Register</a>
          </li>

        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>

<?php
// 🔥 AUTO MARK AS READ
if (isset($uid) && !empty($notifications)) {
  $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $uid");
}
?>