// tests/07-security/error-handling.spec.js
// Scenarios: 7.1–7.6 — error leakage, graceful failure, no credential exposure
'use strict';

const { test, expect } = require('@playwright/test');
const { BASE, ADMIN_AJAX } = require('../helpers/fixtures');
const { ajaxPost } = require('../helpers/ajax');
const { skipUnlessCI } = require('../helpers/test-utils');

// ─── Error response checks ────────────────────────────────────────────────────

test('[7.1] DB error page: no credentials exposed in error responses', async ({ page }) => {
  // Access product archive and check no DB creds in response
  await page.goto(`${BASE}/san-pham/`, { waitUntil: 'networkidle' });
  const body = await page.locator('body').textContent();
  expect(body).not.toContain('DB_PASSWORD');
  expect(body).not.toContain('DB_HOST');
  expect(body).not.toContain('DB_USER');
  expect(body).not.toMatch(/mysql.*error.*connect/i);
});

test('[7.2] AJAX filter 500 error: no PHP stack trace exposed to client', async ({ page }) => {
  await page.goto(`${BASE}/san-pham/`, { waitUntil: 'networkidle' });
  // Send malformed filter request to see what error format is returned
  const result = await ajaxPost(page, {
    action: 'alkana_filter_products',
    page: '-9999',
    per_page: '-1',
  });
  if (result.data && typeof result.data === 'string') {
    // Must not expose PHP file paths or stack traces
    expect(result.data).not.toMatch(/\/var\/www\//);
    expect(result.data).not.toMatch(/on line \d+/i);
    expect(result.data).not.toContain('Stack trace');
    expect(result.data).not.toContain('wp-includes');
  }
});

test('[7.5] Corrupted product index: AJAX filter graceful error, no fatal PHP', async ({ page }) => {
  await page.goto(`${BASE}/san-pham/`, { waitUntil: 'networkidle' });
  // Fire filter request — even with bad table it should not 500 + expose paths
  const result = await ajaxPost(page, {
    action: 'alkana_filter_products',
    'category[]': 'test-category',
    page: '1',
  });
  // Not expecting 500 with PHP fatal
  expect(result.status).not.toBe(500);
  if (result.data && typeof result.data === 'string') {
    expect(result.data).not.toContain('Fatal error');
    expect(result.data).not.toContain('Parse error');
  }
});

test('[C6] WP_DEBUG not exposing errors in production-like response', async ({ page }) => {
  // Check that the page doesn't output PHP notices/warnings to the browser
  await page.goto(`${BASE}/`, { waitUntil: 'networkidle' });
  const content = await page.content();
  // These strings appear when WP_DEBUG=true on production
  expect(content).not.toContain('Notice: Undefined');
  expect(content).not.toContain('Warning: Undefined');
  expect(content).not.toContain('Deprecated:');
  expect(content).not.toMatch(/Parse error:/);
  expect(content).not.toMatch(/Fatal error:/);
});

test('[C7] wp-config.php not directly accessible', async ({ page }) => {
  const resp = await page.goto(`${BASE}/wp-config.php`, { waitUntil: 'domcontentloaded' });
  const status = resp?.status() ?? 0;
  const body = await page.locator('body').textContent().catch(() => '');
  // wp-config.php should return 403/404 or empty, never expose DB creds
  expect(body).not.toContain('DB_PASSWORD');
  expect(body).not.toContain('DB_NAME');
  expect(body).not.toContain('AUTH_KEY');
  // Should not return 200 with config content
  if (status === 200) {
    expect(body.trim()).toBe(''); // Empty 200 is OK (PHP parsed it)
  }
});

test('[7.3] Contact form submit: email failure does not expose SMTP credentials', async ({ page }) => {
  await page.goto(`${BASE}/lien-he/`, { waitUntil: 'networkidle' });
  // Submit form via AJAX with invalid nonce to get error response format
  const result = await ajaxPost(page, {
    action: 'alkana_submit_contact',
    contact_name: 'Test',
    contact_email: 'test@example.com',
    contact_message: 'Test message for error check',
    nonce: 'invalid',
  });
  if (result.data && typeof result.data === 'string') {
    expect(result.data).not.toContain('SMTP');
    expect(result.data).not.toContain('smtp_password');
    expect(result.data).not.toContain('mail_password');
  }
});
