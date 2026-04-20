# Alkana Toolkit — PowerShell CLI Smoke Tests
# Phase 05: Verifies toolkit CLI behavior without a real WP install

$ScriptsDir   = [System.IO.Path]::GetFullPath("$PSScriptRoot\..\..\..\scripts")
$ToolkitPath  = "$ScriptsDir\alkana-toolkit.php"
$RestorePath  = "$ScriptsDir\alkana-restore.php"
$BackupPsPath = "$ScriptsDir\alkana-backup.ps1"

$pass = 0
$fail = 0
$errors = @()

function Assert-Equal {
    param([string]$label, $expected, $actual)
    if ($expected -eq $actual) {
        Write-Host "  ✅ $label" -ForegroundColor Green
        $script:pass++
    } else {
        Write-Host "  ❌ $label — expected: $expected, got: $actual" -ForegroundColor Red
        $script:fail++
        $script:errors += $label
    }
}

function Assert-Contains {
    param([string]$label, [string]$haystack, [string]$needle)
    if ($haystack -like "*$needle*") {
        Write-Host "  ✅ $label" -ForegroundColor Green
        $script:pass++
    } else {
        Write-Host "  ❌ $label — '$needle' not found in output" -ForegroundColor Red
        $script:fail++
        $script:errors += $label
    }
}

function Assert-NotContains {
    param([string]$label, [string]$haystack, [string]$needle)
    if ($haystack -notlike "*$needle*") {
        Write-Host "  ✅ $label" -ForegroundColor Green
        $script:pass++
    } else {
        Write-Host "  ❌ $label — '$needle' found but should not be present" -ForegroundColor Red
        $script:fail++
        $script:errors += $label
    }
}

Write-Host "`nAlkana CLI Smoke Tests (PowerShell)" -ForegroundColor Cyan
Write-Host "====================================`n"

# ── Test 1: PHP CLI accessible ───────────────────────────────────────────────
Write-Host "1. PHP CLI check" -ForegroundColor Yellow
$phpVersion = (php --version 2>&1) -join " "
Assert-Contains "PHP CLI is available" $phpVersion "PHP"

# ── Test 2: Toolkit syntax check ──────────────────────────────────────────────
Write-Host "`n2. Toolkit syntax check" -ForegroundColor Yellow
$syntaxCheck = (php -l $ToolkitPath 2>&1) -join " "
Assert-Contains "alkana-toolkit.php has no syntax errors" $syntaxCheck "No syntax errors"

# ── Test 3: Toolkit --help output ─────────────────────────────────────────────
Write-Host "`n3. Toolkit help output" -ForegroundColor Yellow
$helpOutput = (php $ToolkitPath --action=help 2>&1) -join "`n"
Assert-Contains "Help shows backup action" $helpOutput "backup"
Assert-Contains "Help shows restore action" $helpOutput "restore"

# ── Test 4: Toolkit rejects unknown action gracefully ─────────────────────────
Write-Host "`n4. Unknown action handling" -ForegroundColor Yellow
$unknownOutput = (php $ToolkitPath --action=unknown_xyz 2>&1) -join "`n"
# Should not crash with a fatal error
Assert-NotContains "No PHP fatal error on unknown action" $unknownOutput "Fatal error"

# ── Test 5: Restore action requires --file ────────────────────────────────────
Write-Host "`n5. Restore requires --file" -ForegroundColor Yellow
$restoreNoFile = (php $ToolkitPath --action=restore 2>&1) -join "`n"
Assert-Contains "Restore complains about missing --file" $restoreNoFile "--file"

# ── Test 6: Script files exist ────────────────────────────────────────────────
Write-Host "`n6. Script files exist" -ForegroundColor Yellow
if (Test-Path $ToolkitPath) {
    Write-Host "  ✅ alkana-toolkit.php exists" -ForegroundColor Green
    $script:pass++
} else {
    Write-Host "  ❌ alkana-toolkit.php not found at: $ToolkitPath" -ForegroundColor Red
    $script:fail++
    $script:errors += "alkana-toolkit.php exists"
}
if (Test-Path $BackupPsPath) {
    Write-Host "  ✅ alkana-backup.ps1 exists" -ForegroundColor Green
    $script:pass++
} else {
    Write-Host "  ⚠ alkana-backup.ps1 not found (optional)" -ForegroundColor Yellow
}

# ── Test 7: restore.php syntax check ─────────────────────────────────────────
Write-Host "`n7. restore.php syntax check" -ForegroundColor Yellow
$restoreSyntax = (php -l $RestorePath 2>&1) -join " "
Assert-Contains "alkana-restore.php has no syntax errors" $restoreSyntax "No syntax errors"

# ── Test 8: Toolkit class structure via reflection ────────────────────────────
Write-Host "`n8. Class structure via PHP reflection" -ForegroundColor Yellow
$toolkitForward = $ToolkitPath -replace "\\", "/"
$phpLines = [System.Collections.Generic.List[string]]::new()
$phpLines.Add("<?php")
$phpLines.Add("require_once '$toolkitForward';")
$phpLines.Add('$classes = ["AlkanaSerializer","AlkanaDatabaseHandler","AlkanaFileArchiver","AlkanaToolkit"];')
$phpLines.Add('foreach ($classes as $cls) { echo class_exists($cls) ? "OK:$cls\n" : "MISSING:$cls\n"; }')
$phpLines.Add('$ref = new ReflectionMethod("AlkanaToolkit","getDiskFreeSpace");')
$phpLines.Add('echo $ref->isProtected() ? "OK:getDiskFreeSpace-protected\n" : "FAIL:not-protected\n";')
$phpLines.Add('$ref2 = new ReflectionMethod("AlkanaToolkit","getBaseDir");')
$phpLines.Add('echo $ref2->isPublic() ? "OK:getBaseDir-public\n" : "FAIL:not-public\n";')
$phpLines.Add('$ctor = new ReflectionMethod("AlkanaDatabaseHandler","__construct");')
$phpLines.Add('$has = false; foreach ($ctor->getParameters() as $p) { if ($p->getName()==="injectedPdo") $has=true; }')
$phpLines.Add('echo $has ? "OK:injectedPdo-param\n" : "FAIL:injectedPdo-param-missing\n";')
$reflectFile = [System.IO.Path]::GetTempFileName() + ".php"
[System.IO.File]::WriteAllLines($reflectFile, $phpLines)
$reflectOutput = (php $reflectFile 2>&1) -join "`n"
Remove-Item $reflectFile -ErrorAction SilentlyContinue

Assert-Contains "AlkanaSerializer exists" $reflectOutput "OK:AlkanaSerializer"
Assert-Contains "AlkanaDatabaseHandler exists" $reflectOutput "OK:AlkanaDatabaseHandler"
Assert-Contains "AlkanaFileArchiver exists" $reflectOutput "OK:AlkanaFileArchiver"
Assert-Contains "AlkanaToolkit exists" $reflectOutput "OK:AlkanaToolkit"
Assert-Contains "getDiskFreeSpace is protected (D-2 refactor)" $reflectOutput "OK:getDiskFreeSpace-protected"
Assert-Contains "getBaseDir is public (D-3 refactor)" $reflectOutput "OK:getBaseDir-public"
Assert-Contains "injectedPdo param exists (D-1 refactor)" $reflectOutput "OK:injectedPdo-param"

# ── Summary ───────────────────────────────────────────────────────────────────
Write-Host "`n=====================================" -ForegroundColor Cyan
Write-Host "CLI Smoke Tests Complete" -ForegroundColor Cyan
Write-Host "  Passed: $pass" -ForegroundColor Green
Write-Host "  Failed: $fail" -ForegroundColor $(if ($fail -gt 0) { "Red" } else { "Green" })

if ($errors.Count -gt 0) {
    Write-Host "`nFailed tests:" -ForegroundColor Red
    $errors | ForEach-Object { Write-Host "  - $_" -ForegroundColor Red }
}

if ($fail -gt 0) {
    exit 1
}
exit 0
