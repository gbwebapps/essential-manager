const initialized = new WeakSet();

/** @param {ParentNode} scope Area contenente i menu. @returns {void} Mantiene i menu aderenti alla navbar, anche quando cambia altezza. */
export function initMenuTop(scope = document) {
    const menus = [...scope.querySelectorAll('[data-menu-top]')];
    if (scope instanceof Element && scope.matches('[data-menu-top]')) menus.unshift(scope);
    menus.forEach(menu => {
        if (initialized.has(menu)) return;
        initialized.add(menu);
        if (menu.dataset.menuTopAttached !== 'true') return;
        const navbar = document.querySelector('#navbar-top-view .navbar');
        if (!navbar) return;

        const update = () => {
            const height = `${navbar.getBoundingClientRect().height}px`;
            document.documentElement.style.setProperty('--menu-top-navbar-height', height);
        };
        update();
        new ResizeObserver(update).observe(navbar);
    });
}

initMenuTop();
