/**
 * Search Modal functionality
 *
 * @package Alkana
 */

(function () {
	'use strict';

	const modal = document.getElementById('search-modal');
	const toggleBtn = document.getElementById('search-toggle');
	const closeBtn = document.getElementById('search-modal-close');
	const input = document.getElementById('search-modal-input');
	const results = document.getElementById('search-results');
	const loading = document.getElementById('search-loading');

	if (!modal || !toggleBtn || !closeBtn || !input || !results) return;

	const nonce = modal.getAttribute('data-search-nonce');
	let debounceTimer;

	// Open modal
	toggleBtn.addEventListener('click', () => {
		modal.classList.remove('hidden');
		input.focus();
	});

	// Close modal
	const closeModal = () => {
		modal.classList.add('hidden');
		input.value = '';
		results.innerHTML = '';
	};

	closeBtn.addEventListener('click', closeModal);

	// Close on backdrop click
	modal.addEventListener('click', (e) => {
		if (e.target === modal) closeModal();
	});

	// Close on Escape key
	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
			closeModal();
		}
	});

	// Search with debounce
	input.addEventListener('input', (e) => {
		clearTimeout(debounceTimer);
		const term = e.target.value.trim();

		if (term.length < 2) {
			results.innerHTML = '';
			return;
		}

		loading.classList.remove('hidden');

		debounceTimer = setTimeout(async () => {
			try {
				const response = await fetch(
					`${window.AlkanaConfig.ajaxUrl}?action=alkana_search&nonce=${nonce}&q=${encodeURIComponent(term)}`
				);
				const data = await response.json();

				renderResults(data.results);
			} catch (error) {
				console.error('Search error:', error);
				results.innerHTML = '<p class="text-sm text-red-500 py-4">Đã xảy ra lỗi khi tìm kiếm.</p>';
			} finally {
				loading.classList.add('hidden');
			}
		}, 300);
	});

	// Render results
	function renderResults(items) {
		if (!items || items.length === 0) {
			results.innerHTML = '<p class="text-sm text-gray-500 py-4">Không tìm thấy kết quả</p>';
			return;
		}

		const products = items.filter((i) => i.type === 'product');
		const posts = items.filter((i) => i.type !== 'product');

		let html = '';

		if (products.length > 0) {
			html += '<div class="mb-4"><h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Sản phẩm</h3>';
			html += '<ul class="space-y-1">';
			products.forEach((item) => {
				html += `<li><a href="${escapeHtml(item.url)}" class="block px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors text-sm text-[--color-secondary] hover:text-[--color-primary]">${escapeHtml(item.title)}</a></li>`;
			});
			html += '</ul></div>';
		}

		if (posts.length > 0) {
			html += '<div><h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Trang & Bài viết</h3>';
			html += '<ul class="space-y-1">';
			posts.forEach((item) => {
				html += `<li><a href="${escapeHtml(item.url)}" class="block px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors text-sm text-[--color-secondary] hover:text-[--color-primary]">${escapeHtml(item.title)}</a></li>`;
			});
			html += '</ul></div>';
		}

		results.innerHTML = html;
	}

	// Escape HTML
	function escapeHtml(text) {
		const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
		return text.replace(/[&<>"']/g, (m) => map[m]);
	}

	// Focus trap
	modal.addEventListener('keydown', (e) => {
		if (e.key === 'Tab') {
			const focusable = modal.querySelectorAll('button, input, a');
			const first = focusable[0];
			const last = focusable[focusable.length - 1];

			if (e.shiftKey && document.activeElement === first) {
				e.preventDefault();
				last.focus();
			} else if (!e.shiftKey && document.activeElement === last) {
				e.preventDefault();
				first.focus();
			}
		}
	});
})();
