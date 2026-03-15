#!/usr/bin/env bash
# Unified MariaDB physical backup script (FULL or INCREMENTAL)
#
# Usage:
#   sudo ./mariabackup.sh full
#   sudo ./mariabackup.sh inc
#
# Features kept (minimal + intentional):
# - FULL: full backup, rsync, prune by days, SUCCESS + FAILURE email
# - INC : incremental backup, rsync, prune vs latest full + by hours, FAILURE email only
# - Logs synced to remote
#
set -euo pipefail

########################################
# CONFIG
########################################
BACKUP_ROOT="/home/damicm/local-backup"
REMOTE_USER="czhu"
REMOTE_HOST="cca.in2p3.fr"         
REMOTE_DIR="/sps/damic/lbc-sc-backup"         # remote destination root
MYSQL_CNF="/etc/cron-backup.cnf"  # contains [client] user/password (chmod 600)

RETENTION_DAYS=30       # full backups
RETENTION_HOURS=72     # incrementals

EMAIL_TO="xzhu98@jh.edu damicm.sc@gmail.com dvenega1@jh.edu"
EMAIL_FROM=""
SUBJECT_PREFIX="[LBC-SC Backup]"
########################################

# Make backups readable by all users:
# - dirs: 755
# - files: 644
# (We still rely on MYSQL_CNF permissions being tight separately.)
umask 022

HOST="$(hostname -f 2>/dev/null || hostname)"
TS="$(date +%Y%m%d-%H%M%S)"
LOGDIR="${BACKUP_ROOT}/logs"
mkdir -p "$LOGDIR"

MODE="${1:-}"
case "$MODE" in
  full) MODE="full" ;;
  inc|incremental) MODE="inc" ;;
  *)
    echo "Usage: $0 {full|inc}" >&2
    exit 2
    ;;
esac

DEST="${BACKUP_ROOT}/${MODE}/${TS}"
LOG="${LOGDIR}/${MODE}-${TS}.log"
mkdir -p "$DEST"

log() { echo "[$(date -Is)] $*" | tee -a "$LOG"; }

send_mail() {
  local subject="$1"
  local body="$2"
  local recipients="${EMAIL_TO//,/ }"

  command -v mail >/dev/null 2>&1 || return 0
  if [[ -n "${EMAIL_FROM:-}" ]]; then
    printf "%s\n" "$body" | mail -r "$EMAIL_FROM" -s "$subject" $recipients
  else
    printf "%s\n" "$body" | mail -s "$subject" $recipients
  fi
}

send_success_full_mail() {
  send_mail "${SUBJECT_PREFIX}[FULL] OK on ${HOST}" \
"FULL backup finished successfully on ${HOST}
Time: $(date -Is)

Local:  ${DEST}
Remote: ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_DIR}/full/

Log: ${LOG}

$(tail -n 40 "$LOG" 2>/dev/null || true)
"
}

on_error() {
  local exit_code=$?
  local line_no="${1:-?}"
  local cmd="${2:-?}"

  send_mail "${SUBJECT_PREFIX}[${MODE^^}] FAILED on ${HOST}" \
"${MODE^^} backup FAILED on ${HOST}
Time: $(date -Is)
Exit code: ${exit_code}
Failed at line: ${line_no}
Command: ${cmd}

Backup dest: ${DEST}
Remote: ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_DIR}/${MODE}/

$(tail -n 120 "$LOG" 2>/dev/null || true)
"
  exit "$exit_code"
}
trap 'on_error "${LINENO}" "${BASH_COMMAND}"' ERR

require_root() {
  [[ "${EUID:-$(id -u)}" -eq 0 ]] || {
    echo "ERROR: must run as root (sudo)." >&2
    exit 1
  }
}

ensure_local_perms() {
  # Ensure parents are traversable/readable by everyone
  mkdir -p "${BACKUP_ROOT}/full" "${BACKUP_ROOT}/inc" "${BACKUP_ROOT}/logs"
  chmod 755 "$BACKUP_ROOT" "${BACKUP_ROOT}/full" "${BACKUP_ROOT}/inc" "${BACKUP_ROOT}/logs" || true
}

make_tree_world_readable() {
  # dirs: 755, files: 644
  local path="$1"
  [[ -d "$path" ]] || return 0
  find "$path" -type d -exec chmod 755 {} + || true
  find "$path" -type f -exec chmod 644 {} + || true
}

rsync_dir() {
  local subdir="$1"
  log "Rsync ${subdir}/ to ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_DIR}/${subdir}/"
  rsync -a --chmod=Du=rwx,Dgo=rx,Fu=rw,Fgo=r --info=stats2,progress2 --partial --inplace \
    "${BACKUP_ROOT}/${subdir}/" \
    "${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_DIR}/${subdir}/" \
    2>&1 | tee -a "$LOG"
}

prune_full() {
  # Local: keep only the latest full backup (save space)
  local latest_full
  latest_full="$(readlink -f "${BACKUP_ROOT}/full/LATEST" 2>/dev/null)"
  if [[ -n "$latest_full" && -d "$latest_full" ]]; then
    log "Prune local FULL backups: keep only latest ($(basename "$latest_full"))"
    find "${BACKUP_ROOT}/full" -mindepth 1 -maxdepth 1 -type d ! -path "$latest_full" -print -exec rm -rf {} \;
  fi
  # Remote: keep full backups for RETENTION_DAYS, remove older ones
  log "Prune remote FULL backups older than ${RETENTION_DAYS} days"
  ssh -o BatchMode=yes "${REMOTE_USER}@${REMOTE_HOST}" "
    find '${REMOTE_DIR}/full' -mindepth 1 -maxdepth 1 -type d -mtime +${RETENTION_DAYS} -print -exec rm -rf {} \;
  "
}

cleanup_inc_older_than_latest_full() {
  local cutoff
  cutoff="$(basename "$(readlink -f "${BACKUP_ROOT}/full/LATEST" 2>/dev/null || true)")"

  [[ "$cutoff" =~ ^[0-9]{8}-[0-9]{6}$ ]] || return 0

  log "Cleanup INC backups older than latest FULL: $cutoff"

  find "${BACKUP_ROOT}/inc" -mindepth 1 -maxdepth 1 -type d -printf '%f\n' \
    | awk -v c="$cutoff" '$0 < c {print}' \
    | while read -r d; do rm -rf "${BACKUP_ROOT}/inc/$d"; done

  ssh -o BatchMode=yes "${REMOTE_USER}@${REMOTE_HOST}" bash -lc "
    find '${REMOTE_DIR}/inc' -mindepth 1 -maxdepth 1 -type d -printf '%f\n' \
      | awk -v c='$cutoff' '\$0 < c {print}' \
      | while read -r d; do rm -rf '${REMOTE_DIR}/inc/'\"\$d\"; done
  "
}

prune_inc_by_hours() {
  log "Extra INC pruning: older than ${RETENTION_HOURS} hours (local)"
  find "${BACKUP_ROOT}/inc" -mindepth 1 -maxdepth 1 -type d \
    -mmin +$((RETENTION_HOURS*60)) -print -exec rm -rf {} \;
}

run_full() {
  log "Starting FULL backup to $DEST"
  mariabackup --defaults-file="$MYSQL_CNF" --backup --target-dir="$DEST"
  ln -sfn "$DEST" "${BACKUP_ROOT}/full/LATEST"

  # Make this backup readable by all users
  make_tree_world_readable "$DEST"
}

run_inc() {
  local base="${BACKUP_ROOT}/inc/LATEST"
  [[ -d "$base" ]] || base="${BACKUP_ROOT}/full/LATEST"
  [[ -d "$base" ]] || { echo "ERROR: no FULL backup found"; exit 1; }

  log "Starting INC backup to $DEST (base: $base)"
  mariabackup --defaults-file="$MYSQL_CNF" --backup \
    --target-dir="$DEST" \
    --incremental-basedir="$base"
  ln -sfn "$DEST" "${BACKUP_ROOT}/inc/LATEST"

  # Make this backup readable by all users
  make_tree_world_readable "$DEST"
}

main() {
  require_root
  ensure_local_perms

  # Ensure the log file itself is readable
  : > "$LOG"
  chmod 644 "$LOG" || true

  log "Mode: $MODE"

  if [[ "$MODE" == "full" ]]; then
    run_full
    rsync_dir "full"
    prune_full
    send_success_full_mail
  else
    run_inc
    rsync_dir "inc"
    cleanup_inc_older_than_latest_full
    prune_inc_by_hours
    # send_success_full_mail # comment it out when you don't want to have countless emails
  fi

  rsync_dir "logs"
  log "${MODE^^} backup finished OK"
}

main "$@"
