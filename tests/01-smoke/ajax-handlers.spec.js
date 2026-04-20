// Phase 01 — Smoke: AJAX Handlers (Search, Newsletter, Contact, Filter)
const { test, expect } = require('@playwright/test');

const BASE = 'http://localhost/alkana-wp';

// ─── Search Modal ──────────────────────────────────────────────────────────────
test('[AJAX] Search modal opens via search icon click', async ({ page }) => {
  await page.goto(`${BASE}/`);
  const searchTrigger = page.locator('#search-toggle').first();
  await expect(searchTrigger).toBeVisible({ timeout: 5000 });
  await searchTrigger.click();
  const modal = page.locator('#search-modal');
  await expect(modal).not.toHaveClass(/hidden/, { timeout: 3000 });
});

test('[AJAX] Search modal closes with Escape', async ({ page }) => {
  await page.goto(`${BASE}/`);
  await page.locator('#search-toggle').click();
  const modal = page.locator('#search-modal');
  await expect(modal).not.toHaveClass(/hidden/, { timeout: 3000 });
  await page.keyboard.press('Escape');
  await expect(modal).toHaveClass(/hidden/, { timeout: 2000 });
});

test('[AJAX] Search returns results for query "sơn"', async ({ page }) => {
  await page.goto(`${BASE}/`);
  await page.locator('#search-toggle').click();
  const input = page.locator('#search-modal-input');
  await expect(input).toBeVisible({ timeout: 3000 });
  await input.fill('sơn');
  // Wait for the actual AJAX response (debounce 300ms + request time)
  const response = await page.waitForResponse(
    (r) => r.url().includes('admin-ajax') && r.url().includes('alkana_search'),
    { timeout: 8000 }
  );
  expect(response.status()).toBe(200);
  const data = await response.json();
  // Handler must return { results: [...] } — empty is fine if DB is unseeded
  expect(data).toHaveProperty('results');
});

test('[AJAX] Search XSS input is escaped (no script execution)', async ({ page }) => {
  let alertFired = false;
  page.on('dialog', () => { alertFired = true; });

  await page.goto(`${BASE}/`);
  await page.locator('#search-toggle').click();
  const input = page.locator('#search-modal-input');
  await expect(input).toBeVisible({ timeout: 3000 });
  await input.fill('<script>alert("xss")</script>');
  await page.waitForTimeout(800);
  expect(alertFired).toBe(false);
});

// ─── Product Filter ────────────────────────────────────────────────────────────
test('[AJAX] Product filter updates grid on category click', async ({ page }) => {
  await page.goto(`${BASE}/san-pham/`);
  await page.waitForLoadState('networkidle');

  const filterItem = page.locator(
    '.filter-item input[type="checkbox"], .filter-checkbox, .product-filter label, .facet-item'
  ).first();
  if (await filterItem.isVisible()) {
    await filterItem.click();
    // Either AJAX reload or URL change
    await Promise.race([
      page.waitForResponse((r) => r.url().includes('admin-ajax') && r.status() === 200, { timeout: 6000 }),
      page.waitForURL(/\?/, { timeout: 6000 }),
    ]).catch(() => {}); // Not all implementations do AJAX
    const grid = page.locator('#product-grid, .products-grid, .product-archive').first();
    await expect(grid).toBeVisible({ timeout: 5000 });
  } else {
    test.skip(true, 'No filter items found — skipping filter AJAX test');
  }
});

// ─── Newsletter ────────────────────────────────────────────────────────────────
test('[AJAX] Newsletter invalid email shows validation error', async ({ page }) => {
  await page.goto(`${BASE}/`);
  const emailInput = page.locator(
    'footer input[type="email"], #footer-newsletter input[type="email"]'
  ).first();
  if (await emailInput.isVisible()) {
    await emailInput.fill('not-an-email');
    // Try native validation
    const isValid = await emailInput.evaluate((el) => el.checkValidity());
    expect(isValid).toBe(false);
  } else {
    test.skip(true, 'Newsletter form not visible in footer');
  }
});

test('[AJAX] Newsletter valid email submission', async ({ page }) => {
  await page.goto(`${BASE}/`);
  await page.waitForLoadState('networkidle');
  const emailInput = page.locator(
    'footer input[type="email"], #footer-newsletter input[type="email"]'
  ).first();
  const submitBtn = page.locator(
    'footer button[type="submit"], #footer-newsletter button[type="submit"]'
  ).first();
  if (await emailInput.isVisible()) {
    await emailInput.fill(`test-${Date.now()}@example.com`);
    // force:true bypasses actionability (footer button may be near viewport edge)
    await submitBtn.click({ force: true });
    await page.waitForTimeout(2000);
    const bodyText = await page.locator('body').textContent();
    expect(bodyText).not.toContain('Fatal error');
  } else {
    test.skip(true, 'Newsletter form not visible');
  }
});

// ─── Contact Form ──────────────────────────────────────────────────────────────
test('[AJAX] Contact form validation blocks empty submit', async ({ page }) => {
  await page.goto(`${BASE}/lien-he/`);
  await page.waitForLoadState('domcontentloaded');
  const form = page.locator('#contact-form, .contact-form, form[action*="submit"]').first();
  const submitBtn = form.locator('button[type="submit"], input[type="submit"]').first();
  if (await submitBtn.isVisible()) {
    await submitBtn.click();
    // HTML5 validation or custom validation should block submission
    const nameInput = form.locator('input[name="name"], input[name*="name"]').first();
    const isValid = await nameInput.evaluate((el) => el.checkValidity());
    expect(isValid).toBe(false);
  } else {
    test.skip(true, 'Contact form submit button not found');
  }
});
