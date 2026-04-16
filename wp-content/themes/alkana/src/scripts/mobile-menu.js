/**
 * mobile-menu.js — Slide-from-right drawer navigation for mobile.
 */

const toggle  = document.getElementById( 'nav-toggle' );
const drawer  = document.getElementById( 'nav-drawer' );
const overlay = document.getElementById( 'nav-overlay' );
const close   = document.getElementById( 'nav-close' );

function openDrawer() {
    if ( ! drawer ) return;
    drawer.classList.remove( 'translate-x-full' );
    drawer.classList.add( 'translate-x-0' );
    drawer.setAttribute( 'aria-hidden', 'false' );
    if ( toggle ) toggle.setAttribute( 'aria-expanded', 'true' );
    if ( overlay ) overlay.classList.remove( 'hidden' );
    document.body.classList.add( 'nav-open' );
    document.body.style.overflow = 'hidden';
}

function closeDrawer() {
    if ( ! drawer ) return;
    drawer.classList.remove( 'translate-x-0' );
    drawer.classList.add( 'translate-x-full' );
    drawer.setAttribute( 'aria-hidden', 'true' );
    if ( toggle ) toggle.setAttribute( 'aria-expanded', 'false' );
    if ( overlay ) overlay.classList.add( 'hidden' );
    document.body.classList.remove( 'nav-open' );
    document.body.style.overflow = '';
}

if ( toggle ) toggle.addEventListener( 'click', openDrawer );
if ( close )  close.addEventListener( 'click', closeDrawer );
if ( overlay ) overlay.addEventListener( 'click', closeDrawer );

document.addEventListener( 'keydown', ( e ) => {
    if ( e.key === 'Escape' ) closeDrawer();
} );

// Accordion sub-menu toggles inside the drawer
document.querySelectorAll( '.nav-accordion__toggle[data-has-sub]' ).forEach( ( btn ) => {
    btn.addEventListener( 'click', () => {
        const isExpanded = btn.getAttribute( 'aria-expanded' ) === 'true';
        const sub = btn.nextElementSibling;
        if ( sub ) sub.classList.toggle( 'is-open', ! isExpanded );
        btn.setAttribute( 'aria-expanded', String( ! isExpanded ) );
    } );
} );
