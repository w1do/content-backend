# Инфраструктура и настройки сервера

## HTTPS и Proxy (Mixed Content Fix)
- Настроено корректное определение протокола HTTPS при работе приложения за reverse-proxy (например, Nginx/Traefik). 
  - В `bootstrap/app.php` добавлена конфигурация `$middleware->trustProxies(at: '*');` для доверия заголовкам `X-Forwarded-Proto`.
  - В `AppServiceProvider` метод `URL::forceHttps()` (который не существует в Laravel 11/13 фасаде URL) заменен на корректный `URL::forceScheme('https')` для принудительной генерации HTTPS-ссылок в production.

## Настройки маршрутизации (Docker Compose / Traefik)
- **Исправление маршрутизации домена**: В файле `compose.prod.yaml` исправлены Traefik-лейблы для сервиса `app-backend-gaz`.
  - Изменено имя роутера с конфликтующего `botsync` на уникальное `backend_gaz`.
  - Добавлены правила для роутера на 80 порту (entrypoint `web`), которые принудительно перенаправляют все HTTP-запросы (`backend.gaztochka.ru`) на HTTPS (через middleware `redirectscheme`), решая проблему перенаправления на чужой сайт.
