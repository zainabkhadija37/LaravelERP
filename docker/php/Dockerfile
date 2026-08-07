FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    icu-dev \
    oniguruma-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip intl mbstring gd bcmath opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --optimize-autoloader --no-dev --no-interaction || true

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
