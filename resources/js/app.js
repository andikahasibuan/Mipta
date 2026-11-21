import Chart from 'chart.js/auto';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import '@fontsource/plus-jakarta-sans/400.css';
import '@fontsource/plus-jakarta-sans/500.css';
import '@fontsource/plus-jakarta-sans/600.css';
import '@fontsource/plus-jakarta-sans/700.css';

// Chart.js tersedia untuk grafik di halaman admin.
window.Chart = Chart;

// Sembunyikan atau tampilkan sidebar.
const sidebarToggle = document.querySelector('[data-sidebar-toggle]');

const setSidebarCollapsed = (collapsed) => {
    document.documentElement.dataset.sidebarState = collapsed ? 'collapsed' : 'expanded';

    if (!sidebarToggle) return;

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

// Sidebar sebagai drawer pada layar kecil.
const sidebarDrawer = document.querySelector('[data-sidebar-drawer]');
const sidebarBackdrop = document.querySelector('[data-sidebar-backdrop]');
const sidebarOpenButton = document.querySelector('[data-sidebar-open]');
const desktopSidebarQuery = window.matchMedia('(min-width: 64rem)');

const setSidebarDrawerOpen = (open) => {
    if (!sidebarDrawer) return;

    if (desktopSidebarQuery.matches) {
        sidebarDrawer.removeAttribute('inert');
        sidebarDrawer.removeAttribute('aria-hidden');
        sidebarBackdrop?.classList.add('hidden');
        sidebarOpenButton?.setAttribute('aria-expanded', 'false');

        return;
    }

    sidebarDrawer.classList.toggle('-translate-x-full', !open);
    sidebarDrawer.classList.toggle('translate-x-0', open);
    sidebarDrawer.toggleAttribute('inert', !open);

    if (open) {
        sidebarDrawer.removeAttribute('aria-hidden');
    } else {
        sidebarDrawer.setAttribute('aria-hidden', 'true');
    }

    sidebarBackdrop?.classList.toggle('hidden', !open);
    sidebarOpenButton?.setAttribute('aria-expanded', String(open));
};

const syncSidebarDrawer = () => {
    setSidebarDrawerOpen(false);
};

if (sidebarDrawer) {
    syncSidebarDrawer();

    sidebarOpenButton?.addEventListener('click', () => {
        setSidebarDrawerOpen(true);
    });

    sidebarBackdrop?.addEventListener('click', () => {
        setSidebarDrawerOpen(false);
    });

    sidebarDrawer.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            setSidebarDrawerOpen(false);
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setSidebarDrawerOpen(false);
        }
    });

    desktopSidebarQuery.addEventListener('change', syncSidebarDrawer);
}

// Tutup dropdown saat klik di luar atau menekan Escape.
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

// Tutup pesan flash.
document.querySelectorAll('[data-flash-dismiss]').forEach((button) => {
    button.addEventListener('click', () => {
        button.closest('[data-flash-message]')?.remove();
    });
});

// Perbarui notifikasi topbar tanpa memuat ulang halaman.
const notificationMenu = document.querySelector('[data-notification-menu]');
const notificationFeedUrl = notificationMenu?.dataset.notificationFeedUrl;
const notificationUserId = notificationMenu?.dataset.notificationUserId;
const notificationContent = notificationMenu?.querySelector('[data-notification-content]');
const notificationBadge = notificationMenu?.querySelector('[data-notification-badge]');
const notificationPollInterval = 60000;

let isFetchingNotifications = false;

const refreshNotifications = async () => {
    if (!notificationMenu || !notificationFeedUrl || isFetchingNotifications || document.hidden) {
        return;
    }

    isFetchingNotifications = true;

    try {
        const response = await fetch(notificationFeedUrl, {
            headers: { Accept: 'application/json' },
            cache: 'no-store',
        });

        if (!response.ok) return;

        const data = await response.json();
        const unreadCount = Number(data.unread_count ?? 0);

        if (
            notificationContent &&
            typeof data.content_html === 'string' &&
            notificationContent.innerHTML !== data.content_html
        ) {
            notificationContent.innerHTML = data.content_html;
        }

        if (notificationBadge) {
            notificationBadge.textContent = unreadCount > 9 ? '9+' : String(unreadCount);
            notificationBadge.classList.toggle('hidden', unreadCount === 0);
        }
    } catch {
        // Polling berikutnya akan mencoba kembali.
    } finally {
        isFetchingNotifications = false;
    }
};

if (notificationMenu) {
    setInterval(refreshNotifications, notificationPollInterval);

    notificationMenu.addEventListener('toggle', () => {
        if (notificationMenu.open) refreshNotifications();
    });

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) refreshNotifications();
    });
}

// Terima notifikasi baru secara real-time melalui Reverb.
if (notificationMenu && notificationUserId && import.meta.env.VITE_REVERB_APP_KEY) {
    window.Pusher = Pusher;

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? 80),
        wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 443),
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });

    window.Echo.private(`App.Models.User.${notificationUserId}`)
        .notification(() => refreshNotifications());
}

// Hapus sorotan baris data setelah dua detik.
document.querySelectorAll('[data-highlighted-row]').forEach((row) => {
    window.setTimeout(() => {
        row.classList.remove('notification-row-highlight');
    }, 2000);
});

// Tampilkan atau sembunyikan isi password.
document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
    const input = document.getElementById(toggle.dataset.passwordToggle);

    if (!input) return;

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

// Popup konfirmasi custom MIPTA.
const confirmationModal = document.querySelector('#confirmation-modal');
const confirmationTitle = document.querySelector('#confirmation-title');
const confirmationMessage = document.querySelector('#confirmation-message');
const confirmationCancel = document.querySelector('#confirmation-cancel');
const confirmationAccept = document.querySelector('#confirmation-accept');

let pendingConfirmationForm = null;
let previousFocusedElement = null;

const closeConfirmationModal = () => {
    if (!confirmationModal) return;

    confirmationModal.classList.add('hidden');
    confirmationModal.classList.remove('flex');
    confirmationModal.setAttribute('aria-hidden', 'true');

    pendingConfirmationForm = null;
    previousFocusedElement?.focus();
};

if (confirmationModal) {
    document.addEventListener('submit', (event) => {
        const form = event.target.closest('form[data-confirm-form]');

        if (!form) return;

        if (form.dataset.confirmed === 'true') {
            delete form.dataset.confirmed;
            return;
        }

        event.preventDefault();

        pendingConfirmationForm = form;
        previousFocusedElement = document.activeElement;

        confirmationTitle.textContent =
            form.dataset.confirmTitle || 'Konfirmasi Penghapusan';

        confirmationMessage.textContent =
            form.dataset.confirmMessage || 'Apakah kamu yakin ingin menghapus data ini?';

        confirmationModal.classList.remove('hidden');
        confirmationModal.classList.add('flex');
        confirmationModal.setAttribute('aria-hidden', 'false');

        confirmationCancel?.focus();
    });

    confirmationCancel?.addEventListener('click', closeConfirmationModal);

    confirmationAccept?.addEventListener('click', () => {
        if (!pendingConfirmationForm) return;

        const form = pendingConfirmationForm;
        form.dataset.confirmed = 'true';

        closeConfirmationModal();
        form.requestSubmit();
    });

    confirmationModal.addEventListener('click', (event) => {
        if (event.target === confirmationModal) {
            closeConfirmationModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (
            event.key === 'Escape' &&
            !confirmationModal.classList.contains('hidden')
        ) {
            closeConfirmationModal();
        }
    });
}

// Popup notifikasi berhasil dan gagal.
const notificationModal = document.querySelector('#notification-modal');
const notificationTitle = document.querySelector('#notification-title');
const notificationMessage = document.querySelector('#notification-message');
const notificationIconWrapper = document.querySelector('#notification-icon-wrapper');
const notificationSuccessIcon = document.querySelector('#notification-success-icon');
const notificationErrorIcon = document.querySelector('#notification-error-icon');
const notificationClose = document.querySelector('#notification-close');

const notificationMarker = document.querySelector('[data-popup-notification]');

let notificationPreviousFocus = null;

const closeNotificationModal = () => {
    if (!notificationModal) return;

    notificationModal.classList.add('hidden');
    notificationModal.classList.remove('flex');
    notificationModal.setAttribute('aria-hidden', 'true');

    notificationPreviousFocus?.focus();
};

if (notificationModal && notificationMarker) {
    const type = notificationMarker.dataset.popupType || 'success';
    const isError = type === 'error';

    notificationTitle.textContent =
        notificationMarker.dataset.popupTitle || (isError ? 'Gagal!' : 'Berhasil!');

    notificationMessage.textContent =
        notificationMarker.dataset.popupMessage || 'Proses telah selesai.';

    if (isError) {
        notificationIconWrapper.classList.remove('bg-[#E0F2F3]', 'text-[#018F98]');
        notificationIconWrapper.classList.add('bg-red-50', 'text-red-500');

        notificationSuccessIcon.classList.add('hidden');
        notificationErrorIcon.classList.remove('hidden');

        notificationClose.classList.remove('bg-[#018F98]', 'hover:bg-[#01777E]');
        notificationClose.classList.add('bg-red-500', 'hover:bg-red-600');
    } else {
        notificationIconWrapper.classList.remove('bg-red-50', 'text-red-500');
        notificationIconWrapper.classList.add('bg-[#E0F2F3]', 'text-[#018F98]');

        notificationSuccessIcon.classList.remove('hidden');
        notificationErrorIcon.classList.add('hidden');

        notificationClose.classList.remove('bg-red-500', 'hover:bg-red-600');
        notificationClose.classList.add('bg-[#018F98]', 'hover:bg-[#01777E]');

        // Jalankan ulang animasi centang.
        const checkPath = notificationSuccessIcon.querySelector('.notification-check-path');

        if (checkPath) {
            checkPath.style.animation = 'none';
            void checkPath.getBoundingClientRect();
            checkPath.style.animation = '';
        }
    }

    notificationPreviousFocus = document.activeElement;

    notificationModal.classList.remove('hidden');
    notificationModal.classList.add('flex');
    notificationModal.setAttribute('aria-hidden', 'false');

    notificationClose?.focus();

    notificationClose?.addEventListener('click', closeNotificationModal);

    notificationModal.addEventListener('click', (event) => {
        if (event.target === notificationModal) {
            closeNotificationModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (
            event.key === 'Escape' &&
            !notificationModal.classList.contains('hidden')
        ) {
            closeNotificationModal();
        }
    });
}
