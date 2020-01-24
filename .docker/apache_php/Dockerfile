FROM lavoweb/php-7.3:latest

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js (& npm)
RUN curl -sL https://deb.nodesource.com/setup_13.x | bash - \
    && apt-get install -y nodejs

# Install amqp
RUN apt-get update && apt-get install -y librabbitmq-dev libssh-dev \
    && docker-php-ext-install bcmath sockets \
    && pecl install amqp \
    && docker-php-ext-enable amqp
