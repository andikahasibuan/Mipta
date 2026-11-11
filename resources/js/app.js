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

const closeRowActions = () => {
    document.querySelectorAll('[data-row-actions][open]').forEach((menu) => {
        menu.removeAttribute('open');
    });
};

// Posisikan menu aksi secara fixed agar tidak terikat pada overflow tabel.
const positionRowActionsMenu = (details) => {
    const trigger = details.querySelector('summary');
    const menu = details.querySelector('[data-row-actions-menu]');

    if (!trigger || !menu) {
        return;
    }

    const triggerRect = trigger.getBoundingClientRect();
    const viewportPadding = 8;

    menu.style.visibility = 'hidden';
    menu.style.top = '0px';
    menu.style.left = '0px';

    const menuWidth = menu.offsetWidth;
    const menuHeight = menu.offsetHeight;

    let left = triggerRect.right - menuWidth;
    left = Math.max(viewportPadding, Math.min(left, window.innerWidth - menuWidth - viewportPadding));

    let top = triggerRect.bottom + viewportPadding;

    if (top + menuHeight > window.innerHeight - viewportPadding) {
        top = triggerRect.top - menuHeight - viewportPadding;
    }

    top = Math.max(viewportPadding, top);

    menu.style.left = `${left}px`;
    menu.style.top = `${top}px`;
    menu.style.visibility = 'visible';
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

            positionRowActionsMenu(menu);
        }
    });
});

window.addEventListener('scroll', closeRowActions, true);
window.addEventListener('resize', closeRowActions);

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
