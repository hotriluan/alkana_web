/**
 * Form validation utilities
 *
 * @package Alkana
 */

(function () {
	'use strict';

	/**
	 * Basic email validation
	 * @param {string} email - Email address
	 * @returns {boolean}
	 */
	window.validateEmail = function (email) {
		return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
	};

	/**
	 * Basic phone validation (Vietnamese format)
	 * @param {string} phone - Phone number
	 * @returns {boolean}
	 */
	window.validatePhone = function (phone) {
		// Vietnamese phone: starts with 0, 10-11 digits
		return /^0[0-9]{9,10}$/.test(phone.replace(/[\s\-]/g, ''));
	};

	/**
	 * Add required field validation to forms
	 */
	document.querySelectorAll('form[data-validate]').forEach((form) => {
		form.addEventListener('submit', (e) => {
			const inputs = form.querySelectorAll('[required]');
			let valid = true;

			inputs.forEach((input) => {
				if (!input.value.trim()) {
					valid = false;
					input.classList.add('border-red-500');
				} else {
					input.classList.remove('border-red-500');
				}

				// Email validation
				if (input.type === 'email' && input.value && !validateEmail(input.value)) {
					valid = false;
					input.classList.add('border-red-500');
				}

				// Phone validation (if has data-validate-phone attribute)
				if (
					input.hasAttribute('data-validate-phone') &&
					input.value &&
					!validatePhone(input.value)
				) {
					valid = false;
					input.classList.add('border-red-500');
				}
			});

			if (!valid) {
				e.preventDefault();
			}
		});
	});
})();
