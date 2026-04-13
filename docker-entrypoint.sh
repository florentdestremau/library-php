#!/bin/sh
set -e

mkdir -p /storage
mkdir -p /app/var/cache/prod /app/var/log
chmod -R 777 /app/var

# Create schema if DB is brand new
echo "[entrypoint] Updating database schema..."
php /app/bin/console doctrine:schema:update --force --env=prod --no-debug

# Load fixtures only if the database is empty (first boot)
# dbal:run-sql returns a formatted table — extract the numeric value
BOOK_COUNT=$(php /app/bin/console dbal:run-sql \
    "SELECT COUNT(*) FROM book" --env=prod --no-debug 2>/dev/null \
    | tr -d ' |' | grep -E '^[0-9]+$' | head -1)
BOOK_COUNT=${BOOK_COUNT:-0}

if [ "${BOOK_COUNT}" = "0" ]; then
    echo "[entrypoint] Empty database — loading fixtures..."
    php /app/bin/console doctrine:fixtures:load --no-interaction --env=prod --no-debug
    echo "[entrypoint] Fixtures loaded."
else
    echo "[entrypoint] Database already seeded (${BOOK_COUNT} books), skipping fixtures."
fi

echo "[entrypoint] Starting FrankenPHP..."
exec "$@"
