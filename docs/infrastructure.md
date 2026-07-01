# Инфраструктура и настройки сервера

## HTTPS и Proxy
- **Mixed Content Fix**: Настроено корректное определение протокола HTTPS при работе приложения за reverse-proxy (например, Nginx/Traefik). 
  - В `bootstrap/app.php` добавлена конфигурация `$middleware->trustProxies(at: '*');` для доверия заголовкам `X-Forwarded-Proto`.
  - В `AppServiceProvider` метод `URL::forceHttps()` (который не существует) заменен на корректный `URL::forceScheme('https')` для принудительной генерации HTTPS-ссылок в production.
