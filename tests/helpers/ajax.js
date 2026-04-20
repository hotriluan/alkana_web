// tests/helpers/ajax.js
// AJAX request helpers for admin-ajax.php calls
'use strict';

const { ADMIN_AJAX } = require('./fixtures');

/**
 * Send an AJAX POST to admin-ajax.php from the browser context.
 * Returns parsed JSON or null on failure.
 */
async function ajaxPost(page, params = {}) {
  return page.evaluate(async ({ url, params }) => {
    const form = new URLSearchParams();
    for (const [k, v] of Object.entries(params)) {
      if (Array.isArray(v)) {
        v.forEach((item) => form.append(`${k}[]`, item));
      } else {
        form.append(k, v);
      }
    }
    try {
      const r = await fetch(url, {
        method: 'POST',
        body: form,
        credentials: 'include',
      });
      const text = await r.text();
      try {
        return { status: r.status, data: JSON.parse(text) };
      } catch {
        return { status: r.status, data: text };
      }
    } catch (err) {
      return { status: 0, data: null, error: err.message };
    }
  }, { url: ADMIN_AJAX, params });
}

/**
 * Get nonce from page meta or inline script.
 * Common WP pattern: alkanaData.nonce
 */
async function getPageNonce(page, key = 'alkanaData') {
  return page.evaluate((key) => {
    return window[key]?.nonce || null;
  }, key);
}

module.exports = { ajaxPost, getPageNonce };
