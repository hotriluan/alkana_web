<?php
/**
 * Alkana Backup Toolkit — WP Admin Page + AJAX Handlers
 *
 * Registers admin menu, renders backup GUI, handles AJAX endpoints.
 * 4-step AJAX chain for safe backup (no single-request timeout).
 *
 * @package Alkana
 */

defined('ABSPATH') || exit;

// ── Admin Menu ─────────────────────────────────────────────────────────────────
add_action('admin_menu', 'alkana_backup_register_menu', 20);

function alkana_backup_register_menu(): void
{
    add_submenu_page(
        'alkana-settings',
        __('Backup Toolkit', 'alkana'),
        __('Backup Toolkit', 'alkana'),
        'manage_options',
        'alkana-backup',
        'alkana_backup_render_page'
    );
}

// ── AJAX Handlers ──────────────────────────────────────────────────────────────
add_action('wp_ajax_alkana_backup_step1', 'alkana_backup_ajax_step1');
add_action('wp_ajax_alkana_backup_step2', 'alkana_backup_ajax_step2');
add_action('wp_ajax_alkana_backup_step3', 'alkana_backup_ajax_step3');
add_action('wp_ajax_alkana_backup_step4', 'alkana_backup_ajax_step4');
add_action('wp_ajax_alkana_download_backup', 'alkana_backup_ajax_download');
add_action('wp_ajax_alkana_delete_backup', 'alkana_backup_ajax_delete');
add_action('wp_ajax_alkana_save_schedule', 'alkana_backup_ajax_save_schedule');

/**
 * Load the toolkit engine.
 */
function alkana_backup_get_toolkit(): AlkanaToolkit
{
    $toolkitPath = ABSPATH . '../scripts/alkana-toolkit.php';
    if (!file_exists($toolkitPath)) {
        $toolkitPath = get_template_directory() . '/../../../../scripts/alkana-toolkit.php';
    }
    // Try relative from ABSPATH (scripts/ is sibling of wp root)
    $candidates = [
        ABSPATH . 'scripts/alkana-toolkit.php',
        ABSPATH . '../scripts/alkana-toolkit.php',
        dirname(ABSPATH) . '/scripts/alkana-toolkit.php',
    ];
    foreach ($candidates as $path) {
        if (file_exists($path)) {
            $toolkitPath = $path;
            break;
        }
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

    return new AlkanaToolkit($dbConfig);
}

/**
 * Step 1: Initialize backup — create temp dir, check env.
 */
function alkana_backup_ajax_step1(): void
{
    check_ajax_referer('alkana_backup_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permission denied'], 403);
    }

    // Atomic lock — set then verify ownership to prevent TOCTOU race
    $sessionId = bin2hex(random_bytes(8));
    $existingLock = get_transient('alkana_backup_lock');
    if ($existingLock !== false) {
        wp_send_json_error(['message' => 'Backup already in progress', 'step' => 1]);
    }
    set_transient('alkana_backup_lock', $sessionId, 1800);
    if (get_transient('alkana_backup_lock') !== $sessionId) {
        wp_send_json_error(['message' => 'Backup already in progress', 'step' => 1]);
    }

    $mode = sanitize_text_field($_POST['mode'] ?? 'full');
    if (!in_array($mode, ['full', 'db', 'files'], true)) {
        $mode = 'full';
    }

    // Store session info
    set_transient('alkana_backup_session', [
        'id'   => $sessionId,
        'mode' => $mode,
        'started' => time(),
    ], 1800);

    wp_send_json_success([
        'step'    => 1,
        'message' => 'Backup initialized',
        'session' => $sessionId,
        'mode'    => $mode,
    ]);
}

/**
 * Step 2: Database export.
 */
function alkana_backup_ajax_step2(): void
{
    check_ajax_referer('alkana_backup_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permission denied'], 403);
    }

    $session = get_transient('alkana_backup_session');
    if (!$session) {
        wp_send_json_error(['message' => 'No active backup session', 'step' => 2]);
    }
    $postedSession = sanitize_text_field($_POST['session'] ?? '');
    if ($session['id'] !== $postedSession) {
        wp_send_json_error(['message' => 'Session mismatch', 'step' => 2]);
    }

    try {
        $toolkit = alkana_backup_get_toolkit();
        $backupDir = defined('ALKANA_BACKUP_DIR') ? ALKANA_BACKUP_DIR : ABSPATH . '../backups';

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $tmpDir = $backupDir . '/alkana_tmp_' . $session['id'];
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
            file_put_contents($tmpDir . '/.htaccess', "Require all denied\n");
        }

        // Update session with tmpDir
        $session['tmpDir'] = $tmpDir;
        set_transient('alkana_backup_session', $session, 1800);

        if (in_array($session['mode'], ['full', 'db'], true)) {
            $dbHandler = new AlkanaDatabaseHandler(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD, $GLOBALS['table_prefix'] ?? 'wp_');
            $dbHandler->export($tmpDir . '/database.sql');
            $dbSize = file_exists($tmpDir . '/database.sql') ? filesize($tmpDir . '/database.sql') : 0;
            wp_send_json_success([
                'step'    => 2,
                'message' => 'Database exported (' . AlkanaToolkit::humanFileSize($dbSize) . ')',
            ]);
        } else {
            wp_send_json_success([
                'step'    => 2,
                'message' => 'Database export skipped (files-only mode)',
            ]);
        }
    } catch (\Throwable $e) {
        delete_transient('alkana_backup_lock');
        delete_transient('alkana_backup_session');
        error_log('[Alkana Backup] DB export failed: ' . $e->getMessage());
        wp_send_json_error(['message' => 'Database export failed. Check server logs.', 'step' => 2]);
    }
}

/**
 * Step 3: File compression.
 */
function alkana_backup_ajax_step3(): void
{
    check_ajax_referer('alkana_backup_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permission denied'], 403);
    }

    $session = get_transient('alkana_backup_session');
    if (!$session || !isset($session['tmpDir'])) {
        wp_send_json_error(['message' => 'No active backup session', 'step' => 3]);
    }
    $postedSession = sanitize_text_field($_POST['session'] ?? '');
    if ($session['id'] !== $postedSession) {
        wp_send_json_error(['message' => 'Session mismatch', 'step' => 3]);
    }

    try {
        $tmpDir = $session['tmpDir'];

        if (in_array($session['mode'], ['full', 'files'], true)) {
            $wpContentDir = ABSPATH . 'wp-content';
            $excludes = defined('ALKANA_EXCLUDE_PATTERNS') ? ALKANA_EXCLUDE_PATTERNS : [
                'node_modules', '.git', 'src/', '.venv', '*.log',
            ];
            AlkanaFileArchiver::createArchive($wpContentDir, $tmpDir . '/wp-content.zip', $excludes);
            $zipSize = file_exists($tmpDir . '/wp-content.zip') ? filesize($tmpDir . '/wp-content.zip') : 0;
            wp_send_json_success([
                'step'    => 3,
                'message' => 'Files compressed (' . AlkanaToolkit::humanFileSize($zipSize) . ')',
            ]);
        } else {
            wp_send_json_success([
                'step'    => 3,
                'message' => 'File compression skipped (db-only mode)',
            ]);
        }
    } catch (\Throwable $e) {
        delete_transient('alkana_backup_lock');
        delete_transient('alkana_backup_session');
        error_log('[Alkana Backup] File compression failed: ' . $e->getMessage());
        wp_send_json_error(['message' => 'File compression failed. Check server logs.', 'step' => 3]);
    }
}

/**
 * Step 4: Manifest + finalize ZIP + cleanup + rotation.
 */
function alkana_backup_ajax_step4(): void
{
    check_ajax_referer('alkana_backup_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permission denied'], 403);
    }

    $session = get_transient('alkana_backup_session');
    if (!$session || !isset($session['tmpDir'])) {
        wp_send_json_error(['message' => 'No active backup session', 'step' => 4]);
    }
    $postedSession = sanitize_text_field($_POST['session'] ?? '');
    if ($session['id'] !== $postedSession) {
        wp_send_json_error(['message' => 'Session mismatch', 'step' => 4]);
    }

    try {
        $tmpDir    = $session['tmpDir'];
        $backupDir = dirname($tmpDir);
        $toolkit   = alkana_backup_get_toolkit();

        // Generate manifest
        $manifest = [
            'backup_date'    => gmdate('Y-m-d\TH:i:s\Z'),
            'backup_mode'    => $session['mode'],
            'site_url'       => home_url(),
            'wp_version'     => get_bloginfo('version'),
            'php_version'    => PHP_VERSION,
            'plugins'        => alkana_backup_get_plugin_list(),
            'checksums'      => AlkanaFileArchiver::generateChecksums($tmpDir),
        ];
        file_put_contents($tmpDir . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // Package final ZIP
        $timestamp = date('Ymd-His');
        $filename  = "alkana-{$session['mode']}-{$timestamp}.zip";
        $finalZip  = $backupDir . '/' . $filename;

        AlkanaFileArchiver::createArchive($tmpDir, $finalZip, []);

        // Rotation
        $keep = (int) get_option('alkana_backup_keep', 5);
        $toolkit->rotateBackups($backupDir, $keep);

        // Cleanup
        AlkanaToolkit::deleteDir($tmpDir);
        delete_transient('alkana_backup_lock');
        delete_transient('alkana_backup_session');

        $fileSize = file_exists($finalZip) ? filesize($finalZip) : 0;

        wp_send_json_success([
            'step'     => 4,
            'message'  => 'Backup complete!',
            'file'     => $filename,
            'size'     => AlkanaToolkit::humanFileSize($fileSize),
            'plugins'  => count($manifest['plugins']),
        ]);
    } catch (\Throwable $e) {
        delete_transient('alkana_backup_lock');
        delete_transient('alkana_backup_session');
        error_log('[Alkana Backup] Finalize failed: ' . $e->getMessage());
        wp_send_json_error(['message' => 'Backup finalize failed. Check server logs.', 'step' => 4]);
    }
}

/**
 * Get active plugin list for manifest.
 */
function alkana_backup_get_plugin_list(): array
{
    if (!function_exists('get_plugins')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $allPlugins    = get_plugins();
    $activePlugins = get_option('active_plugins', []);
    $list = [];

    foreach ($allPlugins as $path => $data) {
        $slug = dirname($path);
        if ($slug === '.') {
            $slug = basename($path, '.php');
        }
        $list[] = [
            'slug'    => $slug,
            'name'    => $data['Name'] ?? $slug,
            'version' => $data['Version'] ?? 'unknown',
            'active'  => in_array($path, $activePlugins, true),
        ];
    }

    return $list;
}

/**
 * Download backup file.
 */
function alkana_backup_ajax_download(): void
{
    check_ajax_referer('alkana_backup_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_die('Permission denied');
    }

    $file = sanitize_file_name($_GET['file'] ?? '');
    if (empty($file)) {
        wp_die('No file specified');
    }

    $backupDir = defined('ALKANA_BACKUP_DIR') ? ALKANA_BACKUP_DIR : ABSPATH . '../backups';
    $filePath  = $backupDir . '/' . $file;

    // Security: ensure file is in backup dir and is a ZIP
    $realBackup = realpath($backupDir);
    $realFile   = realpath($filePath);
    if ($realFile === false || !str_starts_with($realFile, $realBackup) || !str_ends_with($file, '.zip')) {
        wp_die('Invalid file');
    }

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $file . '"');
    header('Content-Length: ' . filesize($realFile));
    readfile($realFile);
    exit;
}

/**
 * Delete backup file.
 */
function alkana_backup_ajax_delete(): void
{
    check_ajax_referer('alkana_backup_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permission denied'], 403);
    }

    $file = sanitize_file_name($_POST['file'] ?? '');
    if (empty($file)) {
        wp_send_json_error(['message' => 'No file specified']);
    }

    $backupDir = defined('ALKANA_BACKUP_DIR') ? ALKANA_BACKUP_DIR : ABSPATH . '../backups';
    $filePath  = $backupDir . '/' . $file;

    // Security: path traversal check
    $realBackup = realpath($backupDir);
    $realFile   = realpath($filePath);
    if ($realFile === false || !str_starts_with($realFile, $realBackup) || !str_ends_with($file, '.zip')) {
        wp_send_json_error(['message' => 'Invalid file']);
    }

    if (@unlink($realFile)) {
        wp_send_json_success(['message' => 'Backup deleted']);
    } else {
        wp_send_json_error(['message' => 'Could not delete file']);
    }
}

/**
 * Save schedule settings.
 */
function alkana_backup_ajax_save_schedule(): void
{
    check_ajax_referer('alkana_backup_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permission denied'], 403);
    }

    $frequency = sanitize_text_field($_POST['frequency'] ?? 'off');
    $keep      = (int) ($_POST['keep'] ?? 5);

    if (!in_array($frequency, ['off', 'daily', 'weekly'], true)) {
        $frequency = 'off';
    }
    $keep = max(1, min(50, $keep));

    update_option('alkana_backup_schedule', $frequency);
    update_option('alkana_backup_keep', $keep);

    // Update cron
    $hook = 'alkana_scheduled_backup';
    wp_clear_scheduled_hook($hook);

    if ($frequency !== 'off') {
        wp_schedule_event(time() + 3600, $frequency, $hook);
    }

    $nextRun = wp_next_scheduled($hook);

    wp_send_json_success([
        'message'   => 'Schedule saved',
        'frequency' => $frequency,
        'keep'      => $keep,
        'next_run'  => $nextRun ? gmdate('Y-m-d H:i:s', $nextRun) . ' UTC' : 'Not scheduled',
    ]);
}

// ── Render Admin Page ──────────────────────────────────────────────────────────
function alkana_backup_render_page(): void
{
    if (!current_user_can('manage_options')) {
        wp_die(__('Permission denied.', 'alkana'));
    }

    $nonce     = wp_create_nonce('alkana_backup_nonce');
    $frequency = get_option('alkana_backup_schedule', 'off');
    $keep      = (int) get_option('alkana_backup_keep', 5);
    $nextRun   = wp_next_scheduled('alkana_scheduled_backup');

    // Get backup list
    $backupDir = defined('ALKANA_BACKUP_DIR') ? ALKANA_BACKUP_DIR : ABSPATH . '../backups';
    $backups   = [];
    if (is_dir($backupDir)) {
        $files = glob($backupDir . '/alkana-*.zip');
        if ($files) {
            foreach ($files as $f) {
                $backups[] = [
                    'file' => basename($f),
                    'size' => size_format(filesize($f), 1),
                    'date' => date('M j, Y H:i', filemtime($f)),
                ];
            }
            usort($backups, fn($a, $b) => strcmp($b['file'], $a['file']));
        }
    }
    ?>
    <div class="wrap">
        <h1>🔧 Alkana Backup Toolkit</h1>

        <!-- Quick Backup -->
        <div class="card" style="max-width:700px;margin-top:20px;">
            <h2>Quick Backup</h2>
            <p>
                <label for="alkana-backup-mode"><strong>Mode:</strong></label>
                <select id="alkana-backup-mode">
                    <option value="full">Full (DB + Files)</option>
                    <option value="db">Database Only</option>
                    <option value="files">Files Only</option>
                </select>
                &nbsp;
                <button id="alkana-backup-btn" class="button button-primary">🔄 Backup Now</button>
            </p>
            <div id="alkana-backup-progress" style="display:none;margin-top:15px;">
                <div style="background:#f0f0f0;border-radius:4px;overflow:hidden;height:24px;">
                    <div id="alkana-progress-bar" style="background:#0073aa;height:100%;width:0%;transition:width 0.3s;border-radius:4px;"></div>
                </div>
                <p id="alkana-progress-text" style="margin-top:8px;color:#666;"></p>
            </div>
            <div id="alkana-backup-result" style="display:none;margin-top:15px;padding:10px;border-radius:4px;"></div>
        </div>

        <!-- Schedule -->
        <div class="card" style="max-width:700px;margin-top:20px;">
            <h2>Schedule</h2>
            <p>
                <label><strong>Frequency:</strong></label>
                <select id="alkana-schedule-freq">
                    <option value="off" <?php selected($frequency, 'off'); ?>>Off</option>
                    <option value="daily" <?php selected($frequency, 'daily'); ?>>Daily</option>
                    <option value="weekly" <?php selected($frequency, 'weekly'); ?>>Weekly</option>
                </select>
                &nbsp;
                <label><strong>Keep:</strong></label>
                <select id="alkana-schedule-keep">
                    <?php foreach ([3, 5, 10, 20] as $n): ?>
                        <option value="<?php echo $n; ?>" <?php selected($keep, $n); ?>><?php echo $n; ?> backups</option>
                    <?php endforeach; ?>
                </select>
                &nbsp;
                <button id="alkana-save-schedule" class="button">💾 Save Schedule</button>
            </p>
            <p id="alkana-next-run" style="color:#666;">
                <?php if ($nextRun): ?>
                    Next run: <?php echo esc_html(gmdate('M j, Y H:i', $nextRun)); ?> UTC
                <?php else: ?>
                    Not scheduled
                <?php endif; ?>
            </p>
        </div>

        <!-- Backup History -->
        <div class="card" style="max-width:700px;margin-top:20px;">
            <h2>Backup History</h2>
            <?php if (empty($backups)): ?>
                <p style="color:#999;">No backups found.</p>
            <?php else: ?>
                <table class="widefat striped" style="max-width:100%;">
                    <thead>
                        <tr>
                            <th>File</th>
                            <th>Size</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="alkana-backup-list">
                        <?php foreach ($backups as $b): ?>
                            <tr data-file="<?php echo esc_attr($b['file']); ?>">
                                <td><code><?php echo esc_html($b['file']); ?></code></td>
                                <td><?php echo esc_html($b['size']); ?></td>
                                <td><?php echo esc_html($b['date']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin-ajax.php?action=alkana_download_backup&file=' . urlencode($b['file']) . '&nonce=' . $nonce)); ?>" class="button button-small">⬇ Download</a>
                                    <button class="button button-small alkana-delete-btn" data-file="<?php echo esc_attr($b['file']); ?>">✕ Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <script>
    (function() {
        const nonce = <?php echo wp_json_encode($nonce); ?>;
        const ajaxUrl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;

        // ── Backup Flow ──
        const backupBtn = document.getElementById('alkana-backup-btn');
        const progressWrap = document.getElementById('alkana-backup-progress');
        const progressBar = document.getElementById('alkana-progress-bar');
        const progressText = document.getElementById('alkana-progress-text');
        const resultDiv = document.getElementById('alkana-backup-result');

        if (backupBtn) {
            backupBtn.addEventListener('click', async function() {
                const mode = document.getElementById('alkana-backup-mode').value;
                backupBtn.disabled = true;
                progressWrap.style.display = 'block';
                resultDiv.style.display = 'none';

                const steps = [
                    { action: 'alkana_backup_step1', label: 'Initializing...' },
                    { action: 'alkana_backup_step2', label: 'Exporting database...' },
                    { action: 'alkana_backup_step3', label: 'Compressing files...' },
                    { action: 'alkana_backup_step4', label: 'Finalizing backup...' },
                ];

                let sessionId = '';

                for (let i = 0; i < steps.length; i++) {
                    const pct = ((i) / steps.length) * 100;
                    progressBar.style.width = pct + '%';
                    progressText.textContent = 'Step ' + (i + 1) + '/4: ' + steps[i].label;

                    try {
                        const fd = new FormData();
                        fd.append('action', steps[i].action);
                        fd.append('nonce', nonce);
                        fd.append('mode', mode);
                        if (sessionId) {
                            fd.append('session', sessionId);
                        }

                        const resp = await fetch(ajaxUrl, { method: 'POST', body: fd });
                        const json = await resp.json();

                        if (!json.success) {
                            throw new Error(json.data?.message || 'Unknown error at step ' + (i + 1));
                        }

                        // Capture session ID from step 1
                        if (i === 0 && json.data?.session) {
                            sessionId = json.data.session;
                        }

                        progressText.textContent = 'Step ' + (i + 1) + '/4: ' + (json.data?.message || 'Done');
                    } catch (err) {
                        progressBar.style.width = '100%';
                        progressBar.style.background = '#dc3232';
                        progressText.textContent = '❌ ' + err.message;
                        resultDiv.style.display = 'block';
                        resultDiv.style.background = '#fef1f1';
                        resultDiv.style.borderLeft = '4px solid #dc3232';
                        resultDiv.textContent = 'Backup failed at step ' + (i + 1) + ': ' + err.message;
                        backupBtn.disabled = false;
                        return;
                    }
                }

                progressBar.style.width = '100%';
                progressBar.style.background = '#46b450';
                progressText.textContent = 'Backup complete!';
                resultDiv.style.display = 'block';
                resultDiv.style.background = '#ecf7ed';
                resultDiv.style.borderLeft = '4px solid #46b450';
                resultDiv.textContent = '✅ Backup created successfully. Refresh the page to see it in the history.';
                backupBtn.disabled = false;
            });
        }

        // ── Save Schedule ──
        const saveBtn = document.getElementById('alkana-save-schedule');
        if (saveBtn) {
            saveBtn.addEventListener('click', async function() {
                const fd = new FormData();
                fd.append('action', 'alkana_save_schedule');
                fd.append('nonce', nonce);
                fd.append('frequency', document.getElementById('alkana-schedule-freq').value);
                fd.append('keep', document.getElementById('alkana-schedule-keep').value);

                try {
                    const resp = await fetch(ajaxUrl, { method: 'POST', body: fd });
                    const json = await resp.json();
                    if (json.success) {
                        document.getElementById('alkana-next-run').textContent = 'Next run: ' + (json.data.next_run || 'Not scheduled');
                        alert('Schedule saved!');
                    } else {
                        alert('Error: ' + (json.data?.message || 'Unknown'));
                    }
                } catch (err) {
                    alert('Request failed: ' + err.message);
                }
            });
        }

        // ── Delete Backup ──
        document.querySelectorAll('.alkana-delete-btn').forEach(btn => {
            btn.addEventListener('click', async function() {
                const file = this.dataset.file;
                if (!confirm('Delete backup: ' + file + '?')) return;

                const fd = new FormData();
                fd.append('action', 'alkana_delete_backup');
                fd.append('nonce', nonce);
                fd.append('file', file);

                try {
                    const resp = await fetch(ajaxUrl, { method: 'POST', body: fd });
                    const json = await resp.json();
                    if (json.success) {
                        const row = document.querySelector('tr[data-file="' + CSS.escape(file) + '"]');
                        if (row) row.remove();
                    } else {
                        alert('Error: ' + (json.data?.message || 'Unknown'));
                    }
                } catch (err) {
                    alert('Request failed: ' + err.message);
                }
            });
        });
    })();
    </script>
    <?php
}
