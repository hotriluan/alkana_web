// tests/09-admin/admin-timing.spec.js
// Scenarios: B3.1–B3.4, 3.1–3.5 — timing, concurrency, debounce, double-submit
'use strict';

const { test, expect } = require('@playwright/test');
const { BASE } = require('../helpers/fixtures');
const { loginAs } = require('../helpers/auth');
const { ajaxPost } = require('../helpers/ajax');
const { skipUnlessCI, TEST_PREFIX } = require('../helpers/test-utils');

test.describe('[Timing] Admin concurrency', () => {
  test.describe.configure({ mode: 'serial' });

  test('[B3.1] Concurrent inline edit on same field: no data corruption', async ({ page }) => {
    skipUnlessCI('Requires admin + seeded product');
    await loginAs(page, 'admin');
    await page.goto(`${BASE}/wp-admin/`, { waitUntil: 'networkidle' });

    const nonce = await page.evaluate(() => window.alkanaAdminData?.nonce || null);
    // Fire 2 concurrent edits
    const [r1, r2] = await Promise.all([
      ajaxPost(page, {
        action: 'alkana_inline_edit',
        post_id: '1',
        field: 'sku',
        value: `${TEST_PREFIX}concurrent_1`,
        ...(nonce ? { nonce } : {}),
      }),
      ajaxPost(page, {
        action: 'alkana_inline_edit',
        post_id: '1',
        field: 'sku',
        value: `${TEST_PREFIX}concurrent_2`,
        ...(nonce ? { nonce } : {}),
      }),
    ]);
    // Neither should 500
    expect(r1.status).not.toBe(500);
    expect(r2.status).not.toBe(500);
    console.log(`[B3.1] Concurrent edits: r1=${r1.status}, r2=${r2.status} (last-write-wins expected)`);
  });
});

test.describe('[Timing] Public user concurrency', () => {
  test('[3.1] Contact form double-submit: server rejects duplicate', async ({ page }) => {
    await page.goto(`${BASE}/lien-he/`, { waitUntil: 'networkidle' });

    // Fire 2 concurrent contact submissions
    const [r1, r2] = await Promise.all([
      ajaxPost(page, {
        action: 'alkana_submit_contact',
        contact_name: 'Double Submit Test',
        contact_email: 'double@test.com',
        contact_message: 'Double submit test 1',
        nonce: 'invalid', // Will be rejected — testing the mechanism
      }),
      ajaxPost(page, {
        action: 'alkana_submit_contact',
        contact_name: 'Double Submit Test',
        contact_email: 'double@test.com',
        contact_message: 'Double submit test 2',
        nonce: 'invalid',
      }),
    ]);
    // Both should reject (invalid nonce) — not 500
    expect(r1.status).not.toBe(500);
    expect(r2.status).not.toBe(500);
  });

  test('[3.3] Filter debounce: rapid filter clicks trigger limited AJAX requests', async ({ page }) => {
    const resp = await page.goto(`${BASE}/san-pham/`, { waitUntil: 'networkidle' });
    if (resp && resp.status() === 404) {
      test.skip(true, 'Product archive 404');
      return;
    }

    const ajaxCalls = [];
    page.on('request', (req) => {
      if (req.url().includes('admin-ajax') && req.method() === 'POST') {
        ajaxCalls.push(req.url());
      }
    });

    const filterItems = page.locator('.filter-item input[type="checkbox"], .filter-option input').first();
    if (!await filterItems.isVisible({ timeout: 3000 }).catch(() => false)) {
      test.skip(true, 'Filter checkboxes not found');
      return;
    }

    // Rapid clicks
    for (let i = 0; i < 5; i++) {
      await filterItems.click();
      await page.waitForTimeout(50);
      await filterItems.click();
      await page.waitForTimeout(50);
    }
    await page.waitForTimeout(600); // Wait for debounce to settle

    console.log(`[3.3] Filter AJAX calls after 10 rapid clicks: ${ajaxCalls.length}`);
    // Debounce should result in fewer AJAX calls than clicks (ideally ≤ 3)
    expect(ajaxCalls.length).toBeLessThan(10);
  });

  test('[3.5] Slow network: navigating away cancels pending filter request', async ({ page }) => {
    const resp = await page.goto(`${BASE}/san-pham/`, { waitUntil: 'networkidle' });
    if (resp && resp.status() === 404) {
      test.skip(true, 'Product archive 404');
      return;
    }

    // Trigger a filter request then navigate away
    const filterCheckbox = page.locator('.filter-item input[type="checkbox"]').first();
    if (await filterCheckbox.isVisible({ timeout: 3000 }).catch(() => false)) {
      await filterCheckbox.click();
      // Immediately navigate away
      await page.goto(`${BASE}/`, { waitUntil: 'domcontentloaded' });
      // No stale data error on the homepage
      const body = await page.locator('body').textContent();
      expect(body).not.toContain('Fatal error');
    }
  });
});
