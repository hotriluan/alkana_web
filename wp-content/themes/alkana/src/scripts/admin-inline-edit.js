/**
 * Alkana Admin — Inline Grid Editing
 * Double-click on .inline-editable cells in the product list table
 * to edit in place and save via AJAX.
 */

/* global alkanaInlineEdit */

// ARIA live region for screen reader announcements
let ariaLive = document.getElementById( 'alkana-inline-edit-live' );
if ( ! ariaLive ) {
	ariaLive = document.createElement( 'div' );
	ariaLive.id = 'alkana-inline-edit-live';
	ariaLive.setAttribute( 'aria-live', 'polite' );
	ariaLive.setAttribute( 'aria-atomic', 'true' );
	ariaLive.className = 'sr-only';
	document.body.appendChild( ariaLive );
}

function announce( message ) {
	ariaLive.textContent = '';
	// Brief delay ensures AT picks up the update
	requestAnimationFrame( () => { ariaLive.textContent = message; } );
}

document.addEventListener( 'dblclick', ( e ) => {
	const cell = e.target.closest( '.inline-editable' );
	if ( ! cell || cell.classList.contains( 'is-editing' ) ) return;

	const current = cell.dataset.current || '';
	const field   = cell.dataset.field;
	const postId  = cell.dataset.postId;

	cell.classList.add( 'is-editing' );

	const input = document.createElement( 'input' );
	input.type      = 'text';
	input.value     = current;
	input.className = 'inline-edit-input';
	cell.textContent = '';
	cell.appendChild( input );
	input.focus();
	input.select();

	function restore() {
		cell.textContent = current || '—';
		cell.classList.remove( 'is-editing' );
	}

	async function save() {
		const newValue = input.value.trim();

		if ( newValue === current ) {
			restore();
			return;
		}

		cell.classList.remove( 'is-editing' );

		try {
			const res = await fetch( window.ajaxurl, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: new URLSearchParams( {
					action:       'alkana_inline_edit',
					post_id:      postId,
					field:        field,
					value:        newValue,
					_ajax_nonce:  alkanaInlineEdit.nonce,
				} ),
			} );

			const data = await res.json();

			if ( data.success ) {
				const display = data.data.display_value || '—';
				cell.textContent    = display;
				cell.dataset.current = display;
				cell.classList.add( 'flash-success' );
				setTimeout( () => cell.classList.remove( 'flash-success' ), 1000 );
				announce( 'Đã lưu thành công' );
			} else {
				throw new Error( data.data || 'Server error' );
			}
		} catch ( err ) {
			console.error( '[Alkana InlineEdit]', err );
			cell.textContent = current || '—';
			cell.dataset.current = current;
			cell.classList.add( 'flash-error' );
			setTimeout( () => cell.classList.remove( 'flash-error' ), 1000 );
			announce( 'Lưu thất bại, vui lòng thử lại' );
		}
	}

	input.addEventListener( 'blur', save );
	input.addEventListener( 'keydown', ( ev ) => {
		if ( ev.key === 'Enter' ) {
			ev.preventDefault();
			save();
		}
		if ( ev.key === 'Escape' ) {
			ev.preventDefault();
			restore();
		}
	} );
} );
