FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

FROM php:8.4-cli-alpine

RUN apk add --no-cache $PHPIZE_DEPS linux-headers mariadb-connector-c-dev oniguruma-dev supervisor \
    && docker-php-ext-install pdo_mysql mbstring pcntl \
    && apk del $PHPIZE_DEPS linux-headers oniguruma-dev

WORKDIR /var/www/html

COPY --from=vendor /app/vendor ./vendor
COPY . .
COPY docker/supervisord.conf /etc/supervisord.conf

RUN php artisan package:discover --ansi \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

CMD ["supervisord", "-c", "/etc/supervisord.conf"]
