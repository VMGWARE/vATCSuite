#!/bin/bash

set -Eeo pipefail

ATISGENDIR=/var/www/html
ARTISAN="php ${ATISGENDIR}/artisan"

# Generate key if not set. This runs before cron and supervisor start so every process picks up the same key.
if [ -n "${APP_KEY:-}" ] && [ "$APP_KEY" != "ChangeMeBy32KeyLengthOrGenerated" ]; then
    echo "APP_KEY already set"
else
    # Drop the placeholder so it can't shadow the key written to .env
    unset APP_KEY
    if grep -q '^APP_KEY=ChangeMeBy32KeyLengthOrGenerated$' ${ATISGENDIR}/.env; then
        echo "APP_KEY not set, generating one. Set APP_KEY to keep sessions valid when the container is recreated."
        ${ARTISAN} key:generate --force --no-interaction
    else
        echo "Using the APP_KEY generated on a previous start"
    fi
fi

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

# The artisan commands above run as root, so hand anything they created (e.g. Basset's cache) back to Apache
chown -R www-data:www-data ${STORAGE}

exec "$@"
