// Stock app frontend: AdminLTE 4 + Bootstrap 5.3 (no Tailwind/Alpine).
import 'bootstrap/dist/css/bootstrap.min.css';
import 'admin-lte/dist/css/adminlte.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import 'admin-lte/dist/js/adminlte.min.js';

// Bootstrap 5.3 native dark mode via data-bs-theme on <html>.
(function () {
    const KEY = 'stockapp-theme';
    const root = document.documentElement;
    const saved = localStorage.getItem(KEY);
    if (saved) {
        root.setAttribute('data-bs-theme', saved);
    } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
        root.setAttribute('data-bs-theme', 'dark');
    }

    const toggle = document.getElementById('darkModeToggle');
    if (toggle) {
        toggle.addEventListener('click', () => {
            const next = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-bs-theme', next);
            localStorage.setItem(KEY, next);
            document.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: next } }));
        });
    }

    // Shared delete-confirmation modal: point its form at the trigger's data-action.
    document.addEventListener('show.bs.modal', (e) => {
        if (e.target.id !== 'deleteModal') return;
        const btn = document.activeElement;
        const action = btn && btn.getAttribute('data-action');
        if (action) {
            document.getElementById('deleteModalForm').setAttribute('action', action);
        }
    });
})();
