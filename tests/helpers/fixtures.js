// tests/helpers/fixtures.js
// Test constants, XSS payloads, SQL injection strings, test data
'use strict';

const BASE = 'http://localhost/alkana-wp';
const ADMIN_AJAX = `${BASE}/wp-admin/admin-ajax.php`;

// ─── Test account credentials ──────────────────────────────────────────────────
const CREDENTIALS = {
  admin: {
    user: process.env.WP_ADMIN_USER || 'admin',
    pass: process.env.WP_ADMIN_PASS || 'admin',
  },
  contentEditor: {
    user: process.env.WP_CONTENT_EDITOR_USER || 'content_editor',
    pass: process.env.WP_CONTENT_EDITOR_PASS || 'content_editor',
  },
  techEditor: {
    user: process.env.WP_TECH_EDITOR_USER || 'tech_editor',
    pass: process.env.WP_TECH_EDITOR_PASS || 'tech_editor',
  },
};

// ─── Security payloads ────────────────────────────────────────────────────────
const XSS_PAYLOADS = [
  '<script>alert(1)</script>',
  '"><svg/onload=alert(1)>',
  "';alert('xss');//",
  '<img src=x onerror=alert(1)>',
  'javascript:alert(1)',
];

const SQL_INJECTION_PAYLOADS = [
  "' OR 1=1 --",
  '" OR ""="',
  "1; DROP TABLE wp_posts; --",
  "' UNION SELECT * FROM wp_users --",
];

const PATH_TRAVERSAL_PAYLOADS = [
  '../../etc/passwd',
  '../wp-config.php',
  '....//....//etc/passwd',
];

// ─── Test data prefixes (for cleanup) ────────────────────────────────────────
const TEST_PREFIX = '__test_';

// ─── Vietnamese test data ─────────────────────────────────────────────────────
const VIETNAMESE_NAME = 'Nguyễn Thị Phượng Hoàng';
const VIETNAMESE_EMAIL = 'nguyen.test@example.com';
const VIETNAMESE_MESSAGE = 'Xin chào, tôi muốn hỏi về sản phẩm sơn lót.';

module.exports = {
  BASE,
  ADMIN_AJAX,
  CREDENTIALS,
  XSS_PAYLOADS,
  SQL_INJECTION_PAYLOADS,
  PATH_TRAVERSAL_PAYLOADS,
  TEST_PREFIX,
  VIETNAMESE_NAME,
  VIETNAMESE_EMAIL,
  VIETNAMESE_MESSAGE,
};
