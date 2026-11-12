(function () {
    const THEME_KEY = 'theme';
    const SIDEBAR_KEY = 'sidebar';

    function applyTheme(theme) {
        const light = theme === 'light';
        document.documentElement.classList.toggle('light', light);
        document.querySelectorAll('#themeToggle').forEach(btn => {
            btn.setAttribute('aria-checked', light ? 'true' : 'false');
            btn.classList.toggle('is-light', light);
            const labelLight = btn.dataset.labelLight || 'Light theme';
            const labelDark = btn.dataset.labelDark || 'Dark theme';
            const label = light ? labelDark : labelLight;
            btn.setAttribute('aria-label', label);
            btn.title = label;
        });
    }

    function isMobile() { return window.matchMedia('(max-width: 900px)').matches; }

    function setSidebar(state) {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        if (isMobile()) {
            const open = state === 'open';
            if (sidebar) sidebar.classList.toggle('open', open);
            if (overlay) overlay.classList.toggle('show', open);
            document.body.style.overflow = open ? 'hidden' : '';
        } else {
            const collapsed = state === 'collapsed';
            document.documentElement.classList.toggle('sidebar-collapsed', collapsed);
            localStorage.setItem(SIDEBAR_KEY, collapsed ? 'collapsed' : 'expanded');
            if (sidebar) sidebar.classList.remove('open');
            if (overlay) overlay.classList.remove('show');
            document.body.style.overflow = '';
        }
    }

    function toggleSidebar() {
        if (isMobile()) {
            const sidebar = document.getElementById('sidebar');
            const open = sidebar && sidebar.classList.contains('open');
            setSidebar(open ? 'closed' : 'open');
        } else {
            const collapsed = document.documentElement.classList.contains('sidebar-collapsed');
            setSidebar(collapsed ? 'expanded' : 'collapsed');
        }
    }

    applyTheme(localStorage.getItem(THEME_KEY) === 'light' ? 'light' : 'dark');

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('#themeToggle').forEach(btn => {
            btn.addEventListener('click', () => {
                const next = document.documentElement.classList.contains('light') ? 'dark' : 'light';
                localStorage.setItem(THEME_KEY, next);
                applyTheme(next);
            });
        });

        const toggleBtn = document.getElementById('sidebarToggle');
        const overlay = document.getElementById('sidebarOverlay');
        if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
        if (overlay) overlay.addEventListener('click', () => setSidebar('closed'));
        document.querySelectorAll('.sidebar .nav-item').forEach(a => {
            a.addEventListener('click', () => { if (isMobile()) setSidebar('closed'); });
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && isMobile()) setSidebar('closed');
        });
        window.addEventListener('resize', () => {
            if (!isMobile()) {
                if (overlay) overlay.classList.remove('show');
                const sidebar = document.getElementById('sidebar');
                if (sidebar) sidebar.classList.remove('open');
                document.body.style.overflow = '';
                const s = localStorage.getItem(SIDEBAR_KEY);
                document.documentElement.classList.toggle('sidebar-collapsed', s === 'collapsed');
            } else {
                document.documentElement.classList.remove('sidebar-collapsed');
                setSidebar('closed');
            }
        });

        const toast = document.getElementById('toast');
        if (toast) {
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }

        // Logout is performed only via the explicit logout button/link.
    });
})();