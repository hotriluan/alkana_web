/**
 * sticky-cta.js — Adds body class to offset sticky CTA bar.
 * Only activates on mobile (< 1024px).
 */

function applyStickyCtaOffset() {
    const cta = document.querySelector('.sticky-cta');
    if (!cta) return;

    const isMobile = window.matchMedia('(max-width: 1023px)').matches;
    document.body.classList.toggle('has-sticky-cta', isMobile);
}

applyStickyCtaOffset();
window.addEventListener('resize', applyStickyCtaOffset);
