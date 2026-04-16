/**
 * Hero Parallax — lightweight scroll-based parallax for hero background.
 * Applies a CSS translate3d transform to .hero-slide__bg on scroll.
 * Disabled on reduced-motion preference and mobile (<768px).
 */

const SPEED = 0.22; // portion of scrollY applied as offset (0 = none, 1 = full)

/**
 * @returns {void}
 */
export function initParallax() {
	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		return;
	}
	if ( window.innerWidth < 768 ) {
		return;
	}

	const section = document.querySelector( '.hero-slider-section' );
	if ( ! section ) return;

	// Expand bg divs slightly so edge never shows during parallax offset.
	section.classList.add( 'has-parallax' );

	const bgs = section.querySelectorAll( '.hero-slide__bg' );
	if ( ! bgs.length ) return;

	let ticking = false;

	function applyParallax() {
		const scrollY  = window.scrollY;
		const sectionH = section.offsetHeight;

		// Only apply while hero section is in view.
		if ( scrollY > sectionH ) {
			ticking = false;
			return;
		}

		const offset = scrollY * SPEED;
		bgs.forEach( function ( bg ) {
			bg.style.transform = 'translate3d(0,' + offset + 'px,0)';
		} );
		ticking = false;
	}

	window.addEventListener( 'scroll', function () {
		if ( ! ticking ) {
			requestAnimationFrame( applyParallax );
			ticking = true;
		}
	}, { passive: true } );
}
