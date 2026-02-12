FROM php:8.4-fpm

WORKDIR /application/assets

# system deps
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libzip-dev \
    libpq-dev \
    postgresql-client \
    librabbitmq-dev \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        zip

# Install Node.js 20
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php-fpm/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh \
    && sed -i 's/\r$//' /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
