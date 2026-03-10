<?php
/**
 * Item Details — item_details.php
 * Shows: image, description, map, claim button, possible matches
 */
session_start();
require_once 'config/db.php';

if (!isset($_GET['id'])) { header('Location: dashboard.php'); exit; }

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT i.*, u.name AS poster_name FROM items i JOIN users u ON i.user_id=u.id WHERE i.id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) { header('Location: dashboard.php?error=Item not found'); exit; }

// ══ Possible matches (same category, opposite type) ══
$opp = $item['type'] === 'lost' ? 'found' : 'lost';
$mStmt = $conn->prepare("SELECT * FROM items WHERE type=? AND category=? AND id!=? AND status='approved' LIMIT 4");
$mStmt->bind_param('ssi', $opp, $item['category'], $id);
$mStmt->execute();
$matches = $mStmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include 'includes/head.php'; ?>
  <title><?= e($item['title']) ?> — Lost &amp; Found Hub</title>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container py-5" style="max-width:900px;">
  <?php include 'includes/alerts.php'; ?>

  <a href="dashboard.php" class="btn btn-outline-secondary mb-4">
    <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
  </a>

  <div class="row g-4">
    <!-- ══ Left Column: Image + Map ══ -->
    <div class="col-lg-6">
      <div class="card shadow-card overflow-hidden">
        <div class="position-relative">
          <?php if ($item['image']): ?>
            <img src="uploads/<?= e($item['image']) ?>" alt="<?= e($item['title']) ?>" class="w-100" style="max-height:400px;object-fit:cover;">
          <?php else: ?>
            <div class="d-flex align-items-center justify-content-center bg-light" style="height:300px;">
              <span style="font-size:5rem;">📦</span>
            </div>
          <?php endif; ?>
          <span class="badge position-absolute top-0 start-0 m-3 fs-6 <?= $item['type']==='lost' ? 'bg-danger' : 'bg-success' ?>">
            <?= $item['type']==='lost' ? '🔴 Lost' : '🟢 Found' ?>
          </span>
          <span class="badge position-absolute top-0 end-0 m-3 fs-6 status-<?= $item['status'] ?>">
            <?= ucfirst($item['status']) ?>
          </span>
        </div>
      </div>

      <?php if ($item['latitude'] && $item['longitude']): ?>
      <div class="card shadow-card mt-3">
        <div class="card-body">
          <h6 class="font-heading fw-bold mb-3"><i class="bi bi-geo-alt me-2 text-primary-custom"></i>Location Map</h6>
          <div id="map" class="map-container" style="height:250px;"></div>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- ══ Right Column: Details ══ -->
    <div class="col-lg-6">
      <div class="card shadow-card">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge <?= $item['type']==='lost' ? 'bg-danger' : 'bg-success' ?>"><?= ucfirst($item['type']) ?></span>
            <span class="badge status-<?= $item['status'] ?>"><?= ucfirst($item['status']) ?></span>
            <span class="badge bg-light text-dark border"><?= e($item['category']) ?></span>
          </div>

          <h2 class="font-heading fw-bold"><?= e($item['title']) ?></h2>
          <p class="text-muted mt-2"><?= nl2br(e($item['description'])) ?></p>

          <div class="d-flex flex-column gap-2 text-muted mt-4 mb-4">
            <span><i class="bi bi-geo-alt me-2 text-primary-custom"></i><?= e($item['location_name']) ?></span>
            <span><i class="bi bi-calendar me-2 text-primary-custom"></i><?= date('F d, Y', strtotime($item['date'])) ?></span>
            <span><i class="bi bi-person me-2 text-primary-custom"></i>Posted by <?= e($item['poster_name']) ?></span>
          </div>

          <?php if (isset($_SESSION['user_id']) && $item['status'] === 'approved'): ?>
            <button class="btn btn-primary btn-lg w-100 mb-2" data-bs-toggle="modal" data-bs-target="#claimModal">
              <i class="bi bi-hand-index me-2"></i>Claim This Item
            </button>
          <?php elseif (!isset($_SESSION['user_id'])): ?>
            <a href="login.php?error=Please login to claim items" class="btn btn-primary btn-lg w-100 mb-2">
              <i class="bi bi-box-arrow-in-right me-2"></i>Login to Claim
            </a>
          <?php endif; ?>
          <button class="btn btn-outline-secondary w-100">
            <i class="bi bi-flag me-2"></i>Report Listing
          </button>
        </div>
      </div>

      <!-- ══ Possible Matches ══ -->
      <?php if (count($matches) > 0): ?>
      <div class="card shadow-card mt-3">
        <div class="card-body">
          <h6 class="font-heading fw-bold mb-3">
            <i class="bi bi-lightning me-2 text-warning"></i>Possible Matches
          </h6>
          <div class="row g-2">
            <?php foreach ($matches as $m): ?>
            <div class="col-6">
              <a href="item_details.php?id=<?= $m['id'] ?>" class="card text-decoration-none h-100 border-0 shadow-sm">
                <div class="card-body p-2">
                  <small class="fw-semibold d-block text-truncate text-dark"><?= e($m['title']) ?></small>
                  <small class="text-muted"><i class="bi bi-geo-alt"></i> <?= e($m['location_name']) ?></small>
                </div>
              </a>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ════════ CLAIM MODAL ════════ -->
<div class="modal fade" id="claimModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title font-heading fw-bold">Claim: <?= e($item['title']) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="api/claim_item.php" method="POST" id="claimForm">
        <div class="modal-body">
          <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
          <div class="mb-3">
            <label class="form-label fw-medium">Student Name</label>
            <input type="text" name="student_name" class="form-control"
                   value="<?= isset($_SESSION['user_name']) ? e($_SESSION['user_name']) : '' ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-medium">Student ID</label>
            <input type="text" name="student_id_number" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-medium">Contact Number</label>
            <input type="text" name="contact_number" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-medium">Proof of Ownership</label>
            <textarea name="proof_description" class="form-control" rows="3"
                      placeholder="Describe how you can prove this item is yours..." required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Submit Claim Request</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ════════ QR CODE SUCCESS MODAL ════════ -->
<?php
  // Show QR code if claim was just submitted (passed via GET)
  $showQR = $_GET['qr_code'] ?? '';
?>
<?php if ($showQR): ?>
<div class="modal fade" id="qrSuccessModal" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-header border-0">
        <h5 class="modal-title font-heading fw-bold w-100">✅ Claim Submitted!</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small">Save this QR code — an admin will scan it to verify your claim when you collect the item.</p>
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode($showQR) ?>" alt="QR Code" class="mb-3">
        <p class="small text-muted text-break"><?= e($showQR) ?></p>
        <button class="btn btn-outline-primary btn-sm" onclick="navigator.clipboard.writeText('<?= e($showQR) ?>'); this.textContent='Copied!';">
          <i class="bi bi-clipboard me-1"></i>Copy QR Code
        </button>
      </div>
      <div class="modal-footer border-0 justify-content-center">
        <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">Done</button>
      </div>
    </div>
  </div>
</div>
<script>document.addEventListener('DOMContentLoaded', () => new bootstrap.Modal(document.getElementById('qrSuccessModal')).show());</script>
<?php endif; ?>

<?php include 'includes/scripts.php'; ?>
<?php if ($item['latitude'] && $item['longitude']): ?>
<script>
  initMapPicker('map', null, null, <?= $item['latitude'] ?>, <?= $item['longitude'] ?>, true);
</script>
<?php endif; ?>
</body>
</html>
