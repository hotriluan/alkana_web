<?php
/**
 * Alkana Backup Toolkit — WP-Cron Scheduled Backup
 *
 * Registers cron hook for automated backups (daily/weekly).
 * Settings stored in wp_options: alkana_backup_schedule, alkana_backup_keep.
 *
 * @package Alkana
 */

defined('ABSPATH') || exit;

// ── Register Cron Hook ─────────────────────────────────────────────────────────
add_action('alkana_scheduled_backup', 'alkana_cron_run_backup');

/**
 * Execute scheduled backup via WP-Cron.
 */
function alkana_cron_run_backup(): void
{
    // Load toolkit
    $candidates = [
        ABSPATH . 'scripts/alkana-toolkit.php',
        ABSPATH . '../scripts/alkana-toolkit.php',
        dirname(ABSPATH) . '/scripts/alkana-toolkit.php',
    ];
    $toolkitPath = null;
    foreach ($candidates as $path) {
        if (file_exists($path)) {
            $toolkitPath = $path;
            break;
        }
    }

    if ($toolkitPath === null) {
        error_log('[Alkana Backup] Toolkit not found — skipping scheduled backup');
        return;
    }

    if (!class_exists('AlkanaToolkit')) {
        require_once $toolkitPath;
    }

    $dbConfig = [
        'host'   => DB_HOST,
        'name'   => DB_NAME,
        'user'   => DB_USER,
        'pass'   => DB_PASSWORD,
        'prefix' => $GLOBALS['table_prefix'] ?? 'wp_',
    ];

    try {
        $toolkit = new AlkanaToolkit($dbConfig);
        $result  = $toolkit->backup('full');

        $keep = (int) get_option('alkana_backup_keep', 5);
        $toolkit->rotateBackups(
            defined('ALKANA_BACKUP_DIR') ? ALKANA_BACKUP_DIR : ABSPATH . '../backups',
            $keep
        );

        error_log('[Alkana Backup] Scheduled backup complete: ' . $result['file']);
    } catch (\Throwable $e) {
        error_log('[Alkana Backup] Scheduled backup failed: ' . $e->getMessage());
    }
}

// ── Reschedule on Settings Change ──────────────────────────────────────────────
add_action('init', 'alkana_backup_maybe_schedule', 20);

/**
 * Ensure cron is scheduled according to saved settings.
 */
function alkana_backup_maybe_schedule(): void
{
    $frequency = get_option('alkana_backup_schedule', 'off');
    $hook      = 'alkana_scheduled_backup';
    $scheduled = wp_next_scheduled($hook);

    if ($frequency === 'off' && $scheduled) {
        wp_clear_scheduled_hook($hook);
    } elseif ($frequency !== 'off' && !$scheduled) {
        wp_schedule_event(time() + 3600, $frequency, $hook);
    }
}
