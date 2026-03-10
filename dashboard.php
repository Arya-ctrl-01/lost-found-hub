<?php
/**
 * Dashboard — dashboard.php
 * Search + Filter + Split Lost/Found panels
 */
session_start();
require_once 'config/db.php';

$search   = trim($_GET['search'] ?? '');
$category = $_GET['category'] ?? 'all';

$categories = ['Electronics','Books & Stationery','Clothing & Accessories','ID Cards & Documents','Keys','Bags & Wallets','Sports Equipment','Jewelry','Other'];

// Build query
$sql = "SELECT * FROM items WHERE status IN ('pending','approved')";
$params = []; $types = '';

if ($search !== '') {
    $sql .= " AND (title LIKE ? OR description LIKE ? OR location_name LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like, $like]);
    $types .= 'sss';
}
if ($category !== 'all') {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= 's';
}
$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$lostItems  = array_filter($items, fn($i) => $i['type'] === 'lost');
$foundItems = array_filter($items, fn($i) => $i['type'] === 'found');

// Compute matched item IDs (items that have an opposite-type item with same category or similar title)
$matchedIds = [];
foreach ($items as $item) {
    $oppositeType = $item['type'] === 'lost' ? 'found' : 'lost';
    foreach ($items as $other) {
        if ($other['type'] === $oppositeType && $other['id'] !== $item['id']) {
            $sameCat = $other['category'] === $item['category'];
            $myWords = explode(' ', strtolower($item['title']));
            $titleMatch = false;
            foreach ($myWords as $w) {
                if (strlen($w) > 2 && str_contains(strtolower($other['title']), $w)) {
                    $titleMatch = true;
                    break;
                }
            }
            if ($sameCat || $titleMatch) {
                $matchedIds[$item['id']] = true;
                break;
            }
        }
    }
}

// ══ Smart Matching: find possible matches for logged-in user's items ══
$matchGroups = [];
if (isset($_SESSION['user_id'])) {
    $myStmt = $conn->prepare("SELECT * FROM items WHERE user_id=? AND status IN ('pending','approved')");
    $myStmt->bind_param('i', $_SESSION['user_id']);
    $myStmt->execute();
    $myItems = $myStmt->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($myItems as $myItem) {
        $opposite = $myItem['type'] === 'lost' ? 'found' : 'lost';
        $candStmt = $conn->prepare("SELECT * FROM items WHERE type=? AND status='approved' AND id!=? LIMIT 20");
        $candStmt->bind_param('si', $opposite, $myItem['id']);
        $candStmt->execute();
        $candidates = $candStmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $scored = [];
        foreach ($candidates as $cand) {
            $score = 0;
            // Category match
            if ($cand['category'] === $myItem['category']) $score += 30;
            // Title word overlap
            $myWords = explode(' ', strtolower($myItem['title']));
            $candWords = explode(' ', strtolower($cand['title']));
            $overlap = count(array_intersect($myWords, $candWords));
            $score += $overlap * 20;
            // Date proximity (within 7 days)
            $dayDiff = abs(strtotime($myItem['date']) - strtotime($cand['date'])) / 86400;
            if ($dayDiff <= 7) $score += 20;
            if ($dayDiff <= 1) $score += 10;
            // Location similarity
            if (!empty($myItem['location_name']) && !empty($cand['location_name'])) {
                $locWord = explode(' ', strtolower($cand['location_name']))[0];
                if (str_contains(strtolower($myItem['location_name']), $locWord)) $score += 20;
            }
            if ($score >= 30) {
                $cand['match_score'] = $score;
                $scored[] = $cand;
            }
        }
        usort($scored, fn($a, $b) => $b['match_score'] - $a['match_score']);
        $scored = array_slice($scored, 0, 3);
        if (count($scored) > 0) {
            $matchGroups[] = ['item' => $myItem, 'matches' => $scored];
        }
    }
}
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

  <!-- ══════ Smart Match Suggestions ══════ -->
  <?php if (!empty($matchGroups)): ?>
  <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, rgba(13,110,253,0.05), rgba(13,110,253,0.02)); border-left: 4px solid #0d6efd !important;">
    <div class="card-body p-4">
      <h5 class="font-heading fw-bold mb-1">
        <i class="bi bi-lightning-charge text-warning me-2"></i>Possible Matches Found
      </h5>
      <p class="text-muted small mb-3">We found items that may match yours based on name, category, location, and date.</p>
      <?php foreach ($matchGroups as $group): ?>
        <p class="fw-medium mb-2">
          Matches for your <?= $group['item']['type'] ?> item: 
          <span class="text-primary">"<?= e($group['item']['title']) ?>"</span>
        </p>
        <div class="row g-2 mb-3">
          <?php foreach ($group['matches'] as $m): ?>
          <div class="col-sm-6 col-lg-4">
            <a href="item_details.php?id=<?= $m['id'] ?>" class="card text-decoration-none h-100 border-0 shadow-sm">
              <div class="card-body p-3">
                <span class="badge bg-<?= $m['type']==='lost'?'danger':'success' ?> mb-2"><?= ucfirst($m['type']) ?></span>
                <h6 class="fw-semibold text-dark mb-1"><?= e($m['title']) ?></h6>
                <small class="text-muted d-block"><i class="bi bi-geo-alt me-1"></i><?= e($m['location_name']) ?></small>
                <small class="text-muted"><i class="bi bi-calendar me-1"></i><?= date('M d, Y', strtotime($m['date'])) ?></small>
                <div class="mt-2"><span class="badge bg-primary bg-opacity-10 text-primary">Match: <?= $m['match_score'] ?>%</span></div>
              </div>
            </a>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- ══════ Search & Filter Bar ══════ -->
  <form method="GET" class="row g-3 mb-4">
    <div class="col-md-8">
      <div class="input-group">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
        <input type="text" name="search" id="searchInput" class="form-control border-start-0"
               placeholder="Search items by name, description, location..."
               value="<?= e($search) ?>">
      </div>
    </div>
    <div class="col-md-3">
      <select name="category" class="form-select" onchange="this.form.submit()">
        <option value="all">All Categories</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat ?>" <?= $category===$cat ? 'selected' : '' ?>><?= $cat ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-1">
      <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i></button>
    </div>
  </form>

  <!-- ══════ Lost / Found Panels ══════ -->
  <div class="row">
    <!-- LEFT: Lost Items -->
    <div class="col-lg-6 mb-4">
      <h3 class="font-heading fw-bold text-danger mb-4">🔴 Lost Items (<?= count($lostItems) ?>)</h3>
      <?php if (count($lostItems) === 0): ?>
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

    <!-- RIGHT: Found Items -->
    <div class="col-lg-6 mb-4">
      <h3 class="font-heading fw-bold text-success mb-4">🟢 Found Items (<?= count($foundItems) ?>)</h3>
      <?php if (count($foundItems) === 0): ?>
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
</div>

<footer class="footer py-4 mt-auto">
  <div class="container text-center"><p class="text-muted mb-0">&copy; <?= date('Y') ?> University Lost &amp; Found Hub</p></div>
</footer>

<?php include 'includes/scripts.php'; ?>
</body>
</html>
