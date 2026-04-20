<#
.SYNOPSIS
    Alkana Backup Toolkit — PowerShell CLI Wrapper
.DESCRIPTION
    Wrapper for alkana-toolkit.php: backup, restore, deploy, list, verify.
.EXAMPLE
    ./scripts/alkana-backup.ps1 -Action backup -Mode full
    ./scripts/alkana-backup.ps1 -Action deploy -Target production
    ./scripts/alkana-backup.ps1 -Action restore -File ./backups/alkana-full-20260417.zip -Url "https://alkana.vn"
    ./scripts/alkana-backup.ps1 -Action list
    ./scripts/alkana-backup.ps1 -Action verify -File ./backups/alkana-full-20260417.zip
#>

param(
    [Parameter(Mandatory)]
    [ValidateSet('backup', 'restore', 'deploy', 'list', 'verify')]
    [string]$Action,

    [ValidateSet('full', 'db', 'files')]
    [string]$Mode = 'full',

    [string]$File,

    [ValidateSet('production', 'staging')]
    [string]$Target = 'production',

    [string]$Url,

    [int]$Keep = 5,

    [switch]$CleanUp
)

$ErrorActionPreference = 'Stop'

# ── Paths ───────────────────────────────────────────────────────────────────────
$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$ProjectRoot = Split-Path -Parent $ScriptDir
$ToolkitPhp = Join-Path $ScriptDir 'alkana-toolkit.php'
$ThemeDir = Join-Path $ProjectRoot 'wp-content\themes\alkana'

# ── Helpers ─────────────────────────────────────────────────────────────────────
function Write-Status {
    param([string]$Message, [string]$Color = 'Green')
    Write-Host "  ✅ " -NoNewline -ForegroundColor $Color
    Write-Host $Message
}

function Write-Warn {
    param([string]$Message)
    Write-Host "  ⚠️  " -NoNewline -ForegroundColor Yellow
    Write-Host $Message
}

function Write-Err {
    param([string]$Message)
    Write-Host "  ❌ " -NoNewline -ForegroundColor Red
    Write-Host $Message
}

function Write-Header {
    param([string]$Title)
    Write-Host ""
    Write-Host "══════════════════════════════════════════════" -ForegroundColor Cyan
    Write-Host "  $Title" -ForegroundColor Cyan
    Write-Host "══════════════════════════════════════════════" -ForegroundColor Cyan
    Write-Host ""
}

function Test-Prerequisite {
    param([string]$Command, [string]$Label)
    $found = Get-Command $Command -ErrorAction SilentlyContinue
    if (-not $found) {
        Write-Err "$Label not found in PATH"
        return $false
    }
    Write-Status "$Label found: $($found.Source)"
    return $true
}

# ── Prerequisite Check ──────────────────────────────────────────────────────────
Write-Header "Alkana Backup Toolkit"

if (-not (Test-Path $ToolkitPhp)) {
    Write-Err "Core engine not found: $ToolkitPhp"
    exit 1
}

if (-not (Test-Prerequisite 'php' 'PHP')) {
    exit 1
}

# ── Actions ─────────────────────────────────────────────────────────────────────
switch ($Action) {
    'backup' {
        Write-Host "Starting $Mode backup..." -ForegroundColor Yellow
        $args = @("$ToolkitPhp", "--action=backup", "--mode=$Mode", "--wp-root=$ProjectRoot")
        $output = & php @args 2>&1
        $exitCode = $LASTEXITCODE
        foreach ($line in ($output -split "`n")) {
            if ($line.Trim()) { Write-Host "  $line" }
        }
        if ($exitCode -ne 0) {
            Write-Err "Backup failed (exit code $exitCode)"
            exit 1
        }
        Write-Host ""
    }

    'restore' {
        if (-not $File) {
            Write-Err "-File parameter is required for restore"
            exit 1
        }
        if (-not (Test-Path $File)) {
            Write-Err "Backup file not found: $File"
            exit 1
        }
        Write-Host "Starting restore from $File..." -ForegroundColor Yellow
        $args = @("$ToolkitPhp", "--action=restore", "--file=$File", "--wp-root=$ProjectRoot")
        if ($Url) { $args += "--url=$Url" }
        $output = & php @args 2>&1
        $exitCode = $LASTEXITCODE
        foreach ($line in ($output -split "`n")) {
            if ($line.Trim()) { Write-Host "  $line" }
        }
        if ($exitCode -ne 0) {
            Write-Err "Restore failed (exit code $exitCode)"
            exit 1
        }
        Write-Host ""
    }

    'deploy' {
        Write-Host "Starting deploy pipeline ($Target)..." -ForegroundColor Yellow
        Write-Host ""

        # Step 1: Vite build
        Write-Host "[1/3] Building frontend assets..." -ForegroundColor Cyan
        if (-not (Test-Path (Join-Path $ThemeDir 'package.json'))) {
            Write-Warn "No package.json in theme dir — skipping Vite build"
        } else {
            Push-Location $ThemeDir
            try {
                if (-not (Test-Prerequisite 'npm' 'npm')) {
                    Pop-Location
                    exit 1
                }
                $buildOutput = & npm run build 2>&1
                $buildExit = $LASTEXITCODE
                if ($buildExit -ne 0) {
                    Write-Err "Vite build failed:"
                    $buildOutput | ForEach-Object { Write-Host "    $_" -ForegroundColor Red }
                    Pop-Location
                    exit 1
                }
                Write-Status "Vite build complete"
            } finally {
                Pop-Location
            }
        }

        # Step 2: PHP lint
        Write-Host "[2/3] Running PHP lint..." -ForegroundColor Cyan
        $phpFiles = Get-ChildItem -Path (Join-Path $ThemeDir '*.php') -Recurse -ErrorAction SilentlyContinue |
            Where-Object { $_.FullName -notmatch 'node_modules|vendor' }
        $lintFailed = $false
        foreach ($f in $phpFiles) {
            $lint = & php -l $f.FullName 2>&1
            if ($LASTEXITCODE -ne 0) {
                Write-Err "Lint failed: $($f.Name)"
                $lintFailed = $true
            }
        }
        if ($lintFailed) {
            Write-Err "PHP lint errors found — aborting deploy"
            exit 1
        }
        Write-Status "PHP lint passed ($($phpFiles.Count) files)"

        # Step 3: Backup
        Write-Host "[3/3] Creating deploy package..." -ForegroundColor Cyan
        $args = @("$ToolkitPhp", "--action=backup", "--mode=full", "--wp-root=$ProjectRoot")
        $output = & php @args 2>&1
        $exitCode = $LASTEXITCODE
        foreach ($line in ($output -split "`n")) {
            if ($line.Trim()) { Write-Host "  $line" }
        }
        if ($exitCode -ne 0) {
            Write-Err "Deploy package creation failed"
            exit 1
        }

        Write-Host ""
        Write-Status "Deploy package ready! Upload the ZIP to your hosting and run alkana-restore.php"

        # Optional cleanup
        if ($CleanUp) {
            Write-Host "Cleaning up temporary files..." -ForegroundColor Yellow
            $tmpDirs = Get-ChildItem -Path (Join-Path $ProjectRoot 'backups') -Directory -Filter 'alkana_tmp_*' -ErrorAction SilentlyContinue
            $cleaned = 0
            foreach ($d in $tmpDirs) {
                Remove-Item $d.FullName -Recurse -Force -ErrorAction SilentlyContinue
                $cleaned++
            }
            if ($cleaned -gt 0) {
                Write-Status "Cleaned up $cleaned temporary directories"
            }
        }
        Write-Host ""
    }

    'list' {
        $args = @("$ToolkitPhp", "--action=list", "--wp-root=$ProjectRoot")
        $output = & php @args 2>&1
        foreach ($line in ($output -split "`n")) {
            Write-Host $line
        }
    }

    'verify' {
        if (-not $File) {
            Write-Err "-File parameter is required for verify"
            exit 1
        }
        if (-not (Test-Path $File)) {
            Write-Err "Backup file not found: $File"
            exit 1
        }
        $args = @("$ToolkitPhp", "--action=verify", "--file=$File", "--wp-root=$ProjectRoot")
        $output = & php @args 2>&1
        foreach ($line in ($output -split "`n")) {
            Write-Host $line
        }
    }
}
