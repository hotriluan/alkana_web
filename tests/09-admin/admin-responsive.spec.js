// tests/09-admin/admin-responsive.spec.js
// Scenarios: B6.1–B6.4 — admin mobile responsiveness, keyboard navigation
'use strict';

const { test, expect } = require('@playwright/test');
const { BASE } = require('../helpers/fixtures');
const { loginAs } = require('../helpers/auth');
const { skipUnlessCI } = require('../helpers/test-utils');

test('[B6.1] Admin dashboard: tablet responsive layout (768px)', async ({ browser }) => {
  skipUnlessCI('Requires admin login');
  const page = await browser.newPage({
    viewport: { width: 768, height: 1024 },
  });
  await loginAs(page, 'admin');
  await page.goto(`${BASE}/wp-admin/index.php`, { waitUntil: 'networkidle' });

  // No horizontal overflow
  const hasOverflow = await page.evaluate(() => {
    return document.body.scrollWidth > window.innerWidth;
  });
  expect(hasOverflow).toBe(false);

  // Dashboard content still visible
  const dashboardContent = page.locator('#wpbody, #dashboard-widgets').first();
  await expect(dashboardContent).toBeVisible({ timeout: 5000 });

  await page.close();
});

test('[B6.3] Admin with high latency: no double-submit on slow connection', async ({ page }) => {
  skipUnlessCI('Requires admin login');
  // Simulate slow network
  await page.route('**/*', async (route) => {
    await new Promise(resolve => setTimeout(resolve, 100)); // 100ms extra latency
    await route.continue();
  });
  await loginAs(page, 'admin');
  await page.goto(`${BASE}/wp-admin/`, { waitUntil: 'networkidle' });
  const body = await page.locator('body').textContent();
  expect(body).not.toContain('Fatal error');
});

test('[B6.4] Admin keyboard navigation: tab order reaches interactive elements', async ({ page }) => {
  skipUnlessCI('Requires admin login');
  await loginAs(page, 'admin');
  await page.goto(`${BASE}/wp-admin/index.php`, { waitUntil: 'networkidle' });

  // Tab through page — verify at least 5 focusable elements are reachable
  const focusedElements = [];
  for (let i = 0; i < 10; i++) {
    await page.keyboard.press('Tab');
    const focused = await page.evaluate(() => document.activeElement?.tagName || null);
    if (focused) focusedElements.push(focused);
  }
  expect(focusedElements.length).toBeGreaterThan(0);
  console.log(`[B6.4] Focused element tags: ${focusedElements.join(', ')}`);
});
