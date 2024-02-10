FROM php:8.2-fpm

## < Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer
RUN php -r "unlink('composer-setup.php');"
## > Install composer

## < Install PHP extensions
ENV XDEBUG_MODE off

RUN docker-php-ext-install opcache

RUN pecl install xdebug \
	&& docker-php-ext-enable xdebug

RUN apt-get update \
    && apt-get install -y \
        libmemcached-dev \
        libssl-dev \
        zlib1g-dev \
	&& pecl install memcached-3.2.0 \
	&& docker-php-ext-enable memcached

RUN pecl install apcu \
    && docker-php-ext-enable apcu

RUN apt-get update && apt-get install -y \
        libpq-dev \
    && docker-php-ext-install pdo_pgsql pgsql

RUN apt-get update \
    && apt-get install -y libzip-dev zlib1g-dev \
    && docker-php-ext-install zip

RUN apt-get update \
    && apt-get install -y libicu-dev \
    && docker-php-ext-install intl

RUN docker-php-ext-install ctype iconv

RUN apt-get clean \
    && rm -rf /var/lib/apt/lists/*
## > Install PHP extensions
