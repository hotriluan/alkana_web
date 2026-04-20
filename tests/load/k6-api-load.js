// tests/load/k6-api-load.js
// Alkana Web — k6 Load Test: Critical AJAX Endpoints
// Run: k6 run tests/load/k6-api-load.js --env BASE_URL=http://localhost:8080
import http from 'k6/http';
import { check, sleep } from 'k6';

const BASE_URL = __ENV.BASE_URL || 'http://localhost:8080';
const AJAX_URL = `${BASE_URL}/wp-admin/admin-ajax.php`;
const NONCE_ENDPOINT = `${BASE_URL}/wp-json/alkana/v1/test-nonce`;

export const options = {
  stages: [
    { duration: '30s', target: 10  }, // ramp-up
    { duration: '60s', target: 50  }, // sustained load
    { duration: '30s', target: 100 }, // stress spike
    { duration: '30s', target: 0   }, // ramp-down
  ],
  thresholds: {
    http_req_duration: ['p(95)<2000'], // 95th percentile < 2s
    http_req_failed:   ['rate<0.05'],  // < 5% error rate
  },
};

/**
 * setup() runs once before VU iterations.
 * Calls /wp-json/alkana/v1/test-nonce (active only when WP_DEBUG=true in Docker).
 * Returns deterministic JSON — no HTML parsing, no DOM scraping.
 */
export function setup() {
  const res = http.get(NONCE_ENDPOINT);

  if (res.status !== 200) {
    console.error(
      `[setup] Failed to get nonces from ${NONCE_ENDPOINT}: ` +
      `status=${res.status}. Ensure WP_DEBUG=true in Docker WORDPRESS_CONFIG_EXTRA.`
    );
    return { filter_nonce: '', search_nonce: '', contact_nonce: '' };
  }

  let nonces;
  try {
    nonces = JSON.parse(res.body);
  } catch (e) {
    console.error(`[setup] Failed to parse nonce response: ${e.message}`);
    return { filter_nonce: '', search_nonce: '', contact_nonce: '' };
  }

  console.log('[setup] Nonces acquired successfully.');
  return {
    filter_nonce:  nonces.filter_nonce  || '',
    search_nonce:  nonces.search_nonce  || '',
    contact_nonce: nonces.contact_nonce || '',
  };
}

/**
 * Default function — runs per VU per iteration.
 * @param {object} data - returned from setup()
 */
export default function (data) {
  const { filter_nonce, search_nonce, contact_nonce } = data;

  const ajaxHeaders = {
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'X-Requested-With': 'XMLHttpRequest',
    },
  };

  // ── Endpoint 1: Product Filter ─────────────────────────────────────────────
  const filterBody = new URLSearchParams({
    action: 'alkana_filter_products',
    nonce: filter_nonce,
    page: '1',
    sort: 'latest',
  }).toString();

  const filterRes = http.post(AJAX_URL, filterBody, ajaxHeaders);

  check(filterRes, {
    'filter_products: status 200':   (r) => r.status === 200,
    'filter_products: success true': (r) => {
      try { return JSON.parse(r.body).success === true; } catch { return false; }
    },
  });

  sleep(0.5);

  // ── Endpoint 2: Search ─────────────────────────────────────────────────────
  const searchRes = http.get(
    `${AJAX_URL}?action=alkana_search&nonce=${search_nonce}&q=alkana`,
    { headers: { 'X-Requested-With': 'XMLHttpRequest' } }
  );

  check(searchRes, {
    'search: status 200':  (r) => r.status === 200,
    'search: has results': (r) => {
      try { return 'results' in JSON.parse(r.body); } catch { return false; }
    },
  });

  sleep(0.5);

  // ── Endpoint 3: Contact Form Submit ────────────────────────────────────────
  const contactBody = new URLSearchParams({
    action: 'alkana_submit_contact',
    _alkana_nonce: contact_nonce,
    name: 'k6 Load Test',
    email: 'k6-loadtest@example.com',
    phone: '0900000000',
    message: 'Load test submission — automated, please ignore.',
  }).toString();

  const contactRes = http.post(AJAX_URL, contactBody, ajaxHeaders);

  check(contactRes, {
    'contact: status 200':            (r) => r.status === 200,
    // 429 = rate limiter fired without banning the IP (expected at 100 VU spike)
    // 403 = WAF/IP ban — investigate immediately on first run
    'contact: not rate-banned (429)': (r) => r.status !== 429,
    'contact: not waf-blocked (403)': (r) => r.status !== 403,
    // Rate limiter returns success: false with HTTP 200 — that is acceptable
    'contact: JSON body': (r) => {
      try { JSON.parse(r.body); return true; } catch { return false; }
    },
  });

  // First-run monitoring: log all non-200 contact responses with VU/iteration context.
  // After the first run, inspect k6-results.json for 429/403 counts:
  //   jq '.metrics.http_req_failed | .values' k6-results.json
  //   jq '[.[] | select(.metric=="checks" and (.data.tags.check | test("429|403")))]' k6-results.json
  if (contactRes.status !== 200) {
    console.warn(
      `[contact] Non-200 response: status=${contactRes.status}. ` +
      `VU=${__VU} iter=${__ITER}. ` +
      `If 429 — rate limiter (expected). If 403 — WAF/ban (investigate).`
    );
  }

  sleep(1);
}
