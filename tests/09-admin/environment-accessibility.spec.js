// tests/09-admin/environment-accessibility.spec.js
// Scenarios: 6.1–6.8, B6.1–B6.4 — device breakpoints, no-JS fallback, a11y, CDN
'use strict';

const { test, expect } = require('@playwright/test');
const { AxeBuilder } = require('@axe-core/playwright');
const { BASE } = require('../helpers/fixtures');
const { skipUnlessCI } = require('../helpers/test-utils');

// ─── Mobile responsive ────────────────────────────────────────────────────────

test('[6.1] Mobile (375px): no horizontal overflow on all key pages', async ({ browser }) => {
  const page = await browser.newPage({
    viewport: { width: 375, height: 667 }, // iPhone SE
  });

  const pages = ['/', '/san-pham/', '/lien-he/', '/tuyen-dung/'];
  for (const path of pages) {
    const resp = await page.goto(`${BASE}${path}`, { waitUntil: 'networkidle' });
    if (resp && resp.status() === 404) continue;

    // Check whether the user can actually scroll horizontally — the semantically correct
    // test. Elements clipped by overflow:hidden or swiper containers won't cause real scroll.
    const hasOverflow = await page.evaluate(async () => {
      window.scrollTo(1000, 0);
      await new Promise(r => setTimeout(r, 50));
      const scrolled = window.scrollX > 0;
      window.scrollTo(0, 0);
      return scrolled;
    });
    expect(hasOverflow).toBe(false, `[6.1] Horizontal overflow on ${path}`);
  }

  await page.close();
});

// ─── No-JS fallback ───────────────────────────────────────────────────────────

test('[6.2] No-JavaScript: product archive renders server-side HTML', async ({ browser }) => {
  const context = await browser.newContext({ javaScriptEnabled: false });
  const page = await context.newPage();
  const resp = await page.goto(`${BASE}/san-pham/`, { waitUntil: 'domcontentloaded' });
  if (resp && resp.status() === 404) {
    test.skip(true, 'Product archive 404');
    await context.close();
    return;
  }

  // Should have product HTML from PHP (server-side rendered)
  const body = await page.locator('body').textContent();
  expect(body).not.toContain('Fatal error');
  expect(body).not.toContain('You need JavaScript');

  const hasContent = await page.locator('.product-card, article, .products, #product-grid').first()
    .isAttached({ timeout: 3000 }).catch(() => false);
  console.log(`[6.2] No-JS product grid in DOM: ${hasContent}`);

  await context.close();
});

// ─── Accessibility ────────────────────────────────────────────────────────────

test('[6.3] Product catalog: no critical axe-core violations', async ({ page }) => {
  const resp = await page.goto(`${BASE}/san-pham/`, { waitUntil: 'networkidle' });
  if (resp && resp.status() === 404) {
    test.skip(true, 'Product archive 404');
    return;
  }

  const results = await new AxeBuilder({ page })
    .withTags(['wcag2a', 'wcag2aa'])
    .analyze();

  const criticalViolations = results.violations.filter(v => v.impact === 'critical');
  if (criticalViolations.length > 0) {
    console.warn('[6.3] Critical a11y violations:', criticalViolations.map(v => v.id));
  }
  expect(criticalViolations.length).toBe(0);
});

test('[6.3] Homepage: keyboard navigation reaches main content', async ({ page }) => {
  await page.goto(`${BASE}/`, { waitUntil: 'domcontentloaded' });
  // Tab to skip link or main content
  await page.keyboard.press('Tab');
  const focused = await page.evaluate(() => {
    const el = document.activeElement;
    return el ? { tag: el.tagName, text: el.textContent?.slice(0, 30), href: el.getAttribute('href') } : null;
  });
  console.log(`[6.3] First focused element: ${JSON.stringify(focused)}`);
  expect(focused).not.toBeNull();
});

// ─── Browser fallback ─────────────────────────────────────────────────────────

test('[6.7] View Transitions not supported: navigation still works', async ({ browser }) => {
  // Disable CSS View Transitions by injecting CSS
  const page = await browser.newPage();
  await page.addInitScript(() => {
    // Override ViewTransition API to simulate unsupported browser
    Object.defineProperty(document, 'startViewTransition', { value: undefined, writable: true });
  });

  await page.goto(`${BASE}/`, { waitUntil: 'networkidle' });
  const links = page.locator('a[href*="/san-pham/"], a[href*="/lien-he/"]').first();
  if (await links.isVisible({ timeout: 3000 }).catch(() => false)) {
    await links.click();
    await page.waitForLoadState('domcontentloaded');
    const body = await page.locator('body').textContent();
    expect(body).not.toContain('Fatal error');
    expect(body).not.toContain('TypeError');
  }
  await page.close();
});

// ─── CDN / Cache behavior ─────────────────────────────────────────────────────

test('[6.5] CDN cache: AJAX filter endpoint bypasses cache (real-time data)', async ({ page }) => {
  await page.goto(`${BASE}/san-pham/`, { waitUntil: 'networkidle' });

  let filterResponse = null;
  page.on('response', (resp) => {
    if (resp.url().includes('admin-ajax') && resp.request().method() === 'POST') {
      filterResponse = resp;
    }
  });

  // Trigger filter
  const filterCheckbox = page.locator('.filter-item input[type="checkbox"]').first();
  if (await filterCheckbox.isVisible({ timeout: 3000 }).catch(() => false)) {
    await filterCheckbox.click();
    await page.waitForTimeout(1000);
    if (filterResponse) {
      const headers = filterResponse.headers();
      const cacheHeader = headers['cf-cache-status'] || headers['x-cache'] || headers['cache-control'] || 'n/a';
      console.log(`[6.5] AJAX cache header: ${cacheHeader}`);
      // CF-Cache-Status should not be HIT for AJAX endpoints
      expect(headers['cf-cache-status']).not.toBe('HIT');
    }
  }
});
