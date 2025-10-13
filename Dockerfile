# --- Base Image ---
FROM php:8.2-fpm

# --- System dependencies ---
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

# --- Set working directory ---
WORKDIR /var/www/html

# --- Copy Composer from the official Composer image ---
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# --- Copy project files ---
COPY ./web /var/www/html

# --- Copy PHP configuration (from your etc/php/php.ini) ---
COPY ./etc/php/php.ini /usr/local/etc/php/conf.d/php.ini

# --- Set permissions for web directory ---
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# --- Expose PHP-FPM port ---
EXPOSE 9000

# --- Start PHP-FPM ---
CMD ["php-fpm"]
