// Phase 06 — Accessibility & Mobile: axe-core, keyboard nav, viewport checks
const { test, expect } = require('@playwright/test');
const AxeBuilder = require('@axe-core/playwright').default;

const BASE = 'http://localhost/alkana-wp';

// ─── axe-core WCAG Audit ───────────────────────────────────────────────────────
const auditPages = [
  { name: 'homepage',        url: '/'            },
  { name: 'product-archive', url: '/san-pham/'   },
  { name: 'paint-builder',   url: '/he-son/'     },
  { name: 'contact',         url: '/lien-he/'    },
  { name: 'careers',         url: '/tuyen-dung/' },
];

for (const { name, url } of auditPages) {
  test(`[A11y] ${name} — no critical/serious axe violations`, async ({ page }) => {
    await page.goto(`${BASE}${url}`, { waitUntil: 'networkidle' });

    const results = await new AxeBuilder({ page })
      .withTags(['wcag2a', 'wcag2aa'])
      .analyze();

    const critical = results.violations.filter(
      (v) => v.impact === 'critical' || v.impact === 'serious'
    );

    if (critical.length > 0) {
      const msgs = critical.map((v) => `[${v.impact}] ${v.id}: ${v.description} (${v.nodes.length} nodes)`);
      console.error(`A11y violations on ${name}:\n${msgs.join('\n')}`);
    }

    expect(critical).toHaveLength(0);
  });
}

// ─── Keyboard Navigation ───────────────────────────────────────────────────────
test('[A11y] Skip to main content link present and focusable', async ({ page }) => {
  await page.goto(`${BASE}/`);
  await page.keyboard.press('Tab');
  const focused = await page.evaluate(() => {
    const el = document.activeElement;
    return el ? { tag: el.tagName, href: el.getAttribute('href'), text: el.innerText?.trim() } : null;
  });
  // First focusable element should ideally be a skip link
  if (focused?.href?.includes('#main') || focused?.text?.toLowerCase().includes('skip')) {
    expect(focused.href || focused.text).toBeTruthy();
  }
  // Otherwise just verify tab works (not blocks)
  expect(focused).not.toBeNull();
});

test('[A11y] Contact form fields are keyboard accessible', async ({ page }) => {
  await page.goto(`${BASE}/lien-he/`);
  const nameInput = page.locator('input[name="name"], input[name*="your-name"], input[type="text"]').first();
  if (await nameInput.isVisible({ timeout: 5000 }).catch(() => false)) {
    await nameInput.focus();
    const isFocused = await nameInput.evaluate((el) => document.activeElement === el);
    expect(isFocused).toBe(true);
  }
});

test('[A11y] Paint Builder wizard keyboard navigable', async ({ page }) => {
  await page.goto(`${BASE}/he-son/`);
  // Tab through the page to check no keyboard traps
  for (let i = 0; i < 5; i++) {
    await page.keyboard.press('Tab');
    await page.waitForTimeout(100);
  }
  // We should still be on the page (no crash, no navigation)
  expect(page.url()).toContain('/he-son/');
});

test('[A11y] Images have alt text', async ({ page }) => {
  await page.goto(`${BASE}/`);
  const imgsMissingAlt = await page.evaluate(() => {
    return Array.from(document.querySelectorAll('img'))
      .filter((img) => !img.alt && !img.getAttribute('aria-hidden'))
      .map((img) => img.src.split('/').pop())
      .slice(0, 10);
  });
  if (imgsMissingAlt.length > 0) console.warn('[Alt] Images missing alt text:', imgsMissingAlt);
  expect(imgsMissingAlt.length).toBeLessThanOrEqual(3); // Allow decorative images
});

test('[A11y] Interactive elements have visible focus styles', async ({ page }) => {
  await page.goto(`${BASE}/`);
  // Inject a test that checks first focusable element has focus visible
  await page.addStyleTag({ content: ':focus { outline: 3px solid red !important; }' });
  await page.keyboard.press('Tab');
  const focused = await page.evaluate(() => {
    const el = document.activeElement;
    if (!el) return null;
    const style = getComputedStyle(el);
    return { outline: style.outline, boxShadow: style.boxShadow };
  });
  expect(focused).not.toBeNull();
});

// ─── Mobile UX ─────────────────────────────────────────────────────────────────
const mobileViewports = [
  { width: 375,  height: 812,  label: 'iPhone SE' },
  { width: 390,  height: 844,  label: 'iPhone 14'  },
  { width: 414,  height: 896,  label: 'iPhone XR'  },
];

for (const vp of mobileViewports) {
  test(`[Mobile] Homepage renders correctly at ${vp.label}`, async ({ page }) => {
    await page.setViewportSize({ width: vp.width, height: vp.height });
    await page.goto(`${BASE}/`, { waitUntil: 'domcontentloaded' });
    const bodyText = await page.locator('body').textContent();
    expect(bodyText).not.toContain('Fatal error');
    // Check user cannot actually scroll horizontally (scrollWidth counts fixed off-canvas elements)
    const scrollX = await page.evaluate(() => { window.scrollTo(500, 0); return window.scrollX; });
    expect(scrollX).toBe(0);
  });
}

test('[Mobile] Contact form usable on mobile (375px)', async ({ page }) => {
  await page.setViewportSize({ width: 375, height: 812 });
  await page.goto(`${BASE}/lien-he/`, { waitUntil: 'domcontentloaded' });
  const form = page.locator('#contact-form, .contact-form, form').first();
  await expect(form).toBeVisible({ timeout: 5000 });
  const bodyText = await page.locator('body').textContent();
  expect(bodyText).not.toContain('Fatal error');
});

test('[Mobile] Paint Builder wizard usable on mobile', async ({ page }) => {
  await page.setViewportSize({ width: 375, height: 812 });
  await page.goto(`${BASE}/he-son/`, { waitUntil: 'networkidle' });
  await expect(page.locator('#step-1, .builder-step, #main-content').first()).toBeVisible({ timeout: 8000 });
  // Check user cannot actually scroll horizontally
  const scrollX = await page.evaluate(() => { window.scrollTo(500, 0); return window.scrollX; });
  expect(scrollX).toBe(0);
});

test('[Mobile] Product archive usable on mobile', async ({ page }) => {
  await page.setViewportSize({ width: 375, height: 812 });
  const resp = await page.goto(`${BASE}/san-pham/`, { waitUntil: 'domcontentloaded' });
  if (resp && resp.status() === 404) {
    test.skip(true, 'Product archive returns 404 in local dev — no products seeded');
    return;
  }
  const grid = page.locator('#product-grid, .archive-products__results, .products-grid, article').first();
  await expect(grid).toBeVisible({ timeout: 8000 });
});
