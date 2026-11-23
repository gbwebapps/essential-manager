import './backend.js';

const studio = document.querySelector('.manager-desk-studio');
const current = studio?.querySelector('[data-task-current="true"]');

if (current) {
    const navbar = document.querySelector('#navbar-top-view .navbar');
    const navigation = document.getElementById('manager-top-navigation');
    const bottom = document.querySelector('#navbar-bottom-view .navbar');

    /** @returns {void} Tiene il corrente sotto le barre; evita di coprire contenuti su finestre troppo basse. */
    const updateSticky = () => {
        const top = (navbar?.getBoundingClientRect().height || 0) + (navigation?.getBoundingClientRect().height || 0) + 12;
        const available = window.innerHeight - top - (bottom?.getBoundingClientRect().height || 0) - 12;
        studio.style.setProperty('--studio-sticky-top', `${top}px`);
        current.classList.toggle('manager-studio-current-sticky', current.getBoundingClientRect().height <= available);
    };

    const observer = new ResizeObserver(updateSticky);
    [navbar, navigation, bottom, current].filter(Boolean).forEach(node => observer.observe(node));
    window.addEventListener('resize', updateSticky, { passive: true });
    updateSticky();
}
