---
name: laravel-deploy
description: "Используйте этот навык для деплоя Laravel приложений и сборки Docker-образов. Включает лучшие практики по оптимизации, настройке окружения и безопасности. Охватывает многоэтапную сборку (multi-stage build), настройку Nginx, Supervisord и автоматизацию через entrypoint-скрипты."
license: MIT
metadata:
  author: Junie
---

# Laravel Deployment & Dockerization

Этот навык описывает процесс подготовки Laravel приложения к продакшену, сборку оптимизированных Docker-образов и выполнение шагов деплоя.

## Сборка Docker-образа

В этом проекте используется многоэтапная сборка (multi-stage build) для минимизации размера финального образа и разделения зависимостей.

### Основные этапы (Stages):

1.  **composer_stage**: Установка PHP зависимостей через Composer без dev-пакетов.
2.  **node_stage**: Сборка фронтенд-активов (Vite, React, Tailwind CSS 4).
3.  **Final Stage**: Финальный образ на базе `php:8.5-fpm-alpine`, включающий Nginx и Supervisord.

### Пример сборки:

```bash
# Сборка образа с тегом 'prod-app'
docker build -t prod-app .
```

## Структура Docker-конфигурации

- `Dockerfile`: Основной файл сборки.
- `.dockerignore`: Список игнорируемых файлов.
- `docker/nginx.conf`: Конфигурация Nginx для обслуживания Laravel.
- `docker/php.ini`: Настройки PHP для продакшена.
- `docker/supervisord.conf`: Управление процессами (php-fpm, nginx, worker).
- `docker/entrypoint.sh`: Скрипт, запускаемый при старте контейнера.

## Примеры файлов (Reference)

Для наглядности, все конфигурационные файлы текущего проекта доступны в папке `reference/`:

- [Dockerfile](reference/Dockerfile)
- [.dockerignore](reference/.dockerignore)
- [nginx.conf](reference/docker/nginx.conf)
- [php.ini](reference/docker/php.ini)
- [supervisord.conf](reference/docker/supervisord.conf)
- [entrypoint.sh](reference/docker/entrypoint.sh)

## Оптимизация для продакшена

При деплое необходимо выполнить кэширование конфигурации и маршрутов для повышения производительности. В данном проекте это автоматизировано в `entrypoint.sh`:

```bash
# Выполняется внутри контейнера при старте
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan storage:link
```

## Деплой-чеклист

1.  **Environment**: Убедитесь, что `APP_ENV=production` и `APP_DEBUG=false`.
2.  **Secrets**: Используйте переменные окружения для хранения ключей API и паролей БД.
3.  **Permissions**: Права на `storage` и `bootstrap/cache` должны быть `775`, владелец `www-data`.
4.  **Database**: Всегда запускайте миграции с флагом `--force`.
5.  **Assets**: Убедитесь, что `npm run build` выполнен (в Dockerfile это делается автоматически).

## Команды для управления (через Docker/Sail)

Если вы работаете в окружении Sail, используйте:

```bash
./vendor/bin/sail artisan config:cache
./vendor/bin/sail artisan migrate --force
```

Для локальной проверки продакшн-образа:

```bash
docker run -p 8080:80 --env-file .env prod-app
```

## Пример Dockerfile (Production)

Ниже приведен пример оптимизированного Dockerfile для этого проекта:

```dockerfile
# Stage 1: PHP Dependencies
FROM php:8.5-fpm-alpine AS composer_stage
WORKDIR /var/www/html
RUN apk add --no-cache git unzip libzip-dev libpng-dev libpq-dev
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs

# Stage 2: Frontend Assets
FROM node:22-alpine AS node_stage
WORKDIR /var/www/html
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
COPY --from=composer_stage /var/www/html/vendor ./vendor
RUN npm run build

# Stage 3: Final Production Image
FROM php:8.5-fpm-alpine
WORKDIR /var/www/html
RUN apk add --no-cache nginx supervisor libzip libpng libpq icu-libs freetype libjpeg-turbo git unzip

# Install PHP extensions
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS libzip-dev libpng-dev postgresql-dev icu-dev zlib-dev freetype-dev libjpeg-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install bcmath gd intl pdo_pgsql zip pcntl \
    && pecl install redis && docker-php-ext-enable redis \
    && apk del .build-deps

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint

COPY --from=composer_stage /var/www/html/vendor ./vendor
COPY --from=node_stage /var/www/html/public/build ./public/build
COPY . .

RUN composer dump-autoload --optimize --no-dev \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80
ENTRYPOINT ["entrypoint"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
```

## Работа с очередями и Horizon

Если проект использует очереди (например, Laravel Horizon), убедитесь, что Supervisord настроен на запуск `php artisan horizon` или `php artisan queue:work`.

В текущем проекте за это отвечает `docker/supervisord.conf`.
