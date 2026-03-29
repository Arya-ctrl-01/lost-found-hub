<?php
/**
 * Dashboard — FINAL VERSION (Optimized)
 * Features:
 * - Search
 * - Category filter
 * - Type filter (lost/found)
 * - Pagination
 */
session_start();
require_once 'config/db.php';

$search   = trim($_GET['search'] ?? '');
$category = $_GET['category'] ?? 'all';
$typeFilter = $_GET['type'] ?? 'all';

$categories = ['Electronics','Books & Stationery','Clothing & Accessories','ID Cards & Documents','Keys','Bags & Wallets','Sports Equipment','Jewelry','Other'];

// PAGINATION
$limit = 8;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// BUILD QUERY
$sql = "SELECT * FROM items WHERE status IN ('pending','approved')";
$params = [];
$types = '';

// SEARCH
if ($search !== '') {
    $sql .= " AND (title LIKE ? OR description LIKE ? OR location_name LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like, $like]);
    $types .= 'sss';
}

// CATEGORY FILTER
if ($category !== 'all') {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= 's';
}

// TYPE FILTER (lost/found)
if ($typeFilter !== 'all') {
    $sql .= " AND type = ?";
    $params[] = $typeFilter;
    $types .= 's';
}

// ORDER + PAGINATION
$sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

// EXECUTE
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// SPLIT ITEMS
$lostItems  = array_filter($items, fn($i) => $i['type'] === 'lost');
$foundItems = array_filter($items, fn($i) => $i['type'] === 'found');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <?php include 'includes/head.php'; ?>
  <title>Dashboard — Lost &amp; Found Hub</title>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container py-4">
  <?php include 'includes/alerts.php'; ?>

  <!-- SEARCH + FILTER -->
  <form method="GET" class="row g-3 mb-4">
    
    <div class="col-md-6">
      <input type="text" name="search" class="form-control"
             placeholder="Search items..."
             value="<?= htmlspecialchars($search) ?>">
    </div>

    <div class="col-md-2">
      <select name="category" class="form-select">
        <option value="all">All Categories</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat ?>" <?= $category===$cat ? 'selected' : '' ?>><?= $cat ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-2">
      <select name="type" class="form-select">
        <option value="all">All Types</option>
        <option value="lost" <?= $typeFilter==='lost' ? 'selected' : '' ?>>Lost</option>
        <option value="found" <?= $typeFilter==='found' ? 'selected' : '' ?>>Found</option>
      </select>
    </div>

    <div class="col-md-2">
      <button type="submit" class="btn btn-primary w-100">Apply</button>
    </div>

  </form>

  <!-- LOST / FOUND PANELS -->
  <div class="row">

    <!-- LOST -->
    <div class="col-lg-6 mb-4">
      <h3 class="fw-bold text-danger mb-3">🔴 Lost Items (<?= count($lostItems) ?>)</h3>

      <?php if (empty($lostItems)): ?>
        <p class="text-muted">No lost items found.</p>
      <?php else: ?>
        <div class="row g-3">
          <?php foreach ($lostItems as $item): ?>
            <div class="col-sm-6">
              <?php include 'includes/item_card.php'; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- FOUND -->
    <div class="col-lg-6 mb-4">
      <h3 class="fw-bold text-success mb-3">🟢 Found Items (<?= count($foundItems) ?>)</h3>

      <?php if (empty($foundItems)): ?>
        <p class="text-muted">No found items found.</p>
      <?php else: ?>
        <div class="row g-3">
          <?php foreach ($foundItems as $item): ?>
            <div class="col-sm-6">
              <?php include 'includes/item_card.php'; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </div>

  <!-- PAGINATION -->
  <div class="d-flex justify-content-center mt-4">

    <?php if ($page > 1): ?>
      <a class="btn btn-outline-primary me-2"
         href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&type=<?= urlencode($typeFilter) ?>">
         Prev
      </a>
    <?php endif; ?>

    <span class="align-self-center">Page <?= $page ?></span>

    <?php if (count($items) === $limit): ?>
      <a class="btn btn-outline-primary ms-2"
         href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&type=<?= urlencode($typeFilter) ?>">
         Next
      </a>
    <?php endif; ?>

  </div>

</div>

<footer class="footer py-4 mt-auto">
  <div class="container text-center">
    <p class="text-muted mb-0">&copy; <?= date('Y') ?> Lost &amp; Found Hub</p>
  </div>
</footer>

<?php include 'includes/scripts.php'; ?>
</body>
</html>