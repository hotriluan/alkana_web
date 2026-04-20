// tests/helpers/auth.js
// Login helpers for admin, content_editor, tech_editor
'use strict';

const { CREDENTIALS, BASE } = require('./fixtures');

/**
 * Log in as a WordPress user via the login form.
 * Returns the page after successful login.
 */
async function loginAs(page, role = 'admin') {
  const creds = CREDENTIALS[role];
  if (!creds) throw new Error(`Unknown role: ${role}`);

  await page.goto(`${BASE}/wp-login.php`, { waitUntil: 'domcontentloaded' });
  await page.fill('#user_login', creds.user);
  await page.fill('#user_pass', creds.pass);
  await page.click('#wp-submit');
  // Wait for redirect away from login page
  await page.waitForURL((url) => !url.pathname.endsWith('wp-login.php'), { timeout: 10000 });
  return page;
}

/**
 * Log out the current user.
 */
async function logout(page) {
  await page.goto(`${BASE}/wp-login.php?action=logout`, { waitUntil: 'domcontentloaded' });
  const confirmLink = page.locator('a[href*="action=logout"]');
  if (await confirmLink.isVisible({ timeout: 2000 }).catch(() => false)) {
    await confirmLink.click();
    await page.waitForLoadState('domcontentloaded');
  }
}

/**
 * Get a WordPress nonce for a given action (requires admin session).
 */
async function getNonce(page, action) {
  const resp = await page.evaluate(async ({ base, action }) => {
    const form = new URLSearchParams();
    form.append('action', 'alkana_get_nonce');
    form.append('nonce_action', action);
    const r = await fetch(`${base}/wp-admin/admin-ajax.php`, {
      method: 'POST',
      body: form,
      credentials: 'include',
    });
    return r.json().catch(() => null);
  }, { base: BASE, action });
  return resp?.nonce || null;
}

module.exports = { loginAs, logout, getNonce };
