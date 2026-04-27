FROM php:8.3-fpm

ARG USER=bianquiviri
ARG UID=1000
ARG GID=1000

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    supervisor \
    libssl-dev \
    libmariadb-dev \
    procps \
    docker.io \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN groupadd -g ${GID} ${USER} || true && \
    useradd -m -u ${UID} -g ${GID} -s /bin/bash ${USER} || true

RUN usermod -u 1000 www-data || true && groupmod -g 1000 www-data || true

WORKDIR /var/www

COPY --chown=www-data:www-data . /var/www

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN chmod -R 755 storage bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache

COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]