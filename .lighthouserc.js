// .lighthouserc.js
// Lighthouse CI configuration for Alkana Web
// Docs: https://github.com/GoogleChrome/lighthouse-ci/blob/main/docs/configuration.md
'use strict';

module.exports = {
  ci: {
    collect: {
      // Number of Lighthouse runs per URL (median result used for assertions)
      numberOfRuns: 3,
      settings: {
        // Simulate mobile throttling — matches real-world conditions
        formFactor: 'mobile',
        throttlingMethod: 'simulate',
        disableStorageReset: false,
      },
    },

    assert: {
      assertions: {
        // HARD gate — CI fails if performance score < 0.9
        'categories:performance': ['error', { minScore: 0.9 }],

        // HARD gate — CI fails if accessibility score < 0.9
        // Phase 5 color-contrast fix ensures this threshold is met.
        'categories:accessibility': ['error', { minScore: 0.9 }],

        // WARN only — does not fail the build
        'categories:best-practices': ['warn', { minScore: 0.85 }],

        // WARN only
        'categories:seo': ['warn', { minScore: 0.85 }],
      },
    },

    upload: {
      // Free temporary public storage — LHCI report link posted as GitHub commit status
      // Reports retained for ~30 days
      target: 'temporary-public-storage',
    },
  },
};
