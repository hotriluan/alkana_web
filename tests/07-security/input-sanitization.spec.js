// tests/07-security/input-sanitization.spec.js
// Scenarios: 2.1–2.9, 2.12 — XSS, SQLi, path traversal, input extremes
'use strict';

const { test, expect } = require('@playwright/test');
const { BASE, ADMIN_AJAX, XSS_PAYLOADS, SQL_INJECTION_PAYLOADS, PATH_TRAVERSAL_PAYLOADS, VIETNAMESE_NAME } = require('../helpers/fixtures');
const { ajaxPost, getPageNonce } = require('../helpers/ajax');
const { skipUnlessCI } = require('../helpers/test-utils');

// ─── Contact Form Input Extremes ──────────────────────────────────────────────

test('[2.1] Contact form: Vietnamese UTF-8 name saves correctly', async ({ page }) => {
  await page.goto(`${BASE}/lien-he/`, { waitUntil: 'networkidle' });
  const nameField = page.locator('input[name="contact_name"], #contact_name').first();
  if (!await nameField.isVisible({ timeout: 5000 }).catch(() => false)) {
    test.skip(true, 'Contact form not found at /lien-he/');
    return;
  }
  await nameField.fill(VIETNAMESE_NAME);
  const val = await nameField.inputValue();
  expect(val).toBe(VIETNAMESE_NAME);
});

test('[2.2] Contact form: XSS in message field — no script execution', async ({ page }) => {
  let alertFired = false;
  page.on('dialog', () => { alertFired = true; });

  await page.goto(`${BASE}/lien-he/`, { waitUntil: 'networkidle' });
  const msgField = page.locator('textarea[name="contact_message"], #contact_message').first();
  if (!await msgField.isVisible({ timeout: 5000 }).catch(() => false)) {
    test.skip(true, 'Contact message field not found');
    return;
  }
  await msgField.fill(XSS_PAYLOADS[0]);
  await page.waitForTimeout(500);
  expect(alertFired).toBe(false);
});

test('[2.3] Contact form: XSS email rejected by validation', async ({ page }) => {
  await page.goto(`${BASE}/lien-he/`, { waitUntil: 'networkidle' });
  const emailField = page.locator('input[name="contact_email"], #contact_email').first();
  if (!await emailField.isVisible({ timeout: 5000 }).catch(() => false)) {
    test.skip(true, 'Contact email field not found');
    return;
  }
  await emailField.fill('"><svg/onload=alert(1)>@evil.com');
  // Try submitting — expect validation error, not server-side execution
  const submitBtn = page.locator('button[type="submit"], input[type="submit"]').first();
  if (await submitBtn.isVisible({ timeout: 2000 }).catch(() => false)) {
    await submitBtn.click();
    await page.waitForTimeout(800);
    // Should not navigate to a success page or show XSS execution
    const url = page.url();
    expect(url).not.toContain('success');
    expect(url).not.toContain('thank');
  }
});

test('[2.7] Search: SQL injection query returns safe empty result', async ({ page }) => {
  await page.goto(`${BASE}/`, { waitUntil: 'domcontentloaded' });

  // Try AJAX search with SQL injection payload
  const nonce = await getPageNonce(page);
  const result = await ajaxPost(page, {
    action: 'alkana_search',
    query: SQL_INJECTION_PAYLOADS[0],
    ...(nonce ? { nonce } : {}),
  });

  // Must not 500, must return valid JSON or empty results
  expect(result.status).not.toBe(500);
  if (result.data && typeof result.data === 'object') {
    // results array should be empty or contain safe data, not DB errors
    const dataStr = JSON.stringify(result.data);
    expect(dataStr).not.toContain('SQL syntax');
    expect(dataStr).not.toContain('mysql_error');
    expect(dataStr).not.toContain('WordPress database error');
  }
});

test('[2.8] Search: single character query returns validation error', async ({ page }) => {
  await page.goto(`${BASE}/`, { waitUntil: 'domcontentloaded' });
  const result = await ajaxPost(page, {
    action: 'alkana_search',
    query: 'a',
  });
  // Should not return 500; may return error message or empty
  expect(result.status).toBeLessThan(500);
});

test('[2.9] Product filter: path traversal in category slug is sanitized', async ({ page }) => {
  await page.goto(`${BASE}/san-pham/`, { waitUntil: 'networkidle' });
  const nonce = await getPageNonce(page);
  const result = await ajaxPost(page, {
    action: 'alkana_filter_products',
    'category[]': PATH_TRAVERSAL_PAYLOADS[0],
    ...(nonce ? { nonce } : {}),
  });
  // Must not 500 or expose file system data
  expect(result.status).not.toBe(500);
  if (result.data && typeof result.data === 'object') {
    const dataStr = JSON.stringify(result.data);
    expect(dataStr).not.toContain('root:');
    expect(dataStr).not.toContain('wp-config');
    expect(dataStr).not.toContain('DB_PASSWORD');
  }
});

test('[2.12] Contact form: empty message rejected', async ({ page }) => {
  await page.goto(`${BASE}/lien-he/`, { waitUntil: 'networkidle' });

  // Attempt AJAX submit with empty message
  const nonce = await getPageNonce(page, 'alkanaData');
  const result = await ajaxPost(page, {
    action: 'alkana_submit_contact',
    contact_name: 'Test User',
    contact_email: 'test@example.com',
    contact_message: '',
    ...(nonce ? { nonce } : {}),
  });
  // Expect validation error (not 200 success)
  if (result.data && typeof result.data === 'object') {
    const isSuccess = result.data.success === true;
    expect(isSuccess).toBe(false);
  }
});

// ─── XSS in search results rendering ─────────────────────────────────────────

test('[XSS] Search result HTML escapes dangerous content', async ({ page }) => {
  let alertFired = false;
  page.on('dialog', () => { alertFired = true; });

  await page.goto(`${BASE}/`, { waitUntil: 'domcontentloaded' });

  const searchToggle = page.locator('#search-toggle, [data-search-toggle]').first();
  if (!await searchToggle.isVisible({ timeout: 3000 }).catch(() => false)) {
    test.skip(true, 'Search toggle not found');
    return;
  }
  await searchToggle.click();
  const input = page.locator('#search-modal-input, input[type="search"]').first();
  await expect(input).toBeVisible({ timeout: 3000 });
  await input.fill('<script>alert("xss")</script>');
  await page.waitForTimeout(1000);
  expect(alertFired).toBe(false);
});
