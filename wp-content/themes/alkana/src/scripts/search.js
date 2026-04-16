/**
 * Search Modal — full-screen purple predictive search
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

	// Close on backdrop click (only if clicking directly on modal backdrop)
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
				results.innerHTML = '<p class="text-sm text-white/50 py-4 text-center">Đã xảy ra lỗi khi tìm kiếm.</p>';
			} finally {
				loading.classList.add('hidden');
			}
		}, 300);
	});

	// Render results — purple-themed with thumbnails
	function renderResults(items) {
		if (!items || items.length === 0) {
			results.innerHTML = '<p class="text-sm text-white/40 py-6 text-center">Không tìm thấy kết quả</p>';
			return;
		}

		const products = items.filter((i) => i.type === 'product');
		const posts = items.filter((i) => i.type !== 'product');

		let html = '';

		if (products.length > 0) {
			html += '<div class="mb-6">';
			html += '<h3 class="text-xs font-semibold text-white/40 uppercase tracking-widest mb-3">Sản phẩm</h3>';
			html += '<div class="space-y-2">';
			products.forEach((item) => {
				const thumb = item.thumbnail
					? `<img src="${escapeHtml(item.thumbnail)}" alt="" class="w-full h-full object-cover" loading="lazy">`
					: '<div class="w-full h-full bg-white/10 flex items-center justify-center"><svg class="w-5 h-5 text-white/20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>';

				html += `<a href="${escapeHtml(item.url)}" class="flex items-center gap-4 px-4 py-3 rounded-xl bg-white/5 hover:bg-white/10 transition-colors">`;
				html += `<div class="w-12 h-12 rounded-lg overflow-hidden flex-shrink-0">${thumb}</div>`;
				html += '<div class="flex-1 min-w-0">';
				html += `<p class="text-sm font-medium text-white truncate">${escapeHtml(item.title)}</p>`;
				if (item.slug) {
					html += `<p class="text-xs text-white/40 font-mono">${escapeHtml(item.slug)}</p>`;
				}
				html += '</div>';
				html += '<svg class="w-4 h-4 text-white/30 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>';
				html += '</a>';
			});
			html += '</div></div>';
		}

		if (posts.length > 0) {
			html += '<div>';
			html += '<h3 class="text-xs font-semibold text-white/40 uppercase tracking-widest mb-3">Trang & Bài viết</h3>';
			html += '<div class="space-y-1">';
			posts.forEach((item) => {
				html += `<a href="${escapeHtml(item.url)}" class="block px-4 py-3 rounded-xl hover:bg-white/10 transition-colors text-sm text-white/80 hover:text-white">${escapeHtml(item.title)}</a>`;
			});
			html += '</div></div>';
		}

		results.innerHTML = html;
	}

	// Escape HTML
	function escapeHtml(text) {
		if (!text) return '';
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
