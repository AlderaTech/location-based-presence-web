#!/usr/bin/env sh
set -e

export PHP_FPM_MAX_CHILDREN="${PHP_FPM_MAX_CHILDREN:-16}"
export PHP_FPM_START_SERVERS="${PHP_FPM_START_SERVERS:-4}"
export PHP_FPM_MIN_SPARE_SERVERS="${PHP_FPM_MIN_SPARE_SERVERS:-2}"
export PHP_FPM_MAX_SPARE_SERVERS="${PHP_FPM_MAX_SPARE_SERVERS:-6}"
export PHP_FPM_MAX_REQUESTS="${PHP_FPM_MAX_REQUESTS:-500}"

mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache

if [ "${RUN_STORAGE_LINK:-false}" = "true" ]; then
    php artisan storage:link || true
fi

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
fi

if [ "${CACHE_LARAVEL_CONFIG:-true}" = "true" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

exec "$@"
