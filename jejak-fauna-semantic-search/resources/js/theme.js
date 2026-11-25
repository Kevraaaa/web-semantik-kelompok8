const themeKey = 'jf_theme';
const themeButton = document.querySelector('[data-theme-toggle]');

const setTheme = (theme) => {
    document.documentElement.dataset.theme = theme;
    localStorage.setItem(themeKey, theme);
    document.querySelector('meta[name="theme-color"]')?.setAttribute('content', theme === 'light' ? '#F5F7F2' : '#182415');
    document.dispatchEvent(new CustomEvent('jf:theme-changed', { detail: { theme } }));
    themeButton?.setAttribute('aria-pressed', String(theme === 'light'));
    themeButton?.setAttribute('aria-label', theme === 'dark' ? 'Aktifkan tema terang' : 'Aktifkan tema gelap');
    const icon = themeButton?.querySelector('[data-theme-icon]');
    if (icon) {
        icon.textContent = theme === 'dark' ? '☼' : '☾';
    }
};

setTheme(localStorage.getItem(themeKey) || 'dark');

themeButton?.addEventListener('click', () => {
    setTheme(document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark');
});
