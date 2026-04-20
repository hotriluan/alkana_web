<?php
/**
 * PHPUnit bootstrap for Alkana Toolkit tests.
 * Loads the toolkit classes without executing the CLI entry point.
 */

declare(strict_types=1);

// Suppress CLI execution guard
define('ALKANA_TOOLKIT_TEST_MODE', true);

// Load toolkit (guarded below by ALKANA_TOOLKIT_TEST_MODE check patch)
$toolkitFile = __DIR__ . '/../../scripts/alkana-toolkit.php';
if (!file_exists($toolkitFile)) {
    throw new \RuntimeException("alkana-toolkit.php not found at: $toolkitFile");
}

// Evaluate the file, skipping the CLI entry block by injecting a fake argv[0]
// The CLI guard is: if (php_sapi_name() === 'cli' && realpath($argv[0]) === realpath(__FILE__))
// Since $argv[0] won't match __FILE__ during PHPUnit, the guard won't fire.
require_once $toolkitFile;
