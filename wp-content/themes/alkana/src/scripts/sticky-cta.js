/**
 * sticky-cta.js — Handles two modes:
 * 1. Product single: IntersectionObserver shows bar when main CTA scrolls out of view
 * 2. Archive mobile: Adds body class for bottom offset
 */

function initStickyCta() {
    // Product single page — IntersectionObserver
    const stickyBar = document.getElementById('sticky-cta-product');
    const mainCta   = document.getElementById('product-cta-main');

    if (stickyBar && mainCta) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        stickyBar.classList.add('translate-y-full');
                        stickyBar.classList.remove('translate-y-0');
                    } else {
                        stickyBar.classList.remove('translate-y-full');
                        stickyBar.classList.add('translate-y-0');
                    }
                });
            },
            { threshold: 0 }
        );
        observer.observe(mainCta);
    }

    // Archive page — always add body offset so footer isn't hidden
    const archiveCta = document.querySelector('.sticky-cta');
    if (archiveCta) {
        document.body.classList.add('has-sticky-cta');
    }
}

initStickyCta();
