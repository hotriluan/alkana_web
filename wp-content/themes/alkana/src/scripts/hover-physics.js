/**
 * Hover Physics — Tilt, spring-lift, and dynamic shadow for cards
 * Applies 3D perspective tilt following cursor position.
 * Disabled on touch devices and when prefers-reduced-motion is set.
 *
 * Usage: Add `data-hover-physics` attribute to any card element.
 *
 * @package Alkana
 */

'use strict';

const TILT_MAX   = 7;   // degrees
const LIFT_PX    = 10;  // px translateY on hover
const PERSPECTIVE = 700; // px

class HoverPhysics {
	constructor( el ) {
		this.el     = el;
		this.bounds = null;
		this.raf    = null;
		this.active = false;

		this.onEnter = this.onEnter.bind( this );
		this.onMove  = this.onMove.bind( this );
		this.onLeave = this.onLeave.bind( this );

		this.el.addEventListener( 'mouseenter', this.onEnter );
		this.el.addEventListener( 'mousemove',  this.onMove );
		this.el.addEventListener( 'mouseleave', this.onLeave );
	}

	onEnter() {
		this.bounds = this.el.getBoundingClientRect();
		this.active = true;
		this.el.style.transition = 'none';
		this.el.style.willChange = 'transform, box-shadow';
	}

	onMove( e ) {
		if ( ! this.active ) return;
		if ( this.raf ) cancelAnimationFrame( this.raf );

		this.raf = requestAnimationFrame( () => {
			const { left, top, width, height } = this.bounds;
			const x = ( e.clientX - left ) / width  - 0.5;  // -0.5 → 0.5
			const y = ( e.clientY - top  ) / height - 0.5;

			const rotX = y * -TILT_MAX;
			const rotY = x *  TILT_MAX;

			const shadowX = rotY * 1.5;
			const shadowY = 12 + Math.abs( x ) * 4 + Math.abs( y ) * 4;
			const shadowBlur = 28 + Math.abs( x ) * 12 + Math.abs( y ) * 12;

			this.el.style.transform = `perspective(${ PERSPECTIVE }px) rotateX(${ rotX }deg) rotateY(${ rotY }deg) translateY(-${ LIFT_PX }px)`;
			this.el.style.boxShadow = `${ shadowX }px ${ shadowY }px ${ shadowBlur }px rgba(103,33,157,0.16)`;
		} );
	}

	onLeave() {
		this.active = false;
		if ( this.raf ) cancelAnimationFrame( this.raf );

		this.el.style.transition = 'transform 0.5s var(--ease-spring, cubic-bezier(0.34,1.56,0.64,1)), box-shadow 0.4s ease-out';
		this.el.style.transform  = '';
		this.el.style.boxShadow  = '';
		this.el.style.willChange = '';
	}

	destroy() {
		this.el.removeEventListener( 'mouseenter', this.onEnter );
		this.el.removeEventListener( 'mousemove',  this.onMove );
		this.el.removeEventListener( 'mouseleave', this.onLeave );
		if ( this.raf ) cancelAnimationFrame( this.raf );
	}
}

// ── Init / re-init (called after view transitions swap) ─────────────────────

const instances = new WeakMap();

function initHoverPhysics() {
	// Respect reduced motion + touch devices
	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) return;
	if ( window.matchMedia( '(hover: none)' ).matches ) return;

	document.querySelectorAll( '[data-hover-physics]' ).forEach( ( el ) => {
		if ( instances.has( el ) ) return; // already initialised
		instances.set( el, new HoverPhysics( el ) );
	} );
}

export function init() {
	initHoverPhysics();
}

// Expose for re-init after view transitions
window.alkanaHoverPhysics = { init: initHoverPhysics };
