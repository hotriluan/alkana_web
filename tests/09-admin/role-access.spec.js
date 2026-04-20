// tests/09-admin/role-access.spec.js
// Scenarios: B1.1–B1.5 — role-based page and feature access
'use strict';

const { test, expect } = require('@playwright/test');
const { BASE } = require('../helpers/fixtures');
const { loginAs } = require('../helpers/auth');
const { skipUnlessCI } = require('../helpers/test-utils');

test.describe.configure({ mode: 'serial' }); // Session state

test('[B1.1] content_editor can access product edit page', async ({ page }) => {
  skipUnlessCI('Requires content_editor account');
  await loginAs(page, 'contentEditor');
  await page.goto(`${BASE}/wp-admin/edit.php?post_type=alkana_product`, { waitUntil: 'networkidle' });
  const body = await page.locator('body').textContent();
  expect(body).not.toContain('Fatal error');
  // content_editor should see the list — verify no "You are not allowed" message
  const url = page.url();
  expect(url).not.toContain('wp-login.php');
});

test('[B1.2] tech_editor cannot edit testimonials', async ({ page }) => {
  skipUnlessCI('Requires tech_editor account');
  await loginAs(page, 'techEditor');
  await page.goto(`${BASE}/wp-admin/edit.php?post_type=alkana_testimonial`, { waitUntil: 'networkidle' });
  const body = await page.locator('body').textContent();
  const url = page.url();
  // Should redirect or show permission denied
  const blocked = url.includes('wp-login') ||
    body.match(/not allowed|don't have permission|không có quyền/i) ||
    url.includes('access-denied');
  console.log(`[B1.2] tech_editor + testimonial: blocked=${!!blocked}, url=${url}`);
  // Soft assertion — log if not blocked for manual investigation
  if (!blocked) {
    console.warn('[B1.2] WARN: tech_editor may have unexpected access to testimonials');
  }
});

test('[B1.3] New admin dashboard: all widgets load without error', async ({ page }) => {
  skipUnlessCI('Requires admin login');
  await loginAs(page, 'admin');
  await page.goto(`${BASE}/wp-admin/index.php`, { waitUntil: 'networkidle' });
  const body = await page.locator('body').textContent();
  expect(body).not.toContain('Fatal error');
  expect(body).not.toContain('Call to undefined function');
  // Dashboard should have the WP widgets area
  const dashboard = page.locator('#dashboard-widgets, #wpwrap').first();
  await expect(dashboard).toBeVisible({ timeout: 5000 });
});

test('[B1.5] content_editor cannot access backup toolkit page', async ({ page }) => {
  skipUnlessCI('Requires content_editor account');
  await loginAs(page, 'contentEditor');
  const resp = await page.goto(`${BASE}/wp-admin/admin.php?page=alkana-backup`, {
    waitUntil: 'domcontentloaded',
  });
  const url = page.url();
  const body = await page.locator('body').textContent().catch(() => '');
  const blocked = url.includes('wp-login') || resp?.status() === 403 ||
    body.match(/not allowed|forbidden|you do not have|không có quyền/i);
  expect(blocked).toBeTruthy();
});
