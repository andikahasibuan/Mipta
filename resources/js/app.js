import Chart from 'chart.js/auto';

// Chart.js tersedia untuk grafik di halaman admin.
window.Chart = Chart;

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
