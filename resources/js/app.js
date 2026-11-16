import Chart from 'chart.js/auto';

// Chart.js tersedia untuk grafik di halaman admin.
window.Chart = Chart;

// Sembunyikan atau tampilkan sidebar. Status kembali terbuka pada setiap muat halaman.
const sidebarToggle = document.querySelector('[data-sidebar-toggle]');

const setSidebarCollapsed = (collapsed) => {
    document.documentElement.dataset.sidebarState = collapsed ? 'collapsed' : 'expanded';

    if (!sidebarToggle) {
        return;
    }

    const label = collapsed ? 'Tampilkan menu' : 'Sembunyikan menu';

    sidebarToggle.setAttribute('aria-expanded', String(!collapsed));
    sidebarToggle.setAttribute('aria-label', label);
    sidebarToggle.setAttribute('title', label);
};

if (sidebarToggle) {
    sidebarToggle.addEventListener('click', () => {
        setSidebarCollapsed(document.documentElement.dataset.sidebarState !== 'collapsed');
    });
}

// Tutup dropdown (profil & notifikasi) saat klik di luar atau menekan Escape.
const dropdownSelector = '[data-profile-menu][open], [data-notification-menu][open]';

const closeDropdowns = () => {
    document.querySelectorAll(dropdownSelector).forEach((menu) => {
        menu.removeAttribute('open');
    });
};

document.addEventListener('click', (event) => {
    document.querySelectorAll(dropdownSelector).forEach((menu) => {
        if (!menu.contains(event.target)) {
            menu.removeAttribute('open');
        }
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeDropdowns();
    }
});

// Tampilkan atau sembunyikan isi password pada form password.
document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
    const input = document.getElementById(toggle.dataset.passwordToggle);

    if (! input) {
        return;
    }

    const showIcon = toggle.querySelector('[data-password-icon-show]');
    const hideIcon = toggle.querySelector('[data-password-icon-hide]');

    toggle.addEventListener('click', () => {
        const shouldShow = input.type === 'password';
        const label = shouldShow ? 'Sembunyikan password' : 'Tampilkan password';

        input.type = shouldShow ? 'text' : 'password';
        toggle.setAttribute('aria-label', label);
        toggle.setAttribute('title', label);
        showIcon?.classList.toggle('hidden', shouldShow);
        hideIcon?.classList.toggle('hidden', !shouldShow);
    });
});
