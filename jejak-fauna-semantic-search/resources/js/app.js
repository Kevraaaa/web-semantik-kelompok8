import './theme.js';
import './toast.js';

const page = document.body.dataset.page;

if (page === 'home' || page === 'explore' || page === 'detail') {
    import('./search.js');
}

if (page === 'map' || page === 'report' || page === 'home' || page === 'detail' || page === 'admin') {
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

if (page === 'login' || page === 'register' || page === 'profile' || page === 'admin') {
    import('./account.js');
}

if (page === 'report') {
    import('./report.js');
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

let dummyLoggedIn = localStorage.getItem('jf_logged_in') === 'true';

const syncAccount = () => {
    document.querySelectorAll('[data-auth-guest]').forEach((element) => {
        element.classList.toggle('hidden', dummyLoggedIn);
    });
    document.querySelectorAll('[data-auth-user]').forEach((element) => {
        element.classList.toggle('hidden', !dummyLoggedIn);
    });
};

Object.defineProperty(window, '__DUMMY_LOGGED_IN__', {
    configurable: true,
    get: () => dummyLoggedIn,
    set: (value) => {
        dummyLoggedIn = Boolean(value);
        localStorage.setItem('jf_logged_in', String(dummyLoggedIn));
        syncAccount();
    },
});

syncAccount();

document.querySelectorAll('[data-logout]').forEach((button) => {
    button.addEventListener('click', () => {
        window.__DUMMY_LOGGED_IN__ = false;
        window.location.href = '/';
    });
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
    document.querySelector('[data-report-success]')?.dispatchEvent(new CustomEvent('jf:modal-escape'));
});
