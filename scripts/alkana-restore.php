<?php
/**
 * Alkana Standalone Restore Tool
 *
 * Self-contained: works WITHOUT WordPress installed.
 * Upload to server, open in browser or run via CLI.
 *
 * Flexible lock: renames to .lock after 24h or 3 successful restores.
 *
 * @requires PHP 8.1+, ZipArchive, PDO
 */

declare(strict_types=1);

// ── Embedded AlkanaSerializer ──────────────────────────────────────────────────
class AlkanaRestoreSerializer
{
    public static function isSerialized(mixed $data): bool
    {
        if (!is_string($data) || $data === '') {
            return false;
        }
        $data = trim($data);
        if ($data === 'N;') {
            return true;
        }
        if (preg_match('/^[aOC]:\d+:/s', $data) || preg_match('/^[sibdN]/', $data)) {
            $test = @unserialize($data, ['allowed_classes' => false]);
            return $test !== false || $data === 'b:0;';
        }
        return false;
    }

    public static function recursiveReplace(mixed $data, string $search, string $replace): mixed
    {
        if (is_string($data)) {
            if (self::isSerialized($data)) {
                $unserialized = @unserialize($data);
                if ($unserialized !== false || $data === 'b:0;') {
                    $replaced = self::recursiveReplace($unserialized, $search, $replace);
                    return serialize($replaced);
                }
            }
            return str_replace($search, $replace, $data);
        }
        if (is_array($data)) {
            $result = [];
            foreach ($data as $key => $value) {
                $newKey = is_string($key) ? str_replace($search, $replace, $key) : $key;
                $result[$newKey] = self::recursiveReplace($value, $search, $replace);
            }
            return $result;
        }
        if (is_object($data)) {
            foreach (get_object_vars($data) as $key => $value) {
                $data->$key = self::recursiveReplace($value, $search, $replace);
            }
            return $data;
        }
        return $data;
    }
}

// ── Environment Check ──────────────────────────────────────────────────────────
function alkana_restore_check_env(): array
{
    $checks = [];
    $checks['php_version'] = [
        'ok'    => version_compare(PHP_VERSION, '8.1.0', '>='),
        'label' => 'PHP ' . PHP_VERSION . ' (requires 8.1+)',
    ];
    $checks['ziparchive'] = [
        'ok'    => class_exists('ZipArchive'),
        'label' => 'ZipArchive extension',
    ];
    $checks['pdo'] = [
        'ok'    => class_exists('PDO') && in_array('mysql', PDO::getAvailableDrivers()),
        'label' => 'PDO MySQL driver',
    ];
    return $checks;
}

// ── State-Machine SQL Import ───────────────────────────────────────────────────
function alkana_restore_import_sql(string $sqlFile, PDO $pdo): array
{
    $stats = ['statements' => 0, 'errors' => 0, 'error_details' => []];
    $fh = fopen($sqlFile, 'r');
    if ($fh === false) {
        throw new RuntimeException("Cannot open SQL file: $sqlFile");
    }

    $buffer   = '';
    $inString = false;
    $quoteChar = '';
    $escaped  = false;

    while (!feof($fh)) {
        $line = fgets($fh);
        if ($line === false) {
            break;
        }
        $trimmed = trim($line);
        if (!$inString && ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '#'))) {
            continue;
        }

        $len = strlen($line);
        for ($i = 0; $i < $len; $i++) {
            $char = $line[$i];
            if ($escaped) {
                $buffer .= $char;
                $escaped = false;
                continue;
            }
            if ($char === '\\') {
                $buffer .= $char;
                $escaped = true;
                continue;
            }
            if ($inString) {
                $buffer .= $char;
                if ($char === $quoteChar) {
                    $inString = false;
                }
                continue;
            }
            if ($char === "'" || $char === '"') {
                $inString = true;
                $quoteChar = $char;
                $buffer .= $char;
                continue;
            }
            if ($char === ';') {
                $stmt = trim($buffer);
                if ($stmt !== '') {
                    try {
                        $pdo->exec($stmt);
                        $stats['statements']++;
                    } catch (PDOException $e) {
                        $stats['errors']++;
                        $stats['error_details'][] = substr($e->getMessage(), 0, 200);
                    }
                }
                $buffer = '';
                continue;
            }
            $buffer .= $char;
        }
    }

    $stmt = trim($buffer);
    if ($stmt !== '') {
        try {
            $pdo->exec($stmt);
            $stats['statements']++;
        } catch (PDOException $e) {
            $stats['errors']++;
        }
    }

    fclose($fh);
    return $stats;
}

// ── URL Search-Replace ─────────────────────────────────────────────────────────
function alkana_restore_search_replace(PDO $pdo, string $search, string $replace): array
{
    $stats = ['tables' => 0, 'rows' => 0, 'changes' => 0];
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        $quotedTable = "`" . str_replace('`', '``', $table) . "`";
        $colStmt = $pdo->query("SHOW COLUMNS FROM $quotedTable");
        $textCols = [];
        $primaryKey = null;

        while ($col = $colStmt->fetch(PDO::FETCH_ASSOC)) {
            $type = strtolower($col['Type']);
            if (preg_match('/varchar|text|longtext|mediumtext|tinytext/', $type)) {
                $textCols[] = $col['Field'];
            }
            if ($col['Key'] === 'PRI') {
                $primaryKey = $col['Field'];
            }
        }

        if (empty($textCols) || $primaryKey === null) {
            continue;
        }
        $stats['tables']++;

        $conditions = array_map(
            fn($c) => "`" . str_replace('`', '``', $c) . "` LIKE " . $pdo->quote("%$search%"),
            $textCols
        );
        $where = implode(' OR ', $conditions);
        $rows = $pdo->query("SELECT * FROM $quotedTable WHERE $where");

        while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
            $stats['rows']++;
            $updates = [];
            foreach ($textCols as $col) {
                if ($row[$col] === null || strpos($row[$col], $search) === false) {
                    continue;
                }
                $newVal = AlkanaRestoreSerializer::recursiveReplace($row[$col], $search, $replace);
                if ($newVal !== $row[$col]) {
                    $updates[$col] = $newVal;
                }
            }
            if (!empty($updates)) {
                $setParts = [];
                $params = [];
                foreach ($updates as $col => $val) {
                    $setParts[] = "`" . str_replace('`', '``', $col) . "` = ?";
                    $params[] = $val;
                }
                $params[] = $row[$primaryKey];
                $sql = "UPDATE $quotedTable SET " . implode(', ', $setParts)
                     . " WHERE `" . str_replace('`', '``', $primaryKey) . "` = ?";
                $pdo->prepare($sql)->execute($params);
                $stats['changes']++;
            }
        }
    }
    return $stats;
}

// ── Recursive Delete Directory ─────────────────────────────────────────────────
function alkana_restore_delete_dir(string $dir): bool
{
    if (!is_dir($dir)) {
        return false;
    }
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($items as $item) {
        if ($item->isDir()) {
            @rmdir($item->getRealPath());
        } else {
            @unlink($item->getRealPath());
        }
    }
    return @rmdir($dir);
}

// ── Track Restore Count / Age for Flexible Lock ────────────────────────────────
function alkana_restore_check_lock(): void
{
    $lockFile = __FILE__ . '.meta';
    $meta = ['count' => 0, 'created' => time()];

    if (file_exists($lockFile)) {
        $data = json_decode(file_get_contents($lockFile), true);
        if (is_array($data)) {
            $meta = $data;
        }
    }

    // Lock after 3 successes or 24h age
    if ($meta['count'] >= 3 || (time() - $meta['created']) > 86400) {
        @rename(__FILE__, __FILE__ . '.lock');
        @unlink($lockFile);
    }
}

function alkana_restore_increment_count(): void
{
    $lockFile = __FILE__ . '.meta';
    $meta = ['count' => 0, 'created' => time()];

    if (file_exists($lockFile)) {
        $data = json_decode(file_get_contents($lockFile), true);
        if (is_array($data)) {
            $meta = $data;
        }
    }

    $meta['count']++;
    file_put_contents($lockFile, json_encode($meta));
}

// ── CLI Mode ───────────────────────────────────────────────────────────────────
if (php_sapi_name() === 'cli') {
    $options = getopt('', ['file:', 'db-host:', 'db-name:', 'db-user:', 'db-pass:', 'db-prefix:', 'url:', 'target:', 'help']);

    if (isset($options['help']) || !isset($options['file'])) {
        echo "Alkana Restore Tool (CLI)\n";
        echo "Usage: php alkana-restore.php --file=backup.zip [options]\n\n";
        echo "Options:\n";
        echo "  --file       Path to backup ZIP (required)\n";
        echo "  --db-host    Database host (default: localhost)\n";
        echo "  --db-name    Database name (required)\n";
        echo "  --db-user    Database user (required)\n";
        echo "  --db-pass    Database password\n";
        echo "  --db-prefix  Table prefix (default: wp_)\n";
        echo "  --url        New site URL\n";
        echo "  --target     Target directory (default: current dir)\n";
        exit(0);
    }

    // Env check
    $checks = alkana_restore_check_env();
    foreach ($checks as $check) {
        $icon = $check['ok'] ? '✅' : '❌';
        echo "  $icon {$check['label']}\n";
        if (!$check['ok']) {
            echo "Aborting: environment check failed.\n";
            exit(1);
        }
    }

    $zipFile   = $options['file'];
    $dbHost    = $options['db-host'] ?? 'localhost';
    $dbName    = $options['db-name'] ?? '';
    $dbUser    = $options['db-user'] ?? '';
    $dbPass    = $options['db-pass'] ?? '';
    $dbPrefix  = $options['db-prefix'] ?? 'wp_';
    $newUrl    = $options['url'] ?? null;
    $targetDir = $options['target'] ?? dirname($zipFile);

    if (!file_exists($zipFile)) {
        fwrite(STDERR, "❌ File not found: $zipFile\n");
        exit(1);
    }

    // Pre-flight ZIP check
    $zip = new ZipArchive();
    $result = $zip->open($zipFile);
    if ($result !== true) {
        fwrite(STDERR, "❌ Cannot open ZIP: error code $result\n");
        exit(1);
    }
    echo "✅ ZIP verified ({$zip->numFiles} entries)\n";

    // Extract to temp
    $tmpDir = sys_get_temp_dir() . '/alkana_restore_' . bin2hex(random_bytes(8));
    mkdir($tmpDir, 0755, true);
    $zip->extractTo($tmpDir);
    $zip->close();
    echo "✅ Extracted to temp directory\n";

    // Read manifest
    $manifestFile = $tmpDir . '/manifest.json';
    $manifest = null;
    if (file_exists($manifestFile)) {
        $manifest = json_decode(file_get_contents($manifestFile), true);
        echo "✅ Manifest: {$manifest['backup_mode']} backup from {$manifest['backup_date']}\n";
    }

    // DB import
    $dbSql = $tmpDir . '/database.sql';
    if (file_exists($dbSql) && $dbName) {
        $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $stats = alkana_restore_import_sql($dbSql, $pdo);
        echo "✅ Database imported ({$stats['statements']} statements, {$stats['errors']} errors)\n";

        // URL replace
        if ($newUrl && $manifest && isset($manifest['site_url'])) {
            $oldUrl = rtrim($manifest['site_url'], '/');
            $newUrl = rtrim($newUrl, '/');
            if ($oldUrl !== $newUrl) {
                $replStats = alkana_restore_search_replace($pdo, $oldUrl, $newUrl);
                echo "✅ URL replaced: $oldUrl → $newUrl ({$replStats['changes']} changes)\n";
            }
        }
    }

    // Extract wp-content
    $wpContentZip = $tmpDir . '/wp-content.zip';
    if (file_exists($wpContentZip)) {
        $wcZip = new ZipArchive();
        $wcZip->open($wpContentZip);
        $wcTarget = $targetDir . '/wp-content';
        if (!is_dir($wcTarget)) {
            mkdir($wcTarget, 0755, true);
        }
        $wcZip->extractTo($wcTarget);
        $wcZip->close();
        echo "✅ wp-content restored\n";
    }

    // Cleanup
    alkana_restore_delete_dir($tmpDir);
    alkana_restore_increment_count();
    alkana_restore_check_lock();
    echo "\n✅ Restore complete!\n";
    exit(0);
}

// ── Browser Mode ───────────────────────────────────────────────────────────────
@set_time_limit(0);
@ini_set('memory_limit', '512M');

// Security: require ALKANA_RESTORE_SECRET to be defined or passed as query param
if (!defined('ALKANA_RESTORE_SECRET')) {
    define('ALKANA_RESTORE_SECRET', ''); // Set a secret before deploying!
}
if (ALKANA_RESTORE_SECRET !== '') {
    $providedSecret = $_GET['secret'] ?? $_POST['secret'] ?? '';
    if (!hash_equals(ALKANA_RESTORE_SECRET, $providedSecret)) {
        http_response_code(403);
        echo '<!DOCTYPE html><html><body><h1>403 Forbidden</h1><p>Invalid or missing restore secret.</p></body></html>';
        exit(1);
    }
}

// CSRF token
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
if (!isset($_SESSION['alkana_csrf'])) {
    $_SESSION['alkana_csrf'] = bin2hex(random_bytes(32));
}

$envChecks = alkana_restore_check_env();
$envOk = true;
foreach ($envChecks as $check) {
    if (!$check['ok']) {
        $envOk = false;
    }
}

// Process POST
$processing = false;
$results = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $envOk) {
    // CSRF check
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['alkana_csrf'], $_POST['csrf_token'])) {
        $results[] = ['type' => 'error', 'msg' => 'Invalid CSRF token. Refresh the page and try again.'];
    } else {
        // Rate limit
        if (!isset($_SESSION['alkana_attempts'])) {
            $_SESSION['alkana_attempts'] = 0;
        }
        $_SESSION['alkana_attempts']++;

        if ($_SESSION['alkana_attempts'] > 10) {
            $results[] = ['type' => 'error', 'msg' => 'Rate limit exceeded. Please wait.'];
        } else {
            $processing = true;
            ob_implicit_flush(true);

            $zipFile   = trim($_POST['zip_file'] ?? '');
            $dbHost    = trim($_POST['db_host'] ?? 'localhost');
            $dbName    = trim($_POST['db_name'] ?? '');
            $dbUser    = trim($_POST['db_user'] ?? '');
            $dbPass    = $_POST['db_pass'] ?? '';
            $dbPrefix  = trim($_POST['db_prefix'] ?? 'wp_');
            $newUrl    = trim($_POST['new_url'] ?? '');
            $targetDir = trim($_POST['target_dir'] ?? dirname(__FILE__));
            $verify    = isset($_POST['verify_checksums']);
            $updateWpConfig = isset($_POST['update_wpconfig']);

            // Validate
            if (empty($zipFile) || !file_exists($zipFile)) {
                $results[] = ['type' => 'error', 'msg' => 'Backup ZIP file not found: ' . htmlspecialchars($zipFile)];
            } else {
                // Pre-flight ZIP integrity
                $zip = new ZipArchive();
                $zipResult = $zip->open($zipFile);
                if ($zipResult !== true) {
                    $results[] = ['type' => 'error', 'msg' => "ZIP file is corrupt or unreadable (error: $zipResult)"];
                } else {
                    $numFiles = $zip->numFiles;
                    $zip->close();
                    $results[] = ['type' => 'ok', 'msg' => "ZIP verified ($numFiles entries)"];

                    // Disk space warning
                    $zipSize = filesize($zipFile);
                    $freeSpace = @disk_free_space($targetDir ?: dirname(__FILE__));
                    if ($freeSpace !== false && $freeSpace < $zipSize * 2) {
                        $results[] = ['type' => 'warn', 'msg' => "Low disk space warning. Free: " . round($freeSpace / 1024 / 1024) . "MB, Need: ~" . round($zipSize * 2 / 1024 / 1024) . "MB"];
                    }

                    // Extract to temp
                    $tmpDir = sys_get_temp_dir() . '/alkana_restore_' . bin2hex(random_bytes(8));
                    mkdir($tmpDir, 0755, true);
                    file_put_contents($tmpDir . '/.htaccess', "Require all denied\n");

                    $zip = new ZipArchive();
                    $zip->open($zipFile);
                    $zip->extractTo($tmpDir);
                    $zip->close();
                    $results[] = ['type' => 'ok', 'msg' => 'Files extracted to temp directory'];

                    // Manifest
                    $manifest = null;
                    if (file_exists($tmpDir . '/manifest.json')) {
                        $manifest = json_decode(file_get_contents($tmpDir . '/manifest.json'), true);
                        $results[] = ['type' => 'ok', 'msg' => "Manifest: {$manifest['backup_mode']} backup, {$manifest['backup_date']}"];
                    }

                    // Checksums
                    if ($verify && $manifest && isset($manifest['checksums'])) {
                        $mismatches = 0;
                        foreach ($manifest['checksums'] as $file => $hash) {
                            $path = $tmpDir . '/' . $file;
                            if (!file_exists($path) || hash_file('sha256', $path) !== $hash) {
                                $mismatches++;
                            }
                        }
                        $results[] = ['type' => $mismatches > 0 ? 'warn' : 'ok', 'msg' => "Checksums verified ($mismatches mismatches)"];
                    }

                    // DB import
                    if (file_exists($tmpDir . '/database.sql') && $dbName) {
                        try {
                            $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
                            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            ]);
                            $stats = alkana_restore_import_sql($tmpDir . '/database.sql', $pdo);
                            $results[] = ['type' => 'ok', 'msg' => "Database imported ({$stats['statements']} statements, {$stats['errors']} errors)"];

                            // URL replace
                            if ($newUrl && $manifest && isset($manifest['site_url'])) {
                                $oldUrl = rtrim($manifest['site_url'], '/');
                                $cleanNewUrl = rtrim($newUrl, '/');
                                if ($oldUrl !== $cleanNewUrl) {
                                    $replStats = alkana_restore_search_replace($pdo, $oldUrl, $cleanNewUrl);
                                    $results[] = ['type' => 'ok', 'msg' => "URLs replaced: $oldUrl → $cleanNewUrl ({$replStats['changes']} changes in {$replStats['tables']} tables)"];
                                }
                            }
                        } catch (PDOException $e) {
                            $results[] = ['type' => 'error', 'msg' => 'Database error: ' . htmlspecialchars($e->getMessage())];
                        }
                    }

                    // Restore wp-content
                    if (file_exists($tmpDir . '/wp-content.zip')) {
                        $wcTarget = $targetDir . '/wp-content';
                        if (!is_dir($wcTarget)) {
                            mkdir($wcTarget, 0755, true);
                        }
                        $wcZip = new ZipArchive();
                        $wcZip->open($tmpDir . '/wp-content.zip');
                        $wcZip->extractTo($wcTarget);
                        $wcZip->close();
                        $results[] = ['type' => 'ok', 'msg' => 'wp-content restored'];
                    }

                    // Update wp-config
                    if ($updateWpConfig && $dbName) {
                        $wpConfigPath = $targetDir . '/wp-config.php';
                        if (file_exists($wpConfigPath)) {
                            $config = file_get_contents($wpConfigPath);
                            $replacements = [
                                'DB_NAME'     => $dbName,
                                'DB_USER'     => $dbUser,
                                'DB_PASSWORD' => $dbPass,
                                'DB_HOST'     => $dbHost,
                            ];
                            foreach ($replacements as $const => $val) {
                                $escaped = str_replace("'", "\\'", str_replace('\\', '\\\\', $val));
                                $config = preg_replace(
                                    "/define\s*\(\s*['\"]" . $const . "['\"]\s*,\s*['\"][^'\"]*?['\"]\s*\)/",
                                    "define('" . $const . "', '" . $escaped . "')",
                                    $config
                                );
                            }
                            if ($newUrl) {
                                // Add or update WP_HOME and WP_SITEURL
                                foreach (['WP_HOME', 'WP_SITEURL'] as $const) {
                                    $escapedUrl = str_replace("'", "\\'", str_replace('\\', '\\\\', rtrim($newUrl, '/')));
                                    if (preg_match("/define\s*\(\s*['\"]" . $const . "['\"]/", $config)) {
                                        $config = preg_replace(
                                            "/define\s*\(\s*['\"]" . $const . "['\"]\s*,\s*['\"][^'\"]*?['\"]\s*\)/",
                                            "define('" . $const . "', '" . $escapedUrl . "')",
                                            $config
                                        );
                                    }
                                }
                            }
                            file_put_contents($wpConfigPath, $config);
                            $results[] = ['type' => 'ok', 'msg' => 'wp-config.php updated'];
                        } else {
                            $results[] = ['type' => 'warn', 'msg' => 'wp-config.php not found at target — skipped'];
                        }
                    }

                    // Set permissions (silent success)
                    if (is_dir($targetDir . '/wp-content')) {
                        @chmod($targetDir . '/wp-content', 0755);
                        @chmod($targetDir . '/wp-content/uploads', 0755);
                    }

                    // Cleanup temp
                    alkana_restore_delete_dir($tmpDir);

                    // Track & lock
                    alkana_restore_increment_count();
                    alkana_restore_check_lock();

                    $results[] = ['type' => 'ok', 'msg' => '🎉 Restore complete!'];
                }
            }
        }
    }
    // Regenerate CSRF
    $_SESSION['alkana_csrf'] = bin2hex(random_bytes(32));
}

// ── HTML Output ────────────────────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alkana Restore Tool</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f0f2f5; color: #1a1a2e; line-height: 1.6; padding: 20px; }
        .container { max-width: 640px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 20px rgba(0,0,0,0.08); padding: 40px; }
        h1 { font-size: 24px; margin-bottom: 8px; }
        .subtitle { color: #666; margin-bottom: 30px; font-size: 14px; }
        .section { margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid #eee; }
        .section:last-child { border-bottom: none; }
        .section h2 { font-size: 16px; color: #333; margin-bottom: 12px; }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px; color: #555; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; margin-bottom: 12px; }
        input[type="text"]:focus, input[type="password"]:focus { border-color: #4C0682; outline: none; box-shadow: 0 0 0 3px rgba(76,6,130,0.1); }
        .checkbox { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
        .checkbox input { margin: 0; }
        .checkbox label { display: inline; margin: 0; font-weight: 400; }
        .btn { display: inline-block; padding: 12px 24px; background: #4C0682; color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; width: 100%; }
        .btn:hover { background: #67219D; }
        .btn:disabled { background: #ccc; cursor: not-allowed; }
        .result { margin-top: 8px; padding: 8px 12px; border-radius: 6px; font-size: 13px; }
        .result-ok { background: #ecf7ed; border-left: 4px solid #46b450; }
        .result-warn { background: #fff8e1; border-left: 4px solid #ffb300; }
        .result-error { background: #fef1f1; border-left: 4px solid #dc3232; }
        .env-check { display: flex; gap: 8px; align-items: center; padding: 4px 0; font-size: 13px; }
        .env-ok { color: #46b450; }
        .env-fail { color: #dc3232; }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        @media (max-width: 600px) { .row { grid-template-columns: 1fr; } .container { padding: 24px 16px; } }
    </style>
</head>
<body>
<div class="container">
    <h1>🔧 Alkana Restore Tool</h1>
    <p class="subtitle">Self-contained restore script — works without WordPress</p>

    <!-- Environment Check -->
    <div class="section">
        <h2>Environment</h2>
        <?php foreach ($envChecks as $check): ?>
            <div class="env-check">
                <span class="<?php echo $check['ok'] ? 'env-ok' : 'env-fail'; ?>">
                    <?php echo $check['ok'] ? '✅' : '❌'; ?>
                </span>
                <?php echo htmlspecialchars($check['label']); ?>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($envOk): ?>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['alkana_csrf']); ?>">

        <div class="section">
            <h2>Backup File</h2>
            <label for="zip_file">Path to ZIP file</label>
            <input type="text" id="zip_file" name="zip_file" placeholder="./alkana-full-20260417-143022.zip" value="<?php echo htmlspecialchars($_POST['zip_file'] ?? ''); ?>" required>
        </div>

        <div class="section">
            <h2>Database Credentials</h2>
            <div class="row">
                <div>
                    <label for="db_host">Host</label>
                    <input type="text" id="db_host" name="db_host" value="<?php echo htmlspecialchars($_POST['db_host'] ?? 'localhost'); ?>">
                </div>
                <div>
                    <label for="db_name">Database Name</label>
                    <input type="text" id="db_name" name="db_name" value="<?php echo htmlspecialchars($_POST['db_name'] ?? ''); ?>">
                </div>
            </div>
            <div class="row">
                <div>
                    <label for="db_user">Username</label>
                    <input type="text" id="db_user" name="db_user" value="<?php echo htmlspecialchars($_POST['db_user'] ?? ''); ?>">
                </div>
                <div>
                    <label for="db_pass">Password</label>
                    <input type="password" id="db_pass" name="db_pass">
                </div>
            </div>
            <label for="db_prefix">Table Prefix</label>
            <input type="text" id="db_prefix" name="db_prefix" value="<?php echo htmlspecialchars($_POST['db_prefix'] ?? 'wp_'); ?>" style="width:120px;">
        </div>

        <div class="section">
            <h2>Site Configuration</h2>
            <label for="new_url">New Site URL</label>
            <input type="text" id="new_url" name="new_url" placeholder="https://alkana.vn" value="<?php echo htmlspecialchars($_POST['new_url'] ?? ''); ?>">
            <label for="target_dir">Target Directory</label>
            <input type="text" id="target_dir" name="target_dir" value="<?php echo htmlspecialchars($_POST['target_dir'] ?? dirname(__FILE__)); ?>">
        </div>

        <div class="section">
            <div class="checkbox">
                <input type="checkbox" id="verify_checksums" name="verify_checksums" checked>
                <label for="verify_checksums">Verify checksums after extract</label>
            </div>
            <div class="checkbox">
                <input type="checkbox" id="update_wpconfig" name="update_wpconfig" checked>
                <label for="update_wpconfig">Update wp-config.php</label>
            </div>
        </div>

        <button type="submit" class="btn">🔄 Start Restore</button>
    </form>
    <?php endif; ?>

    <?php if (!empty($results)): ?>
    <div class="section" style="margin-top:24px;border-top:1px solid #eee;padding-top:24px;">
        <h2>Progress</h2>
        <?php foreach ($results as $r): ?>
            <div class="result result-<?php echo $r['type']; ?>">
                <?php echo $r['msg']; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<p style="text-align:center;color:#999;font-size:12px;margin-top:20px;">
    ⚠️ Delete this script after use for security.
</p>
</body>
</html>
