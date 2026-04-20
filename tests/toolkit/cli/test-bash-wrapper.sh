#!/usr/bin/env bash
# Alkana Toolkit — Bash CLI Smoke Tests
# Phase 05: Verifies toolkit CLI behavior on Linux/macOS

TOOLKIT_PATH="${1:-$(dirname "$0")/../../scripts/alkana-toolkit.php}"
RESTORE_PATH="$(dirname "$0")/../../scripts/alkana-restore.php"
BACKUP_SCRIPT="$(dirname "$0")/../../scripts/alkana-backup.sh"

PASS=0
FAIL=0
ERRORS=()

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

assert_contains() {
    local label="$1"
    local haystack="$2"
    local needle="$3"
    if echo "$haystack" | grep -q "$needle"; then
        echo -e "  ${GREEN}✅ $label${NC}"
        ((PASS++))
    else
        echo -e "  ${RED}❌ $label — '$needle' not found${NC}"
        ((FAIL++))
        ERRORS+=("$label")
    fi
}

assert_not_contains() {
    local label="$1"
    local haystack="$2"
    local needle="$3"
    if ! echo "$haystack" | grep -q "$needle"; then
        echo -e "  ${GREEN}✅ $label${NC}"
        ((PASS++))
    else
        echo -e "  ${RED}❌ $label — '$needle' found but should not be${NC}"
        ((FAIL++))
        ERRORS+=("$label")
    fi
}

assert_file_exists() {
    local label="$1"
    local file="$2"
    if [ -f "$file" ]; then
        echo -e "  ${GREEN}✅ $label${NC}"
        ((PASS++))
    else
        echo -e "  ${RED}❌ $label — file not found: $file${NC}"
        ((FAIL++))
        ERRORS+=("$label")
    fi
}

echo -e "\n${CYAN}Alkana CLI Smoke Tests (Bash)${NC}"
echo "======================================"

# ── Test 1: PHP CLI accessible ─────────────────────────────────────────────────
echo -e "\n${YELLOW}1. PHP CLI check${NC}"
PHP_VER=$(php --version 2>&1)
assert_contains "PHP CLI is available" "$PHP_VER" "PHP"

# ── Test 2: Toolkit syntax check ──────────────────────────────────────────────
echo -e "\n${YELLOW}2. Toolkit syntax check${NC}"
SYNTAX=$(php -l "$TOOLKIT_PATH" 2>&1)
assert_contains "alkana-toolkit.php has no syntax errors" "$SYNTAX" "No syntax errors"

# ── Test 3: Toolkit --help output ─────────────────────────────────────────────
echo -e "\n${YELLOW}3. Toolkit help output${NC}"
HELP=$(php "$TOOLKIT_PATH" --action=help 2>&1)
assert_contains "Help shows backup action" "$HELP" "backup"
assert_contains "Help shows restore action" "$HELP" "restore"

# ── Test 4: Unknown action handling ───────────────────────────────────────────
echo -e "\n${YELLOW}4. Unknown action handling${NC}"
UNKNOWN=$(php "$TOOLKIT_PATH" --action=unknown_xyz 2>&1)
assert_not_contains "No PHP fatal error on unknown action" "$UNKNOWN" "Fatal error"

# ── Test 5: Restore requires --file ───────────────────────────────────────────
echo -e "\n${YELLOW}5. Restore requires --file${NC}"
RESTORE_NO_FILE=$(php "$TOOLKIT_PATH" --action=restore 2>&1)
assert_contains "Restore complains about missing --file" "$RESTORE_NO_FILE" "\-\-file"

# ── Test 6: Script files exist ────────────────────────────────────────────────
echo -e "\n${YELLOW}6. Script files exist${NC}"
assert_file_exists "alkana-toolkit.php exists" "$TOOLKIT_PATH"
assert_file_exists "alkana-restore.php exists" "$RESTORE_PATH"
assert_file_exists "alkana-backup.sh exists" "$BACKUP_SCRIPT"

# ── Test 7: restore.php syntax ────────────────────────────────────────────────
echo -e "\n${YELLOW}7. restore.php syntax check${NC}"
RESTORE_SYNTAX=$(php -l "$RESTORE_PATH" 2>&1)
assert_contains "alkana-restore.php has no syntax errors" "$RESTORE_SYNTAX" "No syntax errors"

# ── Test 8: Class structure via PHP reflection ────────────────────────────────
echo -e "\n${YELLOW}8. Class structure via PHP reflection${NC}"
REFLECT_SCRIPT=$(cat <<'PHPEOF'
<?php
$toolkitPath = $argv[1];
require_once $toolkitPath;
$classes = ['AlkanaSerializer', 'AlkanaDatabaseHandler', 'AlkanaFileArchiver', 'AlkanaToolkit'];
foreach ($classes as $cls) {
    echo class_exists($cls) ? "OK:$cls\n" : "MISSING:$cls\n";
}
$ref = new ReflectionMethod('AlkanaToolkit', 'getDiskFreeSpace');
echo $ref->isProtected() ? "OK:getDiskFreeSpace-protected\n" : "FAIL:getDiskFreeSpace-not-protected\n";
$ref2 = new ReflectionMethod('AlkanaToolkit', 'getBaseDir');
echo $ref2->isPublic() ? "OK:getBaseDir-public\n" : "FAIL:getBaseDir-not-public\n";
$ctor = new ReflectionMethod('AlkanaDatabaseHandler', '__construct');
$hasInjectedPdo = false;
foreach ($ctor->getParameters() as $p) {
    if ($p->getName() === 'injectedPdo') $hasInjectedPdo = true;
}
echo $hasInjectedPdo ? "OK:injectedPdo-param\n" : "FAIL:injectedPdo-param-missing\n";
PHPEOF
)

REFLECT=$(echo "$REFLECT_SCRIPT" | php -- "$TOOLKIT_PATH" 2>&1)
assert_contains "AlkanaSerializer exists" "$REFLECT" "OK:AlkanaSerializer"
assert_contains "AlkanaDatabaseHandler exists" "$REFLECT" "OK:AlkanaDatabaseHandler"
assert_contains "AlkanaFileArchiver exists" "$REFLECT" "OK:AlkanaFileArchiver"
assert_contains "AlkanaToolkit exists" "$REFLECT" "OK:AlkanaToolkit"
assert_contains "getDiskFreeSpace is protected (D-2)" "$REFLECT" "OK:getDiskFreeSpace-protected"
assert_contains "getBaseDir is public (D-3)" "$REFLECT" "OK:getBaseDir-public"
assert_contains "injectedPdo param exists (D-1)" "$REFLECT" "OK:injectedPdo-param"

# ── Summary ───────────────────────────────────────────────────────────────────
echo -e "\n${CYAN}======================================${NC}"
echo -e "${CYAN}CLI Smoke Tests Complete${NC}"
echo -e "  ${GREEN}Passed: $PASS${NC}"
if [ $FAIL -gt 0 ]; then
    echo -e "  ${RED}Failed: $FAIL${NC}"
    echo -e "\nFailed tests:"
    for err in "${ERRORS[@]}"; do
        echo -e "  ${RED}- $err${NC}"
    done
    exit 1
else
    echo -e "  ${GREEN}Failed: 0${NC}"
fi
exit 0
