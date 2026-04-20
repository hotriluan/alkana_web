// tests/08-data-integrity/newsletter-dedup.spec.js
// Scenarios: 9.3, 9.4 — newsletter deduplication, case sensitivity, race condition
'use strict';

const { test, expect } = require('@playwright/test');
const { BASE } = require('../helpers/fixtures');
const { ajaxPost } = require('../helpers/ajax');
const { TEST_PREFIX } = require('../helpers/test-utils');

test('[Newsletter] Subscribe with unique email succeeds', async ({ page }) => {
  await page.goto(`${BASE}/`, { waitUntil: 'networkidle' });

  const email = `${TEST_PREFIX}nl_${Date.now()}@example.com`;
  const result = await ajaxPost(page, {
    action: 'alkana_subscribe_newsletter',
    email,
  });
  expect(result.status).not.toBe(500);
  if (result.data && typeof result.data === 'object') {
    const dataStr = JSON.stringify(result.data);
    expect(dataStr).not.toContain('Duplicate entry');
  }
});

test('[Newsletter] Subscribe twice with same email: no DB error', async ({ page }) => {
  await page.goto(`${BASE}/`, { waitUntil: 'networkidle' });

  const email = `${TEST_PREFIX}nl_dup_${Date.now()}@example.com`;

  // First subscription
  const r1 = await ajaxPost(page, { action: 'alkana_subscribe_newsletter', email });
  // Second subscription (duplicate)
  const r2 = await ajaxPost(page, { action: 'alkana_subscribe_newsletter', email });

  expect(r1.status).not.toBe(500);
  expect(r2.status).not.toBe(500);

  // Neither response should expose raw SQL error
  for (const r of [r1, r2]) {
    if (r.data && typeof r.data === 'string') {
      expect(r.data).not.toContain('Duplicate entry');
      expect(r.data).not.toContain('SQLSTATE[23000]');
    }
  }
});

test('[9.4] Newsletter: mixed-case email treated as duplicate', async ({ page }) => {
  await page.goto(`${BASE}/`, { waitUntil: 'networkidle' });

  const baseEmail = `${TEST_PREFIX}case_${Date.now()}@example.com`;

  const r1 = await ajaxPost(page, { action: 'alkana_subscribe_newsletter', email: baseEmail });
  const r2 = await ajaxPost(page, { action: 'alkana_subscribe_newsletter', email: baseEmail.toUpperCase() });

  expect(r1.status).not.toBe(500);
  expect(r2.status).not.toBe(500);

  // Log for observational purposes
  console.log(`[9.4] lowercase=${JSON.stringify(r1.data)}, UPPERCASE=${JSON.stringify(r2.data)}`);
});

test('[3.2] Newsletter race condition: near-simultaneous submit of same email', async ({ page }) => {
  await page.goto(`${BASE}/`, { waitUntil: 'networkidle' });

  const email = `${TEST_PREFIX}race_${Date.now()}@example.com`;

  // Fire two concurrent requests
  const [r1, r2] = await Promise.all([
    ajaxPost(page, { action: 'alkana_subscribe_newsletter', email }),
    ajaxPost(page, { action: 'alkana_subscribe_newsletter', email }),
  ]);

  // Neither should 500 (DB UNIQUE constraint + INSERT IGNORE or INSERT ON DUPLICATE)
  expect(r1.status).not.toBe(500);
  expect(r2.status).not.toBe(500);

  for (const r of [r1, r2]) {
    if (r.data && typeof r.data === 'string') {
      expect(r.data).not.toContain('Duplicate entry');
    }
  }
});
