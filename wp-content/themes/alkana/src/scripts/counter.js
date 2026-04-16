/**
 * Animated stat counter.
 * Triggers count-up animation when .stat-counter elements enter the viewport.
 * Reads `data-count` attribute for target numeric value.
 */

const DURATION = 2000; // ms

/**
 * Ease-out cubic easing.
 * @param {number} t - progress 0..1
 * @returns {number}
 */
function easeOut( t ) {
	return 1 - Math.pow( 1 - t, 3 );
}

/**
 * Animate a single counter element from 0 → target.
 * @param {HTMLElement} el
 */
function animateCounter( el ) {
	const target = parseInt( el.dataset.count, 10 );
	if ( isNaN( target ) || target <= 0 ) {
		el.textContent = el.dataset.count ?? '0';
		return;
	}

	const start = performance.now();

	function tick( now ) {
		const elapsed  = now - start;
		const progress = Math.min( elapsed / DURATION, 1 );
		const value    = Math.round( easeOut( progress ) * target );

		el.textContent = value.toLocaleString( 'vi-VN' );

		if ( progress < 1 ) {
			requestAnimationFrame( tick );
		} else {
			el.textContent = target.toLocaleString( 'vi-VN' );
		}
	}

	requestAnimationFrame( tick );
}

/**
 * Set up IntersectionObserver to trigger counters once on enter.
 */
export function initCounters() {
	const counters = document.querySelectorAll( '.stat-counter[data-count]' );
	if ( ! counters.length ) return;

	const observer = new IntersectionObserver(
		( entries ) => {
			entries.forEach( ( entry ) => {
				if ( entry.isIntersecting ) {
					animateCounter( entry.target );
					observer.unobserve( entry.target );
				}
			} );
		},
		{ threshold: 0.3 }
	);

	counters.forEach( ( el ) => observer.observe( el ) );
}
