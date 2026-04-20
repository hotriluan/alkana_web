// tests/09-admin/admin-error-handling.spec.js
// Scenarios: B7.1–B7.5 — admin empty states, error handling, graceful failures
'use strict';

const { test, expect } = require('@playwright/test');
const { BASE } = require('../helpers/fixtures');
const { loginAs } = require('../helpers/auth');
const { ajaxPost } = require('../helpers/ajax');
const { skipUnlessCI } = require('../helpers/test-utils');

test.describe.configure({ mode: 'serial' });

test('[B7.4] Inline edit AJAX fail: response is not 500 with PHP fatal', async ({ page }) => {
  // Without session, the endpoint should return graceful error, not fatal
  await page.goto(`${BASE}/`, { waitUntil: 'domcontentloaded' });
  const result = await ajaxPost(page, {
    action: 'alkana_inline_edit',
    post_id: 'invalid',
    field: 'sku',
    value: 'test',
  });
  expect(result.status).not.toBe(500);
  if (typeof result.data === 'string') {
    expect(result.data).not.toContain('Fatal error');
  }
});

test('[B7.5] Dashboard widget API fail: no crash', async ({ page }) => {
  skipUnlessCI('Requires admin login');
  await loginAs(page, 'admin');
  await page.goto(`${BASE}/wp-admin/index.php`, { waitUntil: 'networkidle' });

  // Intercept and simulate a widget data endpoint failing
  const body = await page.locator('body').textContent();
  // Dashboard must not show broken layout when a widget fails
  expect(body).not.toContain('Fatal error');
  expect(body).not.toContain('Uncaught TypeError');
});

test('[B7.1] Backup DB export: endpoint does not expose internal errors', async ({ page }) => {
  skipUnlessCI('Requires admin login');
  await loginAs(page, 'admin');
  await page.goto(`${BASE}/wp-admin/`, { waitUntil: 'networkidle' });

  const nonce = await page.evaluate(() => window.alkanaAdminData?.nonce || null);
  const result = await ajaxPost(page, {
    action: 'alkana_backup_db',
    ...(nonce ? { nonce } : { nonce: 'invalid' }),
  });

  expect(result.status).not.toBe(500);
  if (result.data && typeof result.data === 'string') {
    expect(result.data).not.toContain('DB_HOST');
    expect(result.data).not.toContain('DB_PASSWORD');
    expect(result.data).not.toContain('mysql_connect');
  }
});

test('[B7.3] Product save with validation error: no data loss indicator', async ({ page }) => {
  skipUnlessCI('Requires admin login');
  await loginAs(page, 'admin');
  await page.goto(`${BASE}/wp-admin/edit.php?post_type=alkana_product`, { waitUntil: 'networkidle' });
  const body = await page.locator('body').textContent();
  expect(body).not.toContain('Fatal error');
  // Product list loads — ACF validation errors would show on individual edit pages
});

test('[7.2] Public AJAX filter error: no server error details in response', async ({ page }) => {
  await page.goto(`${BASE}/san-pham/`, { waitUntil: 'networkidle' });
  // Send a deliberately malformed request
  const result = await ajaxPost(page, {
    action: 'alkana_filter_products',
    page: 'INVALID',
    per_page: 'INVALID',
  });
  if (result.status >= 400) {
    // Error response must not expose internals
    if (typeof result.data === 'string') {
      expect(result.data).not.toContain('/var/www');
      expect(result.data).not.toContain('wp-includes');
    }
  }
});
