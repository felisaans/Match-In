document.addEventListener('DOMContentLoaded', () => {
    // ---------- Welcome banner (dismiss = sembunyi untuk tampilan ini saja, akan muncul lagi tiap login/reload) ----------
    const banner = document.getElementById('welcome-banner');
    const bannerClose = document.getElementById('welcome-banner-close');
    if (banner && bannerClose) {
        bannerClose.addEventListener('click', () => {
            banner.classList.add('hidden');
        });
    }

    // ---------- FAB "Cara Pakai MatchIn" ----------
    const fab = document.getElementById('fab-help');
    const modal = document.getElementById('how-to-modal');
    const modalClose = document.getElementById('how-to-modal-close');
    if (fab && modal) {
        const openModal = () => modal.classList.remove('hidden');
        const closeModal = () => modal.classList.add('hidden');

        fab.addEventListener('click', openModal);
        modalClose?.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
        });
    }
});
