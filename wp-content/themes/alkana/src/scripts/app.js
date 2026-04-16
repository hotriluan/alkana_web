/**
 * Alkana Theme — app.js entry point
 * Imports all JS modules. Vite tree-shakes unused code at build time.
 */

import './mobile-menu.js';
import './header-scroll.js';
import { initCounters } from './counter.js';
import { initParallax } from './parallax.js';
import './bottom-sheet.js';
import { init as initHoverPhysics } from './hover-physics.js';
import { init as initScrollReveal } from './scroll-reveal.js';
import { initViewTransitions } from './view-transitions.js';

document.addEventListener( 'DOMContentLoaded', () => {
	initCounters();
	initParallax();
	initHoverPhysics();
	initScrollReveal();
	initViewTransitions();
} );
import './filter.js';
import './sticky-cta.js';
import './search.js';
import './back-to-top.js';
import './form-validation.js';
import './newsletter.js';
import './gallery.js';
