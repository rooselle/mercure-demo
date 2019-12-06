FROM php:7.2-cli

# Install php
RUN apt-get update \
    && apt-get install -y --no-install-recommends vim curl debconf subversion git apt-transport-https apt-utils \
    build-essential locales acl mailutils wget nodejs zip unzip \
    gnupg gnupg1 gnupg2

RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-configure opcache --enable-opcache \
    && docker-php-ext-install opcache

# Install APCu
RUN pecl install apcu
RUN echo "extension=apcu.so" > /usr/local/etc/php/conf.d/apcu.ini

# Install amqp
RUN apt-get update && apt-get install -y librabbitmq-dev libssh-dev \
    && docker-php-ext-install bcmath sockets \
    && pecl install amqp \
    && docker-php-ext-enable amqp

WORKDIR /home/wwwroot/mercure-demo
ENTRYPOINT ["php", "/home/wwwroot/mercure-demo/bin/console", "messenger:consume", "amqp_test"]
