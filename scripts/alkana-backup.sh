#!/usr/bin/env bash
#
# Alkana Backup Toolkit — Bash CLI Wrapper
# Usage:
#   ./scripts/alkana-backup.sh --action backup --mode full
#   ./scripts/alkana-backup.sh --action deploy --target production
#   ./scripts/alkana-backup.sh --action restore --file ./backups/alkana-full-20260417.zip --url "https://alkana.vn"
#   ./scripts/alkana-backup.sh --action list
#   ./scripts/alkana-backup.sh --action verify --file ./backups/alkana-full-20260417.zip
#

set -euo pipefail

# ── Paths ───────────────────────────────────────────────────────────────────────
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
TOOLKIT_PHP="$SCRIPT_DIR/alkana-toolkit.php"
THEME_DIR="$PROJECT_ROOT/wp-content/themes/alkana"

# ── Defaults ────────────────────────────────────────────────────────────────────
ACTION=""
MODE="full"
FILE=""
TARGET="production"
URL=""
KEEP=5
CLEANUP=false

# ── Colors ──────────────────────────────────────────────────────────────────────
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

status()  { echo -e "  ${GREEN}✅${NC} $1"; }
warn()    { echo -e "  ${YELLOW}⚠️${NC}  $1"; }
err()     { echo -e "  ${RED}❌${NC} $1"; }
header()  { echo -e "\n${CYAN}══════════════════════════════════════════════${NC}"; echo -e "  ${CYAN}$1${NC}"; echo -e "${CYAN}══════════════════════════════════════════════${NC}\n"; }

# ── Arg parsing ─────────────────────────────────────────────────────────────────
while [[ $# -gt 0 ]]; do
    case "$1" in
        --action)  ACTION="$2";  shift 2 ;;
        --mode)    MODE="$2";    shift 2 ;;
        --file)    FILE="$2";    shift 2 ;;
        --target)  TARGET="$2";  shift 2 ;;
        --url)     URL="$2";     shift 2 ;;
        --keep)    KEEP="$2";    shift 2 ;;
        --cleanup) CLEANUP=true; shift   ;;
        *) err "Unknown option: $1"; exit 1 ;;
    esac
done

if [[ -z "$ACTION" ]]; then
    echo "Alkana Backup Toolkit"
    echo "Usage: $0 --action <action> [options]"
    echo ""
    echo "Actions:"
    echo "  backup   Create a backup (--mode full|db|files)"
    echo "  restore  Restore from backup (--file path [--url new-url])"
    echo "  deploy   Build + backup for deployment (--target production|staging)"
    echo "  list     List existing backups"
    echo "  verify   Verify backup checksums (--file path)"
    exit 0
fi

# ── Prerequisite check ──────────────────────────────────────────────────────────
header "Alkana Backup Toolkit"

if [[ ! -f "$TOOLKIT_PHP" ]]; then
    err "Core engine not found: $TOOLKIT_PHP"
    exit 1
fi

if ! command -v php &>/dev/null; then
    err "PHP not found in PATH"
    exit 1
fi
status "PHP found: $(command -v php)"

# ── Actions ─────────────────────────────────────────────────────────────────────
case "$ACTION" in
    backup)
        echo -e "${YELLOW}Starting $MODE backup...${NC}"
        php "$TOOLKIT_PHP" --action=backup --mode="$MODE" --wp-root="$PROJECT_ROOT"
        ;;

    restore)
        if [[ -z "$FILE" ]]; then
            err "--file is required for restore"
            exit 1
        fi
        if [[ ! -f "$FILE" ]]; then
            err "Backup file not found: $FILE"
            exit 1
        fi
        echo -e "${YELLOW}Starting restore from $FILE...${NC}"
        ARGS="--action=restore --file=$FILE --wp-root=$PROJECT_ROOT"
        if [[ -n "$URL" ]]; then
            ARGS="$ARGS --url=$URL"
        fi
        php "$TOOLKIT_PHP" $ARGS
        ;;

    deploy)
        echo -e "${YELLOW}Starting deploy pipeline ($TARGET)...${NC}"
        echo ""

        # Step 1: Vite build
        echo -e "${CYAN}[1/3] Building frontend assets...${NC}"
        if [[ ! -f "$THEME_DIR/package.json" ]]; then
            warn "No package.json in theme dir — skipping Vite build"
        else
            pushd "$THEME_DIR" > /dev/null
            if ! command -v npm &>/dev/null; then
                err "npm not found in PATH"
                popd > /dev/null
                exit 1
            fi
            if ! npm run build; then
                err "Vite build failed"
                popd > /dev/null
                exit 1
            fi
            status "Vite build complete"
            popd > /dev/null
        fi

        # Step 2: PHP lint
        echo -e "${CYAN}[2/3] Running PHP lint...${NC}"
        LINT_FAILED=false
        LINT_COUNT=0
        while IFS= read -r -d '' phpfile; do
            if ! php -l "$phpfile" &>/dev/null; then
                err "Lint failed: $(basename "$phpfile")"
                LINT_FAILED=true
            fi
            LINT_COUNT=$((LINT_COUNT + 1))
        done < <(find "$THEME_DIR" -name '*.php' -not -path '*/node_modules/*' -not -path '*/vendor/*' -print0)

        if $LINT_FAILED; then
            err "PHP lint errors found — aborting deploy"
            exit 1
        fi
        status "PHP lint passed ($LINT_COUNT files)"

        # Step 3: Backup
        echo -e "${CYAN}[3/3] Creating deploy package...${NC}"
        php "$TOOLKIT_PHP" --action=backup --mode=full --wp-root="$PROJECT_ROOT"

        echo ""
        status "Deploy package ready! Upload the ZIP to your hosting and run alkana-restore.php"

        # Optional cleanup
        if $CLEANUP; then
            echo -e "${YELLOW}Cleaning up temporary files...${NC}"
            CLEANED=0
            for d in "$PROJECT_ROOT"/backups/alkana_tmp_*; do
                if [[ -d "$d" ]]; then
                    rm -rf "$d"
                    CLEANED=$((CLEANED + 1))
                fi
            done
            if [[ $CLEANED -gt 0 ]]; then
                status "Cleaned up $CLEANED temporary directories"
            fi
        fi
        echo ""
        ;;

    list)
        php "$TOOLKIT_PHP" --action=list --wp-root="$PROJECT_ROOT"
        ;;

    verify)
        if [[ -z "$FILE" ]]; then
            err "--file is required for verify"
            exit 1
        fi
        if [[ ! -f "$FILE" ]]; then
            err "Backup file not found: $FILE"
            exit 1
        fi
        php "$TOOLKIT_PHP" --action=verify --file="$FILE" --wp-root="$PROJECT_ROOT"
        ;;

    *)
        err "Unknown action: $ACTION"
        exit 1
        ;;
esac
