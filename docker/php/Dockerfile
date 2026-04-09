FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
        bash \
        curl \
        git \
        libpq \
        libzip-dev \
        oniguruma-dev \
        postgresql-dev \
        unzip \
        zip \
    && docker-php-ext-install \
        bcmath \
        mbstring \
        pdo \
        pdo_pgsql \
    && curl -sS https://getcomposer.org/installer | php -- \
        --install-dir=/usr/local/bin \
        --filename=composer

CMD ["php-fpm"]
