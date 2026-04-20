// tests/global-teardown.js
// Global teardown: cleanup __test_ prefixed entities (dev) or restore DB (CI)
'use strict';

const IS_CI = !!process.env.CI;
const IS_DOCKER = process.env.DOCKER_SEEDED === 'true';

module.exports = async function globalTeardown() {
  if (IS_CI && IS_DOCKER) {
    // Docker volumes are removed by `docker compose down -v` in the CI workflow.
    // No DB restore needed — the next run starts with a fresh volume + seed SQL.
    console.log('[global-teardown] Docker CI mode — volumes cleaned by docker compose down -v.');
  } else if (IS_CI) {
    console.log('[global-teardown] CI mode — DB restore would run here (implement with mysqldump restore).');
    // Example: execSync('mysql -u root alkana_wp < /tmp/alkana_snapshot.sql');
  } else {
    console.log('[global-teardown] Dev mode — cleanup of __test_ prefixed data skipped.');
    // In a real implementation: WP-CLI or direct DB query to remove TEST_PREFIX entities
  }
};
