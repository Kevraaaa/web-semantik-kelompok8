import './theme.js';
import './toast.js';

const page = document.body.dataset.page;

if (page === 'home' || page === 'explore' || page === 'detail') {
    import('./search.js');
}

if (page === 'map' || page === 'home' || page === 'detail' || page === 'admin') {
    import('./map.js');
}

if (page === 'graph' || page === 'home') {
    import('./graph.js');
}

if (page === 'explore' || page === 'detail' || page === 'home') {
    import('./favorites.js');
}

if (page === 'explore' || page === 'compare' || page === 'detail' || page === 'home' || page === 'profile') {
    import('./compare.js');
}

if (page === 'profile') {
    import('./account.js');
}

if (page === 'admin') {
    import('./admin.js');
}

const menuButton = document.querySelector('[data-menu-toggle]');
const mobileMenu = document.querySelector('[data-mobile-menu]');

menuButton?.addEventListener('click', () => {
    const expanded = menuButton.getAttribute('aria-expanded') === 'true';
    menuButton.setAttribute('aria-expanded', String(!expanded));
    mobileMenu?.classList.toggle('hidden', expanded);
});

document.querySelector('[data-account-toggle]')?.addEventListener('click', (event) => {
    const button = event.currentTarget;
    const menu = document.querySelector('[data-account-menu]');
    const expanded = button.getAttribute('aria-expanded') === 'true';
    button.setAttribute('aria-expanded', String(!expanded));
    menu?.classList.toggle('hidden', expanded);
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    const accountMenu = document.querySelector('[data-account-menu]');
    const accountToggle = document.querySelector('[data-account-toggle]');
    if (accountMenu && !accountMenu.classList.contains('hidden')) {
        accountMenu.classList.add('hidden');
        accountToggle?.setAttribute('aria-expanded', 'false');
        accountToggle?.focus();
    }
    mobileMenu?.classList.add('hidden');
    menuButton?.setAttribute('aria-expanded', 'false');
    document.querySelector('[data-filter-drawer]')?.classList.add('hidden');
});
