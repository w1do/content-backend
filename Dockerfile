# Stage 1: PHP Dependencies
FROM php:8.5-fpm-alpine AS composer_stage

WORKDIR /var/www/html

# Install dependencies for composer
RUN apk add --no-cache git unzip libzip-dev libpng-dev libpq-dev

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files
COPY composer.json composer.lock ./

# Install production dependencies
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs

# Stage 2: Frontend Assets
FROM node:22-alpine AS node_stage

WORKDIR /var/www/html

# Install npm dependencies (cached layer — only invalidated when package files change)
COPY package.json package-lock.json ./
RUN npm ci

# Copy source files — COPY . . invalidates cache when any tracked file changes.
# If Dokploy caches layers between deploys, enable "No cache" / "Force rebuild" option,
# or pass --build-arg CACHEBUST=$(git rev-parse HEAD) as a build argument.
ARG CACHEBUST
COPY . .
# Copy vendor from composer_stage to ensure Ziggy is available for SSR build
COPY --from=composer_stage /var/www/html/vendor ./vendor

RUN npm run build

# Stage 3: Run Tests (gate for production build)
FROM php:8.5-fpm-alpine AS test_stage

WORKDIR /var/www/html

RUN apk add --no-cache libzip libpng libpq icu-libs sqlite-libs \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS libzip-dev libpng-dev postgresql-dev icu-dev zlib-dev sqlite-dev \
    && docker-php-ext-install bcmath intl pdo_sqlite pdo_pgsql zip pcntl \
    && pecl install redis && docker-php-ext-enable redis \
    && apk del .build-deps

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs

COPY . .
RUN composer dump-autoload

# RUN cp .env.example .env && php artisan key:generate && php artisan test --no-ansi --exclude-group slow

RUN php artisan key:generate && php artisan test --no-ansi --exclude-group slow

# Stage 4: Final Production Image
FROM php:8.5-fpm-alpine

LABEL maintainer="Junie"
LABEL description="Minimal production image for Laravel 13 RAG System"

WORKDIR /var/www/html

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    libzip \
    libpng \
    libpq \
    icu-libs \
    freetype \
    libjpeg-turbo \
    git \
    unzip

# Install PHP extensions
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    libzip-dev \
    libpng-dev \
    postgresql-dev \
    icu-dev \
    zlib-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    bcmath \
    gd \
    intl \
    pdo_pgsql \
    zip \
    pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Copy Composer binary from stage 1
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy configuration files
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/php.ini $PHP_INI_DIR/conf.d/99-overrides.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint

# Implicitly require test_stage to have passed
COPY --from=test_stage /var/www/html/vendor/autoload.php /dev/null

# Copy vendor from composer_stage
COPY --from=composer_stage /var/www/html/vendor ./vendor

# Copy application files
COPY . .

# Copy build assets from node_stage (must be AFTER COPY . . to avoid overwrite)
COPY --from=node_stage /var/www/html/public/build ./public/build

# Final optimization and permissions
RUN composer dump-autoload --optimize --no-dev \
    && mkdir -p /var/log/supervisor \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Remove unnecessary files to further reduce size
RUN rm -rf docker tests node_modules

EXPOSE 80

ENTRYPOINT ["entrypoint"]

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
