FROM fhsinchy/php-nginx-base:php8.1.3-fpm-nginx1.20.2-alpine3.15

# set composer related environment variables
ENV PATH="/composer/vendor/bin:$PATH" \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_VENDOR_DIR=/var/www/vendor \
    COMPOSER_HOME=/composer

# install composer
COPY --from=composer:2.5.5 /usr/bin/composer /usr/bin/composer

# install application dependencies
WORKDIR /var/www/app
COPY ./src/composer.json ./src/composer.lock* ./
RUN composer install --no-scripts --no-autoloader --ansi --no-interaction

# add custom php-fpm pool settings, these get written at entrypoint startup
ENV FPM_PM_MAX_CHILDREN=20 \
    FPM_PM_START_SERVERS=2 \
    FPM_PM_MIN_SPARE_SERVERS=1 \
    FPM_PM_MAX_SPARE_SERVERS=3

# set application environment variables
ENV APP_NAME="Redbeard's ATIS Generator" \
    APP_ENV=production \
    APP_DEBUG=false

# copy entrypoint files
COPY ./docker/docker-php-* /usr/local/bin/
RUN dos2unix /usr/local/bin/docker-php-entrypoint
RUN dos2unix /usr/local/bin/docker-php-entrypoint-dev

# copy nginx configuration
COPY ./docker/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/default.conf /etc/nginx/conf.d/default.conf

# copy application code
WORKDIR /var/www/app
COPY ./src .
RUN composer dump-autoload -o \
    && chown -R :www-data /var/www/app \
    && chmod -R 775 /var/www/app/storage /var/www/app/bootstrap/cache

# Run storage link
RUN php artisan storage:link

# expose port 80
EXPOSE 80

# run supervisor
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisord.conf"]
