<?php
/**
 * Social share buttons.
 * LinkedIn, Facebook, and Copy Link functionality.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

$permalink = get_permalink();
$title     = get_the_title();
$encoded_url = rawurlencode( $permalink );
$encoded_title = rawurlencode( $title );
?>

<div class="share-buttons flex items-center gap-3">
	<span class="text-sm text-gray-500 font-medium">Chia sẻ:</span>
	
	<!-- LinkedIn -->
	<a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo esc_attr( $encoded_url ); ?>" 
	   target="_blank" 
	   rel="noopener noreferrer"
	   class="share-btn share-btn--linkedin w-9 h-9 rounded-full bg-gray-100 hover:bg-[#0077B5] hover:text-white text-gray-500 flex items-center justify-center transition-colors duration-300"
	   aria-label="Chia sẻ trên LinkedIn"
	   title="Chia sẻ trên LinkedIn">
		<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
			<path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
		</svg>
	</a>

	<!-- Facebook -->
	<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_attr( $encoded_url ); ?>" 
	   target="_blank" 
	   rel="noopener noreferrer"
	   class="share-btn share-btn--facebook w-9 h-9 rounded-full bg-gray-100 hover:bg-[#1877F2] hover:text-white text-gray-500 flex items-center justify-center transition-colors duration-300"
	   aria-label="Chia sẻ trên Facebook"
	   title="Chia sẻ trên Facebook">
		<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
			<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
		</svg>
	</a>

	<!-- Copy Link -->
	<button type="button"
	        class="share-btn share-btn--copy w-9 h-9 rounded-full bg-gray-100 hover:bg-[--color-primary] hover:text-white text-gray-500 flex items-center justify-center transition-colors duration-300"
	        data-url="<?php echo esc_attr( $permalink ); ?>"
	        aria-label="Sao chép liên kết"
	        title="Sao chép liên kết">
		<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
		</svg>
	</button>

	<!-- Success feedback (hidden by default) -->
	<span class="share-feedback text-xs text-green-600 font-medium opacity-0 transition-opacity duration-300" aria-live="polite">
		Đã sao chép!
	</span>
</div>

<script>
(function() {
	'use strict';
	
	// Copy link functionality
	var copyButtons = document.querySelectorAll('.share-btn--copy');
	
	copyButtons.forEach(function(button) {
		button.addEventListener('click', function(e) {
			e.preventDefault();
			
			var url = button.getAttribute('data-url');
			var feedback = button.parentElement.querySelector('.share-feedback');
			
			if (!url) return;
			
			// Use Clipboard API
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(url)
					.then(function() {
						showFeedback(button, feedback);
					})
					.catch(function(err) {
						console.error('Failed to copy:', err);
						fallbackCopy(url, button, feedback);
					});
			} else {
				fallbackCopy(url, button, feedback);
			}
		});
	});
	
	// Fallback copy method for older browsers
	function fallbackCopy(text, button, feedback) {
		var textarea = document.createElement('textarea');
		textarea.value = text;
		textarea.style.position = 'fixed';
		textarea.style.opacity = '0';
		document.body.appendChild(textarea);
		textarea.select();
		
		try {
			document.execCommand('copy');
			showFeedback(button, feedback);
		} catch (err) {
			console.error('Fallback copy failed:', err);
		}
		
		document.body.removeChild(textarea);
	}
	
	// Show success feedback
	function showFeedback(button, feedback) {
		if (!feedback) return;
		
		feedback.style.opacity = '1';
		
		setTimeout(function() {
			feedback.style.opacity = '0';
		}, 2000);
	}
})();
</script>
