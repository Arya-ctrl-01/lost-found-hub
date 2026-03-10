<?php
/**
 * Shared Navbar — included in all pages
 * Uses: Bootstrap 5 navbar + custom brand styling
 */
$currentPage = basename($_SERVER['PHP_SELF']);
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

    <!-- Nav items -->
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
          <li class="nav-item">
            <a class="nav-link position-relative" href="<?= $BASE_URL ?>/notifications.php">
              <i class="bi bi-bell"></i>
              <?php
                // Count unread notifications
                $uid = $_SESSION['user_id'];
                $nr = $conn->query("SELECT COUNT(*) as c FROM notifications WHERE user_id=$uid AND is_read=0");
                $unread = $nr ? $nr->fetch_assoc()['c'] : 0;
                if ($unread > 0):
              ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem">
                  <?= $unread ?>
                </span>
              <?php endif; ?>
            </a>
          </li>
          <li class="nav-item">
            <span class="nav-link text-muted" style="cursor:default">
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
