<?php
/**
 * Admin Dashboard — admin/admin_dashboard.php
 * Sidebar + Stats + Items Table + Claims Table + QR Verification
 */
session_start();
require_once '../config/db.php';
requireAdmin();

$tab = $_GET['tab'] ?? 'overview';

// ══ Stats ══
$totalItems   = $conn->query("SELECT COUNT(*) c FROM items")->fetch_assoc()['c'];
$pendingItems = $conn->query("SELECT COUNT(*) c FROM items WHERE status='pending'")->fetch_assoc()['c'];
$totalClaims  = $conn->query("SELECT COUNT(*) c FROM claims")->fetch_assoc()['c'];
$pendingClaims= $conn->query("SELECT COUNT(*) c FROM claims WHERE status='pending'")->fetch_assoc()['c'];
$totalUsers   = $conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'];
$lostCount    = $conn->query("SELECT COUNT(*) c FROM items WHERE type='lost'")->fetch_assoc()['c'];
$foundCount   = $conn->query("SELECT COUNT(*) c FROM items WHERE type='found'")->fetch_assoc()['c'];

// ══ Data ══
$items  = $conn->query("SELECT i.*, u.name AS poster_name FROM items i JOIN users u ON i.user_id=u.id ORDER BY i.created_at DESC LIMIT 50")->fetch_all(MYSQLI_ASSOC);
$claims = $conn->query("SELECT c.*, i.title AS item_title, i.type AS item_type, u.name AS claimer_name, u.email AS claimer_email FROM claims c JOIN items i ON c.item_id=i.id JOIN users u ON c.claimer_id=u.id ORDER BY c.created_at DESC LIMIT 50")->fetch_all(MYSQLI_ASSOC);
$users  = $conn->query("SELECT * FROM users ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

// Adjust BASE_URL for admin subfolder
$BASE_URL_ADMIN = $BASE_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $BASE_URL = '..'; include '../includes/head.php'; $BASE_URL = $BASE_URL_ADMIN; ?>
  <title>Admin Dashboard — Lost &amp; Found Hub</title>
</head>
<body>

<div class="d-flex">
  <!-- ════════ SIDEBAR ════════ -->
  <aside class="admin-sidebar d-none d-lg-block" id="adminSidebar">
    <a class="sidebar-brand" href="<?= $BASE_URL ?>/index.php">
      <span class="brand-icon"><i class="bi bi-search"></i></span>
      Admin Panel
    </a>
    <nav class="p-3 d-flex flex-column gap-1">
      <a href="?tab=overview" class="nav-link-side <?= $tab==='overview'?'active':'' ?>">
        <i class="bi bi-speedometer2"></i> Dashboard
      </a>
      <a href="?tab=items" class="nav-link-side <?= $tab==='items'?'active':'' ?>">
        <i class="bi bi-box-seam"></i> All Items
      </a>
      <a href="?tab=claims" class="nav-link-side <?= $tab==='claims'?'active':'' ?>">
        <i class="bi bi-hand-index"></i> Claims
      </a>
      <a href="?tab=users" class="nav-link-side <?= $tab==='users'?'active':'' ?>">
        <i class="bi bi-people"></i> Users
      </a>
      <a href="?tab=qr" class="nav-link-side <?= $tab==='qr'?'active':'' ?>">
        <i class="bi bi-qr-code-scan"></i> QR Verify
      </a>
      <hr class="border-secondary my-3">
      <a href="<?= $BASE_URL ?>/dashboard.php" class="nav-link-side">
        <i class="bi bi-arrow-left"></i> Back to Site
      </a>
      <a href="<?= $BASE_URL ?>/api/logout.php" class="nav-link-side">
        <i class="bi bi-box-arrow-left"></i> Logout
      </a>
    </nav>
  </aside>

  <!-- ════════ MAIN CONTENT ════════ -->
  <div class="flex-grow-1 bg-light" style="min-height:100vh;">
    <!-- Mobile header -->
    <div class="d-lg-none bg-dark text-white p-3 d-flex align-items-center justify-content-between">
      <span class="fw-bold font-heading">Admin Panel</span>
      <button class="btn btn-outline-light btn-sm" id="sidebarToggle"><i class="bi bi-list"></i></button>
    </div>

    <div class="p-4">
      <?php include '../includes/alerts.php'; ?>

      <h3 class="font-heading fw-bold mb-4">
        <?php
          echo match($tab) {
            'items' => '📦 All Items',
            'claims' => '🤝 Claim Requests',
            'users' => '👥 Users',
            'qr' => '📱 QR Verification',
            default => '📊 Dashboard Overview'
          };
        ?>
      </h3>

      <?php if ($tab === 'overview'): ?>
      <!-- ══════ STATS CARDS ══════ -->
      <div class="row g-3 mb-4">
        <?php
          $stats = [
            ['Total Items', $totalItems, 'bi-box-seam', 'primary'],
            ['Lost Items', $lostCount, 'bi-x-circle', 'danger'],
            ['Found Items', $foundCount, 'bi-check-circle', 'success'],
            ['Pending Items', $pendingItems, 'bi-clock', 'warning'],
            ['Total Claims', $totalClaims, 'bi-hand-index', 'info'],
            ['Total Users', $totalUsers, 'bi-people', 'secondary'],
          ];
          foreach ($stats as $s):
        ?>
        <div class="col-sm-6 col-lg-4 col-xl-2">
          <div class="card border-0 shadow-card">
            <div class="card-body text-center py-3">
              <i class="bi <?= $s[2] ?> text-<?= $s[3] ?> fs-3 d-block mb-2"></i>
              <h3 class="fw-bold mb-0"><?= $s[1] ?></h3>
              <small class="text-muted"><?= $s[0] ?></small>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Quick tables -->
      <div class="card border-0 shadow-card mb-4">
        <div class="card-header bg-white py-3"><h5 class="mb-0 fw-bold">Recent Pending Items</h5></div>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead><tr><th>Item</th><th>Type</th><th>Posted By</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach (array_filter($items, fn($i)=>$i['status']==='pending') as $i): ?>
              <tr>
                <td class="fw-medium"><?= e($i['title']) ?></td>
                <td><span class="badge bg-<?= $i['type']==='lost'?'danger':'success' ?>"><?= ucfirst($i['type']) ?></span></td>
                <td><?= e($i['poster_name']) ?></td>
                <td><?= date('M d', strtotime($i['created_at'])) ?></td>
                <td>
                  <a href="<?= $BASE_URL ?>/api/admin_action.php?action=approve&id=<?= $i['id'] ?>" class="btn btn-success btn-sm"><i class="bi bi-check"></i></a>
                  <a href="<?= $BASE_URL ?>/api/admin_action.php?action=reject&id=<?= $i['id'] ?>" class="btn btn-danger btn-sm"><i class="bi bi-x"></i></a>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (count(array_filter($items, fn($i)=>$i['status']==='pending'))===0): ?>
              <tr><td colspan="5" class="text-muted text-center py-3">No pending items 🎉</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($tab === 'items'): ?>
      <!-- ══════ ALL ITEMS TABLE ══════ -->
      <div class="card border-0 shadow-card">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead><tr><th>Item</th><th>Type</th><th>Category</th><th>Posted By</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach ($items as $i): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <?php if ($i['image']): ?>
                      <img src="<?= $BASE_URL ?>/uploads/<?= e($i['image']) ?>" class="rounded" style="width:40px;height:40px;object-fit:cover;">
                    <?php endif; ?>
                    <a href="<?= $BASE_URL ?>/item_details.php?id=<?= $i['id'] ?>" class="fw-medium text-decoration-none"><?= e($i['title']) ?></a>
                  </div>
                </td>
                <td><span class="badge bg-<?= $i['type']==='lost'?'danger':'success' ?>"><?= ucfirst($i['type']) ?></span></td>
                <td><?= e($i['category']) ?></td>
                <td><?= e($i['poster_name']) ?></td>
                <td><span class="badge status-<?= $i['status'] ?>"><?= ucfirst($i['status']) ?></span></td>
                <td><?= date('M d, Y', strtotime($i['created_at'])) ?></td>
                <td>
                  <div class="d-flex gap-1">
                    <a href="<?= $BASE_URL ?>/item_details.php?id=<?= $i['id'] ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-eye"></i></a>
                    <?php if ($i['status']==='pending'): ?>
                      <a href="<?= $BASE_URL ?>/api/admin_action.php?action=approve&id=<?= $i['id'] ?>" class="btn btn-success btn-sm"><i class="bi bi-check"></i></a>
                      <a href="<?= $BASE_URL ?>/api/admin_action.php?action=reject&id=<?= $i['id'] ?>" class="btn btn-warning btn-sm"><i class="bi bi-x"></i></a>
                    <?php endif; ?>
                    <a href="<?= $BASE_URL ?>/api/admin_action.php?action=delete&id=<?= $i['id'] ?>" class="btn btn-outline-danger btn-sm btn-confirm-delete"><i class="bi bi-trash"></i></a>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($tab === 'claims'): ?>
      <!-- ══════ CLAIMS TABLE ══════ -->
      <div class="card border-0 shadow-card">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead><tr><th>Student</th><th>Item</th><th>Proof</th><th>Status</th><th>QR Code</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach ($claims as $c): ?>
              <tr>
                <td>
                  <p class="fw-medium mb-0"><?= e($c['student_name']) ?></p>
                  <small class="text-muted"><?= e($c['student_id_number']) ?></small>
                </td>
                <td><?= e($c['item_title']) ?></td>
                <td style="max-width:200px;"><small class="text-truncate d-block"><?= e($c['proof_description']) ?></small></td>
                <td><span class="badge status-<?= $c['status'] ?>"><?= ucfirst($c['status']) ?></span></td>
                <td>
                  <?php if ($c['qr_code']): ?>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#qr<?= $c['id'] ?>">
                      <i class="bi bi-qr-code"></i>
                    </button>
                  <?php endif; ?>
                </td>
                <td><?= date('M d', strtotime($c['created_at'])) ?></td>
                <td>
                  <?php if ($c['status']==='pending'): ?>
                    <a href="<?= $BASE_URL ?>/api/claim_action.php?action=approve&id=<?= $c['id'] ?>" class="btn btn-success btn-sm">Approve</a>
                    <a href="<?= $BASE_URL ?>/api/claim_action.php?action=reject&id=<?= $c['id'] ?>" class="btn btn-danger btn-sm">Reject</a>
                  <?php elseif ($c['status']==='approved'): ?>
                    <a href="<?= $BASE_URL ?>/api/claim_action.php?action=collected&id=<?= $c['id'] ?>" class="btn btn-primary btn-sm">Mark Collected</a>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>
              </tr>

              <!-- QR Modal -->
              <div class="modal fade" id="qr<?= $c['id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-sm modal-dialog-centered">
                  <div class="modal-content text-center">
                    <div class="modal-header"><h6 class="modal-title fw-bold">Claim QR Code</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                      <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode($c['qr_code']) ?>" alt="QR" class="mb-2">
                      <p class="small text-muted mb-0"><?= e($c['qr_code']) ?></p>
                    </div>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($tab === 'users'): ?>
      <!-- ══════ USERS TABLE ══════ -->
      <div class="card border-0 shadow-card">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead><tr><th>Name</th><th>Student ID</th><th>Email</th><th>Role</th><th>Joined</th></tr></thead>
            <tbody>
              <?php foreach ($users as $u): ?>
              <tr>
                <td class="fw-medium"><?= e($u['name']) ?></td>
                <td><?= e($u['student_id']) ?></td>
                <td><?= e($u['email']) ?></td>
                <td><span class="badge bg-<?= $u['role']==='admin'?'danger':'primary' ?>"><?= ucfirst($u['role']) ?></span></td>
                <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($tab === 'qr'): ?>
      <!-- ══════ QR VERIFICATION ══════ -->
      <div class="card border-0 shadow-card mb-4">
        <div class="card-body p-4">
          <p class="text-muted mb-4">Enter or scan the QR code value to verify a claim and mark the item as returned.</p>
          <form action="<?= $BASE_URL ?>/api/verify_qr.php" method="POST" class="row g-3 mb-4">
            <div class="col-md-8">
              <input type="text" name="qr_code" id="qrInput" class="form-control form-control-lg" placeholder="Enter QR code value..." required>
            </div>
            <div class="col-md-4">
              <button type="submit" class="btn btn-primary btn-lg w-100">
                <i class="bi bi-qr-code-scan me-2"></i>Verify &amp; Return
              </button>
            </div>
          </form>

          <!-- Live preview of matching claim -->
          <div id="qrPreview"></div>
        </div>
      </div>

      <!-- Approved claims awaiting collection -->
      <?php $approvedClaims = array_filter($claims, fn($c) => $c['status'] === 'approved'); ?>
      <div class="card border-0 shadow-card">
        <div class="card-header bg-white py-3">
          <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2"></i>Approved Claims Awaiting Collection</h5>
        </div>
        <div class="card-body">
          <?php if (count($approvedClaims) === 0): ?>
            <p class="text-muted text-center py-3">No approved claims awaiting collection.</p>
          <?php else: ?>
            <div class="row g-3">
              <?php foreach ($approvedClaims as $ac): ?>
              <div class="col-sm-6 col-lg-4">
                <div class="card shadow-sm text-center">
                  <div class="card-body p-3">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=<?= urlencode($ac['qr_code']) ?>" alt="QR" class="mb-2">
                    <p class="fw-semibold mb-0"><?= e($ac['student_name']) ?></p>
                    <small class="text-muted d-block"><?= e($ac['item_title']) ?></small>
                    <small class="text-muted d-block">ID: <?= e($ac['student_id_number']) ?></small>
                    <a href="<?= $BASE_URL ?>/api/claim_action.php?action=collected&id=<?= $ac['id'] ?>" class="btn btn-primary btn-sm w-100 mt-2">
                      <i class="bi bi-check-circle me-1"></i>Mark Collected
                    </a>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- QR live search script -->
      <script>
      const claims = <?= json_encode(array_values($claims)) ?>;
      const qrInput = document.getElementById('qrInput');
      const qrPreview = document.getElementById('qrPreview');
      if (qrInput) {
        qrInput.addEventListener('input', function() {
          const val = this.value.trim();
          if (!val) { qrPreview.innerHTML = ''; return; }
          const match = claims.find(c => c.qr_code === val);
          if (match) {
            qrPreview.innerHTML = `
              <div class="card border-primary border-opacity-25" style="background:rgba(13,110,253,0.03);">
                <div class="card-body d-flex align-items-center gap-3">
                  <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=${encodeURIComponent(match.qr_code)}" class="rounded">
                  <div>
                    <p class="fw-semibold mb-0">Claim by: ${match.student_name}</p>
                    <small class="text-muted">Item: ${match.item_title}</small><br>
                    <small class="text-muted">ID: ${match.student_id_number} | Contact: ${match.contact_number || 'N/A'}</small><br>
                    <span class="badge status-${match.status} mt-1">${match.status.charAt(0).toUpperCase() + match.status.slice(1)}</span>
                  </div>
                </div>
              </div>`;
          } else {
            qrPreview.innerHTML = '<p class="text-muted small">No matching claim found for this code.</p>';
          }
        });
      }
      </script>
      <?php endif; ?>

    </div>
  </div>
</div>

<?php $BASE_URL = '..'; include '../includes/scripts.php'; ?>
</body>
</html>
