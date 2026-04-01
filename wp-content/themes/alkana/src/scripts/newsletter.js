/**
 * Newsletter subscription form handler
 *
 * @package Alkana
 */

(function () {
	'use strict';

	const form = document.getElementById('newsletter-form');
	const messageContainer = document.getElementById('newsletter-message');

	if (!form || !messageContainer) return;

	const emailInput = form.querySelector('input[name="email"]');
	const submitBtn = form.querySelector('button[type="submit"]');

	form.addEventListener('submit', async (e) => {
		e.preventDefault();

		// Reset message
		messageContainer.classList.add('hidden');
		messageContainer.textContent = '';
		messageContainer.className = 'mt-3 text-sm text-white/90 hidden';

		// Validate email
		const email = emailInput.value.trim();
		if (!email || !isValidEmail(email)) {
			showMessage('Vui lòng nhập địa chỉ email hợp lệ.', 'error');
			return;
		}

		// Disable submit button
		const originalBtnText = submitBtn.textContent;
		submitBtn.disabled = true;
		submitBtn.textContent = 'Đang gửi...';

		try {
			const formData = new FormData(form);
			formData.append('action', 'alkana_newsletter');

			const response = await fetch(window.AlkanaConfig.ajaxUrl, {
				method: 'POST',
				body: formData,
			});

			const data = await response.json();

			if (data.success) {
				showMessage(data.data.message, 'success');
				form.reset();
			} else {
				showMessage(data.data.message || 'Đã xảy ra lỗi. Vui lòng thử lại.', 'error');
			}
		} catch (error) {
			console.error('Newsletter error:', error);
			showMessage('Đã xảy ra lỗi. Vui lòng thử lại sau.', 'error');
		} finally {
			submitBtn.disabled = false;
			submitBtn.textContent = originalBtnText;
		}
	});

	/**
	 * Display message to user
	 * @param {string} message - Message text
	 * @param {string} type - 'success' or 'error'
	 */
	function showMessage(message, type) {
		messageContainer.textContent = message;
		messageContainer.classList.remove('hidden');
		
		if (type === 'error') {
			messageContainer.classList.add('text-red-100');
		} else {
			messageContainer.classList.add('text-white');
		}

		// Auto-hide success message after 5 seconds
		if (type === 'success') {
			setTimeout(() => {
				messageContainer.classList.add('hidden');
			}, 5000);
		}
	}

	/**
	 * Simple email validation
	 * @param {string} email - Email address
	 * @returns {boolean}
	 */
	function isValidEmail(email) {
		return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
	}
})();
