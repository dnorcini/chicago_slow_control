# SSH key–based login to lyon already set up

#!/usr/bin/env bash
set -euo pipefail

# === Config ===
DB_NAME="die_qc"
DB_USER="root"
DB_PASS="MyLife4Aiur"                
LOCAL_SQL="/var/www/html/QC_production/tmp/sql_backup.sql"
LOCAL_UPLOADS="/var/www/html/QC_production/uploads/"
REMOTE_USER="czhu"
REMOTE_HOST="cca.in2p3.fr"
REMOTE_DIR="/pbs/home/c/czhu/ccdqc_backup"

# Optional: log file
LOG_FILE="/var/www/html/QC_production/tmp/ccdqc_backup.log"

# Prevent overlap
exec 9>/var/www/html/QC_production/tmp/ccdqc_backup.lock
flock -n 9 || { echo "$(date -Is) Another backup is running. Exit."; exit 0; }

log() { echo "$(date -Is) $*"; }

{
  log "==== CCDQC backup started ===="

  # 1) Dump SQL (your exact command, with a couple safe flags)
  # --single-transaction is safe for InnoDB and keeps the dump consistent
  # --routines --triggers --events ensures full schema coverage
  mysqldump -u "${DB_USER}" -p"${DB_PASS}" \
    --single-transaction --routines --triggers --events \
    "${DB_NAME}" > "${LOCAL_SQL}"

  log "SQL dump written to ${LOCAL_SQL}"

  # Ensure remote dir exists (works when using SSH key auth)
  ssh "${REMOTE_USER}@${REMOTE_HOST}" "mkdir -p '${REMOTE_DIR}'"

  # 2) Rsync SQL dump
  rsync -avz "${LOCAL_SQL}" \
    "${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_DIR}/"
  log "SQL dump synced to ${REMOTE_HOST}:${REMOTE_DIR}"

  # 3) Rsync uploads folder (your exact command + a couple of reliability flags)
  rsync -avz --progress --partial --inplace \
    "${LOCAL_UPLOADS}" \
    "${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_DIR}/"
  log "Uploads synced to ${REMOTE_HOST}:${REMOTE_DIR}"

  log "==== CCDQC backup finished ===="
} >> "${LOG_FILE}" 2>&1
