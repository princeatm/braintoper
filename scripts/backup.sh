#!/bin/bash
# Database Backup Script for BrainToper
# Run this daily via cron: 0 2 * * * /path/to/braintoper/scripts/backup.sh

set -e

# Configuration
BACKUP_DIR="/backups/braintoper"
DAYS_TO_KEEP=30
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/braintoper_$TIMESTAMP.sql.gz"

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# Load environment
export $(cat /path/to/braintoper/.env | xargs)

echo "[$(date)] Starting database backup..."

# Create backup
mysqldump \
    --host="$DB_HOST" \
    --user="$DB_USER" \
    --password="$DB_PASSWORD" \
    "$DB_NAME" | gzip > "$BACKUP_FILE"

echo "[$(date)] Backup created: $BACKUP_FILE"

# Remove old backups
find "$BACKUP_DIR" -name "braintoper_*.sql.gz" -mtime +$DAYS_TO_KEEP -delete
echo "[$(date)] Old backups cleaned up (older than $DAYS_TO_KEEP days)"

# Verify backup
if gunzip -t "$BACKUP_FILE" 2>/dev/null; then
    echo "[$(date)] Backup verified successfully"
else
    echo "[$(date)] ERROR: Backup verification failed!"
    exit 1
fi

echo "[$(date)] Backup script completed"
