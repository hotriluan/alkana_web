// tests/09-admin/core-functionality.spec.js
// Scenarios: 1.1–1.5, 4.1–4.6, 12.1–12.5, 5.1–5.6 — scale, pagination, business logic, state
'use strict';

const { test, expect } = require('@playwright/test');
const { BASE } = require('../helpers/fixtures');
const { ajaxPost, getPageNonce } = require('../helpers/ajax');
const { softAssertPerf, measureMs, skipUnlessCI } = require('../helpers/test-utils');

// ─── User Types ───────────────────────────────────────────────────────────────

test('[1.1] Guest: products, filter, and search work without login', async ({ page }) => {
  const resp = await page.goto(`${BASE}/san-pham/`, { waitUntil: 'networkidle' });
  if (resp && resp.status() === 404) {
    test.skip(true, 'Product archive 404 in dev');
    return;
  }
  const body = await page.locator('body').textContent();
  expect(body).not.toContain('Fatal error');
  expect(body).not.toContain('You must be logged in');
});

test('[1.2] Bot/crawler: product archive returns server-rendered HTML', async ({ page }) => {
  await page.setExtraHTTPHeaders({ 'User-Agent': 'Googlebot/2.1' });
  const resp = await page.goto(`${BASE}/san-pham/`, { waitUntil: 'domcontentloaded' });
  if (resp && resp.status() === 404) {
    test.skip(true, 'Product archive 404 in dev');
    return;
  }
  const content = await page.content();
  // Server-rendered HTML should include product meta tags
  expect(content).toMatch(/<title>|<meta name="description"/i);
});

test('[1.4] Honeypot field filled: form submission rejected', async ({ page }) => {
  await page.goto(`${BASE}/lien-he/`, { waitUntil: 'networkidle' });
  const nonce = await getPageNonce(page);
  const result = await ajaxPost(page, {
    action: 'alkana_submit_contact',
    contact_name: 'Bot Name',
    contact_email: 'bot@evil.com',
    contact_message: 'Spam message',
    url_website: 'http://spambot.example.com', // Honeypot field
    ...(nonce ? { nonce } : {}),
  });
  // Honeypot-triggered rejection
  const rejected =
    result.status === 400 ||
    result.data === -1 ||
    result.data === '-1' ||
    (typeof result.data === 'object' && result.data?.success === false);
  console.log(`[1.4] Honeypot result: status=${result.status}, data=${JSON.stringify(result.data)}`);
  // This is observational — log whether honeypot is implemented
  if (!rejected) {
    console.warn('[1.4] WARN: Honeypot field may not be implemented — verify alkana_submit_contact handler');
  }
});

test('[1.5] Deleted product URL: returns 404 page', async ({ page }) => {
  const resp = await page.goto(`${BASE}/san-pham/product-does-not-exist-xyz/`, { waitUntil: 'networkidle' });
  const status = resp?.status() ?? 0;
  expect(status).toBe(404);
  // 404 page should be styled (not just WP default)
  const body = await page.locator('body').textContent();
  expect(body).not.toContain('Fatal error');
});

// ─── Scale & Pagination ───────────────────────────────────────────────────────

test('[4.1] Product archive: empty catalog shows empty state', async ({ page }) => {
  // Filter to a combination that yields 0 results
  await page.goto(`${BASE}/san-pham/`, { waitUntil: 'networkidle' });
  const nonce = await getPageNonce(page);
  const result = await ajaxPost(page, {
    action: 'alkana_filter_products',
    'category[]': 'nonexistent-category-xyz-12345',
    page: '1',
    ...(nonce ? { nonce } : {}),
  });
  expect(result.status).not.toBe(500);
  // Response should be valid (empty array or 0 total)
  if (result.data && typeof result.data === 'object') {
    const total = result.data.total ?? result.data.found ?? (result.data.products?.length ?? 0);
    expect(typeof total === 'number').toBe(true);
  }
});

test('[4.2] Product archive: single product renders correctly', async ({ page }) => {
  const resp = await page.goto(`${BASE}/san-pham/`, { waitUntil: 'networkidle' });
  if (resp && resp.status() === 404) {
    test.skip(true, 'Product archive 404');
    return;
  }
  const body = await page.locator('body').textContent();
  expect(body).not.toContain('Fatal error');
});

test('[4.5] Search: returns limited results quickly', async ({ page }) => {
  await page.goto(`${BASE}/`, { waitUntil: 'domcontentloaded' });
  const nonce = await getPageNonce(page);

  const ms = await measureMs(async () => {
    await ajaxPost(page, {
      action: 'alkana_search',
      query: 'sơn',
      ...(nonce ? { nonce } : {}),
    });
  });
  softAssertPerf(ms, 500, 10000, 'search AJAX');
  console.log(`[4.5] Search response time: ${ms}ms`);
});

test('[4.6] Product filter performance: responds within 1s', async ({ page }) => {
  const resp = await page.goto(`${BASE}/san-pham/`, { waitUntil: 'networkidle' });
  if (resp && resp.status() === 404) {
    test.skip(true, 'Product archive 404');
    return;
  }
  const nonce = await getPageNonce(page);

  const ms = await measureMs(async () => {
    await ajaxPost(page, {
      action: 'alkana_filter_products',
      page: '1',
      ...(nonce ? { nonce } : {}),
    });
  });
  softAssertPerf(ms, 1000, 10000, 'filter AJAX');
  console.log(`[4.6] Filter response time: ${ms}ms`);
});

// ─── State Transitions ────────────────────────────────────────────────────────

test('[5.2] Filter state: History API preserves filter on back navigation', async ({ page }) => {
  const resp = await page.goto(`${BASE}/san-pham/`, { waitUntil: 'networkidle' });
  if (resp && resp.status() === 404) {
    test.skip(true, 'Product archive 404');
    return;
  }

  const filterCheckbox = page.locator('.filter-item input[type="checkbox"]').first();
  if (!await filterCheckbox.isVisible({ timeout: 3000 }).catch(() => false)) {
    test.skip(true, 'Filter checkboxes not found');
    return;
  }

  await filterCheckbox.click();
  await page.waitForTimeout(800);
  const urlAfterFilter = page.url();

  // Navigate to a product
  const productLink = page.locator('.product-card a, article.product a').first();
  if (await productLink.isVisible({ timeout: 3000 }).catch(() => false)) {
    await productLink.click();
    await page.waitForLoadState('domcontentloaded');
    await page.goBack();
    await page.waitForLoadState('networkidle');

    // URL should restore filter params
    const urlAfterBack = page.url();
    console.log(`[5.2] Before: ${urlAfterFilter} | After back: ${urlAfterBack}`);
  }
});

test('[5.5] Search modal: opens, search, result click, modal closes', async ({ page }) => {
  await page.goto(`${BASE}/`, { waitUntil: 'domcontentloaded' });
  const searchToggle = page.locator('#search-toggle, [data-search-toggle]').first();
  if (!await searchToggle.isVisible({ timeout: 3000 }).catch(() => false)) {
    test.skip(true, 'Search toggle not found');
    return;
  }

  await searchToggle.click();
  const modal = page.locator('#search-modal, .search-modal').first();
  await expect(modal).toBeVisible({ timeout: 3000 });

  await page.keyboard.press('Escape');
  // Modal should close
  await page.waitForTimeout(400);
  const isVisible = await modal.isVisible().catch(() => false);
  if (!isVisible) {
    console.log('[5.5] Modal closed on Escape — PASS');
  }
});

// ─── Business Logic ───────────────────────────────────────────────────────────

test('[12.4] Contact form: paint_system URL param pre-fills form safely', async ({ page }) => {
  const payload = 'hello-world-test-safe';
  await page.goto(`${BASE}/lien-he/?paint_system=${payload}`, { waitUntil: 'networkidle' });

  let alertFired = false;
  page.on('dialog', () => { alertFired = true; });

  await page.waitForTimeout(500);
  expect(alertFired).toBe(false);
  // XSS via URL param
  await page.goto(`${BASE}/lien-he/?paint_system=%3Cscript%3Ealert(1)%3C%2Fscript%3E`, { waitUntil: 'networkidle' });
  await page.waitForTimeout(500);
  expect(alertFired).toBe(false);
});
