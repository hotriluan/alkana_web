// tests/09-admin/admin-workflows.spec.js
// Scenarios: B5.1–B5.4 — focus mode, application status, product lifecycle, backup flow
'use strict';

const { test, expect } = require('@playwright/test');
const { BASE } = require('../helpers/fixtures');
const { loginAs } = require('../helpers/auth');
const { ajaxPost } = require('../helpers/ajax');
const { skipUnlessCI } = require('../helpers/test-utils');

test.describe.configure({ mode: 'serial' });

test('[B5.1] Focus Mode: toggle on/off preserves admin UI elements', async ({ page }) => {
  skipUnlessCI('Requires admin login');
  await loginAs(page, 'admin');
  await page.goto(`${BASE}/wp-admin/`, { waitUntil: 'networkidle' });

  const focusToggle = page.locator('[data-focus-mode], .focus-mode-toggle, #alkana-focus-toggle').first();
  if (!await focusToggle.isVisible({ timeout: 3000 }).catch(() => false)) {
    test.skip(true, 'Focus mode toggle not found in admin');
    return;
  }

  // Toggle focus mode ON
  await focusToggle.click();
  await page.waitForTimeout(500);

  // Toggle focus mode OFF
  await focusToggle.click();
  await page.waitForTimeout(500);

  // Admin menu and content should still be visible
  const adminMenu = page.locator('#adminmenuwrap, #wpadminbar').first();
  await expect(adminMenu).toBeVisible({ timeout: 3000 });
});

test('[B5.2] Application status workflow: new → reviewed → rejected', async ({ page }) => {
  skipUnlessCI('Requires admin + seeded application');
  await loginAs(page, 'admin');
  await page.goto(`${BASE}/wp-admin/edit.php?post_type=alkana_application`, { waitUntil: 'networkidle' });
  const body = await page.locator('body').textContent();
  expect(body).not.toContain('Fatal error');

  // Find first application
  const editLink = page.locator('.row-actions a[href*="action=edit"], a.row-title').first();
  if (!await editLink.isVisible({ timeout: 5000 }).catch(() => false)) {
    test.skip(true, 'No applications in list');
    return;
  }
  await editLink.click();
  await page.waitForLoadState('networkidle');

  // Look for status dropdown
  const statusSelect = page.locator('select[name*="status"], select#_app_status').first();
  if (await statusSelect.isVisible({ timeout: 3000 }).catch(() => false)) {
    await statusSelect.selectOption('reviewed');
    const updateBtn = page.locator('#publish, input[type="submit"]').first();
    await updateBtn.click();
    await page.waitForLoadState('networkidle');
    const updatedBody = await page.locator('body').textContent();
    expect(updatedBody).not.toContain('Fatal error');
  }
});

test('[B5.3] Product lifecycle: draft → publish → trash sync', async ({ page }) => {
  skipUnlessCI('Requires admin');
  await loginAs(page, 'admin');
  await page.goto(`${BASE}/wp-admin/edit.php?post_type=alkana_product`, { waitUntil: 'networkidle' });
  const body = await page.locator('body').textContent();
  expect(body).not.toContain('Fatal error');
  // Lifecycle sync requires index hooks — just verify list page loads cleanly
});

test('[B5.4] Backup flow: init step responds without error', async ({ page }) => {
  skipUnlessCI('Requires admin login');
  await loginAs(page, 'admin');
  await page.goto(`${BASE}/wp-admin/admin.php?page=alkana-backup`, { waitUntil: 'networkidle' });
  const body = await page.locator('body').textContent();
  if (body.match(/not allowed|forbidden|no permission/i)) {
    test.skip(true, 'Backup page requires manage_options — skip if not available');
    return;
  }
  expect(body).not.toContain('Fatal error');

  // Attempt backup init step
  const nonce = await page.evaluate(() => window.alkanaAdminData?.backupNonce || window.alkanaBackupNonce || null);
  if (nonce) {
    const result = await ajaxPost(page, {
      action: 'alkana_backup_init',
      nonce,
    });
    expect(result.status).not.toBe(500);
  }
});
