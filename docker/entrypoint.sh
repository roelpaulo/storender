#!/bin/sh
set -e

# Ensure persistent data directories exist with correct permissions
mkdir -p /var/www/html/database/data
mkdir -p /var/www/html/storage

chown -R www-data:www-data /var/www/html/database/data /var/www/html/storage
chmod -R 775 /var/www/html/database/data /var/www/html/storage

exec "$@"
