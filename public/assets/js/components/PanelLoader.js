/** Shows loading feedback on a single panel button until the request settles. */
export function startPanelLoader(button) {
    const icon = button.querySelector('.fa-arrows-rotate');
    const wasDisabled = button.disabled;
    const hadFocus = document.activeElement === button;

    button.disabled = true;
    button.setAttribute('aria-busy', 'true');
    icon?.style.setProperty('--fa-animation-duration', 'calc(2s / 1.5)');
    icon?.style.setProperty('animation-play-state', 'running');
    icon?.classList.add('fa-spin');

    return () => {
        icon?.style.setProperty('animation-play-state', 'paused');
        button.removeAttribute('aria-busy');
        button.disabled = wasDisabled;
        if (hadFocus && document.activeElement === document.body) button.focus({ preventScroll: true });
    };
}
