#!/bin/sh
set -eu

exec php /var/www/html/artisan queue:work database --sleep=10 --timeout=0 --tries=3 --queue=default >/proc/1/fd/1 2>/proc/1/fd/2