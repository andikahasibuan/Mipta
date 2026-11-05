import Chart from 'chart.js/auto';

// Chart.js tersedia untuk grafik di halaman admin.
window.Chart = Chart;

// Tutup dropdown (profil & notifikasi) saat klik di luar atau menekan Escape.
const dropdownSelector = '[data-profile-menu][open], [data-notification-menu][open], [data-row-actions][open]';

const closeDropdowns = () => {
    document.querySelectorAll(dropdownSelector).forEach((menu) => {
        menu.removeAttribute('open');
    });
};

// Pastikan hanya satu menu aksi baris yang terbuka dalam satu waktu.
document.querySelectorAll('[data-row-actions]').forEach((menu) => {
    menu.addEventListener('toggle', () => {
        if (menu.open) {
            document.querySelectorAll('[data-row-actions][open]').forEach((other) => {
                if (other !== menu) {
                    other.open = false;
                }
            });
        }
    });
});

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
