// tests/helpers/test-utils.js
// Shared utilities: skipUnlessCI, softAssertPerf, TEST_PREFIX
'use strict';

const { test } = require('@playwright/test');

const TEST_PREFIX = '__test_';

/**
 * Skip on local dev; hard-fail on CI.
 * Use when seeded data or specific env is required.
 */
function skipUnlessCI(reason = 'Requires seeded data / CI environment') {
  if (!process.env.CI) {
    test.skip(true, `[DEV SKIP] ${reason}`);
  }
  // On CI, do nothing — test runs and must pass
}

/**
 * Soft performance assertion.
 * Logs a warning if duration > warnMs. Hard fails only if > failMs.
 */
function softAssertPerf(durationMs, warnMs = 500, failMs = 10000, label = 'operation') {
  if (durationMs > failMs) {
    throw new Error(`[PERF HARD FAIL] ${label} took ${durationMs}ms (limit: ${failMs}ms)`);
  }
  if (durationMs > warnMs) {
    console.warn(`[PERF WARN] ${label} took ${durationMs}ms (warn threshold: ${warnMs}ms)`);
  }
}

/**
 * Measure async operation duration in milliseconds.
 */
async function measureMs(fn) {
  const start = Date.now();
  await fn();
  return Date.now() - start;
}

module.exports = { TEST_PREFIX, skipUnlessCI, softAssertPerf, measureMs };
