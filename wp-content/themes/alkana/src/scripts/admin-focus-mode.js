/**
 * Alkana Admin — Focus Mode
 * Toggle distraction-free dark writing surface on post editor screens.
 * Keyboard: F11 to toggle, ESC to exit. Persists via localStorage.
 */

const STORAGE_KEY = 'alkana_focus_mode';
const toggle      = document.getElementById( 'alkana-focus-toggle' );

if ( toggle ) {
	let active = localStorage.getItem( STORAGE_KEY ) === 'true';

	// Screen reader live region
	let focusLive = document.getElementById( 'alkana-focus-live' );
	if ( ! focusLive ) {
		focusLive = document.createElement( 'div' );
		focusLive.id = 'alkana-focus-live';
		focusLive.setAttribute( 'aria-live', 'assertive' );
		focusLive.setAttribute( 'aria-atomic', 'true' );
		focusLive.className = 'sr-only';
		document.body.appendChild( focusLive );
	}

	function setFocusMode( on ) {
		active = on;
		document.body.classList.toggle( 'alkana-focus-mode', on );
		toggle.setAttribute( 'aria-pressed', String( on ) );
		toggle.classList.toggle( 'is-active', on );
		localStorage.setItem( STORAGE_KEY, String( on ) );
		focusLive.textContent = on ? 'Focus Mode đã bật' : 'Focus Mode đã tắt';
	}

	// Restore preference on load
	if ( active ) {
		setFocusMode( true );
	}

	toggle.addEventListener( 'click', () => setFocusMode( ! active ) );

	document.addEventListener( 'keydown', ( e ) => {
		if ( e.key === 'Escape' && active ) {
			setFocusMode( false );
		}
		if ( e.key === 'F11' ) {
			e.preventDefault();
			setFocusMode( ! active );
		}
	} );
}
