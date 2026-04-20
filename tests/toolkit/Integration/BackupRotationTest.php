<?php
/**
 * Phase 03b — Backup Rotation Tests
 *
 * Covers:
 * - Scenario 12.1: Off-by-one in rotation deletes new backup
 * - rotateBackups() keeps exactly N most recent
 * - rotateBackups() does nothing when count <= keep
 * - rotateBackups() returns correct deleted count
 * - rotateBackups() sorts by filename (date-based naming)
 */

declare(strict_types=1);

namespace AlkanaTests\Integration;

use PHPUnit\Framework\TestCase;

class BackupRotationTest extends TestCase
{
    private string $tmpDir;
    private \AlkanaToolkit $toolkit;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/alkana_rot_' . uniqid();
        mkdir($this->tmpDir, 0755, true);
        $this->toolkit = new \AlkanaToolkit(null);
    }

    protected function tearDown(): void
    {
        \AlkanaToolkit::deleteDir($this->tmpDir);
    }

    private function createFakeBackup(string $name): string
    {
        $path = $this->tmpDir . '/alkana-' . $name . '.zip';
        file_put_contents($path, 'fake-zip-content-' . $name);
        return $path;
    }

    // ── Scenario 12.1: Off-by-one check ───────────────────────────────────────

    public function testRotationKeepsExactlyNFiles(): void
    {
        // Create 7 backups, keep 5 → must delete exactly 2 oldest
        $files = [];
        for ($i = 1; $i <= 7; $i++) {
            // Use zero-padded names so sort order is stable
            $files[] = $this->createFakeBackup(sprintf('20260101-%06d', $i));
            usleep(1000); // ensure distinct mtime
        }

        $deleted = $this->toolkit->rotateBackups($this->tmpDir, 5);

        $remaining = glob($this->tmpDir . '/alkana-*.zip');
        $this->assertCount(5, $remaining, 'Must keep exactly 5 backups');
        $this->assertSame(2, $deleted, 'Must delete exactly 2 old backups');
    }

    public function testRotationDeletesOldestNotNewest(): void
    {
        $old1 = $this->createFakeBackup('20260101-000001');
        $old2 = $this->createFakeBackup('20260101-000002');
        $new1 = $this->createFakeBackup('20260115-000001');
        $new2 = $this->createFakeBackup('20260115-000002');
        $new3 = $this->createFakeBackup('20260120-000001');

        $this->toolkit->rotateBackups($this->tmpDir, 3);

        // Old files must be gone
        $this->assertFileDoesNotExist($old1, 'Oldest backup must be deleted');
        $this->assertFileDoesNotExist($old2, 'Second oldest must be deleted');

        // New files must remain
        $this->assertFileExists($new3, 'Newest backup must be preserved');
    }

    public function testRotationDoesNothingWhenCountEqualsKeep(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->createFakeBackup("20260101-00000$i");
        }

        $deleted = $this->toolkit->rotateBackups($this->tmpDir, 5);
        $remaining = glob($this->tmpDir . '/alkana-*.zip');

        $this->assertSame(0, $deleted);
        $this->assertCount(5, $remaining);
    }

    public function testRotationDoesNothingWhenCountBelowKeep(): void
    {
        $this->createFakeBackup('20260101-000001');
        $this->createFakeBackup('20260101-000002');

        $deleted = $this->toolkit->rotateBackups($this->tmpDir, 5);
        $this->assertSame(0, $deleted);
    }

    public function testRotationExactlyAtBoundaryKeepsPlusOne(): void
    {
        // 6 files, keep 5 → delete exactly 1
        for ($i = 1; $i <= 6; $i++) {
            $this->createFakeBackup(sprintf('20260101-%06d', $i));
        }

        $deleted = $this->toolkit->rotateBackups($this->tmpDir, 5);
        $remaining = glob($this->tmpDir . '/alkana-*.zip');

        $this->assertSame(1, $deleted, 'Exactly 1 file should be deleted at boundary');
        $this->assertCount(5, $remaining);
    }

    public function testRotationOnEmptyDirectory(): void
    {
        $deleted = $this->toolkit->rotateBackups($this->tmpDir, 5);
        $this->assertSame(0, $deleted);
    }

    public function testRotationKeepZeroDeletesAll(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $this->createFakeBackup("20260101-00000$i");
        }

        $deleted = $this->toolkit->rotateBackups($this->tmpDir, 0);
        $remaining = glob($this->tmpDir . '/alkana-*.zip');
        $this->assertSame(3, $deleted);
        $this->assertCount(0, $remaining);
    }
}
