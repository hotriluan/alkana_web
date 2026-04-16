/**
 * Alkana Product Gallery — vanilla JS thumbnail slider.
 *
 * Swaps the main product image when a thumbnail button is clicked.
 * Uses event delegation: binds one listener per page on .gallery-thumbnail-strip.
 */
( function () {
	'use strict';

	const STRIP_SEL  = '.gallery-thumbnail-strip';
	const THUMB_SEL  = '[data-image-url]';
	const MAIN_ID    = 'main-product-image';
	const ACTIVE_CLS = [ 'opacity-100', 'border-alkana-purple-600' ];
	const IDLE_CLS   = [ 'opacity-70', 'border-transparent' ];

	function setActive( strip, activeBtn ) {
		strip.querySelectorAll( THUMB_SEL ).forEach( function ( btn ) {
			const isActive = btn === activeBtn;
			ACTIVE_CLS.forEach( function ( c ) { btn.classList.toggle( c, isActive ); } );
			IDLE_CLS.forEach( function ( c ) { btn.classList.toggle( c, ! isActive ); } );
		} );
	}

	function initStrip( strip ) {
		const mainImg = document.getElementById( MAIN_ID );
		if ( ! mainImg ) return;

		// Mark first thumbnail as active on init
		const firstThumb = strip.querySelector( THUMB_SEL );
		if ( firstThumb ) setActive( strip, firstThumb );

		strip.addEventListener( 'click', function ( e ) {
			const btn = e.target.closest( THUMB_SEL );
			if ( ! btn ) return;

			const url    = btn.dataset.imageUrl;
			const srcset = btn.dataset.imageSrcset;

			mainImg.src    = url;
			mainImg.srcset = srcset || '';

			setActive( strip, btn );

			// Scroll the clicked thumb into view on mobile
			btn.scrollIntoView( { behavior: 'smooth', block: 'nearest', inline: 'center' } );
		} );
	}

	function init() {
		document.querySelectorAll( STRIP_SEL ).forEach( initStrip );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
