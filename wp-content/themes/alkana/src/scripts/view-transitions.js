/**
 * View Transitions — SPA-like page navigation
 * Uses View Transitions API (Chrome 111+, Safari 18+) with graceful fallback.
 * Intercepts same-origin anchor clicks, fetches next page, swaps DOM.
 *
 * @package Alkana
 */

'use strict';

// ── Loading bar ──────────────────────────────────────────────────────────────

let loadingBar = null;

function showLoadingBar() {
	if ( loadingBar ) return;
	loadingBar = document.createElement( 'div' );
	loadingBar.id = 'vt-loading-bar';
	loadingBar.setAttribute( 'aria-hidden', 'true' );
	document.body.appendChild( loadingBar );
}

function hideLoadingBar() {
	if ( loadingBar ) {
		loadingBar.classList.add( 'vt-loading-bar--done' );
		setTimeout( () => {
			loadingBar?.remove();
			loadingBar = null;
		}, 300 );
	}
}

// ── Re-init modules after DOM swap ───────────────────────────────────────────

function reinitModules() {
	// Re-init Alpine.js components if available
	if ( window.Alpine ) {
		window.Alpine.initTree( document.body );
	}

	// Re-init all registered modules (set by app.js after DOMContentLoaded)
	if ( window.alkanaCounters )     window.alkanaCounters.init();
	if ( window.alkanaParallax )     window.alkanaParallax.init();
	if ( window.alkanaScrollReveal ) window.alkanaScrollReveal.init();
	if ( window.alkanaHoverPhysics ) window.alkanaHoverPhysics.init();

	// Re-init header scroll state + other event-driven modules
	document.dispatchEvent( new Event( 'alkana:pageChanged' ) );
}

// ── Nav active state ─────────────────────────────────────────────────────────

/**
 * After a SPA swap the header stays from initial load, so WordPress's
 * server-side `current-menu-item` class is stale. Re-compute it client-side
 * by comparing nav link hrefs against the new URL (longest-prefix wins).
 */
function updateNavActiveState( url ) {
	const navMenu = document.querySelector( '.nav-menu' );
	if ( ! navMenu ) return;

	const normalize = ( p ) => ( p.endsWith( '/' ) ? p : p + '/' );
	const currentPath = normalize( new URL( url ).pathname );

	// Strip active classes from all top-level nav items
	navMenu.querySelectorAll( ':scope > li' ).forEach( ( li ) => {
		li.classList.remove( 'current-menu-item', 'current-menu-ancestor', 'current-menu-parent' );
	} );

	// Pick the nav item whose href is the longest matching prefix of currentPath
	let bestMatch = null;
	let bestLength = 0;

	navMenu.querySelectorAll( ':scope > li > a[href]' ).forEach( ( anchor ) => {
		try {
			const linkPath = normalize( new URL( anchor.href ).pathname );
			if ( currentPath === linkPath || currentPath.startsWith( linkPath ) ) {
				if ( linkPath.length > bestLength ) {
					bestLength = linkPath.length;
					bestMatch = anchor.parentElement;
				}
			}
		} catch {
			// Skip malformed hrefs
		}
	} );

	if ( bestMatch ) {
		bestMatch.classList.add( 'current-menu-item' );
	}
}

// ── Navigation ───────────────────────────────────────────────────────────────

let isNavigating = false;

async function navigateTo( url ) {
	if ( isNavigating ) return;
	isNavigating = true;

	showLoadingBar();

	try {
		const response = await fetch( url, {
			headers: { 'X-Alkana-VT': '1' },
		} );

		if ( ! response.ok ) {
			window.location.href = url;
			return;
		}

		const html = await response.text();
		const parser = new DOMParser();
		const doc = parser.parseFromString( html, 'text/html' );

		const newMain = doc.querySelector( '#main-content' );
		const curMain = document.querySelector( '#main-content' );

		if ( ! newMain || ! curMain ) {
			window.location.href = url;
			return;
		}

		if ( document.startViewTransition ) {
			const transition = document.startViewTransition( () => {
				document.title = doc.title;
				curMain.replaceWith( newMain );
				history.pushState( {}, '', url );
				updateNavActiveState( url );
				window.scrollTo( { top: 0, behavior: 'instant' } );
				reinitModules();
			} );
			await transition.finished;
		} else {
			// Fallback: no animation
			document.title = doc.title;
			curMain.replaceWith( newMain );
			history.pushState( {}, '', url );
			updateNavActiveState( url );
			window.scrollTo( 0, 0 );
			reinitModules();
		}
	} catch {
		window.location.href = url;
	} finally {
		hideLoadingBar();
		isNavigating = false;
	}
}

// ── Click interception ───────────────────────────────────────────────────────

function shouldIntercept( link ) {
	// Skip: external, hash-only, download, data-no-transition, admin, wp-
	if ( ! link ) return false;
	if ( link.origin !== window.location.origin ) return false;
	if ( link.getAttribute( 'href' )?.startsWith( '#' ) ) return false;
	if ( link.hasAttribute( 'download' ) ) return false;
	if ( link.hasAttribute( 'data-no-transition' ) ) return false;
	if ( link.target && link.target !== '_self' ) return false;
	if ( link.href.includes( '/wp-admin' ) ) return false;
	if ( link.href.includes( '/wp-login' ) ) return false;
	if ( link.href === window.location.href ) return false;
	return true;
}

// ── Browser back/forward ─────────────────────────────────────────────────────

window.addEventListener( 'popstate', () => {
	navigateTo( window.location.href );
} );

// ── Init ─────────────────────────────────────────────────────────────────────

export function initViewTransitions() {
	document.addEventListener( 'click', ( e ) => {
		const link = e.target.closest( 'a[href]' );
		if ( ! shouldIntercept( link ) ) return;
		e.preventDefault();
		navigateTo( link.href );
	} );
}
