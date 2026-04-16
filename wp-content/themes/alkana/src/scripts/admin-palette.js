/**
 * Admin Command Palette (Ctrl+K / Cmd+K)
 * Provides blazing-fast product search + navigation for Alkana staff.
 *
 * @package Alkana
 */

(function () {
	'use strict';

	const MODAL_ID = 'alkana-cmd-palette';
	let modal, input, resultsList;
	let debounceTimer;
	let activeIndex = -1;
	let results = [];

	function init() {
		if (!document.getElementById(MODAL_ID)) return;
		modal       = document.getElementById(MODAL_ID);
		input       = document.getElementById('cmd-palette-input');
		resultsList = document.getElementById('cmd-palette-results');

		document.addEventListener('keydown', onGlobalKeydown);
		input.addEventListener('input', onInput);
		input.addEventListener('keydown', onInputKeydown);
		modal.addEventListener('click', onBackdropClick);
	}

	function onGlobalKeydown(e) {
		if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
			e.preventDefault();
			toggleModal();
		}
		if (e.key === 'Escape' && isOpen()) {
			closeModal();
		}
	}

	function isOpen() {
		return modal && !modal.classList.contains('alkana-cmd-hidden');
	}

	function toggleModal() {
		if (isOpen()) {
			closeModal();
		} else {
			openModal();
		}
	}

	function openModal() {
		modal.classList.remove('alkana-cmd-hidden');
		input.value = '';
		resultsList.innerHTML = '';
		activeIndex = -1;
		results = [];
		input.focus();
	}

	function closeModal() {
		modal.classList.add('alkana-cmd-hidden');
	}

	function onBackdropClick(e) {
		if (e.target === modal) closeModal();
	}

	function onInput() {
		clearTimeout(debounceTimer);
		const term = input.value.trim();

		if (term.length < 2) {
			resultsList.innerHTML = '';
			results = [];
			activeIndex = -1;
			return;
		}

		debounceTimer = setTimeout(() => fetchProducts(term), 300);
	}

	function onInputKeydown(e) {
		if (e.key === 'ArrowDown') {
			e.preventDefault();
			if (results.length > 0) {
				activeIndex = Math.min(activeIndex + 1, results.length - 1);
				highlightActive();
			}
		} else if (e.key === 'ArrowUp') {
			e.preventDefault();
			if (results.length > 0) {
				activeIndex = Math.max(activeIndex - 1, 0);
				highlightActive();
			}
		} else if (e.key === 'Enter') {
			e.preventDefault();
			if (activeIndex >= 0 && results[activeIndex]) {
				window.location.href = results[activeIndex].editUrl;
			}
		}
	}

	async function fetchProducts(term) {
		try {
			const res = await fetch(
				`${window.ajaxurl?.replace('/admin-ajax.php', '') || '/wp-json'}/wp/v2/alkana_product?search=${encodeURIComponent(term)}&per_page=8&_fields=id,title,link`
			);
			if (!res.ok) throw new Error('Fetch failed');
			const data = await res.json();

			results = data.map((item) => ({
				id:      item.id,
				title:   item.title.rendered,
				editUrl: `${window.location.origin}/wp-admin/post.php?post=${item.id}&action=edit`,
			}));

			renderResults();
		} catch (err) {
			console.error('[Alkana Palette]', err);
			resultsList.innerHTML = '<div style="padding:12px;color:#999;font-size:13px;">Error fetching results.</div>';
		}
	}

	function renderResults() {
		if (results.length === 0) {
			resultsList.innerHTML = '<div style="padding:12px 16px;color:rgba(255,255,255,0.4);font-size:13px;">No products found</div>';
			activeIndex = -1;
			return;
		}

		activeIndex = 0;
		resultsList.innerHTML = results.map((item, i) => {
			const bg = i === 0 ? 'background:rgba(123,31,162,0.3);' : '';
			return `<div class="cmd-result" data-index="${i}" style="padding:10px 16px;cursor:pointer;border-radius:8px;margin:2px 8px;transition:background 0.15s;${bg}">`
				+ `<span style="color:#fff;font-size:14px;">${escapeHtml(item.title)}</span>`
				+ `<span style="color:rgba(255,255,255,0.35);font-size:11px;margin-left:8px;">ID: ${item.id}</span>`
				+ '</div>';
		}).join('');

		resultsList.querySelectorAll('.cmd-result').forEach((el) => {
			el.addEventListener('click', () => {
				const idx = parseInt(el.dataset.index, 10);
				if (results[idx]) window.location.href = results[idx].editUrl;
			});
			el.addEventListener('mouseenter', () => {
				activeIndex = parseInt(el.dataset.index, 10);
				highlightActive();
			});
		});
	}

	function highlightActive() {
		resultsList.querySelectorAll('.cmd-result').forEach((el, i) => {
			el.style.background = i === activeIndex ? 'rgba(123,31,162,0.3)' : 'transparent';
		});
	}

	function escapeHtml(text) {
		if (!text) return '';
		const d = document.createElement('div');
		d.textContent = text;
		return d.innerHTML;
	}

	// Boot
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
