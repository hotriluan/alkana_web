/**
 * filter.js — AJAX faceted product filter controller.
 *
 * Design rules:
 *  - OR logic within same filter group (e.g., surface: wood OR steel)
 *  - AND logic across different filter groups
 *  - Debounced 300ms before firing AJAX
 *  - URL state updated via History API (shareable links)
 *  - Dynamic count badges per option
 *  - Empty state: consultation CTA
 *
 * Depends on: AlkanaConfig (injected by wp_localize_script in enqueue-assets.php)
 *   AlkanaConfig.ajaxUrl  — admin-ajax.php endpoint
 *   AlkanaConfig.nonce    — wp nonce for alkana_filter action
 */

const DEBOUNCE_MS = 300;
let debounceTimer = null;

/** Current filter state */
const state = {
    surfaces:     [],   // array of surface slugs (OR within group)
    paint_system: '',
    gloss:        '',
    category:     '',
    page:         1,
};

const grid = document.getElementById('product-grid');

// ── Init ──────────────────────────────────────────────────────────────────────

function init() {
    if (!grid) return;                    // Not on product archive page
    readStateFromURL();
    bindFilterControls();
    syncControlsToState();
    // Run filter on load only if there are active filters
    if (hasActiveFilters()) runFilter();
}

// ── State ─────────────────────────────────────────────────────────────────────

function hasActiveFilters() {
    return state.surfaces.length > 0 ||
           state.paint_system ||
           state.gloss ||
           state.category;
}

function readStateFromURL() {
    const p = new URLSearchParams(window.location.search);
    state.surfaces     = p.get('surfaces')?.split(',').filter(Boolean) ?? [];
    state.paint_system = p.get('system')   ?? '';
    state.gloss        = p.get('gloss')    ?? '';
    state.category     = p.get('cat')      ?? '';
    state.page         = parseInt(p.get('page') ?? '1', 10) || 1;
}

function syncControlsToState() {
    // Sync checkboxes/radios to match URL state
    document.querySelectorAll('[data-filter-surface]').forEach((el) => {
        el.checked = state.surfaces.includes(el.value);
    });
    const sysEl   = document.querySelector('[data-filter-system]');
    const glossEl = document.querySelector('[data-filter-gloss]');
    const catEl   = document.querySelector('[data-filter-category]');
    if (sysEl)   sysEl.value   = state.paint_system;
    if (glossEl) glossEl.value = state.gloss;
    if (catEl)   catEl.value   = state.category;
}

function updateURL() {
    const p = new URLSearchParams();
    if (state.surfaces.length)  p.set('surfaces', state.surfaces.join(','));
    if (state.paint_system)     p.set('system',   state.paint_system);
    if (state.gloss)            p.set('gloss',    state.gloss);
    if (state.category)         p.set('cat',      state.category);
    if (state.page > 1)         p.set('page',     String(state.page));
    const qs = p.toString();
    history.pushState(null, '', qs ? `?${qs}` : window.location.pathname);
}

// ── Controls ──────────────────────────────────────────────────────────────────

function bindFilterControls() {
    // Surface checkboxes
    document.querySelectorAll('[data-filter-surface]').forEach((el) => {
        el.addEventListener('change', () => {
            state.surfaces = [...document.querySelectorAll('[data-filter-surface]:checked')]
                .map((e) => e.value);
            onFilterChange();
        });
    });

    // Single-value selects / radios
    document.querySelector('[data-filter-system]')
        ?.addEventListener('change', (e) => { state.paint_system = e.target.value; onFilterChange(); });
    document.querySelector('[data-filter-gloss]')
        ?.addEventListener('change', (e) => { state.gloss = e.target.value; onFilterChange(); });
    document.querySelector('[data-filter-category]')
        ?.addEventListener('change', (e) => { state.category = e.target.value; onFilterChange(); });

    // Pagination — delegated to the pagination container
    document.querySelector('[data-filter-pagination]')
        ?.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-page]');
            if (!btn) return;
            state.page = parseInt(btn.dataset.page, 10);
            runFilter();
            // Scroll product area back into view
            grid?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

    // Active tag remove
    document.querySelector('.active-tags')
        ?.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-remove-filter]');
            if (!btn) return;
            const { type, value } = btn.dataset;
            if (type === 'surface') {
                state.surfaces = state.surfaces.filter((s) => s !== value);
                const cb = document.querySelector(`[data-filter-surface][value="${value}"]`);
                if (cb) cb.checked = false;
            } else if (type === 'system') { state.paint_system = ''; }
            else if (type === 'gloss')    { state.gloss = ''; }
            else if (type === 'category') { state.category = ''; }
            state.page = 1;
            onFilterChange();
        });
}

function onFilterChange() {
    state.page = 1;
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(runFilter, DEBOUNCE_MS);
}

// ── AJAX ──────────────────────────────────────────────────────────────────────

async function runFilter() {
    if (!grid || typeof AlkanaConfig === 'undefined') return;

    grid.classList.add('loading');
    updateURL();
    renderActiveTags();

    try {
        const body = new URLSearchParams({
            action:      'alkana_filter_products',
            nonce:       AlkanaConfig.nonce,
            page:        String(state.page),
            paint_system: state.paint_system,
            gloss_level: state.gloss,
        });
        state.surfaces.forEach((s) => body.append('surface[]', s));
        state.category.split(',').filter(Boolean).forEach((c) => body.append('category[]', c));

        const res  = await fetch(AlkanaConfig.ajaxUrl, {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    body.toString(),
        });
        const json = await res.json();
        if (!json.success) throw new Error('Filter request failed');

        grid.innerHTML = json.data.html;
        updateCounts(json.data.counts);
        renderPagination(json.data.page, json.data.pages, json.data.total);
    } catch (err) {
        console.error('[Alkana Filter]', err);
    } finally {
        grid.classList.remove('loading');
    }
}

// ── UI Updates ────────────────────────────────────────────────────────────────

function updateCounts(counts) {
    if (!counts) return;

    // surface_type → data-count-surface
    Object.entries(counts.surface_type ?? {}).forEach(([slug, count]) => {
        const el = document.querySelector(`[data-count-surface="${slug}"]`);
        if (el) {
            el.textContent = `(${count})`;
            el.closest('.filter-option')?.classList.toggle('is-disabled', count === 0);
        }
    });

    // paint_system → data-count-system
    Object.entries(counts.paint_system ?? {}).forEach(([slug, count]) => {
        const el = document.querySelector(`[data-count-system="${slug}"]`);
        if (el) el.textContent = `(${count})`;
    });

    // gloss_level → data-count-gloss
    Object.entries(counts.gloss_level ?? {}).forEach(([slug, count]) => {
        const el = document.querySelector(`[data-count-gloss="${slug}"]`);
        if (el) el.textContent = `(${count})`;
    });

    // product_category → data-count-category
    Object.entries(counts.product_category ?? {}).forEach(([slug, count]) => {
        const el = document.querySelector(`[data-count-category="${slug}"]`);
        if (el) el.textContent = `(${count})`;
    });
}

function renderPagination(page, pages, total) {
    const container = document.querySelector('[data-filter-pagination]');
    if (!container) return;

    if (!pages || pages <= 1) { container.innerHTML = ''; return; }

    const btns = [];

    // Prev
    if (page > 1) {
        btns.push(`<button class="pagination__btn" data-page="${page - 1}" aria-label="Previous page">&#8592; Prev</button>`);
    }

    // Page numbers (show up to 5 around current)
    const start = Math.max(1, page - 2);
    const end   = Math.min(pages, page + 2);
    if (start > 1) btns.push(`<button class="pagination__btn" data-page="1">1</button>`);
    if (start > 2) btns.push(`<span class="pagination__ellipsis">…</span>`);
    for (let i = start; i <= end; i++) {
        btns.push(`<button class="pagination__btn ${i === page ? 'is-active' : ''}" data-page="${i}" aria-current="${i === page ? 'page' : 'false'}">${i}</button>`);
    }
    if (end < pages - 1) btns.push(`<span class="pagination__ellipsis">…</span>`);
    if (end < pages) btns.push(`<button class="pagination__btn" data-page="${pages}">${pages}</button>`);

    // Next
    if (page < pages) {
        btns.push(`<button class="pagination__btn" data-page="${page + 1}" aria-label="Next page">Next &#8594;</button>`);
    }

    container.innerHTML = btns.join('');
}

function renderActiveTags() {
    const container = document.querySelector('.active-tags');
    if (!container) return;

    const tags = [];
    state.surfaces.forEach((s) => {
        const label = document.querySelector(`[data-filter-surface][value="${s}"]`)
            ?.closest('.filter-option')?.querySelector('.filter-option__label')?.textContent?.trim() ?? s;
        tags.push(`<span class="active-tag">${label}<button class="active-tag__remove" data-remove-filter data-type="surface" data-value="${s}" aria-label="Remove ${label}">×</button></span>`);
    });
    if (state.paint_system) {
        const label = document.querySelector(`[data-filter-system] option[value="${state.paint_system}"]`)?.textContent?.trim() ?? state.paint_system;
        tags.push(`<span class="active-tag">${label}<button class="active-tag__remove" data-remove-filter data-type="system" aria-label="Remove ${label}">×</button></span>`);
    }
    if (state.gloss) {
        const label = document.querySelector(`[data-filter-gloss] option[value="${state.gloss}"]`)?.textContent?.trim() ?? state.gloss;
        tags.push(`<span class="active-tag">${label}<button class="active-tag__remove" data-remove-filter data-type="gloss" aria-label="Remove ${label}">×</button></span>`);
    }

    container.innerHTML = tags.join('');
}

// ── Accordion ────────────────────────────────────────────────────────────────

function initAccordions() {
    document.querySelectorAll('[data-accordion-trigger]').forEach((trigger) => {
        // Start open — content visible
        const content = trigger.nextElementSibling;
        if (!content || !content.hasAttribute('data-accordion-content')) return;

        trigger.setAttribute('aria-expanded', 'true');

        trigger.addEventListener('click', () => {
            const isOpen = trigger.getAttribute('aria-expanded') === 'true';
            const icon   = trigger.querySelector('[data-accordion-icon]');

            if (isOpen) {
                content.classList.add('hidden');
                trigger.setAttribute('aria-expanded', 'false');
                if (icon) icon.textContent = '+';
            } else {
                content.classList.remove('hidden');
                trigger.setAttribute('aria-expanded', 'true');
                if (icon) icon.textContent = '−';
            }
        });
    });
}

// ── Boot ──────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => { init(); initAccordions(); });
// Also run if WP loaded script in <head> with defer
if (document.readyState !== 'loading') { init(); initAccordions(); }
