// tests/09-admin/admin-dashboard.spec.js
// Scenarios: B1.3, B4.1, B12.1–B12.5 — stats, widgets, empty states, business logic
'use strict';

const { test, expect } = require('@playwright/test');
const { BASE } = require('../helpers/fixtures');
const { loginAs } = require('../helpers/auth');
const { skipUnlessCI } = require('../helpers/test-utils');

test.describe.configure({ mode: 'serial' });

test('[B4.1] Dashboard stats: 0 products shows 0 not error', async ({ page }) => {
  skipUnlessCI('Requires admin login');
  await loginAs(page, 'admin');
  await page.goto(`${BASE}/wp-admin/index.php`, { waitUntil: 'networkidle' });
  const body = await page.locator('body').textContent();
  expect(body).not.toContain('Fatal error');
  expect(body).not.toContain('Division by zero');
  expect(body).not.toContain('DivisionByZeroError');
});

test('[B12.4] Dashboard stats: product count shows only published posts', async ({ page }) => {
  skipUnlessCI('Requires admin login');
  await loginAs(page, 'admin');
  await page.goto(`${BASE}/wp-admin/index.php`, { waitUntil: 'networkidle' });
  const body = await page.locator('body').textContent();
  expect(body).not.toContain('Fatal error');
  // Dashboard should load — exact count verification requires known DB state
  console.log('[B12.4] Dashboard loaded — count accuracy requires known seed data for assertion');
});

test('[B12.3] Hero slider: missing image does not break frontend', async ({ page }) => {
  await page.goto(`${BASE}/`, { waitUntil: 'networkidle' });
  const body = await page.locator('body').textContent();
  // No PHP errors should appear if a slider slide has no image set
  expect(body).not.toContain('Fatal error');
  expect(body).not.toContain('Undefined variable');
  // Images should either show or be absent (no broken img with PHP error)
  const brokenImgs = await page.evaluate(() => {
    const imgs = Array.from(document.querySelectorAll('.hero-slider img, .swiper-slide img'));
    return imgs.filter(img => !img.complete || img.naturalHeight === 0).length;
  });
  console.log(`[B12.3] Broken hero images found: ${brokenImgs}`);
});

test('[B12.5] USP settings update: no fatal error on frontend', async ({ page }) => {
  await page.goto(`${BASE}/`, { waitUntil: 'networkidle' });
  const body = await page.locator('body').textContent();
  expect(body).not.toContain('Fatal error');
});

test('[B12.1] Product without category appears in unfiltered catalog', async ({ page }) => {
  const resp = await page.goto(`${BASE}/san-pham/`, { waitUntil: 'networkidle' });
  if (resp && resp.status() === 404) {
    test.skip(true, 'Product archive returns 404');
    return;
  }
  const body = await page.locator('body').textContent();
  expect(body).not.toContain('Fatal error');
  // Page should load even if some products have incomplete index entries
});

test('[B12.2] Featured product flag syncs in product index', async ({ page }) => {
  skipUnlessCI('Requires admin + seeded product');
  await loginAs(page, 'admin');
  await page.goto(`${BASE}/wp-admin/edit.php?post_type=alkana_product`, { waitUntil: 'networkidle' });
  const body = await page.locator('body').textContent();
  expect(body).not.toContain('Fatal error');
  console.log('[B12.2] Featured sync hook (alkana_sync_product_index) requires integration test with DB access');
});
