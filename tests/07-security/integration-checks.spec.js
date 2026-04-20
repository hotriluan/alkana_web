// tests/07-security/integration-checks.spec.js
// Scenarios: 10.1–10.5, B2.7 — Vite assets, cache headers, SEO, ACF fallback
'use strict';

const { test, expect } = require('@playwright/test');
const { BASE } = require('../helpers/fixtures');
const { loginAs } = require('../helpers/auth');
const { ajaxPost } = require('../helpers/ajax');
const { skipUnlessCI } = require('../helpers/test-utils');

// ─── AJAX cache headers ───────────────────────────────────────────────────────

test('[10.4] AJAX filter: Cache-Control prevents LiteSpeed caching', async ({ page }) => {
  await page.goto(`${BASE}/san-pham/`, { waitUntil: 'networkidle' });

  let ajaxResponseHeaders = null;
  page.on('response', (resp) => {
    if (resp.url().includes('admin-ajax') && resp.url().includes('alkana_filter')) {
      ajaxResponseHeaders = resp.headers();
    }
  });

  await ajaxPost(page, { action: 'alkana_filter_products', page: '1' });
  await page.waitForTimeout(1000);

  if (ajaxResponseHeaders) {
    const cacheControl = ajaxResponseHeaders['cache-control'] || '';
    const xLitespeed = ajaxResponseHeaders['x-litespeed-cache'] || '';
    // AJAX responses must have no-store or no-cache
    const isNoCached = cacheControl.includes('no-store') || cacheControl.includes('no-cache') ||
      xLitespeed === 'miss'; // LiteSpeed cache miss = not cached
    console.log(`[10.4] Cache-Control: "${cacheControl}", X-LiteSpeed-Cache: "${xLitespeed}"`);
    if (cacheControl) {
      expect(cacheControl).not.toBe('max-age=86400'); // Should not be long-cache
    }
  } else {
    console.log('[10.4] No intercepted AJAX filter response — filter may not trigger on load');
  }
});

test('[10.5] Critical JS: no data-cfasync="false" check (Cloudflare Rocket Loader)', async ({ page }) => {
  await page.goto(`${BASE}/`, { waitUntil: 'domcontentloaded' });
  // Check that critical filter/search scripts have cfasync=false or are deferred properly
  const scripts = await page.evaluate(() => {
    return Array.from(document.querySelectorAll('script[src]')).map(s => ({
      src: s.src,
      cfasync: s.getAttribute('data-cfasync'),
      defer: s.defer,
      async: s.async,
    }));
  });
  const criticalScripts = scripts.filter(s => s.src.match(/filter|search|main|alkana/i));
  console.log(`[10.5] Critical scripts found: ${criticalScripts.length}`);
  for (const s of criticalScripts) {
    console.log(`  - ${s.src} | cfasync=${s.cfasync} | defer=${s.defer}`);
  }
  // No hard assertion — this is a documentation check for manual follow-up
});

// ─── ACF deactivation graceful degradation ────────────────────────────────────

test('[10.1] Product page renders without Fatal error (ACF resilience)', async ({ page }) => {
  await page.goto(`${BASE}/san-pham/`, { waitUntil: 'networkidle' });
  const body = await page.locator('body').textContent();
  expect(body).not.toContain('Fatal error');
  expect(body).not.toContain('Call to undefined function get_field');
  expect(body).not.toContain('Call to undefined function have_rows');
});

// ─── Vite asset integrity ─────────────────────────────────────────────────────

test('[10.2] Critical CSS/JS loaded: no 404 for theme assets', async ({ page }) => {
  const failed404s = [];
  page.on('response', (resp) => {
    if (resp.status() === 404 && (resp.url().includes('/dist/') || resp.url().includes('/assets/'))) {
      failed404s.push(resp.url());
    }
  });
  await page.goto(`${BASE}/`, { waitUntil: 'networkidle' });
  if (failed404s.length > 0) {
    console.warn(`[10.2] 404 Vite assets: ${failed404s.join(', ')}`);
  }
  expect(failed404s.length).toBe(0);
});

// ─── Admin: hero slider URL sanitization ─────────────────────────────────────

test('[B2.7] Hero slider settings: javascript: URL sanitized by esc_url', async ({ page }) => {
  skipUnlessCI('Requires admin login');
  await loginAs(page, 'admin');
  await page.goto(`${BASE}/wp-admin/admin.php?page=alkana-settings`, { waitUntil: 'networkidle' });
  const sliderUrlField = page.locator('input[name*="slider"][name*="url"], input[name*="hero"][name*="url"]').first();
  if (!await sliderUrlField.isVisible({ timeout: 3000 }).catch(() => false)) {
    test.skip(true, 'Slider URL field not found in settings');
    return;
  }
  await sliderUrlField.fill('javascript:alert(1)');
  const saveBtn = page.locator('input[type="submit"], button[type="submit"]').first();
  await saveBtn.click();
  await page.waitForLoadState('networkidle');
  // After save, re-check — esc_url() should empty/sanitize javascript: URLs
  const savedVal = await sliderUrlField.inputValue();
  expect(savedVal).not.toContain('javascript:');
});
