FROM php:8.2-fpm-alpine
LABEL authors="anna"

RUN apk update --no-cache && \
    apk upgrade --no-cache
RUN apk add --no-cache \
        supervisor
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    linux-headers
RUN apk add --no-cache \
    freetype-dev \
    jpeg-dev \
    icu-dev

RUN apk update && apk upgrade && apk add bash

#####################################
# PHP Extensions
#####################################

# Install the PHP bcmath extension
RUN docker-php-ext-install bcmath

# Install the PHP imagick extension
RUN apk add --update --no-cache autoconf g++ imagemagick imagemagick-dev libtool make pcre-dev \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && apk del autoconf g++ libtool make pcre-dev

# Install the PHP graphics library
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg
RUN docker-php-ext-install gd

# Install the PHP intl extention
RUN docker-php-ext-install intl

# Install the PHP opcache extention
RUN docker-php-ext-install opcache \
    && docker-php-ext-enable opcache

# Install the PHP pgsql extention
RUN apk add libpq-dev \
    && docker-php-ext-install pgsql \
    && docker-php-ext-enable pgsql

# Install the PHP pdo extention
RUN docker-php-ext-install pdo

# Install the PHP pdo_pgsql extention
RUN docker-php-ext-install pdo_pgsql

# install XDebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

#####################################
# Composer
#####################################
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

#Set workdir
WORKDIR /var/www

#####################################
# Cleanup
#####################################
RUN apk del --no-network .build-deps
RUN rm -rf /tmp/* /var/tmp/*

#CMD ["supervisord", "-c", "/etc/supervisor.d/supervisord.ini"]