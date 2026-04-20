// Phase 02 — UI: Visual Regression Screenshots
const { test, expect } = require('@playwright/test');

const BASE   = 'http://localhost/alkana-wp';
const PAGES  = [
  { name: 'homepage',         url: '/'             },
  { name: 'product-archive',  url: '/san-pham/'    },
  { name: 'paint-builder',    url: '/he-son/'       },
  { name: 'about',            url: '/gioi-thieu/'  },
  { name: 'contact',          url: '/lien-he/'     },
  { name: 'careers',          url: '/tuyen-dung/'  },
  { name: 'news',             url: '/tin-tuc/'     },
];

// Capture baseline screenshots — run once with UPDATE_SNAPSHOTS=1 env var to create baselines,
// thereafter used for comparison.
for (const { name, url } of PAGES) {
  test(`[VR] ${name} — fullpage screenshot`, async ({ page }, testInfo) => {
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.goto(`${BASE}${url}`, { waitUntil: 'networkidle' });

    // Pause animations to stabilize screenshot
    await page.addStyleTag({
      content: `
        *, *::before, *::after {
          animation-duration: 0s !important;
          animation-delay: 0s !important;
          transition-duration: 0s !important;
        }
      `,
    });
    await page.waitForTimeout(300);

    await expect(page).toHaveScreenshot(`${name}-desktop.png`, {
      fullPage: true,
      maxDiffPixelRatio: 0.05, // 5% tolerance
    });
  });

  test(`[VR] ${name} — mobile 375px screenshot`, async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 812 });
    await page.goto(`${BASE}${url}`, { waitUntil: 'networkidle' });

    await page.addStyleTag({
      content: `
        *, *::before, *::after {
          animation-duration: 0s !important;
          animation-delay: 0s !important;
          transition-duration: 0s !important;
        }
      `,
    });
    await page.waitForTimeout(300);

    await expect(page).toHaveScreenshot(`${name}-mobile.png`, {
      fullPage: true,
      maxDiffPixelRatio: 0.05,
    });
  });
}
