#!/usr/bin/env bash

# BrainToper First Run Setup Script
# This script initializes the database and creates default accounts

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$SCRIPT_DIR/.."
DB_MIGRATIONS="$PROJECT_ROOT/database/migrations"
DB_SEEDS="$PROJECT_ROOT/database/seeds"

echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║        BrainToper - Initial Setup Script                   ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo

# Check if .env exists
if [ ! -f "$PROJECT_ROOT/.env" ]; then
    echo -e "${RED}✗ .env file not found!${NC}"
    echo "Please copy .env.example to .env and configure it first:"
    echo "  cp .env.example .env"
    exit 1
fi

# Load environment
export $(cat "$PROJECT_ROOT/.env" | xargs)

echo -e "${YELLOW}→ Database Configuration:${NC}"
echo "  Host: $DB_HOST"
echo "  Database: $DB_NAME"
echo "  User: $DB_USER"
echo

# Test database connection
echo -e "${YELLOW}→ Testing database connection...${NC}"
if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" < /dev/null 2>&1; then
    echo -e "${GREEN}✓ Database connection successful${NC}"
else
    echo -e "${RED}✗ Failed to connect to database${NC}"
    echo "Please ensure MySQL is running and credentials are correct in .env"
    exit 1
fi

echo

# Run migrations
echo -e "${YELLOW}→ Running database migrations...${NC}"
if [ -f "$DB_SEEDS/seed.php" ]; then
    php "$DB_SEEDS/seed.php"
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Migrations completed${NC}"
    else
        echo -e "${RED}✗ Migrations failed${NC}"
        exit 1
    fi
else
    echo -e "${RED}✗ Seed file not found at $DB_SEEDS/seed.php${NC}"
    exit 1
fi

echo

# Set proper permissions
echo -e "${YELLOW}→ Setting permissions...${NC}"
chmod -R 755 "$PROJECT_ROOT/storage"
chmod -R 777 "$PROJECT_ROOT/storage/logs"
chmod -R 777 "$PROJECT_ROOT/storage/uploads"
chmod -R 777 "$PROJECT_ROOT/storage/cache"
echo -e "${GREEN}✓ Permissions set${NC}"

echo

# Display next steps
echo -e "${GREEN}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║ Setup Complete! System is ready for use.                  ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════════╝${NC}"
echo

echo -e "${BLUE}Default Credentials (Change in Production):${NC}"
echo -e "${YELLOW}Super Admin:${NC}"
echo "  Code: SUPAD-01-0001"
echo "  PIN:  1234"
echo

echo -e "${YELLOW}Admin:${NC}"
echo "  Code: AD-01-001"
echo "  PIN:  1234"
echo

echo -e "${YELLOW}Teacher:${NC}"
echo "  Code: TEA-01-0001"
echo "  PIN:  1234"
echo

echo -e "${YELLOW}Student:${NC}"
echo "  Register with: STU-XX-XXXX format (e.g., STU-12-3456)"
echo "  System will auto-generate PIN"
echo

echo -e "${BLUE}Next Steps:${NC}"
echo "1. Configure your web server (Apache/Nginx)"
echo "2. Set up SSL certificate"
echo "3. Access the application at https://your-domain.com"
echo "4. Login with credentials above"
echo "5. Create a teacher account and publish an exam"
echo "6. Create student accounts and test exam taking"
echo

echo -e "${YELLOW}Documentation:${NC}"
echo "  Deployment: $PROJECT_ROOT/docs/DEPLOYMENT.md"
echo "  API Docs:   $PROJECT_ROOT/docs/API.md"
echo "  README:     $PROJECT_ROOT/README.md"
echo

echo -e "${GREEN}Setup script completed successfully!${NC}"
