// playwright.config.js
// Alkana Web — Comprehensive Test Suite Configuration
const { defineConfig, devices } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './tests',
  timeout: 30_000,
  expect: { timeout: 5_000 },
  fullyParallel: false, // WordPress local — run sequentially to avoid AJAX race
  retries: 1,
  workers: 2,
  globalSetup: require.resolve('./tests/global-setup.js'),
  globalTeardown: require.resolve('./tests/global-teardown.js'),
  reporter: [
    ['list'],
    ['html', { outputFolder: 'plans/260416-comprehensive-testing/reports/playwright-report', open: 'on-failure' }],
  ],
  
  use: {
    baseURL: process.env.BASE_URL || 'http://localhost/alkana-wp',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'on-first-retry',
    locale: 'vi-VN',
  },

  snapshotDir: './tests/screenshots',

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
    {
      name: 'firefox',
      use: { ...devices['Desktop Firefox'] },
    },
    {
      name: 'webkit',
      use: { ...devices['Desktop Safari'] },
    },
    // Mobile
    {
      name: 'mobile-chrome',
      use: { ...devices['Pixel 7'] },
    },
    {
      name: 'mobile-safari',
      use: { ...devices['iPhone 14'] },
    },
  ],
});
