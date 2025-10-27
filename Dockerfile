FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    curl \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

WORKDIR /var/www/web

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

COPY ./web /var/www/web

COPY ./composer.json /var/www

COPY ./composer.lock /var/www

COPY ./vendor /var/www/vendor

COPY ./etc/php/php.ini /usr/local/etc/php/conf.d/php.ini

RUN chown -R www-data:www-data /var/www/web \
    && chmod -R 755 /var/www/web

EXPOSE 9000

CMD ["php-fpm"]
