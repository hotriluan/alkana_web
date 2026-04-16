/**
 * header-scroll.js — Scroll effects for site header.
 *
 * - Topbar: hides on scroll-down, shows on scroll-up.
 * - Updates --header-height CSS var to account for topbar visibility.
 */

( function () {
	const header  = document.getElementById( 'site-header' );
	const topbar  = document.getElementById( 'topbar' );

	if ( ! header ) return;

	let lastY   = window.scrollY;
	let ticking = false;

	/** Recalculate --header-height so mega panel positions correctly. */
	function updateHeaderHeight() {
		const topbarH = ( topbar && ! topbar.classList.contains( 'topbar--hidden' ) )
			? topbar.getBoundingClientRect().height
			: 0;
		const headerH = header.getBoundingClientRect().height;
		header.style.setProperty( '--header-height', ( topbarH + headerH ) + 'px' );
	}

	function onScroll() {
		const y = window.scrollY;

		// Topbar: hide on scroll-down past 80px, show on scroll-up
		if ( topbar ) {
			if ( y > lastY && y > 80 ) {
				topbar.classList.add( 'topbar--hidden' );
			} else {
				topbar.classList.remove( 'topbar--hidden' );
			}
		}

		updateHeaderHeight();
		lastY   = y;
		ticking = false;
	}

	window.addEventListener( 'scroll', () => {
		if ( ! ticking ) {
			requestAnimationFrame( onScroll );
			ticking = true;
		}
	}, { passive: true } );

	// Initial height calculation
	updateHeaderHeight();
} )();
