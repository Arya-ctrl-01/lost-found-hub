// ===================================================================
// University Lost & Found Hub — Main JavaScript
// ===================================================================

document.addEventListener('DOMContentLoaded', function () {
  // ——— Tooltips & Popovers ———
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));

  // ——— Confirm Delete ———
  document.querySelectorAll('.btn-confirm-delete').forEach(btn => {
    btn.addEventListener('click', function (e) {
      if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
        e.preventDefault();
      }
    });
  });

  // ——— Image Preview ———
  const imageInput = document.getElementById('imageUpload');
  const imagePreview = document.getElementById('imagePreview');
  if (imageInput && imagePreview) {
    imageInput.addEventListener('change', function () {
      const file = this.files[0];
      if (file) {
        if (file.size > 5 * 1024 * 1024) {
          alert('File size must be less than 5MB');
          this.value = '';
          return;
        }
        const reader = new FileReader();
        reader.onload = function (e) {
          imagePreview.src = e.target.result;
          imagePreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
      }
    });
  }

  // ——— Auto-hide alerts after 5 seconds ———
  document.querySelectorAll('.alert-auto-hide').forEach(alert => {
    setTimeout(() => {
      alert.classList.add('fade');
      setTimeout(() => alert.remove(), 300);
    }, 5000);
  });

  // ——— Mobile sidebar toggle ———
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('adminSidebar');
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('show'));
  }

  // ——— Search debounce ———
  const searchInput = document.getElementById('searchInput');
  if (searchInput) {
    let timeout;
    searchInput.addEventListener('input', function () {
      clearTimeout(timeout);
      timeout = setTimeout(() => {
        this.closest('form').submit();
      }, 600);
    });
  }
});

// ——— Initialize Leaflet Map ———
function initMapPicker(mapId, latId, lngId, defaultLat, defaultLng, readonly) {
  defaultLat = defaultLat || 14.5995;
  defaultLng = defaultLng || 120.9842;
  readonly = readonly || false;

  const map = L.map(mapId).setView([defaultLat, defaultLng], 16);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
  }).addTo(map);

  let marker = L.marker([defaultLat, defaultLng]).addTo(map);

  if (!readonly) {
    map.on('click', function (e) {
      map.removeLayer(marker);
      marker = L.marker(e.latlng).addTo(map);
      document.getElementById(latId).value = e.latlng.lat.toFixed(8);
      document.getElementById(lngId).value = e.latlng.lng.toFixed(8);
    });
  }

  // Fix map rendering in tabs/modals
  setTimeout(() => map.invalidateSize(), 200);
  return map;
}
