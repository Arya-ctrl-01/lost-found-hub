<?php
/**
 * Reusable Item Card (Improved)
 * Expects: $item array
 */
?>

<div class="item-card card h-100 shadow-sm border-0">

  <!-- IMAGE SECTION -->
  <div class="card-img-wrapper position-relative overflow-hidden">

    <?php if (!empty($item['image']) && file_exists("uploads/" . $item['image'])): ?>
      <img 
        src="<?= $BASE_URL ?>/uploads/<?= e($item['image']) ?>" 
        alt="<?= e($item['title']) ?>"
        class="w-100"
        style="height:180px; object-fit:cover;"
      >
    <?php else: ?>
      <div class="card-img-placeholder d-flex align-items-center justify-content-center bg-light"
           style="height:180px;">
        <span style="font-size:30px;">📦</span>
      </div>
    <?php endif; ?>

    <!-- TYPE BADGE -->
    <span class="badge position-absolute top-0 start-0 m-2 
      <?= $item['type']==='lost' ? 'bg-danger' : 'bg-success' ?>">
      <?= $item['type']==='lost' ? '🔴 Lost' : '🟢 Found' ?>
    </span>

    <!-- STATUS BADGE -->
    <span class="badge position-absolute top-0 end-0 m-2 
      <?= $item['status']==='approved' ? 'bg-primary' : 'bg-warning text-dark' ?>">
      <?= ucfirst($item['status']) ?>
    </span>

  </div>

  <!-- BODY -->
  <div class="card-body d-flex flex-column">

    <!-- TITLE -->
    <h6 class="card-title fw-semibold mb-1">
      <?= e($item['title']) ?>
    </h6>

    <!-- DESCRIPTION -->
    <p class="card-text text-muted small mb-2" style="min-height:40px;">
      <?= e(substr($item['description'], 0, 80)) ?>...
    </p>

    <!-- META -->
    <div class="small text-muted mb-2">
      <div><i class="bi bi-geo-alt me-1"></i><?= e($item['location_name']) ?></div>
      <div><i class="bi bi-calendar me-1"></i><?= date('M d, Y', strtotime($item['date'])) ?></div>
    </div>

    <!-- CATEGORY -->
    <span class="badge bg-light text-dark border small mb-2">
      <?= e($item['category']) ?>
    </span>

    <!-- BUTTON -->
    <a href="<?= $BASE_URL ?>/item_details.php?id=<?= $item['id'] ?>"
       class="btn btn-primary btn-sm mt-auto">
       <i class="bi bi-eye me-1"></i>View Details
    </a>

  </div>
</div>