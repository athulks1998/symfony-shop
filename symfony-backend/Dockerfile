FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libicu-dev libonig-dev \
    && docker-php-ext-install pdo pdo_mysql zip intl mbstring opcache \
    && a2enmod rewrite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN chown -R www-data:www-data /var/www/html/var

COPY docker/apache.conf /etc/apache2/sites-enabled/000-default.conf
