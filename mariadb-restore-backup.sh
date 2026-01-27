#!/usr/bin/env bash
#
# Restore MariaDB datadir from mariabackup FULL + (optional) INCREMENTAL chain.
# have to be run with sudo (root permission required)
# Modes:
#   --prepare-only   Prepare into WORK_ROOT only (no MariaDB stop, no copy-back)
#   (default)        Full restore (stops MariaDB, moves datadir aside, copy-back)
#
# Usage examples:
#   sudo mariadb-restore-backup.sh --prepare-only --full /path/full --inc /path/inc1 --inc /path/inc2
#   sudo mariadb-restore-backup.sh --full /path/full --inc /path/inc1
#
set -euo pipefail

########################################
# CONFIG (edit these)
########################################
MARIADB_SERVICE="mariadb"
WORK_ROOT="/home/damic/local-recovery"   # scratch prep area

# Email alerts
EMAIL_TO="xzhu98@jh.edu damicm.sc@gmail.com"
EMAIL_FROM=""   # optional
SUBJECT_PREFIX="[CCDQC Restore]"

# If mariadb is down / mysql CLI can't connect, fall back to this datadir:
DATADIR_FALLBACK="/var/lib/mysql/"

# If mysql client needs defaults-file to connect, set it here (optional):
MYSQL_DEFAULTS_FILE="/etc/my.cnf.d/cron-backup.cnf"  # e.g. "/etc/mysql/cron-backup.cnf" or "/root/.my.cnf"

LOG_TAIL_LINES=160
########################################

# CLI args
FULL_DIR=""
INCR_DIRS=()
DATADIR=""          # auto-detected unless --datadir is passed
PREPARE_ONLY=false

die() { echo "ERROR: $*" >&2; exit 1; }

require_dir() {
  local d="$1"
  [[ -n "$d" ]] || die "Empty directory path"
  [[ -d "$d" ]] || die "Directory not found: $d"
}

send_mail() {
  local subject="$1"
  local body="$2"

  # Convert commas to spaces just in case someone uses commas later
  local recipients="${EMAIL_TO//,/ }"

  # Sanitize: drop NULs, strip CR, keep tabs/newlines + printable ASCII
  local clean
  clean="$(printf "%s" "$body" \
    | tr -d '\000' \
    | sed $'s/\r//g' \
    | tr -cd '\11\12\15\40-\176')"

  if command -v mail >/dev/null 2>&1; then
    if [[ -n "${EMAIL_FROM:-}" ]]; then
      printf "%s\n" "$clean" | mail -r "$EMAIL_FROM" -s "$subject" $recipients
    else
      printf "%s\n" "$clean" | mail -s "$subject" $recipients
    fi
  fi
}


mysql_cmd() {
  if [[ -n "$MYSQL_DEFAULTS_FILE" ]]; then
    mysql --defaults-file="$MYSQL_DEFAULTS_FILE" "$@"
  else
    mysql "$@"
  fi
}

detect_datadir() {
  local dd=""

  # Prefer querying the running server (most reliable)
  if command -v mysql >/dev/null 2>&1; then
    dd="$(mysql_cmd -Nse "SHOW VARIABLES LIKE 'datadir';" 2>/dev/null | awk '{print $2}' || true)"
  fi

  # Fallback: attempt to read defaults for mariadb (less reliable)
  if [[ -z "$dd" ]] && command -v mariadb >/dev/null 2>&1; then
    dd="$(mariadb --print-defaults 2>/dev/null | sed -n 's/.*--datadir=\([^ ]*\).*/\1/p' | head -n1 || true)"
  fi

  if [[ -n "$dd" ]]; then
    echo "${dd%/}"
  else
    echo "$DATADIR_FALLBACK"
  fi
}

usage() {
  sed -n '1,120p' "$0"
}

# -------- parse args --------
while [[ $# -gt 0 ]]; do
  case "$1" in
    --full)         FULL_DIR="${2:-}"; shift 2;;
    --inc)          INCR_DIRS+=("${2:-}"); shift 2;;
    --datadir)      DATADIR="${2:-}"; shift 2;;
    --service)      MARIADB_SERVICE="${2:-}"; shift 2;;
    --work)         WORK_ROOT="${2:-}"; shift 2;;
    --prepare-only) PREPARE_ONLY=true; shift 1;;
    -h|--help)      usage; exit 0;;
    *)              die "Unknown argument: $1";;
  esac
done

[[ -n "$FULL_DIR" ]] || die "Missing --full /path/to/full_backup"
require_dir "$FULL_DIR"
for d in "${INCR_DIRS[@]}"; do require_dir "$d"; done

# -------- runtime paths --------
umask 077
HOST="$(hostname -f 2>/dev/null || hostname)"
TS="$(date +%Y%m%d-%H%M%S)"

WORK_DIR="${WORK_ROOT}/${TS}"
PREP_DIR="${WORK_DIR}/prepared"     # where we copy the full backup and prepare it
LOGDIR="${WORK_ROOT}/logs"
LOG="${LOGDIR}/restore-${TS}.log"

mkdir -p "$WORK_DIR" "$LOGDIR"

# Resolve datadir unless overridden
if [[ -z "$DATADIR" ]]; then
  DATADIR="$(detect_datadir)"
fi

# Guardrails
[[ -n "$DATADIR" ]] || die "DATADIR resolved to empty"
[[ "$DATADIR" != "/" ]] || die "DATADIR resolved to '/', aborting"
[[ "$DATADIR" != "/var" ]] || die "DATADIR resolved to '/var', aborting"

log() { echo "[$(date -Is)] $*" | tee -a "$LOG"; }

tail_log_text() { tail -n "$LOG_TAIL_LINES" "$LOG" 2>/dev/null || true; }

MODE_STR="$($PREPARE_ONLY && echo "PREPARE-ONLY" || echo "FULL RESTORE")"

on_error() {
  local exit_code=$?
  local line_no="${1:-?}"
  local cmd="${2:-?}"

  send_mail "${SUBJECT_PREFIX} FAILED on ${HOST}" \
"RESTORE FAILED on ${HOST}
Time: $(date -Is)
Exit code: ${exit_code}
Failed at line: ${line_no}
Command: ${cmd}

Mode: ${MODE_STR}
DATADIR: ${DATADIR}
WORK_DIR: ${WORK_DIR}
Prepared dir: ${PREP_DIR}
Full: ${FULL_DIR}
Incrementals: ${#INCR_DIRS[@]}

$(tail_log_text)
"
  exit "$exit_code"
}
trap 'on_error "${LINENO}" "${BASH_COMMAND}"' ERR

prepare_chain() {
  log "Copying FULL backup into work area: ${PREP_DIR}"
  cp -a "$FULL_DIR" "$PREP_DIR" |& tee -a "$LOG"

  log "Preparing FULL backup (apply redo logs)..."
  mariabackup --prepare --target-dir="$PREP_DIR" |& tee -a "$LOG"

  if [[ ${#INCR_DIRS[@]} -gt 0 ]]; then
    log "Applying incrementals (oldest -> newest): ${#INCR_DIRS[@]} directories"
    for inc in "${INCR_DIRS[@]}"; do
      log "  Applying incremental: $inc"
      mariabackup --prepare --target-dir="$PREP_DIR" --incremental-dir="$inc" |& tee -a "$LOG"
    done
  fi

  log "Prepare step completed OK."
}

stop_db() {
  log "Stopping service: ${MARIADB_SERVICE}"
  systemctl stop "$MARIADB_SERVICE" |& tee -a "$LOG"
}

start_db() {
  log "Starting service: ${MARIADB_SERVICE}"
  systemctl start "$MARIADB_SERVICE" |& tee -a "$LOG"
}

backup_existing_datadir() {
  if [[ -d "$DATADIR" ]]; then
    local old="${DATADIR}.BROKEN.${TS}"
    log "Moving existing DATADIR to: ${old}"
    mv "$DATADIR" "$old" |& tee -a "$LOG"
  else
    log "DATADIR does not exist (will be created by copy-back): ${DATADIR}"
  fi
}

copy_back() {
  log "Copying back prepared data into DATADIR: ${DATADIR}"
  mariabackup --copy-back --target-dir="$PREP_DIR" |& tee -a "$LOG"
  log "Fixing ownership on DATADIR: ${DATADIR}"
  chown -R mysql:mysql "$DATADIR" |& tee -a "$LOG"
  chmod 755 "$DATADIR"
}

# -------- main flow --------
log "Starting restore script on ${HOST}"
log "Mode: ${MODE_STR}"
log "DATADIR: ${DATADIR}"
log "Work dir: ${WORK_DIR}"
log "Full: ${FULL_DIR}"
log "Incrementals: ${#INCR_DIRS[@]}"

prepare_chain

if $PREPARE_ONLY; then
  log "PREPARE-ONLY mode: not stopping MariaDB; not performing copy-back."

  send_mail "${SUBJECT_PREFIX} PREPARE-ONLY OK on ${HOST}" \
"PREPARE-ONLY validation completed successfully on ${HOST}
Time: $(date -Is)

Mode: PREPARE-ONLY (no downtime)
Prepared dir: ${PREP_DIR}
Full: ${FULL_DIR}
Incrementals: ${#INCR_DIRS[@]}

Log: ${LOG}
$(tail -n 80 "$LOG" 2>/dev/null || true)
"
  exit 0
fi

log "FULL RESTORE mode: downtime begins now."

stop_db
backup_existing_datadir
copy_back
start_db

log "Restore completed successfully."

send_mail "${SUBJECT_PREFIX} RESTORE OK on ${HOST}" \
"RESTORE completed successfully on ${HOST}
Time: $(date -Is)

Mode: FULL RESTORE
DATADIR: ${DATADIR}
Prepared dir: ${PREP_DIR}
Full: ${FULL_DIR}
Incrementals: ${#INCR_DIRS[@]}

Log: ${LOG}
$(tail -n 40 "$LOG" 2>/dev/null || true)
"
