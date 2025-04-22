document.addEventListener('DOMContentLoaded', function () {
  // Enable Bootstrap Tooltips
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.forEach(function (tooltipTriggerEl) {
    new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Sidebar toggle logic
  const sidebar = document.getElementById('sidebar');
  const mainContent = document.getElementById('mainContent');
  const toggleBtn = document.getElementById('toggleSidebar');

  toggleBtn.addEventListener('click', function () {
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('collapsed');

    if (window.innerWidth <= 768) {
      sidebar.classList.toggle('show');
    }
  });

  // Close sidebar on outside click (mobile only)
  document.addEventListener('click', function (e) {
    if (window.innerWidth <= 768 && sidebar.classList.contains('show')) {
      if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
        sidebar.classList.remove('show');
      }
    }
  });

  // Reset sidebar on window resize
  window.addEventListener('resize', () => {
    if (window.innerWidth > 768) {
      sidebar.classList.remove('show');
    }
  });
});