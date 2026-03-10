<?php
/**
 * Report Found Item — report_found.php
 */
session_start();
require_once 'config/db.php';
requireLogin();

$categories = ['Electronics','Books & Stationery','Clothing & Accessories','ID Cards & Documents','Keys','Bags & Wallets','Sports Equipment','Jewelry','Other'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include 'includes/head.php'; ?>
  <title>Report Found Item — Lost &amp; Found Hub</title>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-7">
      <div class="card shadow-elevated">
        <div class="card-header bg-success text-white py-3">
          <h4 class="mb-0 font-heading"><i class="bi bi-check-circle me-2"></i>Report Found Item</h4>
        </div>
        <div class="card-body p-4">
          <?php include 'includes/alerts.php'; ?>

          <form action="api/add_item.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="type" value="found">

            <div class="mb-3">
              <label class="form-label fw-medium">Item Name <span class="text-danger">*</span></label>
              <input type="text" name="title" class="form-control" placeholder="e.g. Blue Adidas Backpack" required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-medium">Category <span class="text-danger">*</span></label>
              <select name="category" class="form-select" required>
                <option value="">Select a category</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= $cat ?>"><?= $cat ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label fw-medium">Description <span class="text-danger">*</span></label>
              <textarea name="description" class="form-control" rows="4" placeholder="Describe the item in detail..." required></textarea>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-medium">Location Found <span class="text-danger">*</span></label>
                <input type="text" name="location_name" class="form-control" placeholder="e.g. Cafeteria, Main Campus" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-medium">Date Found <span class="text-danger">*</span></label>
                <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-medium">Pin Location on Map</label>
              <div id="map" class="map-container" style="height:300px;"></div>
              <input type="hidden" name="latitude" id="latitude">
              <input type="hidden" name="longitude" id="longitude">
              <small class="text-muted">Click on the map to mark the exact location</small>
            </div>

            <div class="mb-4">
              <label class="form-label fw-medium">Upload Image</label>
              <input type="file" name="image" id="imageUpload" class="form-control" accept="image/*">
              <img id="imagePreview" src="#" alt="Preview" class="mt-2 rounded" style="display:none;max-height:200px;">
              <small class="text-muted d-block mt-1">Max 5MB • JPG, PNG, GIF</small>
            </div>

            <button type="submit" class="btn btn-success btn-lg w-100">
              <i class="bi bi-send me-2"></i>Register Found Item
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/scripts.php'; ?>
<script>
  initMapPicker('map', 'latitude', 'longitude', 14.5995, 120.9842, false);
</script>
</body>
</html>
