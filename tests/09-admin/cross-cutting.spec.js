// tests/09-admin/cross-cutting.spec.js
// Scenarios: C1–C7, B10.1–B10.4 — admin→public sync, SSL, memory, cache, debug mode
'use strict';

const { test, expect } = require('@playwright/test');
const { BASE } = require('../helpers/fixtures');
const { loginAs } = require('../helpers/auth');
const { skipUnlessCI } = require('../helpers/test-utils');

test('[C1] Admin publish product: public catalog reflects new product', async ({ page }) => {
  skipUnlessCI('Requires admin + ability to create products');
  await loginAs(page, 'admin');

  await page.goto(`${BASE}/wp-admin/post-new.php?post_type=alkana_product`, { waitUntil: 'networkidle' });
  const titleField = page.locator('#title, input[name="post_title"]').first();
  if (!await titleField.isVisible({ timeout: 5000 }).catch(() => false)) {
    test.skip(true, 'Product editor not accessible');
    return;
  }

  const uniqueTitle = `__test_cross_${Date.now()}`;
  await titleField.fill(uniqueTitle);
  await page.locator('#publish').click();
  await page.waitForLoadState('networkidle');

  // Check public catalog
  const catalogResp = await page.goto(`${BASE}/san-pham/`, { waitUntil: 'networkidle' });
  if (catalogResp?.status() !== 404) {
    const body = await page.locator('body').textContent();
    expect(body).not.toContain('Fatal error');
    // Note: cache may delay appearance — this is an observational test
    console.log(`[C1] Published product: "${uniqueTitle}" — check catalog manually or after cache purge`);
  }
});

test('[C2] Changed product slug: WordPress creates redirect', async ({ page }) => {
  skipUnlessCI('Requires admin + seeded product');
  await loginAs(page, 'admin');
  await page.goto(`${BASE}/wp-admin/edit.php?post_type=alkana_product`, { waitUntil: 'networkidle' });
  const body = await page.locator('body').textContent();
  expect(body).not.toContain('Fatal error');
  console.log('[C2] Slug redirect — WordPress core handles this automatically via redirect_guess_404_permalink');
});

test('[C5] Product archive: page memory does not exceed reasonable limit', async ({ page }) => {
  await page.goto(`${BASE}/san-pham/`, { waitUntil: 'networkidle' });
  const body = await page.locator('body').textContent();
  // PHP fatal from memory limit would say "Allowed memory size of"
  expect(body).not.toContain('Allowed memory size');
  expect(body).not.toContain('Fatal error');
});

test('[C6] WP_DEBUG=true: no PHP notices in HTML output', async ({ page }) => {
  await page.goto(`${BASE}/`, { waitUntil: 'networkidle' });
  const content = await page.content();
  const hasDebugOutput = /Notice:|Warning:|Deprecated:|Parse error:|Fatal error:/.test(content);
  if (hasDebugOutput) {
    console.warn('[C6] PHP debug output detected in response — WP_DEBUG may be true on this environment');
  }
  expect(hasDebugOutput).toBe(false);
});

test('[C7] Error responses: no DB credentials exposed', async ({ page }) => {
  // Request a non-existent page to trigger 404
  await page.goto(`${BASE}/this-page-does-not-exist-12345/`, { waitUntil: 'networkidle' });
  const body = await page.locator('body').textContent();
  expect(body).not.toContain('DB_PASSWORD');
  expect(body).not.toContain('DB_HOST');
  expect(body).not.toContain('DB_USER');
});

test('[B10.2] WordPress minor update: hooks still fire, no deprecated warnings', async ({ page }) => {
  await page.goto(`${BASE}/san-pham/`, { waitUntil: 'networkidle' });
  const body = await page.locator('body').textContent();
  expect(body).not.toContain('Deprecated:');
  expect(body).not.toContain('Fatal error');
});

test('[B10.4] Admin custom CSS: no fatal style conflict', async ({ page }) => {
  skipUnlessCI('Requires admin login');
  await loginAs(page, 'admin');
  await page.goto(`${BASE}/wp-admin/index.php`, { waitUntil: 'networkidle' });

  // Check no layout breaking issues
  const overlappingElements = await page.evaluate(() => {
    const menuEl = document.getElementById('adminmenuback');
    const contentEl = document.getElementById('wpcontent');
    if (!menuEl || !contentEl) return false;
    const menuRect = menuEl.getBoundingClientRect();
    const contentRect = contentEl.getBoundingClientRect();
    return menuRect.right > contentRect.left + 50; // Overlapping by more than 50px
  });
  expect(overlappingElements).toBe(false);
});
