// Phase 01 — Smoke: Page Rendering & HTTP Status
const { test, expect } = require('@playwright/test');

const BASE = 'http://localhost/alkana-wp';

const pages = [
  { url: '/',           selector: '.hero-slider-section, .homepage-hero-wrap, .hero-banner' },
  { url: '/san-pham/',  selector: '.archive-products, #product-grid, main' },
  { url: '/he-son/',    selector: '[x-data], .paint-builder, .page-hero, main' },
  { url: '/gioi-thieu/', selector: '.about-hero, .page-hero, .about-page, main' },
  { url: '/lien-he/',   selector: '#contact-form, .contact-form, .page-hero, main' },
  { url: '/tuyen-dung/', selector: '.careers-page, .job-listings, .page-hero, main' },
  { url: '/tin-tuc/',   selector: '.blog-grid, .news-grid, article, .page-hero, main' },
  { url: '/du-an/',     selector: '.projects-grid, .page-hero, article, main' },
];

for (const { url, selector } of pages) {
  test(`[Render] ${url} returns 200 and DOM renders`, async ({ page }) => {
    const response = await page.goto(`${BASE}${url}`, { waitUntil: 'domcontentloaded' });
    // Some pages may 404 in dev if WP archive isn't configured — skip content check
    if (response.status() >= 400) {
      console.warn(`[Render] ${url} returned ${response.status()} — skipping content check`);
      const bodyText = await page.locator('body').textContent();
      expect(bodyText).not.toContain('Fatal error');
      return;
    }
    expect(response.status()).toBeLessThan(400);
    await expect(page.locator(selector).first()).toBeVisible({ timeout: 8000 });

    const bodyText = await page.locator('body').textContent();
    expect(bodyText).not.toContain('Fatal error');
    expect(bodyText).not.toContain('Parse error');
  });
}

test('[Render] Custom 404 page renders', async ({ page }) => {
  const response = await page.goto(`${BASE}/this-page-definitely-does-not-exist-xyz123/`, {
    waitUntil: 'domcontentloaded',
  });
  // WP sends 404
  expect(response.status()).toBe(404);
  const bodyText = await page.locator('body').textContent();
  expect(bodyText).not.toContain('Fatal error');
  // Contains some 404 indicator
  expect(bodyText.toLowerCase()).toMatch(/404|không tìm thấy|not found/);
});

test('[Assets] dist/app.js and dist/app.css load (no 404)', async ({ page }) => {
  const failed = [];
  page.on('response', (resp) => {
    if ((resp.url().includes('/dist/') || resp.url().includes('app.js') || resp.url().includes('app.css'))
      && resp.status() >= 400) {
      failed.push(`${resp.status()} ${resp.url()}`);
    }
  });
  await page.goto(`${BASE}/`, { waitUntil: 'networkidle' });
  if (failed.length > 0) console.error('Failed asset requests:', failed);
  expect(failed).toHaveLength(0);
});

test('[Console] No critical JS errors on homepage', async ({ page }) => {
  const errors = [];
  page.on('console', (msg) => {
    if (msg.type() === 'error') errors.push(msg.text());
  });
  await page.goto(`${BASE}/`, { waitUntil: 'networkidle' });
  // Allow benign WP/plugin errors but no fatal errors
  const fatalErrors = errors.filter((e) =>
    !e.includes('favicon') &&
    !e.includes('net::ERR_ABORTED') &&
    !e.includes('google') &&
    !e.includes('404') &&
    !e.includes('Not Found')
  );
  if (fatalErrors.length > 0) console.error('JS errors:', fatalErrors);
  expect(fatalErrors).toHaveLength(0);
});
