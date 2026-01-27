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
BACKUP_ROOT="/home/damic/local-backup"
REMOTE_USER="sc"
REMOTE_HOST="10.162.60.64"         # remote host pollux, somehow DNS failed to resolve on this machine
REMOTE_DIR="/home/sc/ccdqc-backup"         # remote destination root
MYSQL_CNF="/etc/my.cnf.d/cron-backup.cnf"      # contains [client] user/password (chmod 600)

RETENTION_DAYS=5     # full backups
RETENTION_HOURS=48     # incrementals

EMAIL_TO="xzhu98@jh.edu damicm.sc@gmail.com"
EMAIL_FROM=""
SUBJECT_PREFIX="[JH1 CCDQC Backup]"
########################################

umask 077
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

rsync_dir() {
  local subdir="$1"
  log "Rsync ${subdir}/ to ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_DIR}/${subdir}/"
  rsync -a --chmod=Du=rwx,Dgo=rx,Fu=rw,Fgo=r --info=stats2,progress2 --partial --inplace \
    "${BACKUP_ROOT}/${subdir}/" \
    "${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_DIR}/${subdir}/" \
    2>&1 | tee -a "$LOG"
}

prune_full() {
  log "Prune local FULL backups older than ${RETENTION_DAYS} days"
  find "${BACKUP_ROOT}/full" -mindepth 1 -maxdepth 1 -type d -mtime +"${RETENTION_DAYS}" -print -exec rm -rf {} \;
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
}

main() {
  require_root
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
  fi

  rsync_dir "logs"
  log "${MODE^^} backup finished OK"
}

main "$@"
