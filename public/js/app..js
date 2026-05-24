/* ── SRS app.js ─────────────────────────────────────────── */

document.addEventListener('DOMContentLoaded', function () {

  /* Sidebar toggle */
  const toggle  = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');
  if (toggle && sidebar) {
    // Restore saved state
    if (localStorage.getItem('sidebarCollapsed') === '1') {
      sidebar.classList.add('collapsed');
    }
    toggle.addEventListener('click', function () {
      sidebar.classList.toggle('collapsed');
      localStorage.setItem('sidebarCollapsed',
        sidebar.classList.contains('collapsed') ? '1' : '0');
    });
  }

  /* Auto-dismiss flash alerts after 6s */
  document.querySelectorAll('.alert').forEach(function (el) {
    setTimeout(function () {
      el.style.transition = 'opacity .4s, max-height .4s';
      el.style.opacity    = '0';
      el.style.maxHeight  = '0';
      el.style.overflow   = 'hidden';
      setTimeout(function () { el.remove(); }, 420);
    }, 6000);
  });

  /* Active sidebar link highlight */
  const path = window.location.pathname.replace(/\/$/, '') || '/';
  document.querySelectorAll('.nav-item').forEach(function (link) {
    const href = link.getAttribute('href') || '';
    if (!href) return;
    const base = href.split('?')[0].replace(/\/$/, '') || '/';
    if (path === base || (base.length > 1 && path.startsWith(base))) {
      link.classList.add('active');
    }
  });

});

/* Image preview before upload */
function previewImage(input) {
  const preview = document.getElementById('imagePreview');
  if (!preview || !input.files || !input.files[0]) return;
  const reader = new FileReader();
  reader.onload = function (e) {
    preview.src = e.target.result;
    preview.style.display = 'block';
  };
  reader.readAsDataURL(input.files[0]);
}
