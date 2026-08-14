(() => {
    const popup = document.getElementById('ds44-campaign-popup');
    if (!popup) return;

    const storageKey = 'prevcapital_ds44_popup_seen';
    const closeButton = popup.querySelector('.campaign-popup__close');
    const contactLink = popup.querySelector('[data-campaign-popup-contact]');
    const closeControls = popup.querySelectorAll('[data-campaign-popup-close]');
    let previousFocus = null;

    const wasSeen = () => {
        try {
            return sessionStorage.getItem(storageKey) === '1';
        } catch (_error) {
            return false;
        }
    };

    const remember = () => {
        try {
            sessionStorage.setItem(storageKey, '1');
        } catch (_error) {
            // El aviso sigue funcionando aunque el navegador bloquee el almacenamiento.
        }
    };

    const closePopup = (restoreFocus = true) => {
        popup.classList.remove('is-visible');
        document.body.classList.remove('campaign-popup-open');
        window.setTimeout(() => {
            popup.hidden = true;
            if (restoreFocus && previousFocus instanceof HTMLElement) previousFocus.focus();
        }, 180);
    };

    const openPopup = () => {
        if (wasSeen()) return;
        previousFocus = document.activeElement;
        popup.hidden = false;
        document.body.classList.add('campaign-popup-open');
        window.requestAnimationFrame(() => popup.classList.add('is-visible'));
        remember();
        closeButton?.focus({ preventScroll: true });
    };

    closeControls.forEach((control) => control.addEventListener('click', () => closePopup()));
    contactLink?.addEventListener('click', () => closePopup(false));

    document.addEventListener('keydown', (event) => {
        if (popup.hidden) return;
        if (event.key === 'Escape') closePopup();
        if (event.key === 'Tab') {
            const focusable = [closeButton, contactLink].filter(Boolean);
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    window.setTimeout(openPopup, 850);
})();
