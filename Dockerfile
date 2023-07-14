FROM php:8.1 as php

RUN apt-get update -y
RUN apt-get install -y unzip libpq-dev libcurl4-gnutls-dev
RUN docker-php-ext-install pdo pdo_mysql bcmath

RUN pecl install -o -f redis \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable redis

WORKDIR /var/www
COPY ./src .

COPY --from=composer:2.3.5 /usr/bin/composer /usr/bin/composer

# ==============================================================================
#  node
FROM node:14-alpine as node

WORKDIR /var/www
COPY ./src .

RUN npm install --global cross-env
RUN npm install

VOLUME /var/www/node_modules

# ==============================================================================
# Production

FROM php as production

COPY --from=node /var/www/node_modules ./node_modules
COPY docker/entrypoint.sh /var/www/docker/entrypoint.sh

ENV PORT=8000

EXPOSE 8000
ENTRYPOINT [ "docker/entrypoint.sh" ]