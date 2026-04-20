// tests/07-security/authorization.spec.js
// Scenarios: 8.1–8.6, B8.1–B8.6 — nonces, CSRF, role-based access, rate limits
'use strict';

const { test, expect } = require('@playwright/test');
const { BASE, ADMIN_AJAX } = require('../helpers/fixtures');
const { loginAs } = require('../helpers/auth');
const { ajaxPost } = require('../helpers/ajax');
const { skipUnlessCI } = require('../helpers/test-utils');

// ─── Public user / guest authorization ───────────────────────────────────────

test('[8.1] Guest: alkana_inline_edit AJAX returns 403', async ({ page }) => {
  await page.goto(`${BASE}/`, { waitUntil: 'domcontentloaded' });
  const result = await ajaxPost(page, {
    action: 'alkana_inline_edit',
    post_id: '1',
    field: 'sku',
    value: 'HACKED',
  });
  // Must reject — not logged in, so 403 or WP returns -1 / 0
  const rejected =
    result.status === 403 ||
    result.data === -1 ||
    result.data === '-1' ||
    result.data === 0 ||
    (typeof result.data === 'object' && result.data?.success === false);
  expect(rejected).toBe(true);
});

test('[8.2] Guest accessing /wp-admin/ redirects to login', async ({ page }) => {
  const resp = await page.goto(`${BASE}/wp-admin/`, { waitUntil: 'domcontentloaded' });
  // Should end up on wp-login.php or return 302/403
  const url = page.url();
  const isLoginPage = url.includes('wp-login.php') || url.includes('wp-admin/');
  expect(isLoginPage).toBe(true);
  // If landed on wp-admin/, should see login form or access denied
  if (url.includes('wp-admin/') && !url.includes('wp-login')) {
    const body = await page.locator('body').textContent();
    expect(body).toMatch(/log\s?in|sign\s?in|access denied|not authorized/i);
  }
});

test('[8.3] Expired nonce on contact form returns rejection', async ({ page }) => {
  await page.goto(`${BASE}/lien-he/`, { waitUntil: 'networkidle' });
  const result = await ajaxPost(page, {
    action: 'alkana_submit_contact',
    contact_name: 'Test',
    contact_email: 'test@example.com',
    contact_message: 'Hello',
    nonce: 'invalid_nonce_12345',
  });
  // Must reject — invalid nonce
  const rejected =
    result.status === 403 ||
    result.data === -1 ||
    result.data === '-1' ||
    (typeof result.data === 'object' && result.data?.success === false);
  expect(rejected).toBe(true);
});

test('[8.4] CSRF: external POST to alkana_submit_contact without nonce fails', async ({ page }) => {
  await page.goto(`${BASE}/`, { waitUntil: 'domcontentloaded' });
  // Submit without any nonce (simulates cross-site request)
  const result = await ajaxPost(page, {
    action: 'alkana_submit_contact',
    contact_name: 'CSRF Attacker',
    contact_email: 'attack@evil.com',
    contact_message: 'CSRF attack payload',
    // No nonce intentionally
  });
  const rejected =
    result.status === 403 ||
    result.data === -1 ||
    result.data === '-1' ||
    (typeof result.data === 'object' && result.data?.success === false);
  expect(rejected).toBe(true);
});

test('[8.6] CV files direct URL access — check if protected', async ({ page }) => {
  // This test documents and verifies the security posture of CV uploads
  // We check /wp-content/uploads/cv/ directory listing is blocked
  const resp = await page.goto(`${BASE}/wp-content/uploads/cv/`, { waitUntil: 'domcontentloaded' });
  const status = resp?.status() ?? 0;
  const body = await page.locator('body').textContent().catch(() => '');

  // Directory listing must be blocked (403, 404, or empty response)
  // A 200 with "Index of" is a FAIL
  if (status === 200) {
    expect(body).not.toMatch(/index of/i);
    expect(body).not.toContain('.pdf');
    expect(body).not.toContain('.doc');
  }
  // 403 or 404 is the desired outcome
  console.log(`[8.6] CV directory response: ${status} — ${status === 403 || status === 404 ? 'PASS' : 'CHECK MANUALLY'}`);
});

// ─── Admin role authorization ─────────────────────────────────────────────────

test('[B8.3] Non-admin direct URL access to alkana-backup page redirects', async ({ page }) => {
  skipUnlessCI('Requires content_editor account');
  await loginAs(page, 'contentEditor');
  const resp = await page.goto(`${BASE}/wp-admin/admin.php?page=alkana-backup`, { waitUntil: 'domcontentloaded' });
  const url = page.url();
  // Should redirect away or show access denied
  const blocked = url.includes('wp-login') || url.includes('access-denied') ||
    (resp?.status() === 403) ||
    await page.locator('body').textContent().then(t => /not allowed|forbidden|no permission/i.test(t)).catch(() => false);
  expect(blocked).toBe(true);
});

test('[B8.4] content_editor: alkana_backup AJAX endpoint returns 403', async ({ page }) => {
  skipUnlessCI('Requires content_editor login');
  await loginAs(page, 'contentEditor');
  await page.goto(`${BASE}/wp-admin/`, { waitUntil: 'domcontentloaded' });
  const result = await ajaxPost(page, {
    action: 'alkana_backup_db',
    nonce: 'fake',
  });
  const rejected =
    result.status === 403 ||
    result.data === -1 ||
    result.data === '-1' ||
    (typeof result.data === 'object' && result.data?.success === false);
  expect(rejected).toBe(true);
});

test('[B8.1] tech_editor: inline edit on non-product post type rejected', async ({ page }) => {
  skipUnlessCI('Requires tech_editor login + seeded page post_id');
  await loginAs(page, 'techEditor');
  await page.goto(`${BASE}/wp-admin/`, { waitUntil: 'domcontentloaded' });

  // Get a nonce from the admin page
  const nonce = await page.evaluate(() => window.alkanaAdminData?.nonce || null);
  const result = await ajaxPost(page, {
    action: 'alkana_inline_edit',
    post_id: '2', // Typically the Sample Page post (type=page, not product)
    field: 'sku',
    value: 'HACKED',
    ...(nonce ? { nonce } : {}),
  });
  const rejected =
    result.status === 403 ||
    result.data === -1 ||
    result.data === '-1' ||
    (typeof result.data === 'object' && result.data?.success === false);
  expect(rejected).toBe(true);
});
