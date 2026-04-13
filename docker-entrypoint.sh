#!/bin/sh
set -e

# Ensure storage directory exists and is writable
mkdir -p /storage

# Create or migrate the database schema
echo "[entrypoint] Running database migrations..."
php /app/bin/console doctrine:schema:update --force --env=prod --no-debug 2>&1 || \
    php /app/bin/console doctrine:schema:create --env=prod --no-debug 2>&1 || true

# Fix var/ directory permissions
mkdir -p /app/var/cache/prod /app/var/log
chmod -R 777 /app/var

echo "[entrypoint] Starting FrankenPHP..."
exec "$@"
