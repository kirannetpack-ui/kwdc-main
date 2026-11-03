#!/bin/bash

# Database backup script
BACKUP_DIR="/backups/kwdc"
DATE=$(date '+%Y%m%d_%H%M%S')
RETENTION_DAYS=7

# Create backup directory if not exists
ssh root@your-server.com "mkdir -p $BACKUP_DIR"

# Create backup
ssh root@your-server.com "pg_dump kwdc_production > $BACKUP_DIR/backup_$DATE.sql"

# Compress backup
ssh root@your-server.com "gzip $BACKUP_DIR/backup_$DATE.sql"

# Delete old backups (older than RETENTION_DAYS)
ssh root@your-server.com "find $BACKUP_DIR -name '*.gz' -mtime +$RETENTION_DAYS -delete"

echo "✅ Backup created: backup_$DATE.sql.gz"