// tests/global-setup.js
// Global setup: health check, optional DB snapshot for CI, seeded data verify
'use strict';

const { request } = require('@playwright/test');

const BASE = process.env.BASE_URL || 'http://localhost/alkana-wp';
const IS_CI = !!process.env.CI;

module.exports = async function globalSetup() {
  // ── 1. Health check ──────────────────────────────────────────────────────
  const ctx = await request.newContext({ baseURL: BASE });
  try {
    const resp = await ctx.get('/');
    if (resp.status() >= 500) {
      throw new Error(`WordPress returned ${resp.status()} — is WP running at ${BASE}?`);
    }
    console.log(`[global-setup] WordPress reachable: ${resp.status()}`);
  } catch (err) {
    console.error(`[global-setup] FATAL: ${err.message}`);
    throw err;
  } finally {
    await ctx.dispose();
  }

  // ── 2. CI: create DB snapshot ────────────────────────────────────────────
  if (IS_CI) {
    // When DOCKER_SEEDED=true the Docker container already has seed data loaded
    // via docker-entrypoint-initdb.d/alkana-seed.sql + wpcli search-replace.
    // Skip the PHP seed script entirely — it would overwrite Docker's seeded data
    // and attempt to connect to a local XAMPP DB that doesn't exist in CI.
    if (process.env.DOCKER_SEEDED === 'true') {
      console.log('[global-setup] DOCKER_SEEDED=true — skipping PHP seed, using Docker seed SQL.');
    } else {
      console.log('[global-setup] CI mode — creating DB snapshot...');
      const { execSync } = require('child_process');
      try {
        execSync('php scripts/seed-dummy-data.php --ci', {
          cwd: process.cwd(),
          stdio: 'inherit',
          timeout: 60000,
        });
        console.log('[global-setup] Seed data loaded.');
      } catch (err) {
        console.warn(`[global-setup] Seed script failed (non-fatal): ${err.message}`);
      }
    }
  } else {
    console.log('[global-setup] Dev mode — skipping seed, using existing WP data.');
  }
};
