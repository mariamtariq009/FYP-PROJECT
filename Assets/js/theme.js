(() => {
    const STORAGE_KEY = 'vms_theme';

    function updateThemeIcon(theme) {
        const icon = document.getElementById('theme-icon');
        if (!icon) return;

        if (theme === 'dark') {
            icon.innerHTML = '🌙';
        } else {
            icon.innerHTML = '☀';
        }
    }

    function applyTheme(theme) {
        const t = theme === 'dark' ? 'dark' : 'light';

        document.body.classList.remove('dark', 'light');
        document.body.classList.add(t);

        localStorage.setItem(STORAGE_KEY, t);

        updateThemeIcon(t);
    }

    function getInitialTheme() {
        const saved = localStorage.getItem(STORAGE_KEY);

        if (saved === 'dark' || saved === 'light') {
            return saved;
        }

        return 'light';
    }

    function toggleTheme() {
        const next =
            document.body.classList.contains('dark')
                ? 'light'
                : 'dark';

        applyTheme(next);
    }

    window.addEventListener('DOMContentLoaded', () => {
        const theme = getInitialTheme();
        applyTheme(theme);

        const btn = document.getElementById('theme-toggle');

        if (btn) {
            btn.addEventListener('click', toggleTheme);
        }
    });
})();