(() => {
    const navToggle = document.querySelector('[data-nav-toggle]');
    const siteNav = document.querySelector('[data-site-nav]');

    const closeNavigation = (restoreFocus = false) => {
        if (!navToggle || !siteNav) return;
        const wasOpen = navToggle.getAttribute('aria-expanded') === 'true';
        navToggle.setAttribute('aria-expanded', 'false');
        navToggle.setAttribute('aria-label', 'Abrir menú');
        siteNav.classList.remove('is-open');
        document.body.classList.remove('site-nav-open');
        if (restoreFocus && wasOpen) navToggle.focus({ preventScroll: true });
    };

    navToggle?.addEventListener('click', () => {
        const willOpen = navToggle.getAttribute('aria-expanded') !== 'true';
        navToggle.setAttribute('aria-expanded', String(willOpen));
        navToggle.setAttribute('aria-label', willOpen ? 'Cerrar menú' : 'Abrir menú');
        siteNav?.classList.toggle('is-open', willOpen);
        document.body.classList.toggle('site-nav-open', willOpen);
    });

    siteNav?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => closeNavigation()));
    document.addEventListener('click', (event) => {
        if (navToggle?.getAttribute('aria-expanded') !== 'true') return;
        if (siteNav?.contains(event.target) || navToggle.contains(event.target)) return;
        closeNavigation();
    });
    window.addEventListener('resize', () => {
        if (window.innerWidth > 900) closeNavigation();
    });

    const revealTargets = [...document.querySelectorAll('main > section')]
        .filter((section) => !section.matches('.hero,.page-hero,.web-quote-hero'));
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    revealTargets.forEach((target) => target.classList.add('reveal-target'));
    if (reducedMotion || !('IntersectionObserver' in window)) {
        revealTargets.forEach((target) => target.classList.add('is-revealed'));
    } else {
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-revealed');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -7% 0px' });
        revealTargets.forEach((target) => revealObserver.observe(target));
    }

    const popup = document.getElementById('ds44-campaign-popup');
    if (!popup) {
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeNavigation(true);
        });
        return;
    }

    const storageKey = 'prevcapital_ds44_popup_v3_seen';
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
        if (event.key === 'Escape') closeNavigation(true);
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
