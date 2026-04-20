// tests/09-admin/inline-edit.spec.js
// Scenarios: B2.1–B2.4, B8.1–B8.2, B8.6 — inline edit security + functionality
'use strict';

const { test, expect } = require('@playwright/test');
const { BASE } = require('../helpers/fixtures');
const { loginAs } = require('../helpers/auth');
const { ajaxPost } = require('../helpers/ajax');
const { skipUnlessCI } = require('../helpers/test-utils');

test.describe.configure({ mode: 'serial' });

test('[B2.1] Inline edit: XSS in SKU field is sanitized', async ({ page }) => {
  skipUnlessCI('Requires admin login + seeded product');
  await loginAs(page, 'admin');
  await page.goto(`${BASE}/wp-admin/edit.php?post_type=alkana_product`, { waitUntil: 'networkidle' });

  const nonce = await page.evaluate(() => window.alkanaAdminData?.nonce || null);
  // Get a real product ID from the page
  const firstEditLink = page.locator('.row-actions a[href*="post="]').first();
  const href = await firstEditLink.getAttribute('href').catch(() => null);
  const postIdMatch = href?.match(/post=(\d+)/);
  if (!postIdMatch) {
    test.skip(true, 'No products found for inline edit test');
    return;
  }
  const postId = postIdMatch[1];

  const result = await ajaxPost(page, {
    action: 'alkana_inline_edit',
    post_id: postId,
    field: 'sku',
    value: '<script>alert(1)</script>',
    ...(nonce ? { nonce } : {}),
  });

  if (result.data && typeof result.data === 'object') {
    // The saved/returned value must not contain script tags
    const dataStr = JSON.stringify(result.data);
    expect(dataStr).not.toContain('<script>');
    expect(dataStr).not.toContain('</script>');
  }
});

test('[B2.4] Inline edit: arbitrary field outside allowlist is rejected', async ({ page }) => {
  skipUnlessCI('Requires admin login + seeded product');
  await loginAs(page, 'admin');
  await page.goto(`${BASE}/wp-admin/`, { waitUntil: 'networkidle' });

  const nonce = await page.evaluate(() => window.alkanaAdminData?.nonce || null);
  const result = await ajaxPost(page, {
    action: 'alkana_inline_edit',
    post_id: '1',
    field: 'password', // Not in allowlist: sku/coverage/mix_ratio/gloss_level
    value: 'hacked',
    ...(nonce ? { nonce } : {}),
  });

  // Must reject — field not in allowlist
  const rejected =
    result.data === -1 ||
    result.data === '-1' ||
    result.status === 403 ||
    (typeof result.data === 'object' && result.data?.success === false);
  expect(rejected).toBe(true);
});

test('[B2.2] Inline edit: non-numeric value for coverage field', async ({ page }) => {
  skipUnlessCI('Requires admin login + seeded product');
  await loginAs(page, 'admin');
  await page.goto(`${BASE}/wp-admin/`, { waitUntil: 'networkidle' });

  const nonce = await page.evaluate(() => window.alkanaAdminData?.nonce || null);
  const result = await ajaxPost(page, {
    action: 'alkana_inline_edit',
    post_id: '1',
    field: 'coverage',
    value: 'abc_not_a_number',
    ...(nonce ? { nonce } : {}),
  });

  // Should return validation error or sanitize to 0
  expect(result.status).not.toBe(500);
  if (result.data && typeof result.data === 'object' && result.data?.value) {
    const numVal = parseFloat(result.data.value);
    // Sanitized value should be numeric (0 or original if validation fails)
    expect(isNaN(numVal)).toBe(false);
  }
});

test('[B2.3] Inline edit: non-existent post_id returns error', async ({ page }) => {
  skipUnlessCI('Requires admin login');
  await loginAs(page, 'admin');
  await page.goto(`${BASE}/wp-admin/`, { waitUntil: 'networkidle' });

  const nonce = await page.evaluate(() => window.alkanaAdminData?.nonce || null);
  const result = await ajaxPost(page, {
    action: 'alkana_inline_edit',
    post_id: '99999999',
    field: 'sku',
    value: 'TEST',
    ...(nonce ? { nonce } : {}),
  });

  // Should return error — post doesn't exist
  const isError =
    result.data === -1 ||
    result.data === '-1' ||
    result.status === 403 ||
    result.status === 404 ||
    (typeof result.data === 'object' && result.data?.success === false);
  expect(isError).toBe(true);
});

test('[B8.2] Expired admin session: inline edit AJAX returns graceful error', async ({ page }) => {
  // Simulate expired session by calling endpoint without logging in first
  await page.goto(`${BASE}/`, { waitUntil: 'domcontentloaded' });
  const result = await ajaxPost(page, {
    action: 'alkana_inline_edit',
    post_id: '1',
    field: 'sku',
    value: 'EXPIRED_SESSION_TEST',
    nonce: 'expired_nonce_12345',
  });
  // Must reject gracefully — not 500
  expect(result.status).not.toBe(500);
  const rejected =
    result.data === -1 || result.data === '-1' ||
    result.data === 0  || result.data === '0' || // WP returns '0' for unregistered nopriv actions
    result.status === 400 || result.status === 403 ||
    (typeof result.data === 'object' && result.data?.success === false);
  expect(rejected).toBe(true);
});
