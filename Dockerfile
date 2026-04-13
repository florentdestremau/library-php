# syntax=docker/dockerfile:1.7

###############################################################################
# Stage 1 — Composer deps (no dev)
###############################################################################
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --ignore-platform-reqs \
        --prefer-dist

COPY . .
RUN composer dump-autoload --no-dev --classmap-authoritative --no-scripts

###############################################################################
# Stage 2 — Final image (FrankenPHP)
###############################################################################
FROM dunglas/frankenphp:1-php8.4-alpine

# PHP extensions needed by Symfony + SQLite
RUN install-php-extensions \
        pdo_sqlite \
        sqlite3 \
        intl \
        opcache \
        zip \
        apcu

# Caddy / FrankenPHP config
ENV FRANKENPHP_CONFIG="worker ./public/index.php"
ENV SERVER_NAME=":80"
ENV APP_ENV=prod
ENV APP_DEBUG=0

# SQLite DB lives in a mounted volume
ENV DATABASE_URL="sqlite:////storage/library.db"

# Required at runtime — override via -e APP_SECRET=... or docker secret
ENV APP_SECRET=""

WORKDIR /app

# Copy application (vendor from stage 1)
COPY --from=vendor /app /app

# Compile assets (importmap / AssetMapper)
RUN php bin/console importmap:install --no-interaction 2>/dev/null || true && \
    php bin/console asset-map:compile --no-interaction

# Warm up prod cache (no DB needed at build time)
RUN php bin/console cache:warmup --env=prod --no-debug

# Custom Caddyfile (handles /up health check at HTTP level)
COPY Caddyfile /etc/caddy/Caddyfile

# Schema will be created at first startup via entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80
VOLUME ["/storage"]

HEALTHCHECK --interval=15s --timeout=5s --start-period=10s --retries=3 \
    CMD wget -qO- http://localhost/up || exit 1

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
