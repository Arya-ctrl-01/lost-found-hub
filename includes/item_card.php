<?php
/**
 * Reusable Item Card partial
 * Expects: $item array in scope
 */
?>
<div class="item-card card h-100">
  <div class="card-img-wrapper position-relative">
    <?php if ($item['image']): ?>
      <img src="<?= $BASE_URL ?>/uploads/<?= e($item['image']) ?>" alt="<?= e($item['title']) ?>">
    <?php else: ?>
      <div class="card-img-placeholder"><span>📦</span></div>
    <?php endif; ?>
    <span class="badge position-absolute top-0 start-0 m-2 <?= $item['type']==='lost' ? 'bg-danger' : 'bg-success' ?>">
      <?= $item['type']==='lost' ? '🔴 Lost' : '🟢 Found' ?>
    </span>
    <span class="badge position-absolute top-0 end-0 m-2 status-<?= $item['status'] ?>">
      <?= ucfirst($item['status']) ?>
    </span>
    <?php if (!empty($matchedIds[$item['id']])): ?>
    <span class="badge position-absolute bottom-0 start-50 translate-middle-x mb-2 bg-warning text-dark shadow" style="animation: pulse 2s infinite;">
      🔗 Possible Match Found
    </span>
    <?php endif; ?>
  </div>
  <div class="card-body">
    <h6 class="card-title font-heading fw-semibold"><?= e($item['title']) ?></h6>
    <p class="card-text text-muted small line-clamp-2"><?= e($item['description']) ?></p>
    <div class="d-flex flex-column gap-1 text-muted small mb-2">
      <span><i class="bi bi-geo-alt me-1"></i><?= e($item['location_name']) ?></span>
      <span><i class="bi bi-calendar me-1"></i><?= date('M d, Y', strtotime($item['date'])) ?></span>
    </div>
    <span class="badge bg-light text-dark border small"><?= e($item['category']) ?></span>
    <a href="<?= $BASE_URL ?>/item_details.php?id=<?= $item['id'] ?>" class="btn btn-primary btn-sm w-100 mt-3">
      <i class="bi bi-eye me-1"></i>View Details
    </a>
  </div>
</div>
