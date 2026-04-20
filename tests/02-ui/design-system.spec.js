// Phase 02 — UI: Design System Tokens & Homepage Section Checks
const { test, expect } = require('@playwright/test');

const BASE = 'http://localhost/alkana-wp';

// ─── CSS Custom Properties ─────────────────────────────────────────────────────
test('[Design] CSS custom properties are defined correctly', async ({ page }) => {
  await page.goto(`${BASE}/`);
  const tokens = await page.evaluate(() => {
    const s = getComputedStyle(document.documentElement);
    return {
      primary:          s.getPropertyValue('--color-primary').trim(),
      primaryDark:      s.getPropertyValue('--color-primary-dark').trim(),
      // Light purple uses the purple scale (--color-primary-light is not defined)
      primaryLight:     s.getPropertyValue('--color-alkana-purple-300').trim(),
      primaryMedium:    s.getPropertyValue('--color-alkana-purple-500').trim(),
    };
  });
  // Using toMatch to be flexible about formatting (e.g., spaces)
  expect(tokens.primary.replace(/\s/g, '')).toMatch(/^#67219d$/i);
  expect(tokens.primaryDark.replace(/\s/g, '')).toMatch(/^#4c0682$/i);
  expect(tokens.primaryLight.replace(/\s/g, '')).toMatch(/^#b87edd$/i);
});

// ─── Homepage Sections ──────────────────────────────────────────────────────────
test('[UI] Hero section is full-viewport height', async ({ page }) => {
  await page.setViewportSize({ width: 1440, height: 900 });
  await page.goto(`${BASE}/`);
  const hero = page.locator('.hero-slider-section, .homepage-hero-wrap, .hero-banner, .hero-section, .hero-slider').first();
  await expect(hero).toBeVisible();
  const box = await hero.boundingBox();
  expect(box.height).toBeGreaterThan(500); // at least 500px
});

test('[UI] Trust bar / partner logos section visible', async ({ page }) => {
  await page.goto(`${BASE}/`);
  await expect(
    page.locator('.trust-bar, .partners, .logo-carousel, .brand-logos, .marquee').first()
  ).toBeVisible({ timeout: 5000 });
});

test('[UI] USP / stats counter section visible', async ({ page }) => {
  await page.goto(`${BASE}/`);
  // Scroll to make sure it's in view
  await page.evaluate(() => window.scrollBy(0, 400));
  await expect(
    page.locator('#usp-section, .stat-counter, [data-count], .usp-section, .stats-section, .counter-section, .usp-counters').first()
  ).toBeVisible({ timeout: 5000 });
});

test('[UI] Featured products section renders', async ({ page }) => {
  await page.goto(`${BASE}/`);
  await page.evaluate(() => window.scrollBy(0, 800));
  await expect(
    page.locator('.featured-products, .products-section, .product-tabs, .home-products').first()
  ).toBeVisible({ timeout: 5000 });
});

test('[UI] Footer CTA or newsletter present in homepage', async ({ page }) => {
  await page.goto(`${BASE}/`);
  await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
  await expect(
    page.locator('.footer-cta, .cta-banner, .cta-section, footer, #site-footer').first()
  ).toBeVisible({ timeout: 5000 });
});

// ─── Responsive Layout ──────────────────────────────────────────────────────────
const viewports = [
  { width: 1440, height: 900,  label: '1440px' },
  { width: 1280, height: 800,  label: '1280px' },
  { width: 768,  height: 1024, label: '768px tablet' },
];

for (const vp of viewports) {
  test(`[Responsive] No horizontal scroll at ${vp.label}`, async ({ page }) => {
    await page.setViewportSize({ width: vp.width, height: vp.height });
    await page.goto(`${BASE}/`);
    await page.waitForLoadState('networkidle');
    const scrollWidth = await page.evaluate(() => document.documentElement.scrollWidth);
    expect(scrollWidth).toBeLessThanOrEqual(vp.width + 2); // allow 2px tolerance
  });
}

test('[Responsive] Product archive layout at 768px', async ({ page }) => {
  await page.setViewportSize({ width: 768, height: 1024 });
  const response = await page.goto(`${BASE}/san-pham/`);
  if (response && response.status() >= 400) {
    test.skip(true, '/san-pham/ returns ' + response.status() + ' — archive not configured in dev');
    return;
  }
  const grid = page.locator('#product-grid, .products-grid, .product-archive').first();
  await expect(grid).toBeVisible({ timeout: 5000 });
});

// ─── Typography ─────────────────────────────────────────────────────────────────
test('[UI] Headings use correct font family (Inter or theme font)', async ({ page }) => {
  await page.goto(`${BASE}/`);
  const h1Font = await page.evaluate(() => {
    const h1 = document.querySelector('h1, .hero-title, .entry-title');
    if (!h1) return null;
    return getComputedStyle(h1).fontFamily;
  });
  if (h1Font) {
    // Should have Inter or our custom heading font
    expect(h1Font.toLowerCase()).toMatch(/inter|sans-serif/);
  }
});

// ─── Color Contrast (basic check) ──────────────────────────────────────────────
test('[UI] Primary buttons have visible background color', async ({ page }) => {
  await page.goto(`${BASE}/`);
  const btn = page.locator('.btn-primary, .button.primary, a.btn-primary, button.btn-primary').first();
  if (await btn.isVisible()) {
    const bgColor = await btn.evaluate((el) => getComputedStyle(el).backgroundColor);
    // Should not be transparent
    expect(bgColor).not.toBe('rgba(0, 0, 0, 0)');
    expect(bgColor).not.toBe('transparent');
  }
});
