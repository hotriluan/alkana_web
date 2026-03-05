/**
 * mobile-menu.js — Accordion navigation for mobile drawer.
 */

const toggle = document.querySelector('.nav-toggle');
const drawer = document.querySelector('.nav-drawer');

if (toggle && drawer) {
    toggle.addEventListener('click', () => {
        const isOpen = drawer.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', String(isOpen));
        document.body.classList.toggle('nav-open', isOpen);
    });

    // Close on Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && drawer.classList.contains('is-open')) {
            drawer.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('nav-open');
        }
    });
}

// Accordion sub-menu toggles
document.querySelectorAll('.nav-accordion__toggle[data-has-sub]').forEach((btn) => {
    btn.addEventListener('click', () => {
        const isExpanded = btn.getAttribute('aria-expanded') === 'true';
        const sub = btn.nextElementSibling;
        if (sub) sub.classList.toggle('is-open', !isExpanded);
        btn.setAttribute('aria-expanded', String(!isExpanded));
    });
});
