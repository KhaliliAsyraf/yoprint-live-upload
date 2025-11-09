# Dockerfile
FROM php:8.3-fpm

# system deps
RUN apt-get update && apt-get install -y \
    git zip unzip libzip-dev libpng-dev libonig-dev libicu-dev zlib1g-dev \
    libxml2-dev libcurl4-openssl-dev libjpeg-dev libfreetype6-dev supervisor \
    && docker-php-ext-install pdo_mysql mbstring zip pcntl bcmath

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Allow Git to trust the app directory
RUN git config --global --add safe.directory /var/www/html

# install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN rm -rf vendor composer.lock
RUN composer install --no-dev --prefer-dist --no-autoloader --no-progress --no-interaction --no-scripts --ignore-platform-reqs --optimize-autoloader || true

RUN mkdir -p bootstrap/cache config && \
    find bootstrap/cache -type f -name '*.php' -delete 2>/dev/null || true && \
    rm -f config/l5-swagger.php 2>/dev/null || true && \
    composer install --prefer-dist --no-interaction --no-scripts --optimize-autoloader

# RUN composer install --no-dev --prefer-dist --optimize-autoloader
RUN composer install --optimize-autoloader

# Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Supervisor
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN echo "upload_max_filesize=100M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=100M" >> /usr/local/etc/php/conf.d/uploads.ini


# Publish Livewire config
RUN php artisan livewire:publish --config


# Run artisan setup on container build
RUN php artisan optimize:clear || true


EXPOSE 9000

# Run PHP-FPM under Supervisor (includes queue worker)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
