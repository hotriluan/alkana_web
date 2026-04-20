<?php
/**
 * Alkana Backup / Deploy / Restore Toolkit — Core Engine
 *
 * Single-file PHP toolkit: backup, restore, search-replace for WordPress.
 * Fallback Gracefully pattern: prefers OS commands, pure PHP fallback.
 *
 * @requires PHP 8.1+
 */

declare(strict_types=1);

// ── Configuration ──────────────────────────────────────────────────────────────
define('ALKANA_BACKUP_DIR', __DIR__ . '/../backups');
define('ALKANA_MAX_BACKUPS', 5);
define('ALKANA_EXCLUDE_PATTERNS', [
    'node_modules', '.git', 'src/', '.venv',
    '*.log', '.DS_Store', 'Thumbs.db',
    '.claude/', 'plans/', 'tests/', 'test-results/',
    'scripts/vendor/', 'backups/',
]);

// ── AlkanaSerializer ───────────────────────────────────────────────────────────
class AlkanaSerializer
{
    /**
     * Detect if a string is PHP-serialized data.
     */
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

    /**
     * Recursively replace strings in potentially serialized data.
     * CRITICAL: Uses strlen() (byte count), not mb_strlen().
     */
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
            $props = get_object_vars($data);
            foreach ($props as $key => $value) {
                $data->$key = self::recursiveReplace($value, $search, $replace);
            }
            return $data;
        }

        return $data;
    }
}

// ── AlkanaDatabaseHandler ──────────────────────────────────────────────────────
class AlkanaDatabaseHandler
{
    private readonly string $host;
    private readonly string $name;
    private readonly string $user;
    private readonly string $pass;
    private readonly string $prefix;
    private ?PDO $pdo = null;

    /**
     * @param ?PDO $injectedPdo  Inject a pre-built PDO (e.g. SQLite mock) for testing.
     */
    public function __construct(
        string $host = 'localhost',
        string $name = '',
        string $user = '',
        string $pass = '',
        string $prefix = 'wp_',
        ?PDO   $injectedPdo = null,
    ) {
        $this->host   = $host;
        $this->name   = $name;
        $this->user   = $user;
        $this->pass   = $pass;
        $this->prefix = $prefix;
        $this->pdo    = $injectedPdo;
    }

    private function connect(): PDO
    {
        if ($this->pdo === null) {
            $dsn = "mysql:host={$this->host};dbname={$this->name};charset=utf8mb4";
            $this->pdo = new PDO($dsn, $this->user, $this->pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
            ]);
        }
        return $this->pdo;
    }

    /**
     * Check if shell_exec + mysqldump are available.
     */
    public function canUseShell(): bool
    {
        if (!function_exists('shell_exec') || !is_callable('shell_exec')) {
            return false;
        }
        $disabled = ini_get('disable_functions');
        if (stripos($disabled, 'shell_exec') !== false) {
            return false;
        }
        $check = PHP_OS_FAMILY === 'Windows'
            ? @shell_exec('where mysqldump 2>NUL')
            : @shell_exec('which mysqldump 2>/dev/null');
        return !empty(trim((string) $check));
    }

    /**
     * Multi-stage WP-CLI detection.
     */
    public function detectWpCli(): ?string
    {
        if (!function_exists('shell_exec') || !is_callable('shell_exec')) {
            return null;
        }
        // Stage 1: PATH lookup
        $check = PHP_OS_FAMILY === 'Windows'
            ? @shell_exec('where wp 2>NUL')
            : @shell_exec('which wp 2>/dev/null');
        if (!empty(trim((string) $check))) {
            return trim((string) $check);
        }
        // Stage 2: wp-cli.phar in script dir or WP root
        $candidates = [
            __DIR__ . '/wp-cli.phar',
            __DIR__ . '/../wp-cli.phar',
        ];
        foreach ($candidates as $path) {
            if (file_exists($path)) {
                $info = @shell_exec("php " . escapeshellarg($path) . " --info 2>/dev/null");
                if (!empty($info) && stripos($info, 'WP-CLI') !== false) {
                    return "php " . realpath($path);
                }
            }
        }
        return null;
    }

    /**
     * SQL integrity headers prepended to every dump.
     */
    private function sqlHeaders(): string
    {
        return implode("\n", [
            "SET FOREIGN_KEY_CHECKS=0;",
            "SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";",
            "SET NAMES utf8mb4;",
            "SET TIME_ZONE='+00:00';",
            "",
        ]);
    }

    /**
     * Export database to SQL file.
     * Fallback Gracefully: mysqldump → PDO row-by-row.
     */
    public function export(string $outputFile): bool
    {
        try {
            @set_time_limit(0);

            if ($this->canUseShell()) {
                return $this->exportViaShell($outputFile);
            }
            return $this->exportViaPdo($outputFile);
        } catch (\Throwable $e) {
            $this->logError("DB export failed: " . $e->getMessage());
            throw $e;
        }
    }

    private function exportViaShell(string $outputFile): bool
    {
        $headerFile = $outputFile . '.header';
        file_put_contents($headerFile, $this->sqlHeaders());

        $envPrefix = PHP_OS_FAMILY === 'Windows'
            ? 'set MYSQL_PWD=' . escapeshellarg($this->pass) . '&& '
            : 'MYSQL_PWD=' . escapeshellarg($this->pass) . ' ';
        $cmd = $envPrefix . sprintf(
            'mysqldump --single-transaction --routines --triggers --host=%s --user=%s %s',
            escapeshellarg($this->host),
            escapeshellarg($this->user),
            escapeshellarg($this->name)
        );

        $dumpFile = $outputFile . '.dump';
        if (PHP_OS_FAMILY === 'Windows') {
            $cmd .= ' > ' . escapeshellarg($dumpFile) . ' 2>&1';
        } else {
            $cmd .= ' > ' . escapeshellarg($dumpFile) . ' 2>&1';
        }

        @shell_exec($cmd);

        if (!file_exists($dumpFile) || filesize($dumpFile) === 0) {
            @unlink($headerFile);
            @unlink($dumpFile);
            return $this->exportViaPdo($outputFile);
        }

        // Merge header + dump + footer
        $fout = fopen($outputFile, 'w');
        if ($fout === false) {
            throw new \RuntimeException("Cannot create output file: $outputFile");
        }

        fwrite($fout, file_get_contents($headerFile));
        $fin = fopen($dumpFile, 'r');
        if ($fin !== false) {
            while (!feof($fin)) {
                $chunk = fread($fin, 8192);
                if ($chunk !== false) {
                    fwrite($fout, $chunk);
                }
            }
            fclose($fin);
        }
        fwrite($fout, "\nSET FOREIGN_KEY_CHECKS=1;\n");
        fclose($fout);

        @unlink($headerFile);
        @unlink($dumpFile);
        return true;
    }

    /**
     * PDO-based export — memory-safe row-by-row streaming.
     */
    private function exportViaPdo(string $outputFile): bool
    {
        $pdo = $this->connect();
        $fh  = fopen($outputFile, 'w');
        if ($fh === false) {
            throw new \RuntimeException("Cannot create output file: $outputFile");
        }

        fwrite($fh, "-- Alkana Toolkit DB Export\n");
        fwrite($fh, "-- Date: " . gmdate('Y-m-d H:i:s') . " UTC\n\n");
        fwrite($fh, $this->sqlHeaders());

        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $quotedTable = "`" . str_replace('`', '``', $table) . "`";

            // CREATE TABLE
            $create = $pdo->query("SHOW CREATE TABLE $quotedTable")->fetch();
            $createSql = $create['Create Table'] ?? $create['Create View'] ?? '';
            fwrite($fh, "\nDROP TABLE IF EXISTS $quotedTable;\n");
            fwrite($fh, $createSql . ";\n\n");

            // Skip VIEWs for data
            if (isset($create['Create View'])) {
                continue;
            }

            // INSERT rows — stream one at a time
            $stmt = $pdo->query("SELECT * FROM $quotedTable");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $values = [];
                foreach ($row as $val) {
                    if ($val === null) {
                        $values[] = 'NULL';
                    } elseif (is_numeric($val)) {
                        $values[] = $val;
                    } else {
                        $values[] = $pdo->quote($val);
                    }
                }
                fwrite($fh, "INSERT INTO $quotedTable VALUES (" . implode(',', $values) . ");\n");
            }
        }

        fwrite($fh, "\nSET FOREIGN_KEY_CHECKS=1;\n");
        fclose($fh);
        return true;
    }

    /**
     * Import SQL file into database.
     * State-machine parser: respects quoted strings, never naively splits by ';'.
     */
    public function import(string $sqlFile): bool
    {
        if (!file_exists($sqlFile)) {
            throw new \RuntimeException("Cannot open SQL file: $sqlFile");
        }
        @set_time_limit(0);
        $pdo = $this->connect();
        $fh  = fopen($sqlFile, 'r');
        if ($fh === false) {
            throw new \RuntimeException("Cannot open SQL file: $sqlFile");
        }

        $buffer    = '';
        $inString  = false;
        $quoteChar = '';
        $escaped   = false;

        while (!feof($fh)) {
            $line = fgets($fh);
            if ($line === false) {
                break;
            }

            // Skip comments and empty lines (only when not inside a string)
            $trimmed = trim($line);
            if (!$inString && ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '#'))) {
                continue;
            }

            // State-machine character parser
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

                // Not in string
                if ($char === "'" || $char === '"') {
                    $inString  = true;
                    $quoteChar = $char;
                    $buffer   .= $char;
                    continue;
                }

                if ($char === ';') {
                    $stmt = trim($buffer);
                    if ($stmt !== '') {
                        try {
                            $pdo->exec($stmt);
                        } catch (\PDOException $e) {
                            $this->logError("SQL import error: " . $e->getMessage() . " | Statement: " . substr($stmt, 0, 200));
                        }
                    }
                    $buffer = '';
                    continue;
                }

                $buffer .= $char;
            }
        }

        // Execute remaining buffer
        $stmt = trim($buffer);
        if ($stmt !== '') {
            try {
                $pdo->exec($stmt);
            } catch (\PDOException $e) {
                $this->logError("SQL import error (final): " . $e->getMessage());
            }
        }

        fclose($fh);
        return true;
    }

    /**
     * Serialized-safe URL search-replace across all tables.
     */
    public function searchReplace(string $search, string $replace, ?array $tables = null): array
    {
        $pdo = $this->connect();
        $stats = ['tables' => 0, 'rows' => 0, 'changes' => 0];

        if ($tables === null) {
            $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            if ($driver === 'sqlite') {
                $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
            } else {
                $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            }
        }

        foreach ($tables as $table) {
            $quotedTable = "`" . str_replace('`', '``', $table) . "`";

            // Get text columns — driver-agnostic
            $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            $textCols   = [];
            $primaryKey = null;

            if ($driver === 'sqlite') {
                $colStmt = $pdo->query("PRAGMA table_info(" . trim($quotedTable, '`') . ")");
                while ($col = $colStmt->fetch()) {
                    $type = strtolower($col['type'] ?? '');
                    if (preg_match('/varchar|text|char|clob/', $type) || $type === '') {
                        $textCols[] = $col['name'];
                    }
                    if ((int) $col['pk'] === 1) {
                        $primaryKey = $col['name'];
                    }
                }
            } else {
                $colStmt = $pdo->query("SHOW COLUMNS FROM $quotedTable");
                while ($col = $colStmt->fetch()) {
                    $type = strtolower($col['Type']);
                    if (preg_match('/varchar|text|longtext|mediumtext|tinytext/', $type)) {
                        $textCols[] = $col['Field'];
                    }
                    if ($col['Key'] === 'PRI') {
                        $primaryKey = $col['Field'];
                    }
                }
            }

            if (empty($textCols) || $primaryKey === null) {
                continue;
            }

            $stats['tables']++;

            // Build WHERE clause
            $conditions = array_map(
                fn($c) => "`" . str_replace('`', '``', $c) . "` LIKE " . $pdo->quote("%$search%"),
                $textCols
            );
            $where = implode(' OR ', $conditions);

            $rows = $pdo->query("SELECT * FROM $quotedTable WHERE $where");

            while ($row = $rows->fetch()) {
                $stats['rows']++;
                $updates = [];
                $changed = false;

                foreach ($textCols as $col) {
                    if ($row[$col] === null || strpos($row[$col], $search) === false) {
                        continue;
                    }
                    $newVal = AlkanaSerializer::recursiveReplace($row[$col], $search, $replace);
                    if ($newVal !== $row[$col]) {
                        $updates[$col] = $newVal;
                        $changed = true;
                    }
                }

                if ($changed) {
                    $setParts = [];
                    $params   = [];
                    foreach ($updates as $col => $val) {
                        $setParts[] = "`" . str_replace('`', '``', $col) . "` = ?";
                        $params[]   = $val;
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

    private function logError(string $message): void
    {
        $logFile = ALKANA_BACKUP_DIR . '/alkana-error.log';
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents($logFile, '[' . gmdate('Y-m-d H:i:s') . '] ' . $message . "\n", FILE_APPEND);
    }
}

// ── AlkanaFileArchiver ─────────────────────────────────────────────────────────
class AlkanaFileArchiver
{
    /**
     * Check if shell_exec + zip are available.
     */
    public static function canUseShell(): bool
    {
        if (!function_exists('shell_exec') || !is_callable('shell_exec')) {
            return false;
        }
        $disabled = ini_get('disable_functions');
        if (stripos($disabled, 'shell_exec') !== false) {
            return false;
        }
        $check = PHP_OS_FAMILY === 'Windows'
            ? @shell_exec('where 7z 2>NUL')
            : @shell_exec('which zip 2>/dev/null');
        return !empty(trim((string) $check));
    }

    /**
     * Create ZIP archive from source directories.
     */
    public static function createArchive(string $sourceDir, string $outputFile, array $excludes = []): bool
    {
        @set_time_limit(0);

        if (self::canUseShell() && PHP_OS_FAMILY !== 'Windows') {
            return self::createViaShell($sourceDir, $outputFile, $excludes);
        }
        return self::createViaZipArchive($sourceDir, $outputFile, $excludes);
    }

    private static function createViaShell(string $sourceDir, string $outputFile, array $excludes): bool
    {
        $excludeFlags = '';
        foreach ($excludes as $pattern) {
            $excludeFlags .= ' -x ' . escapeshellarg("*/$pattern/*") . ' ' . escapeshellarg("*/$pattern");
        }

        $cmd = sprintf(
            'cd %s && zip -r %s . %s 2>&1',
            escapeshellarg($sourceDir),
            escapeshellarg(realpath(dirname($outputFile)) . '/' . basename($outputFile)),
            $excludeFlags
        );

        @shell_exec($cmd);
        return file_exists($outputFile) && filesize($outputFile) > 0;
    }

    private static function createViaZipArchive(string $sourceDir, string $outputFile, array $excludes): bool
    {
        $zip = new ZipArchive();
        $result = $zip->open($outputFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($result !== true) {
            throw new \RuntimeException("Cannot create ZIP archive: error code $result");
        }

        $sourceDir = rtrim(realpath($sourceDir), DIRECTORY_SEPARATOR);
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($sourceDir) + 1);
            $relativePathUnix = str_replace('\\', '/', $relativePath);

            // Check excludes
            if (self::shouldExclude($relativePathUnix, $excludes)) {
                continue;
            }

            if ($file->isDir()) {
                $zip->addEmptyDir($relativePathUnix);
            } else {
                $zip->addFile($filePath, $relativePathUnix);
            }
        }

        $zip->close();
        return file_exists($outputFile);
    }

    /**
     * Check if a path matches any exclude pattern.
     */
    private static function shouldExclude(string $path, array $excludes): bool
    {
        foreach ($excludes as $pattern) {
            // Directory pattern (ends with /)
            if (str_ends_with($pattern, '/')) {
                $dir = rtrim($pattern, '/');
                if (str_starts_with($path, $dir . '/') || $path === $dir) {
                    return true;
                }
            }
            // Glob pattern (contains *)
            elseif (str_contains($pattern, '*')) {
                if (fnmatch($pattern, basename($path))) {
                    return true;
                }
            }
            // Exact directory or file match
            else {
                if (str_starts_with($path, $pattern . '/') || $path === $pattern || str_contains($path, '/' . $pattern . '/') || str_contains($path, '/' . $pattern)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Extract ZIP archive to target directory.
     */
    public static function extractArchive(string $zipFile, string $targetDir): bool
    {
        @set_time_limit(0);

        $zip = new ZipArchive();
        $result = $zip->open($zipFile);
        if ($result !== true) {
            throw new \RuntimeException("Cannot open ZIP file: error code $result");
        }

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $zip->extractTo($targetDir);
        $zip->close();
        return true;
    }

    /**
     * Generate SHA256 checksums for all files in a directory.
     */
    public static function generateChecksums(string $dir): array
    {
        $checksums = [];
        $dir = rtrim(realpath($dir), DIRECTORY_SEPARATOR);
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $relativePath = str_replace('\\', '/', substr($file->getRealPath(), strlen($dir) + 1));
                $checksums[$relativePath] = hash_file('sha256', $file->getRealPath());
            }
        }

        return $checksums;
    }
}

// ── AlkanaToolkit (Orchestrator) ───────────────────────────────────────────────
class AlkanaToolkit
{
    private ?AlkanaDatabaseHandler $db = null;

    public function __construct(?array $dbConfig = null)
    {
        if ($dbConfig !== null) {
            $this->db = new AlkanaDatabaseHandler(
                $dbConfig['host'] ?? 'localhost',
                $dbConfig['name'] ?? '',
                $dbConfig['user'] ?? '',
                $dbConfig['pass'] ?? '',
                $dbConfig['prefix'] ?? 'wp_',
            );
        }
    }

    /**
     * Auto-detect DB config from wp-config.php.
     */
    public static function detectDbConfig(string $wpRoot = null): ?array
    {
        $wpRoot = $wpRoot ?? realpath(__DIR__ . '/..');
        $configFile = $wpRoot . '/wp-config.php';

        if (!file_exists($configFile)) {
            return null;
        }

        $content = file_get_contents($configFile);
        $config = [];

        $patterns = [
            'name'   => "/define\s*\(\s*['\"]DB_NAME['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/",
            'user'   => "/define\s*\(\s*['\"]DB_USER['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/",
            'pass'   => "/define\s*\(\s*['\"]DB_PASSWORD['\"]\s*,\s*['\"]([^'\"]*?)['\"]\s*\)/",
            'host'   => "/define\s*\(\s*['\"]DB_HOST['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/",
        ];

        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $content, $m)) {
                $config[$key] = $m[1];
            }
        }

        // Table prefix
        if (preg_match('/\$table_prefix\s*=\s*[\'"]([^\'"]+)[\'"]/', $content, $m)) {
            $config['prefix'] = $m[1];
        } else {
            $config['prefix'] = 'wp_';
        }

        return count($config) >= 4 ? $config : null;
    }

    /**
     * Perform backup.
     *
     * @param string $mode   full|db|files
     * @param string|null $outputDir  Where to store the backup ZIP
     * @return array{file: string, manifest: array}
     */
    public function backup(string $mode = 'full', ?string $outputDir = null): array
    {
        @set_time_limit(0);
        $outputDir = $outputDir ?? ALKANA_BACKUP_DIR;
        $wpRoot    = realpath(__DIR__ . '/..');

        // Ensure backup dir exists + .htaccess protection
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
        $htaccess = $outputDir . '/.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Require all denied\n");
        }
        // Empty index.html
        $indexFile = $outputDir . '/index.html';
        if (!file_exists($indexFile)) {
            file_put_contents($indexFile, '');
        }

        // Random temp directory
        $tmpName = 'alkana_tmp_' . bin2hex(random_bytes(8));
        $tmpDir  = $outputDir . '/' . $tmpName;
        mkdir($tmpDir, 0755, true);
        file_put_contents($tmpDir . '/.htaccess', "Require all denied\n");
        file_put_contents($tmpDir . '/index.html', '');

        // Register cleanup on crash
        register_shutdown_function(function () use ($tmpDir) {
            if (is_dir($tmpDir)) {
                self::deleteDir($tmpDir);
            }
        });

        try {
            // Step 1: DB export
            if (in_array($mode, ['full', 'db'], true) && $this->db !== null) {
                $this->db->export($tmpDir . '/database.sql');
            }

            // Step 2: Files archive
            if (in_array($mode, ['full', 'files'], true)) {
                $wpContentDir = $wpRoot . '/wp-content';
                if (is_dir($wpContentDir)) {
                    AlkanaFileArchiver::createArchive(
                        $wpContentDir,
                        $tmpDir . '/wp-content.zip',
                        ALKANA_EXCLUDE_PATTERNS
                    );
                }
            }

            // Step 3: Generate manifest
            $manifest = $this->generateManifest($mode, $tmpDir, $wpRoot);
            file_put_contents($tmpDir . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            // Step 4: Package final ZIP
            $timestamp = date('Ymd-His');
            $filename  = "alkana-{$mode}-{$timestamp}.zip";
            $finalZip  = $outputDir . '/' . $filename;

            AlkanaFileArchiver::createArchive($tmpDir, $finalZip, []);

            // Step 5: Auto-rotation
            $this->rotateBackups($outputDir, ALKANA_MAX_BACKUPS);

            return ['file' => $finalZip, 'manifest' => $manifest];
        } finally {
            // Always clean up temp
            if (is_dir($tmpDir)) {
                self::deleteDir($tmpDir);
            }
        }
    }

    /**
     * Restore from backup ZIP.
     */
    public function restore(string $zipPath, array $dbConfig, ?string $newUrl = null, ?string $targetDir = null): array
    {
        @set_time_limit(0);
        $targetDir = $targetDir ?? realpath(__DIR__ . '/..');
        $report = ['steps' => [], 'warnings' => [], 'success' => true];

        // Verify ZIP
        $zip = new ZipArchive();
        $result = $zip->open($zipPath);
        if ($result !== true) {
            throw new \RuntimeException("Cannot open backup ZIP: error code $result");
        }
        $zip->close();
        $report['steps'][] = 'ZIP verified';

        // Random temp directory
        $tmpName = 'alkana_restore_' . bin2hex(random_bytes(8));
        $tmpDir  = sys_get_temp_dir() . '/' . $tmpName;
        mkdir($tmpDir, 0755, true);

        register_shutdown_function(function () use ($tmpDir) {
            if (is_dir($tmpDir)) {
                self::deleteDir($tmpDir);
            }
        });

        try {
            // Extract
            AlkanaFileArchiver::extractArchive($zipPath, $tmpDir);
            $report['steps'][] = 'Files extracted to temp';

            // Read manifest
            $manifestFile = $tmpDir . '/manifest.json';
            $manifest = null;
            if (file_exists($manifestFile)) {
                $manifest = json_decode(file_get_contents($manifestFile), true);
                $report['steps'][] = 'Manifest loaded: ' . ($manifest['backup_mode'] ?? 'unknown') . ' backup';
            }

            // Verify checksums
            if ($manifest !== null && isset($manifest['checksums'])) {
                $mismatches = $this->verifyExtractedChecksums($tmpDir, $manifest['checksums']);
                if (!empty($mismatches)) {
                    $report['warnings'][] = count($mismatches) . ' checksum mismatches: ' . implode(', ', array_slice($mismatches, 0, 5));
                }
                $report['steps'][] = 'Checksums verified (' . count($mismatches) . ' mismatches)';
            }

            // Import DB
            $dbSql = $tmpDir . '/database.sql';
            if (file_exists($dbSql)) {
                $db = new AlkanaDatabaseHandler(
                    host:   $dbConfig['host'] ?? 'localhost',
                    name:   $dbConfig['name'] ?? '',
                    user:   $dbConfig['user'] ?? '',
                    pass:   $dbConfig['pass'] ?? '',
                    prefix: $dbConfig['prefix'] ?? 'wp_',
                );
                $db->import($dbSql);
                $report['steps'][] = 'Database imported';

                // URL search-replace
                if ($newUrl !== null && $manifest !== null && isset($manifest['site_url'])) {
                    $oldUrl = rtrim($manifest['site_url'], '/');
                    $newUrl = rtrim($newUrl, '/');
                    if ($oldUrl !== $newUrl) {
                        $stats = $db->searchReplace($oldUrl, $newUrl);
                        $report['steps'][] = "URL replaced: {$oldUrl} → {$newUrl} ({$stats['changes']} changes in {$stats['tables']} tables)";
                    }
                }
            }

            // Extract wp-content
            $wpContentZip = $tmpDir . '/wp-content.zip';
            if (file_exists($wpContentZip)) {
                $wpContentTarget = $targetDir . '/wp-content';
                AlkanaFileArchiver::extractArchive($wpContentZip, $wpContentTarget);
                $report['steps'][] = 'wp-content restored';
            }

            return $report;
        } finally {
            if (is_dir($tmpDir)) {
                self::deleteDir($tmpDir);
            }
        }
    }

    /**
     * Read manifest from a backup ZIP without full extraction.
     */
    public function getManifest(string $zipPath): ?array
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return null;
        }

        $content = $zip->getFromName('manifest.json');
        $zip->close();

        return $content !== false ? json_decode($content, true) : null;
    }

    /**
     * Verify file checksums inside a backup ZIP.
     */
    public function verifyChecksums(string $zipPath): array
    {
        $manifest = $this->getManifest($zipPath);
        if ($manifest === null || !isset($manifest['checksums'])) {
            return ['error' => 'No manifest or checksums found'];
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return ['error' => 'Cannot open ZIP'];
        }

        $results = [];
        foreach ($manifest['checksums'] as $file => $expectedHash) {
            $content = $zip->getFromName($file);
            if ($content === false) {
                $results[$file] = 'missing';
            } else {
                $actual = hash('sha256', $content);
                $results[$file] = ($actual === $expectedHash) ? 'ok' : 'mismatch';
            }
        }

        $zip->close();
        return $results;
    }

    /**
     * Generate manifest.json data.
     */
    private function generateManifest(string $mode, string $tmpDir, string $wpRoot): array
    {
        $manifest = [
            'backup_date'  => gmdate('Y-m-d\TH:i:s\Z'),
            'backup_mode'  => $mode,
            'site_url'     => $this->detectSiteUrl($wpRoot),
            'wp_version'   => $this->detectWpVersion($wpRoot),
            'php_version'  => PHP_VERSION,
            'php_extensions' => get_loaded_extensions(),
            'plugins'      => $this->detectPlugins($wpRoot),
            'checksums'    => [],
        ];

        // Checksums for files in temp dir
        $manifest['checksums'] = AlkanaFileArchiver::generateChecksums($tmpDir);

        return $manifest;
    }

    /**
     * Detect site URL from wp-config or wp_options.
     */
    private function detectSiteUrl(string $wpRoot): string
    {
        // Try wp-config.php defines
        $configFile = $wpRoot . '/wp-config.php';
        if (file_exists($configFile)) {
            $content = file_get_contents($configFile);
            if (preg_match("/define\s*\(\s*['\"]WP_HOME['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/", $content, $m)) {
                return $m[1];
            }
            if (preg_match("/define\s*\(\s*['\"]WP_SITEURL['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/", $content, $m)) {
                return $m[1];
            }
        }

        return 'http://localhost';
    }

    /**
     * Detect WordPress version from wp-includes/version.php.
     */
    private function detectWpVersion(string $wpRoot): string
    {
        $versionFile = $wpRoot . '/wp-includes/version.php';
        if (file_exists($versionFile)) {
            $content = file_get_contents($versionFile);
            if (preg_match("/\\\$wp_version\s*=\s*['\"]([^'\"]+)['\"]/", $content, $m)) {
                return $m[1];
            }
        }
        return 'unknown';
    }

    /**
     * Detect active plugins from filesystem.
     */
    private function detectPlugins(string $wpRoot): array
    {
        $plugins = [];
        $pluginDir = $wpRoot . '/wp-content/plugins';
        if (!is_dir($pluginDir)) {
            return $plugins;
        }

        $dirs = new DirectoryIterator($pluginDir);
        foreach ($dirs as $dir) {
            if ($dir->isDot() || !$dir->isDir()) {
                continue;
            }

            $pluginFile = $dir->getPathname() . '/' . $dir->getFilename() . '.php';
            $headerFile = null;

            // Look for main plugin file
            if (file_exists($pluginFile)) {
                $headerFile = $pluginFile;
            } else {
                // Scan for PHP file with Plugin Name header
                foreach (glob($dir->getPathname() . '/*.php') as $php) {
                    $head = file_get_contents($php, false, null, 0, 8192);
                    if (stripos($head, 'Plugin Name:') !== false) {
                        $headerFile = $php;
                        break;
                    }
                }
            }

            if ($headerFile !== null) {
                $head = file_get_contents($headerFile, false, null, 0, 8192);
                $name = $dir->getFilename();
                $version = 'unknown';
                if (preg_match('/Version:\s*(.+)/i', $head, $m)) {
                    $version = trim($m[1]);
                }
                if (preg_match('/Plugin Name:\s*(.+)/i', $head, $m)) {
                    $name = trim($m[1]);
                }
                $plugins[] = [
                    'slug'    => $dir->getFilename(),
                    'name'    => $name,
                    'version' => $version,
                ];
            }
        }

        return $plugins;
    }

    /**
     * Auto-rotate old backups, keeping only $keep most recent.
     */
    public function rotateBackups(string $backupDir, int $keep = 5): int
    {
        $files = glob($backupDir . '/alkana-*.zip');
        if ($files === false || count($files) <= $keep) {
            return 0;
        }

        // Sort by name (date-based naming = chronological)
        sort($files);
        $toDelete = array_slice($files, 0, count($files) - $keep);
        $deleted = 0;

        foreach ($toDelete as $file) {
            if (@unlink($file)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * List existing backups.
     */
    public function listBackups(?string $backupDir = null): array
    {
        $backupDir = $backupDir ?? ALKANA_BACKUP_DIR;
        $files = glob($backupDir . '/alkana-*.zip');
        if ($files === false) {
            return [];
        }

        $backups = [];
        foreach ($files as $file) {
            $manifest = $this->getManifest($file);
            $backups[] = [
                'file'      => basename($file),
                'path'      => $file,
                'size'      => filesize($file),
                'size_human'=> self::humanFileSize(filesize($file)),
                'date'      => date('Y-m-d H:i:s', filemtime($file)),
                'mode'      => $manifest['backup_mode'] ?? 'unknown',
                'site_url'  => $manifest['site_url'] ?? 'unknown',
            ];
        }

        // Sort newest first
        usort($backups, fn($a, $b) => strcmp($b['date'], $a['date']));
        return $backups;
    }

    /**
     * Verify checksums of extracted files against manifest.
     */
    private function verifyExtractedChecksums(string $dir, array $expected): array
    {
        $mismatches = [];
        foreach ($expected as $file => $hash) {
            $path = $dir . '/' . $file;
            if (!file_exists($path)) {
                $mismatches[] = $file . ' (missing)';
            } elseif (hash_file('sha256', $path) !== $hash) {
                $mismatches[] = $file . ' (hash mismatch)';
            }
        }
        return $mismatches;
    }

    /**
     * Human-readable file size.
     */
    public static function humanFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 1) . ' ' . $units[$i];
    }

    /**
     * Return the WP root / base directory.
     * Reads ALKANA_TEST_ENV for test isolation; defaults to one level above scripts/.
     */
    public function getBaseDir(): string
    {
        $testBase = getenv('ALKANA_TEST_BASE_DIR');
        if ($testBase !== false && $testBase !== '') {
            return rtrim($testBase, DIRECTORY_SEPARATOR);
        }
        return realpath(__DIR__ . '/..');
    }

    /**
     * Return available disk space for the given path.
     * Extracted to allow mocking in tests.
     */
    protected function getDiskFreeSpace(string $path): int
    {
        $free = disk_free_space($path);
        return $free !== false ? (int) $free : PHP_INT_MAX;
    }

    /**
     * Recursively delete a directory.
     */
    public static function deleteDir(string $dir): bool
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
}

// ── CLI Entry Point ────────────────────────────────────────────────────────────
if (php_sapi_name() === 'cli' && isset($argv[0]) && realpath($argv[0]) === realpath(__FILE__)) {
    $options = getopt('', ['action:', 'mode:', 'output:', 'file:', 'url:', 'keep:', 'db-host:', 'db-name:', 'db-user:', 'db-pass:', 'db-prefix:', 'wp-root:']);

    $action = $options['action'] ?? 'help';
    $wpRoot = $options['wp-root'] ?? realpath(__DIR__ . '/..');

    // Auto-detect DB config
    $dbConfig = AlkanaToolkit::detectDbConfig($wpRoot);
    if (isset($options['db-host'])) {
        $dbConfig = [
            'host'   => $options['db-host'] ?? 'localhost',
            'name'   => $options['db-name'] ?? '',
            'user'   => $options['db-user'] ?? '',
            'pass'   => $options['db-pass'] ?? '',
            'prefix' => $options['db-prefix'] ?? 'wp_',
        ];
    }

    $toolkit = new AlkanaToolkit($dbConfig);

    switch ($action) {
        case 'backup':
            $mode = $options['mode'] ?? 'full';
            $output = $options['output'] ?? null;
            echo "Starting {$mode} backup...\n";
            try {
                $result = $toolkit->backup($mode, $output);
                echo "✅ Backup complete: {$result['file']}\n";
                echo "   Size: " . AlkanaToolkit::humanFileSize(filesize($result['file'])) . "\n";
                echo "   Mode: {$result['manifest']['backup_mode']}\n";
                echo "   Plugins: " . count($result['manifest']['plugins']) . "\n";
            } catch (\Throwable $e) {
                fwrite(STDERR, "❌ Backup failed: " . $e->getMessage() . "\n");
                exit(1);
            }
            break;

        case 'restore':
            $file = $options['file'] ?? null;
            $url  = $options['url'] ?? null;
            if (!$file) {
                fwrite(STDERR, "❌ --file is required for restore\n");
                exit(1);
            }
            if (!$dbConfig) {
                fwrite(STDERR, "❌ Database config required (--db-host, --db-name, --db-user, --db-pass or wp-config.php)\n");
                exit(1);
            }
            echo "Starting restore from $file...\n";
            try {
                $report = $toolkit->restore($file, $dbConfig, $url);
                foreach ($report['steps'] as $step) {
                    echo "  ✅ $step\n";
                }
                if (!empty($report['warnings'])) {
                    foreach ($report['warnings'] as $w) {
                        echo "  ⚠️ $w\n";
                    }
                }
                echo "✅ Restore complete!\n";
            } catch (\Throwable $e) {
                fwrite(STDERR, "❌ Restore failed: " . $e->getMessage() . "\n");
                exit(1);
            }
            break;

        case 'verify':
            $file = $options['file'] ?? null;
            if (!$file) {
                fwrite(STDERR, "❌ --file is required for verify\n");
                exit(1);
            }
            echo "Verifying $file...\n";
            $results = $toolkit->verifyChecksums($file);
            if (isset($results['error'])) {
                fwrite(STDERR, "❌ {$results['error']}\n");
                exit(1);
            }
            $ok = $fail = $missing = 0;
            foreach ($results as $f => $status) {
                $icon = match($status) {
                    'ok' => '✅',
                    'mismatch' => '❌',
                    'missing' => '⚠️',
                    default => '?',
                };
                echo "  $icon $f\n";
                match($status) {
                    'ok' => $ok++,
                    'mismatch' => $fail++,
                    'missing' => $missing++,
                    default => null,
                };
            }
            echo "\nTotal: $ok ok, $fail mismatches, $missing missing\n";
            break;

        case 'list':
            $backups = $toolkit->listBackups($options['output'] ?? null);
            if (empty($backups)) {
                echo "No backups found.\n";
            } else {
                echo sprintf("%-40s %-8s %-8s %s\n", 'File', 'Size', 'Mode', 'Date');
                echo str_repeat('-', 80) . "\n";
                foreach ($backups as $b) {
                    echo sprintf("%-40s %-8s %-8s %s\n", $b['file'], $b['size_human'], $b['mode'], $b['date']);
                }
            }
            break;

        default:
            echo "Alkana Backup Toolkit\n";
            echo "Usage: php alkana-toolkit.php --action=<action> [options]\n\n";
            echo "Actions:\n";
            echo "  backup   Create a backup (--mode=full|db|files, --output=dir)\n";
            echo "  restore  Restore from backup (--file=path, --url=new-url)\n";
            echo "  verify   Verify backup checksums (--file=path)\n";
            echo "  list     List existing backups\n";
            break;
    }
}
