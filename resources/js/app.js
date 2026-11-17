import Chart from 'chart.js/auto';
import '@fontsource-variable/plus-jakarta-sans';

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

// Tutup pesan flash tanpa memuat ulang halaman.
document.querySelectorAll('[data-flash-dismiss]').forEach((button) => {
    button.addEventListener('click', () => {
        button.closest('[data-flash-message]')?.remove();
    });
});

// Perbarui notifikasi topbar tanpa memuat ulang halaman.
const notificationMenu = document.querySelector('[data-notification-menu]');
const notificationFeedUrl = notificationMenu?.dataset.notificationFeedUrl;
const notificationContent = notificationMenu?.querySelector('[data-notification-content]');
const notificationBadge = notificationMenu?.querySelector('[data-notification-badge]');
const notificationPollInterval = 30000;

let isFetchingNotifications = false;

const refreshNotifications = async () => {
    if (! notificationMenu || ! notificationFeedUrl || isFetchingNotifications || document.hidden) {
        return;
    }

    isFetchingNotifications = true;

    try {
        const response = await fetch(notificationFeedUrl, {
            headers: { Accept: 'application/json' },
            cache: 'no-store',
        });

        if (! response.ok) {
            return;
        }

        const data = await response.json();
        const unreadCount = Number(data.unread_count ?? 0);

        if (notificationContent && typeof data.content_html === 'string' && notificationContent.innerHTML !== data.content_html) {
            notificationContent.innerHTML = data.content_html;
        }

        if (notificationBadge) {
            notificationBadge.textContent = unreadCount > 9 ? '9+' : String(unreadCount);
            notificationBadge.classList.toggle('hidden', unreadCount === 0);
        }
    } catch {
        // Abaikan gangguan jaringan; polling berikutnya akan mencoba lagi.
    } finally {
        isFetchingNotifications = false;
    }
};

if (notificationMenu) {
    setInterval(refreshNotifications, notificationPollInterval);

    notificationMenu.addEventListener('toggle', () => {
        if (notificationMenu.open) {
            refreshNotifications();
        }
    });

    document.addEventListener('visibilitychange', () => {
        if (! document.hidden) {
            refreshNotifications();
        }
    });
}

// Hapus sorotan baris data setelah dua detik.
document.querySelectorAll('[data-highlighted-row]').forEach((row) => {
    window.setTimeout(() => {
        row.classList.remove('notification-row-highlight');
    }, 2000);
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
