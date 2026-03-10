<?php
/**
 * Landing Page — index.php
 * Hero section + How It Works + Recent Items + Footer
 */
session_start();
require_once 'config/db.php';

// Fetch recent approved items (max 6)
$recentItems = $conn->query(
  "SELECT * FROM items WHERE status='approved' ORDER BY created_at DESC LIMIT 6"
)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include 'includes/head.php'; ?>
  <title>University Lost &amp; Found Hub</title>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<!-- ════════════ HERO SECTION ════════════ -->
<section class="hero-section">
  <div class="hero-pattern"></div>
  <div class="container position-relative text-center py-5">
    <h1 class="display-4 font-heading fw-bold mb-3">
      University Lost &amp;<br>Found Hub
    </h1>
    <p class="lead mx-auto mb-5" style="max-width:620px;">
      Report lost or found items, search across campus, and securely claim your belongings.
      Powered by smart matching and QR verification.
    </p>
    <div class="d-flex flex-wrap justify-content-center gap-3">
      <a href="dashboard.php" class="btn btn-light btn-lg px-4 fw-semibold">
        <i class="bi bi-search me-2"></i>Search Your Item
      </a>
      <a href="report_lost.php" class="btn btn-outline-light btn-lg px-4 fw-semibold">
        Register Lost Item
      </a>
      <a href="report_found.php" class="btn btn-outline-light btn-lg px-4 fw-semibold">
        Register Found Item
      </a>
    </div>
  </div>
</section>

<!-- ════════════ HOW IT WORKS ════════════ -->
<section class="py-5">
  <div class="container">
    <h2 class="text-center font-heading fw-bold mb-5">How It Works</h2>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="feature-card card h-100 text-center p-4 border-0">
          <div class="feature-icon mx-auto mb-3"><i class="bi bi-search"></i></div>
          <h5 class="font-heading fw-semibold">Report &amp; Search</h5>
          <p class="text-muted mb-0">Report lost or found items and search across the entire campus database.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="feature-card card h-100 text-center p-4 border-0">
          <div class="feature-icon mx-auto mb-3"><i class="bi bi-geo-alt"></i></div>
          <h5 class="font-heading fw-semibold">Map Location</h5>
          <p class="text-muted mb-0">Tag exact locations on an interactive map for precise item tracking.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="feature-card card h-100 text-center p-4 border-0">
          <div class="feature-icon mx-auto mb-3"><i class="bi bi-shield-check"></i></div>
          <h5 class="font-heading fw-semibold">Secure Claims</h5>
          <p class="text-muted mb-0">QR-verified claim process ensures items reach their rightful owners.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ════════════ RECENT ITEMS ════════════ -->
<?php if (count($recentItems) > 0): ?>
<section class="py-5" style="background:var(--clr-muted);">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="font-heading fw-bold mb-0">Recent Items</h2>
      <a href="dashboard.php" class="btn btn-link text-decoration-none fw-semibold">View All <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="row g-4">
      <?php foreach ($recentItems as $item): ?>
      <div class="col-sm-6 col-lg-4">
        <div class="item-card card h-100">
          <div class="card-img-wrapper">
            <?php if ($item['image']): ?>
              <img src="uploads/<?= e($item['image']) ?>" alt="<?= e($item['title']) ?>">
            <?php else: ?>
              <div class="card-img-placeholder"><span>📦</span></div>
            <?php endif; ?>
            <span class="badge position-absolute top-0 start-0 m-2 <?= $item['type']==='lost' ? 'bg-danger' : 'bg-success' ?>">
              <?= $item['type']==='lost' ? '🔴 Lost' : '🟢 Found' ?>
            </span>
            <span class="badge position-absolute top-0 end-0 m-2 status-<?= $item['status'] ?>">
              <?= ucfirst($item['status']) ?>
            </span>
          </div>
          <div class="card-body">
            <h6 class="card-title font-heading fw-semibold"><?= e($item['title']) ?></h6>
            <p class="card-text text-muted small line-clamp-2"><?= e($item['description']) ?></p>
            <div class="d-flex flex-column gap-1 text-muted small mb-2">
              <span><i class="bi bi-geo-alt me-1"></i><?= e($item['location_name']) ?></span>
              <span><i class="bi bi-calendar me-1"></i><?= date('M d, Y', strtotime($item['date'])) ?></span>
            </div>
            <span class="badge bg-light text-dark border small"><?= e($item['category']) ?></span>
            <a href="item_details.php?id=<?= $item['id'] ?>" class="btn btn-primary btn-sm w-100 mt-3">
              <i class="bi bi-eye me-1"></i>View Details
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ════════════ FOOTER ════════════ -->
<footer class="footer py-4">
  <div class="container text-center">
    <p class="text-muted mb-0">&copy; <?= date('Y') ?> University Lost &amp; Found Hub. All rights reserved.</p>
  </div>
</footer>

<?php include 'includes/scripts.php'; ?>
</body>
</html>
