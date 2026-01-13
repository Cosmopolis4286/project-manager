FROM php:8.4-fpm

# ============================
# Arguments UID / GID
# ============================
ARG UID=1000
ARG GID=1000

# ============================
# System dependencies
# ============================
RUN apt-get update && apt-get install -y \
    git curl unzip zip \
    nodejs npm \
    libpng-dev libonig-dev libxml2-dev \
    libzip-dev libicu-dev \
    libfreetype6-dev libjpeg-dev libwebp-dev \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

# ============================
# PHP extensions
# ============================
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install \
    pdo_mysql mbstring exif pcntl bcmath zip intl gd

# ============================
# pnpm
# ============================
RUN npm install -g pnpm@10.25.0

# ============================
# Composer
# ============================
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ============================
# Create app user
# ============================
RUN groupadd -g ${GID} laravel \
 && useradd -u ${UID} -g laravel -m laravel

# ============================
# Working directory
# ============================
WORKDIR /var/www

# ============================
# Entrypoint
# ============================
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# ============================
# Switch user
# ============================
USER laravel

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
