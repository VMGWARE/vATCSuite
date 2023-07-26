###
### ~ Essential ATC tools for virtual skies ~ Dockerfile
###
### This file is used for dev purpose. But we use it to build the production image as well.
### OS: Debian
###

FROM php:8.1-apache

# opencontainers annotations https://github.com/opencontainers/image-spec/blob/master/annotations.md
LABEL org.opencontainers.image.authors="The vATC Suite Team <hello@atisgenerator.com>" \
    org.opencontainers.image.title="Essential ATC tools for virtual skies" \
    org.opencontainers.image.description="vATC Suite provides virtual air traffic controllers with essential tools like ATIS and AWOS generation to enhance realism in online flying networks." \
    org.opencontainers.image.url="https://atisgenerator.com/"

# entrypoint.sh dependencies
RUN set -ex; \
    \
    apt-get update; \
    apt-get install -y --no-install-recommends \
    bash \
    busybox-static \
    ; \
    rm -rf /var/lib/apt/lists/*

# Install required PHP extensions
RUN set -ex \
    && apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev \
    && apt-get install -y --no-install-recommends libzip-dev libgmp-dev \
    && docker-php-ext-install pdo_mysql

# Install mysqldump
RUN apt-get update && apt-get install -y mysql-client

RUN set -ex \ 
    && docker-php-ext-install zip

# Install exif extension
RUN apt-get install -y libexif-dev \
    && docker-php-ext-install exif

# Install supervisor
RUN apt-get update && apt-get install -y supervisor \
    && rm -rf /var/lib/apt/lists/*

# TODO: Find out why gmp is not working, or if it is even needed
# RUN set -ex \
#     # && { echo "/usr/include/gmp.h"; echo "/usr/include/x86_64-linux-gnu/gmp.h"; } | xargs -n1 ln -s \
#     && docker-php-ext-install gmp

RUN set -ex \
    && pecl install apcu-5.1.21 \
    && docker-php-ext-enable apcu

RUN set -ex \
    # Removed gmp as it is not working
    # && docker-php-ext-enable zip gmp
    && docker-php-ext-enable zip

# Install cron
RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get -y --no-install-recommends install -y cron \
    # Remove package lists for smaller image sizes
    && rm -rf /var/lib/apt/lists/* \
    && which cron \
    && rm -rf /etc/cron.*/*

# Set crontab for schedules
RUN set -ex; \
    \
    mkdir -p /var/spool/cron/crontabs; \
    rm -f /var/spool/cron/crontabs/root; \
    echo '* * * * * /usr/local/bin/php /var/www/html/artisan schedule:run -v >/proc/1/fd/1 2>/proc/1/fd/2\n#' >> /var/spool/cron/crontabs/root \
    && crontab /var/spool/cron/crontabs/root

# Opcache
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS="0" \
    PHP_OPCACHE_MAX_ACCELERATED_FILES="20000" \
    PHP_OPCACHE_MEMORY_CONSUMPTION="192" \
    PHP_OPCACHE_MAX_WASTED_PERCENTAGE="10"
RUN set -ex; \
    \
    docker-php-ext-enable opcache; \
    { \
    echo '[opcache]'; \
    echo 'opcache.enable=1'; \
    echo 'opcache.revalidate_freq=0'; \
    echo 'opcache.validate_timestamps=${PHP_OPCACHE_VALIDATE_TIMESTAMPS}'; \
    echo 'opcache.max_accelerated_files=${PHP_OPCACHE_MAX_ACCELERATED_FILES}'; \
    echo 'opcache.memory_consumption=${PHP_OPCACHE_MEMORY_CONSUMPTION}'; \
    echo 'opcache.max_wasted_percentage=${PHP_OPCACHE_MAX_WASTED_PERCENTAGE}'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.fast_shutdown=1'; \
    } > $PHP_INI_DIR/conf.d/opcache-recommended.ini; \
    \
    echo 'apc.enable_cli=1' >> $PHP_INI_DIR/conf.d/docker-php-ext-apcu.ini; \
    \
    echo 'memory_limit=512M' > $PHP_INI_DIR/conf.d/memory-limit.ini

RUN set -ex; \
    \
    a2enmod headers rewrite remoteip; \
    { \
    echo RemoteIPHeader X-Real-IP; \
    echo RemoteIPTrustedProxy 10.0.0.0/8; \
    echo RemoteIPTrustedProxy 172.16.0.0/12; \
    echo RemoteIPTrustedProxy 192.168.0.0/16; \
    } > $APACHE_CONFDIR/conf-available/remoteip.conf; \
    a2enconf remoteip

RUN set -ex; \
    APACHE_DOCUMENT_ROOT=/var/www/html/public; \
    sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" $APACHE_CONFDIR/sites-available/*.conf; \
    sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" $APACHE_CONFDIR/apache2.conf $APACHE_CONFDIR/conf-available/*.conf

WORKDIR /var/www/html


# Copy the local (outside Docker) source into the working directory,
# copy system files into their proper homes, and set file ownership
# correctly
COPY --chown=www-data:www-data src/ ./

RUN set -ex; \
    \
    mkdir -p bootstrap/cache; \
    mkdir -p storage; \
    chown -R www-data:www-data bootstrap/cache storage; \
    chmod -R g+w bootstrap/cache storage
COPY --chown=www-data:www-data src/.env.example .env

# Composer installation
COPY docker/install-composer.sh /usr/local/sbin/
RUN chmod +x /usr/local/sbin/install-composer.sh
RUN install-composer.sh

# Install composer dependencies
RUN set -ex; \
    \
    mkdir -p storage/framework/views; \
    composer install --no-interaction --no-progress --no-dev; \
    composer clear-cache; \
    rm -rf .composer

# # Install node dependencies
# RUN set -ex; \
#     \
#     curl -fsSL https://deb.nodesource.com/setup_18.x | bash -; \
#     apt-get install -y nodejs; \
#     npm install -g yarn; \
#     # yarn run inst; \
#     yarn run dev; \
#     \
#     rm -rf /var/lib/apt/lists/*

# Ensure the log file exists
RUN set -ex; \
    \
    rm -f /var/www/html/storage/logs/laravel.log; \
    touch /var/www/html/storage/logs/laravel.log; \
    chown www-data:www-data /var/www/html/storage/logs/laravel.log;

# Copy utility scripts
COPY docker/entrypoint.sh \
    docker/cron.sh \
    docker/queue.sh \
    /usr/local/bin/

# Copy supervisor config
COPY docker/supervisor-queue.conf /etc/supervisor/conf.d/

# Make scripts executable 
RUN chmod +x /usr/local/bin/entrypoint.sh /usr/local/bin/cron.sh /usr/local/bin/queue.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]