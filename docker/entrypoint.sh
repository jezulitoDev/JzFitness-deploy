#!/bin/sh
set -e

if [ -z "$APP_KEY" ]; then
    echo "APP_KEY is required"
    exit 1
fi

php artisan migrate --force --no-interaction
php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
