/**
 * Scroll Reveal — IntersectionObserver-based section/element reveal
 * Fades + slides elements in as they enter the viewport.
 * Supports stagger delay via `data-reveal-delay` (ms).
 * Full group stagger via `data-reveal-stagger` on container.
 *
 * Usage:
 *   <section data-reveal>...</section>
 *   <div data-reveal data-reveal-delay="200">...</div>
 *   <ul data-reveal-stagger>...</ul>
 *
 * @package Alkana
 */

'use strict';

// ── Single element reveal ────────────────────────────────────────────────────

function buildObserver( reducedMotion ) {
	const observer = new IntersectionObserver(
		( entries ) => {
			entries.forEach( ( entry ) => {
				if ( ! entry.isIntersecting ) return;

				const el    = entry.target;
				const delay = parseInt( el.dataset.revealDelay || '0', 10 );

				if ( reducedMotion ) {
					el.classList.remove( 'reveal-hidden' );
					el.classList.add( 'is-revealed' );
					observer.unobserve( el );
					return;
				}

				setTimeout( () => {
					el.classList.add( 'is-revealed' );
				}, delay );

				observer.unobserve( el );
			} );
		},
		{ threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
	);

	return observer;
}

// ── Stagger group reveal ─────────────────────────────────────────────────────

function buildStaggerObserver( reducedMotion ) {
	return new IntersectionObserver(
		( entries ) => {
			entries.forEach( ( entry ) => {
				if ( ! entry.isIntersecting ) return;

				const container = entry.target;

				if ( reducedMotion ) {
					container.classList.add( 'is-revealed' );
				} else {
					container.classList.add( 'is-revealed' );
				}

				staggerObserver.unobserve( container );
			} );
		},
		{ threshold: 0.1, rootMargin: '0px 0px -30px 0px' }
	);
}

let observer        = null;
let staggerObserver = null;

function initScrollReveal() {
	const reducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	// Disconnect previous observer if re-init (view transitions)
	if ( observer ) observer.disconnect();
	if ( staggerObserver ) staggerObserver.disconnect();

	observer        = buildObserver( reducedMotion );
	staggerObserver = buildStaggerObserver( reducedMotion );

	// Single-element reveals
	document.querySelectorAll( '[data-reveal]' ).forEach( ( el ) => {
		if ( el.classList.contains( 'is-revealed' ) ) return; // skip already revealed
		if ( ! reducedMotion ) el.classList.add( 'reveal-hidden' );
		observer.observe( el );
	} );

	// Stagger group containers
	document.querySelectorAll( '[data-reveal-stagger]' ).forEach( ( el ) => {
		if ( el.classList.contains( 'is-revealed' ) ) return;
		staggerObserver.observe( el );
	} );
}

export function init() {
	initScrollReveal();
}

// Expose for re-init after view transitions
window.alkanaScrollReveal = { init: initScrollReveal };
