#!/bin/bash

set -Eeo pipefail

ATISGENDIR=/var/www/html
ARTISAN="php ${ATISGENDIR}/artisan"

# Push env vars for cron
echo "Loading env vars for cron"
env >/etc/environment

# Start cron
echo "Starting cron"
cron -L 2

# Run supervisor
echo "Starting supervisord"
supervisord -c /etc/supervisor/supervisord.conf

# Ensure storage directories are present
STORAGE=${ATISGENDIR}/storage
mkdir -p ${STORAGE}/logs
mkdir -p ${STORAGE}/app/public
mkdir -p ${STORAGE}/framework/views
mkdir -p ${STORAGE}/framework/cache
mkdir -p ${STORAGE}/framework/sessions
chown -R www-data:www-data ${STORAGE}
chmod -R g+rw ${STORAGE}

# Generate key if not set
if [ -z "${APP_KEY:-}" -o "$APP_KEY" = "ChangeMeBy32KeyLengthOrGenerated" ]; then
    ${ARTISAN} key:generate --no-interaction
else
    echo "APP_KEY already set"
fi

# Link public storage
if [ -d "${ATISGENDIR}/public/storage" ]; then
    echo "Public storage already linked"
else
    ${ARTISAN} storage:link
fi

# Run migrations
${ARTISAN} migrate --force

# Generate the sitemap, we queue it so that apache can start up before it runs
${ARTISAN} sitemap:queue

# Configure the site
${ARTISAN} site:configure

# Install laravel backpack
${ARTISAN} backpack:install --no-interaction

# Write to a file the current time
${ARTISAN} uptime

exec "$@"
