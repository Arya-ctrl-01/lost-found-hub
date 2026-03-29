<?php
session_start();
require_once 'config/db.php';

if (!isset($_GET['id'])) { header('Location: dashboard.php'); exit; }

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT i.*, u.name AS poster_name FROM items i JOIN users u ON i.user_id=u.id WHERE i.id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) { header('Location: dashboard.php?error=Item not found'); exit; }

// Matches
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
  <title><?= e($item['title']) ?> — Lost & Found Hub</title>

  <!-- ✅ Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

  <!-- ✅ Leaflet JS -->
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="container py-5" style="max-width:900px;">
  <?php include 'includes/alerts.php'; ?>

  <a href="dashboard.php" class="btn btn-outline-secondary mb-4">
    <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
  </a>

  <div class="row g-4">

    <!-- LEFT -->
    <div class="col-lg-6">

      <!-- IMAGE -->
      <div class="card shadow-card overflow-hidden">
        <div class="position-relative">

          <?php if (!empty($item['image']) && file_exists("uploads/".$item['image'])): ?>
            <img src="uploads/<?= e($item['image']) ?>" class="w-100" style="max-height:400px;object-fit:cover;">
          <?php else: ?>
            <div class="d-flex align-items-center justify-content-center bg-light" style="height:300px;">
              <span style="font-size:5rem;">📦</span>
            </div>
          <?php endif; ?>

          <!-- TYPE -->
          <span class="badge position-absolute top-0 start-0 m-3 fs-6 
            <?= $item['type']==='lost' ? 'bg-danger' : 'bg-success' ?>">
            <?= $item['type']==='lost' ? '🔴 Lost' : '🟢 Found' ?>
          </span>

          <!-- STATUS -->
          <span class="badge position-absolute top-0 end-0 m-3 fs-6 
            <?= $item['status']==='approved' ? 'bg-primary' : 'bg-warning text-dark' ?>">
            <?= ucfirst($item['status']) ?>
          </span>

        </div>
      </div>

      <!-- MAP -->
      <div class="card shadow-card mt-3">
        <div class="card-body">
          <h6 class="fw-bold mb-3">
            <i class="bi bi-geo-alt me-2 text-primary-custom"></i>Location Map
          </h6>

          <?php if ($item['latitude'] && $item['longitude']): ?>
            <div id="map" style="height:250px; border-radius:8px;"></div>
          <?php else: ?>
            <p class="text-muted small">Location not available</p>
          <?php endif; ?>

        </div>
      </div>

    </div>

    <!-- RIGHT -->
    <div class="col-lg-6">
      <div class="card shadow-card">
        <div class="card-body p-4">

          <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge <?= $item['type']==='lost' ? 'bg-danger' : 'bg-success' ?>">
              <?= ucfirst($item['type']) ?>
            </span>

            <span class="badge <?= $item['status']==='approved' ? 'bg-primary' : 'bg-warning text-dark' ?>">
              <?= ucfirst($item['status']) ?>
            </span>

            <span class="badge bg-light text-dark border">
              <?= e($item['category']) ?>
            </span>
          </div>

          <h2 class="fw-bold"><?= e($item['title']) ?></h2>
          <p class="text-muted mt-2"><?= nl2br(e($item['description'])) ?></p>

          <div class="text-muted mt-4 mb-4 small">
            <div><i class="bi bi-geo-alt me-2"></i><?= e($item['location_name']) ?></div>
            <div><i class="bi bi-calendar me-2"></i><?= date('F d, Y', strtotime($item['date'])) ?></div>
            <div><i class="bi bi-person me-2"></i>Posted by <?= e($item['poster_name']) ?></div>
          </div>

          <?php if ($item['type'] === 'lost'): ?>
            <?php if (isset($_SESSION['user_id'])): ?>
              <button class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#claimModal">
                Claim This Item
              </button>
            <?php else: ?>
              <a href="login.php" class="btn btn-primary w-100 mb-2">Login to Claim</a>
            <?php endif; ?>
          <?php endif; ?>

        </div>
      </div>

      <!-- MATCHES -->
      <?php if ($matches): ?>
      <div class="card shadow-card mt-3">
        <div class="card-body">
          <h6 class="fw-bold mb-3">Possible Matches</h6>

          <div class="row g-2">
            <?php foreach ($matches as $m): ?>
            <div class="col-6">
              <a href="item_details.php?id=<?= $m['id'] ?>" class="card p-2 text-decoration-none">
                <small class="fw-semibold"><?= e($m['title']) ?></small>
                <small class="text-muted"><?= e($m['location_name']) ?></small>
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

<?php include 'includes/scripts.php'; ?>

<!-- MAP SCRIPT -->
<?php if ($item['latitude'] && $item['longitude']): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const lat = <?= (float)$item['latitude'] ?>;
    const lng = <?= (float)$item['longitude'] ?>;

    const map = L.map('map').setView([lat, lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    L.marker([lat, lng]).addTo(map)
        .bindPopup("Item Location")
        .openPopup();
});
</script>
<?php endif; ?>

</body>
</html>