<?php if (isset($_GET['success'])): ?>
  <div class="alert alert-success alert-dismissible fade show alert-auto-hide" role="alert">
    <i class="bi bi-check-circle me-2"></i><?= e($_GET['success']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show alert-auto-hide" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i><?= e($_GET['error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
