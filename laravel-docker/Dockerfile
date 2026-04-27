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
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user
RUN groupadd -g ${GID} ${USER} || true && \
    useradd -m -u ${UID} -g ${GID} -s /bin/bash ${USER} || true && \
    groupadd -g 973 docker_host || true && \
    usermod -aG docker,docker_host ${USER} || true

WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=${USER}:${USER} . /var/www

# Change current user to the one we created
USER ${USER}

# Copy supervisor config
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]