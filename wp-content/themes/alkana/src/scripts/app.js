/**
 * Alkana Theme — app.js entry point
 * Imports all JS modules. Vite tree-shakes unused code at build time.
 */

import './mobile-menu.js';
import './header-scroll.js';
import './bottom-sheet.js';
import './filter.js';
import './sticky-cta.js';
import './search.js';
import './back-to-top.js';
import './form-validation.js';
import './newsletter.js';
import './gallery.js';
import { initCounters } from './counter.js';
import { initParallax } from './parallax.js';
import { init as initHoverPhysics } from './hover-physics.js';
import { init as initScrollReveal } from './scroll-reveal.js';
import { initViewTransitions } from './view-transitions.js';

document.addEventListener( 'DOMContentLoaded', () => {
	// Expose init functions as window globals so reinitModules() in
	// view-transitions.js can call them after every SPA DOM swap.
	window.alkanaCounters     = { init: initCounters };
	window.alkanaParallax     = { init: initParallax };
	window.alkanaHoverPhysics = { init: initHoverPhysics };
	window.alkanaScrollReveal = { init: initScrollReveal };

	initCounters();
	initParallax();
	initHoverPhysics();
	initScrollReveal();
	initViewTransitions();
} );
