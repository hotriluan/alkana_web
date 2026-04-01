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

/** Current filter state — all groups are arrays (multi-checkbox OR logic) */
const state = {
    surface:  [],   // array of surface type slugs
    system:   [],   // array of paint system slugs
    gloss:    [],   // array of gloss level slugs
    category: [],   // array of category slugs
    featured: false,
    page:     1,
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
    return state.surface.length > 0 ||
           state.system.length > 0 ||
           state.gloss.length > 0 ||
           state.category.length > 0 ||
           state.featured;
}

function readStateFromURL() {
    const p = new URLSearchParams(window.location.search);
    state.surface  = p.get('surface')?.split(',').filter(Boolean)  ?? [];
    state.system   = p.get('system')?.split(',').filter(Boolean)   ?? [];
    state.gloss    = p.get('gloss')?.split(',').filter(Boolean)    ?? [];
    state.category = p.get('cat')?.split(',').filter(Boolean)      ?? [];
    state.featured = p.get('featured') === '1';
    state.page     = parseInt(p.get('page') ?? '1', 10) || 1;
}

function syncControlsToState() {
    // Sync all checkboxes to match URL state (both desktop + mobile panels)
    document.querySelectorAll('[data-filter-surface]').forEach((el) => {
        el.checked = state.surface.includes(el.value);
    });
    document.querySelectorAll('[data-filter-system]').forEach((el) => {
        el.checked = state.system.includes(el.value);
    });
    document.querySelectorAll('[data-filter-gloss]').forEach((el) => {
        el.checked = state.gloss.includes(el.value);
    });
    document.querySelectorAll('[data-filter-category]').forEach((el) => {
        el.checked = state.category.includes(el.value);
    });
    document.querySelectorAll('[data-filter-featured]').forEach((el) => {
        el.checked = state.featured;
    });
}

function updateURL() {
    const p = new URLSearchParams();
    if (state.surface.length)   p.set('surface',  state.surface.join(','));
    if (state.system.length)    p.set('system',   state.system.join(','));
    if (state.gloss.length)     p.set('gloss',    state.gloss.join(','));
    if (state.category.length)  p.set('cat',      state.category.join(','));
    if (state.featured)         p.set('featured', '1');
    if (state.page > 1)         p.set('page',     String(state.page));
    const qs = p.toString();
    history.pushState(null, '', qs ? `?${qs}` : window.location.pathname);
}

// ── Controls ──────────────────────────────────────────────────────────────────

function bindFilterControls() {
    // Multi-checkbox binding helper — syncs checkboxes across desktop/mobile panels
    function bindCheckboxGroup(attr, stateKey) {
        document.querySelectorAll(`[${attr}]`).forEach((el) => {
            el.addEventListener('change', () => {
                document.querySelectorAll(`[${attr}][value="${el.value}"]`).forEach((s) => { s.checked = el.checked; });
                state[stateKey] = [...new Set(
                    [...document.querySelectorAll(`[${attr}]:checked`)].map((e) => e.value)
                )];
                onFilterChange();
            });
        });
    }

    bindCheckboxGroup('data-filter-surface',  'surface');
    bindCheckboxGroup('data-filter-system',   'system');
    bindCheckboxGroup('data-filter-gloss',    'gloss');
    bindCheckboxGroup('data-filter-category', 'category');

    // Featured toggle — sync across panels
    document.querySelectorAll('[data-filter-featured]').forEach((el) => {
        el.addEventListener('change', () => {
            document.querySelectorAll('[data-filter-featured]').forEach((s) => { s.checked = el.checked; });
            state.featured = el.checked;
            onFilterChange();
        });
    });

    // Pagination — delegated to the pagination container
    document.querySelector('[data-filter-pagination]')
        ?.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-page]');
            if (!btn) return;
            state.page = parseInt(btn.dataset.page, 10);
            runFilter();
            grid?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

    // Active tag remove — delegated
    document.getElementById('filter-active-tags')
        ?.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-remove-filter]');
            if (!btn) return;
            removeFilter(btn.dataset.type, btn.dataset.value);
            state.page = 1;
            onFilterChange();
        });

    // Reset buttons
    document.getElementById('filter-reset')?.addEventListener('click', resetAllFilters);
    document.addEventListener('click', (e) => {
        if (e.target.closest('#filter-reset-empty')) resetAllFilters();
    });
}

function removeFilter(type, value) {
    const attrMap = { surface: 'data-filter-surface', system: 'data-filter-system', gloss: 'data-filter-gloss', category: 'data-filter-category' };
    if (attrMap[type]) {
        state[type] = state[type].filter((s) => s !== value);
        document.querySelectorAll(`[${attrMap[type]}][value="${value}"]`).forEach((cb) => { cb.checked = false; });
    } else if (type === 'featured') {
        state.featured = false;
        document.querySelectorAll('[data-filter-featured]').forEach((el) => { el.checked = false; });
    }
}

function resetAllFilters() {
    state.surface  = [];
    state.system   = [];
    state.gloss    = [];
    state.category = [];
    state.featured = false;
    state.page     = 1;
    document.querySelectorAll('.filter-option__checkbox').forEach((el) => { el.checked = false; });
    onFilterChange();
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
            action: 'alkana_filter_products',
            nonce:  AlkanaConfig.nonce,
            page:   String(state.page),
        });
        state.surface.forEach((s)  => body.append('surface[]', s));
        state.system.forEach((s)   => body.append('paint_system[]', s));
        state.gloss.forEach((s)    => body.append('gloss_level[]', s));
        state.category.forEach((s) => body.append('category[]', s));
        if (state.featured) body.append('is_featured', '1');

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
        // Update product count display
        const countEl = document.getElementById('filter-count');
        if (countEl) countEl.textContent = `${json.data.total} sản phẩm`;
    } catch (err) {
        console.error('[Alkana Filter]', err);
    } finally {
        grid.classList.remove('loading');
    }
}

// ── UI Updates ────────────────────────────────────────────────────────────────

function updateCounts(counts) {
    if (!counts) return;

    const groups = [
        { taxonomy: 'surface_type',     attr: 'data-count-surface' },
        { taxonomy: 'paint_system',     attr: 'data-count-system' },
        { taxonomy: 'gloss_level',      attr: 'data-count-gloss' },
        { taxonomy: 'product_category', attr: 'data-count-category' },
    ];

    groups.forEach(({ taxonomy, attr }) => {
        Object.entries(counts[taxonomy] ?? {}).forEach(([slug, count]) => {
            document.querySelectorAll(`[${attr}="${slug}"]`).forEach((el) => {
                el.textContent = `(${count})`;
                el.closest('.filter-option')?.classList.toggle('is-disabled', count === 0);
            });
        });
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
    const container = document.getElementById('filter-active-tags');
    if (!container) return;

    const tags = [];
    const groups = [
        { key: 'surface',  attr: 'data-filter-surface' },
        { key: 'system',   attr: 'data-filter-system' },
        { key: 'gloss',    attr: 'data-filter-gloss' },
        { key: 'category', attr: 'data-filter-category' },
    ];

    groups.forEach(({ key, attr }) => {
        state[key].forEach((slug) => {
            const label = document.querySelector(`[${attr}][value="${slug}"]`)
                ?.closest('.filter-option')?.querySelector('.filter-option__label')?.textContent?.trim() ?? slug;
            tags.push(`<span class="active-tag">${label}<button class="active-tag__remove" data-remove-filter data-type="${key}" data-value="${slug}" aria-label="Remove ${label}">×</button></span>`);
        });
    });

    if (state.featured) {
        tags.push(`<span class="active-tag">Nổi bật<button class="active-tag__remove" data-remove-filter data-type="featured" aria-label="Remove featured">×</button></span>`);
    }

    container.innerHTML = tags.join('');

    // Show/hide reset button
    const resetBtn = document.getElementById('filter-reset');
    if (resetBtn) resetBtn.classList.toggle('hidden', !hasActiveFilters());
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
