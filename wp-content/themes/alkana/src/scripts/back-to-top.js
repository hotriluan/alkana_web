/**
 * Back to top button functionality.
 */

const backToTopBtn = document.querySelector('.back-to-top');

if (backToTopBtn) {
	let ticking = false;

	// Show/hide button based on scroll position
	const updateButtonVisibility = () => {
		const shouldShow = window.scrollY > 300;
		backToTopBtn.classList.toggle('is-visible', shouldShow);
		backToTopBtn.style.opacity = shouldShow ? '1' : '0';
		backToTopBtn.style.pointerEvents = shouldShow ? 'auto' : 'none';
		ticking = false;
	};

	// Use requestAnimationFrame for performance
	window.addEventListener('scroll', () => {
		if (!ticking) {
			requestAnimationFrame(updateButtonVisibility);
			ticking = true;
		}
	}, { passive: true });

	// Smooth scroll to top on click
	backToTopBtn.addEventListener('click', () => {
		window.scrollTo({ top: 0, behavior: 'smooth' });
	});
}
