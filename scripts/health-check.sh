#!/bin/bash
# BrainToper Health Check Script
# Monitors system health and alerts if issues detected

set -e

# Configuration
LOG_FILE="/var/log/braintoper/health-check.log"
ALERT_EMAIL="admin@your-domain.com"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
    echo -e "$1"
}

check_database() {
    log "Checking database..."
    if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -e "SELECT 1" > /dev/null 2>&1; then
        log "${GREEN}✓ Database is responsive${NC}"
        return 0
    else
        log "${RED}✗ Database is unresponsive${NC}"
        return 1
    fi
}

check_disk_space() {
    log "Checking disk space..."
    USAGE=$(df /path/to/braintoper | awk 'NR==2 {print int($5)}')
    if [ "$USAGE" -lt 90 ]; then
        log "${GREEN}✓ Disk usage is at $USAGE%${NC}"
        return 0
    else
        log "${RED}✗ Disk usage is critical at $USAGE%${NC}"
        return 1
    fi
}

check_php_fpm() {
    log "Checking PHP-FPM..."
    if systemctl is-active --quiet php8.2-fpm; then
        log "${GREEN}✓ PHP-FPM is running${NC}"
        return 0
    else
        log "${RED}✗ PHP-FPM is not running${NC}"
        return 1
    fi
}

check_nginx() {
    log "Checking Nginx..."
    if systemctl is-active --quiet nginx; then
        log "${GREEN}✓ Nginx is running${NC}"
        return 0
    else
        log "${RED}✗ Nginx is not running${NC}"
        return 1
    fi
}

check_websocket() {
    log "Checking WebSocket Server..."
    if nc -z localhost 8080 2>/dev/null; then
        log "${GREEN}✓ WebSocket server is responding${NC}"
        return 0
    else
        log "${RED}✗ WebSocket server is not responding${NC}"
        return 1
    fi
}

# Run checks
log "Starting health check..."

FAILED=0
check_database || ((FAILED++))
check_disk_space || ((FAILED++))
check_php_fpm || ((FAILED++))
check_nginx || ((FAILED++))
check_websocket || ((FAILED++))

if [ "$FAILED" -eq 0 ]; then
    log "${GREEN}All checks passed!${NC}"
else
    log "${RED}$FAILED checks failed!${NC}"
fi

log "Health check completed"
