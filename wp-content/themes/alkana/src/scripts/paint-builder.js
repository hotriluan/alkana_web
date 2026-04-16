/**
 * Paint System Builder — Vanilla JS state machine.
 * No external libraries. Step 1 → 2 → 3 wizard flow.
 *
 * @package Alkana
 */

(function () {
	'use strict';

	// ── Config injected by wp_localize_script ──────────────────────────────
	/** @type {{ ajaxUrl: string, nonce: string }} */
	const CONFIG = window.AlkanaPaintBuilderConfig ?? {};

	// ── State ──────────────────────────────────────────────────────────────
	const state = { surface: null, environment: null, condition: null };

	// ── DOM refs ───────────────────────────────────────────────────────────
	const step1El      = document.getElementById('step-1');
	const step2El      = document.getElementById('step-2');
	const step3El      = document.getElementById('step-3');
	const resultsEl    = document.getElementById('builder-results');
	const skeletonEl   = document.getElementById('builder-skeleton');
	const resetWrapEl  = document.getElementById('builder-reset-wrap');
	const resetBtn     = document.getElementById('builder-reset');

	if ( !step1El ) return; // Not on builder page

	// ── Step dot helpers ───────────────────────────────────────────────────
	function activateDot( n ) {
		const dot   = document.getElementById( 'step-dot-' + n );
		const label = document.getElementById( 'step-label-' + n );
		if ( dot )   { dot.classList.replace( 'bg-gray-200', 'bg-alkana-purple-600' ); dot.classList.replace( 'text-gray-400', 'text-white' ); }
		if ( label ) { label.classList.replace( 'text-gray-400', 'text-alkana-purple-700' ); }
	}

	// ── Step 1: surface card click ─────────────────────────────────────────
	document.querySelectorAll('.surface-card').forEach(( card ) => {
		card.addEventListener('click', () => {
			state.surface = card.dataset.surface;

			// Highlight selected card + update aria-pressed
			document.querySelectorAll('.surface-card').forEach(( c ) => {
				const isSelected = c === card;
				c.classList.toggle( 'border-alkana-purple-600', isSelected );
				c.classList.toggle( 'bg-alkana-purple-50', isSelected );
				c.querySelector('.surface-card__icon')?.classList.toggle( 'bg-alkana-purple-100', isSelected );
				c.setAttribute( 'aria-pressed', String( isSelected ) );
			});

			// Unlock step 2 with smooth reveal
			step2El.classList.remove('hidden');
			step2El.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
			activateDot(2);

			// Reset downstream state
			state.environment = null;
			state.condition   = null;
			document.querySelectorAll('.option-btn').forEach(( b ) => b.classList.remove('is-selected'));
			step3El.classList.add('hidden');
			resetWrapEl?.classList.add('hidden');
		});
	});

	// ── Step 2: environment/condition option click ─────────────────────────
	document.querySelectorAll('.env-btn').forEach(( btn ) => {
		btn.addEventListener('click', () => {
			const group = btn.dataset.group; // environment | condition
			const value = btn.dataset.value;

			// Highlight within group
			document.querySelectorAll(`.option-btn[data-group="${group}"]`).forEach(( b ) => {
				b.classList.toggle( 'is-selected', b === btn );
			});

			state[ group ] = value;

			// When both env fields are selected — fire query
			if ( state.environment && state.condition ) {
				activateDot(3);
				step3El.classList.remove('hidden');
				step3El.scrollIntoView({ behavior: 'smooth', block: 'start' });
				fetchRecommendation();
			}
		});
	});

	// ── Reset ──────────────────────────────────────────────────────────────
	resetBtn?.addEventListener('click', () => {
		state.surface     = null;
		state.environment = null;
		state.condition   = null;

		document.querySelectorAll('.surface-card').forEach(( c ) => {
			c.classList.remove( 'border-alkana-purple-600', 'bg-alkana-purple-50' );
			c.querySelector('.surface-card__icon')?.classList.remove( 'bg-alkana-purple-100' );
			c.setAttribute( 'aria-pressed', 'false' );
		});
		document.querySelectorAll('.option-btn').forEach(( b ) => b.classList.remove('is-selected'));

		step2El.classList.add('hidden');
		step3El.classList.add('hidden');
		resetWrapEl?.classList.add('hidden');

		// Reset dots
		[2, 3].forEach(( n ) => {
			const dot   = document.getElementById( 'step-dot-' + n );
			const label = document.getElementById( 'step-label-' + n );
			if ( dot )   { dot.classList.replace( 'bg-alkana-purple-600', 'bg-gray-200' ); dot.classList.replace( 'text-white', 'text-gray-400' ); }
			if ( label ) { label.classList.replace( 'text-alkana-purple-700', 'text-gray-400' ); }
		});

		step1El.scrollIntoView({ behavior: 'smooth', block: 'start' });
	});

	// ── Fetch recommendation ───────────────────────────────────────────────
	async function fetchRecommendation() {
		if ( !CONFIG.ajaxUrl ) return;

		// Show skeleton
		if ( skeletonEl ) skeletonEl.classList.remove('hidden');
		// Remove previous rendered results
		document.getElementById('builder-product-grid')?.remove();
		document.getElementById('builder-cta-row')?.remove();

		try {
			const body = new URLSearchParams({
				action:      'alkana_paint_builder',
				nonce:       CONFIG.nonce,
				surface:     state.surface,
				environment: state.environment,
				condition:   state.condition,
			});

			const response = await fetch( CONFIG.ajaxUrl, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: body.toString(),
			});

			const json = await response.json();
			if ( !json.success ) throw new Error('Request failed');

			renderResults( json.data );
		} catch ( err ) {
			console.error('[PaintBuilder]', err);
			renderError();
		} finally {
			if ( skeletonEl ) skeletonEl.classList.add('hidden');
			resetWrapEl?.classList.remove('hidden');
		}
	}

	// ── Render helpers ─────────────────────────────────────────────────────
	function escHtml( str ) {
		const d = document.createElement('div');
		d.textContent = str;
		return d.innerHTML;
	}

	function productCard( product, roleLabel ) {
		const thumb = product.thumb
			? `<img src="${escHtml(product.thumb)}" alt="${escHtml(product.title)}" class="builder-card__img w-full h-full object-cover" loading="lazy">`
			: `<div class="builder-card__no-img flex items-center justify-center w-full h-full bg-alkana-purple-50 text-alkana-purple-300">
				<svg class="w-12 h-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
			   </div>`;

		return `
		<div class="builder-card bg-white rounded-xl border border-gray-100 overflow-hidden flex flex-col">
			<div class="aspect-[4/3] overflow-hidden bg-gray-50">${thumb}</div>
			<div class="p-5 flex flex-col flex-1 gap-2">
				<span class="builder-card__role text-xs font-bold uppercase tracking-wider">${escHtml(roleLabel)}</span>
				${product.category ? `<span class="text-xs text-gray-400">${escHtml(product.category)}</span>` : ''}
				<h3 class="text-base font-semibold text-gray-800 leading-snug flex-1">${escHtml(product.title)}</h3>
				${product.sku ? `<p class="text-xs text-gray-500 font-mono">SKU: ${escHtml(product.sku)}</p>` : ''}
				<a href="${escHtml(product.url)}"
				   class="mt-2 btn btn--outline btn--sm text-center w-full">
					Xem chi tiết
				</a>
			</div>
		</div>`;
	}

	function renderResults({ primer, intermediate, topcoat, quote_url }) {
		// Grid — 3-column layout
		const grid = document.createElement('div');
		grid.id    = 'builder-product-grid';

		const noProduct = `<div class="builder-card-empty bg-white rounded-xl border border-dashed border-gray-200 flex flex-col items-center justify-center p-8 text-gray-400 text-sm">
			Không cần lớp này
		</div>`;

		grid.className = 'grid grid-cols-1 sm:grid-cols-3 gap-5 mt-4';
		grid.innerHTML = [
			primer       ? productCard( primer,       'Sơn lót (Primer)'      ) : noProduct,
			intermediate ? productCard( intermediate, 'Sơn trung gian'         ) : noProduct,
			topcoat      ? productCard( topcoat,      'Sơn phủ (Topcoat)'     ) : noProduct,
		].join('');

		// CTA row
		const ctaRow = document.createElement('div');
		ctaRow.id        = 'builder-cta-row';
		ctaRow.className = 'mt-8 text-center';
		ctaRow.innerHTML = `
			<p class="text-sm text-gray-500 mb-4">Cần tư vấn chi tiết về định mức, bảng màu hoặc điều kiện thi công?</p>
			<div class="flex flex-wrap items-center justify-center gap-3">
				<button type="button" onclick="window.print()" class="builder-print-btn btn btn--outline btn--lg inline-flex items-center gap-2">
					<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/></svg>
					In kết quả
				</button>
				<a href="${escHtml(quote_url || '#contact')}"
				   class="btn btn--primary btn--lg inline-flex items-center gap-2">
					<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
					Nhận Báo Giá Hệ Thống
				</a>
			</div>`;

		resultsEl.appendChild( grid );
		resultsEl.appendChild( ctaRow );

		// Encode current wizard state into URL for sharing
		updateURL();
	}

	function renderError() {
		const err = document.createElement('p');
		err.id        = 'builder-product-grid';
		err.className = 'text-center text-red-500 text-sm py-8';
		err.textContent = 'Đã xảy ra lỗi. Vui lòng thử lại sau.';
		resultsEl.appendChild( err );
	}

	// ── URL state — encode current wizard selection for sharing ────────────
	function updateURL() {
		if ( !state.surface || !state.environment || !state.condition ) return;
		const params = new URLSearchParams({
			surface:     state.surface,
			environment: state.environment,
			condition:   state.condition,
		});
		history.replaceState( null, '', '?' + params.toString() );
	}

	// ── Restore from shareable URL on page load ────────────────────────────
	function restoreFromURL() {
		const params = new URLSearchParams( window.location.search );
		const s = params.get('surface');
		const e = params.get('environment');
		const c = params.get('condition');
		if ( !s || !e || !c ) return;

		const surfaceCard = document.querySelector( `.surface-card[data-surface="${CSS.escape(s)}"]` );
		if ( !surfaceCard ) return;

		// Set surface state + highlight card
		state.surface = s;
		surfaceCard.classList.add( 'border-alkana-purple-600', 'bg-alkana-purple-50' );
		surfaceCard.querySelector('.surface-card__icon')?.classList.add( 'bg-alkana-purple-100' );
		surfaceCard.setAttribute( 'aria-pressed', 'true' );

		// Reveal step 2 without scroll
		step2El.classList.remove('hidden');
		activateDot(2);

		// Set environment + condition state
		state.environment = e;
		state.condition   = c;
		const envBtn  = document.querySelector( `.env-btn[data-group="environment"][data-value="${CSS.escape(e)}"]` );
		const condBtn = document.querySelector( `.env-btn[data-group="condition"][data-value="${CSS.escape(c)}"]` );
		envBtn?.classList.add('is-selected');
		condBtn?.classList.add('is-selected');

		// Trigger Step 3 results
		activateDot(3);
		step3El.classList.remove('hidden');
		fetchRecommendation();
	}

	// Restore from URL on page load (supports shared links)
	restoreFromURL();

})();
