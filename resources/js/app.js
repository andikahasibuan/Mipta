// Tutup menu profil saat klik di luar atau menekan Escape.
const closeProfileMenus = () => {
    document.querySelectorAll('[data-profile-menu][open]').forEach((menu) => {
        menu.removeAttribute('open');
    });
};

document.addEventListener('click', (event) => {
    document.querySelectorAll('[data-profile-menu][open]').forEach((menu) => {
        if (!menu.contains(event.target)) {
            menu.removeAttribute('open');
        }
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeProfileMenus();
    }
});
