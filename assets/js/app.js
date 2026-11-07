document.addEventListener('DOMContentLoaded', () => {
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebar-overlay');
  const toggle = document.getElementById('sidebar-toggle');

  if (sidebar && toggle) {
    function openSidebar() {
      sidebar.classList.remove('-translate-x-full');
      overlay?.classList.remove('hidden');
      document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
      sidebar.classList.add('-translate-x-full');
      overlay?.classList.add('hidden');
      document.body.style.overflow = '';
    }

    toggle.addEventListener('click', () => {
      if (sidebar.classList.contains('-translate-x-full')) {
        openSidebar();
      } else {
        closeSidebar();
      }
    });

    overlay?.addEventListener('click', closeSidebar);

    window.addEventListener('resize', () => {
      if (window.innerWidth >= 1024) {
        closeSidebar();
      }
    });
  }

  // ---------- Dark mode toggle ----------
  const THEME_KEY = 'matchin-theme';
  const themeToggle = document.getElementById('theme-toggle');
  const iconSun = document.getElementById('theme-icon-sun');
  const iconMoon = document.getElementById('theme-icon-moon');

  if (themeToggle) {
    function syncIcon() {
      const isDark = document.documentElement.classList.contains('dark');
      iconSun?.classList.toggle('hidden', !isDark);
      iconMoon?.classList.toggle('hidden', isDark);
    }

    syncIcon();

    themeToggle.addEventListener('click', () => {
      const isDark = document.documentElement.classList.toggle('dark');
      try {
        localStorage.setItem(THEME_KEY, isDark ? 'dark' : 'light');
      } catch (e) {
        // localStorage tidak tersedia (mode private dsb), tema tetap berubah untuk sesi ini
      }
      syncIcon();
    });
  }
});
