#!/bin/sh
set -e

mkdir -p /storage
mkdir -p /app/var/cache/prod /app/var/log
chmod -R 777 /app/var

# Persist APP_SECRET across restarts — generate once, store in volume
SECRET_FILE="/storage/app_secret"
if [ -z "${APP_SECRET}" ]; then
    if [ -f "${SECRET_FILE}" ]; then
        APP_SECRET=$(cat "${SECRET_FILE}")
        echo "[entrypoint] APP_SECRET loaded from ${SECRET_FILE}"
    else
        APP_SECRET=$(php -r 'echo bin2hex(random_bytes(32));')
        echo "${APP_SECRET}" > "${SECRET_FILE}"
        chmod 600 "${SECRET_FILE}"
        echo "[entrypoint] APP_SECRET generated and saved to ${SECRET_FILE}"
    fi
    export APP_SECRET
fi

# Create schema if DB is brand new
echo "[entrypoint] Updating database schema..."
php /app/bin/console doctrine:schema:update --force --env=prod --no-debug

# Load fixtures only if the database is empty (first boot)
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
