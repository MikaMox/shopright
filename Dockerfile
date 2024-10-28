ARG PHP_VERSION=8.3.11

FROM php:${PHP_VERSION}-fpm-bookworm AS build

ENV DEBIAN_FRONTEND noninteractive

# Upgrade the system
RUN apt-get update && apt-get upgrade -y

# Install base system packages
RUN apt-get install -y \
  git \
  libxml2-dev \
  zip \
  unzip \
  libzip-dev \
  procps \
  nano

RUN pecl install apcu

# Install and enable PHP extensions
RUN docker-php-ext-install \
  intl \
  soap \
  bcmath \
  zip \
  sockets \
  && docker-php-ext-enable \
  apcu \
  soap

RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

ADD ./docker/php/docker-php-maxexectime.ini /usr/local/etc/php/conf.d/docker-php-maxexectime.ini
ADD ./docker/php/docker-php-apcu-cli.ini /usr/local/etc/php/conf.d/docker-php-apcu-cli.ini
ADD ./docker/php/docker-php-memory-limits.ini /usr/local/etc/php/conf.d/docker-php-memory-limits.ini
ADD ./docker/php/docker-php-logging.ini /usr/local/etc/php/conf.d/docker-php-logging.ini
ADD ./docker/php/docker-php-defaultsockettimeout.ini /usr/local/etc/php/conf.d/docker-php-defaultsockettimeout.ini

RUN addgroup -gid 1000 app \
  && useradd -u 1000 -g app -s /bin/bash -d /application app

COPY ./ /application
RUN chown -R app:app /application/

WORKDIR /application
USER app

RUN composer install --prefer-dist --no-dev --no-autoloader --no-plugins --no-scripts

# END build process


FROM php:${PHP_VERSION}-fpm-bookworm AS base

ENV DEBIAN_FRONTEND noninteractive
RUN apt-get update && apt-get upgrade -y
RUN apt-get install -y \
  libxml2-dev \
  zip \
  unzip \
  libzip-dev \
  apt-utils

RUN pecl install apcu

# Install and enable PHP extensions
RUN docker-php-ext-install \
  intl \
  soap \
  bcmath \
  zip \
  sockets \
  && docker-php-ext-enable \
  apcu \
  soap

RUN apt-get update

ADD ./docker/php/docker-php-maxexectime.ini /usr/local/etc/php/conf.d/docker-php-maxexectime.ini
ADD ./docker/php/docker-php-apcu-cli.ini /usr/local/etc/php/conf.d/docker-php-apcu-cli.ini
ADD ./docker/php/docker-php-memory-limits.ini /usr/local/etc/php/conf.d/docker-php-memory-limits.ini
ADD ./docker/php/docker-php-logging.ini /usr/local/etc/php/conf.d/docker-php-logging.ini
ADD ./docker/php/docker-php-defaultsockettimeout.ini /usr/local/etc/php/conf.d/docker-php-defaultsockettimeout.ini


RUN addgroup -gid 1000 app \
  && useradd -u 1000 -g app -s /bin/bash -d /application app

RUN rm /usr/local/etc/php-fpm.d/www.conf
ADD ./docker/php/www.conf /usr/local/etc/php-fpm.d/

COPY --from=build /application /application
RUN chown -R app:app /application/

USER app

WORKDIR /application

# END base image build




FROM php:${PHP_VERSION}-fpm-bookworm AS dev
ARG VERSION=0.1
ARG COMPOSER_AUTH

COPY --from=build /application /application

ENV DEBIAN_FRONTEND noninteractive
RUN apt-get update && apt-get upgrade -y
RUN apt-get install -y \
  libxml2-dev \
  zip \
  unzip \
  libzip-dev \
  apt-utils

RUN pecl install apcu

# Install and enable PHP extensions
RUN docker-php-ext-install \
  intl \
  soap \
  bcmath \
  zip \
  sockets \
  xml \
  dom \
  && docker-php-ext-enable \
  apcu \
  soap

RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer  

ADD ./docker/php/docker-php-maxexectime.ini /usr/local/etc/php/conf.d/docker-php-maxexectime.ini
ADD ./docker/php/docker-php-apcu-cli.ini /usr/local/etc/php/conf.d/docker-php-apcu-cli.ini
ADD ./docker/php/docker-php-memory-limits.ini /usr/local/etc/php/conf.d/docker-php-memory-limits.ini
ADD ./docker/php/docker-php-logging.ini /usr/local/etc/php/conf.d/docker-php-logging.ini
ADD ./docker/php/docker-php-defaultsockettimeout.ini /usr/local/etc/php/conf.d/docker-php-defaultsockettimeout.ini

RUN addgroup -gid 1000 app \
  && useradd -u 1000 -g app -s /bin/bash -d /application app \
  && chown -R app:app /application/

RUN rm /usr/local/etc/php-fpm.d/www.conf
ADD ./docker/php/www.conf /usr/local/etc/php-fpm.d/

# Install XDebug
USER root
RUN pecl install xdebug-3.3.0 && docker-php-ext-enable xdebug

ADD ./docker/xdebug/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

USER app

COPY --chown=app:app . /application

ENV VERSION=$VERSION

RUN touch /application/logs/xdebug.log \
  && chmod 755 /application/logs/xdebug.log