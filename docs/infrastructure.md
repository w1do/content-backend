# Инфраструктура и настройки сервера

## HTTPS и Proxy (Mixed Content Fix)
- Настроено корректное определение протокола HTTPS при работе приложения за reverse-proxy (например, Nginx/Traefik). 
  - В `bootstrap/app.php` добавлена конфигурация `$middleware->trustProxies(at: '*');` для доверия заголовкам `X-Forwarded-Proto`.
  - В `AppServiceProvider` метод `URL::forceHttps()` (который не существует в Laravel 11/13 фасаде URL) заменен на корректный `URL::forceScheme('https')` для принудительной генерации HTTPS-ссылок в production.

## Настройки маршрутизации (Docker Compose / Traefik)
- **Исправление маршрутизации домена**: В файле `compose.prod.yaml` исправлены Traefik-лейблы для сервиса `app-backend-gaz`.
  - Изменено имя роутера с конфликтующего `botsync` на уникальное `backend_gaz`.
  - Добавлены правила для роутера на 80 порту (entrypoint `web`), которые принудительно перенаправляют все HTTP-запросы (`backend.gaztochka.ru`) на HTTPS (через middleware `redirectscheme`), решая проблему перенаправления на чужой сайт.

## Image Search (SerpApi)
Для реализации поиска изображений в админ-панели используется SerpApi.
- **API Key**: Настраивается через переменную окружения `SERP_API`.
- **Интерфейс**: `App\Domain\Services\ImageSearchProviderInterface`.
- **Реализация**: `App\Infrastructure\Services\SerpApiImageSearchProvider`.
- **DTO**: `App\Application\DTO\ImageSearchResult`.
- **Filament Action**: `App\Filament\Actions\SearchImageAction` — переиспользуемый компонент для форм, который позволяет искать изображения по названию и прикреплять их к модели через Spatie MediaLibrary.
- **Handler**: `App\Application\Handlers\Media\AttachImageFromUrlHandler` — отвечает за загрузку и прикрепление изображения.

## Парсеры товаров (mirgaza.ru)
Реализована система автоматического заполнения данных о товаре по ссылке.
- **Интерфейс**: `App\Domain\Services\ProductParserInterface`.
- **Реализация**: `App\Infrastructure\Services\MirGazaProductParser`.
- **Особенности**:
    - Извлекает название, описание и характеристики.
    - Игнорирует SEO-метатеги `itemprop="description"`, отдавая приоритет контенту страницы.
    - Собирает описание из нескольких вкладок: основное описание, комплектация (`#complete_set_tab`), дополнительные данные (`#custom_tab`).
    - Очищает HTML от лишних элементов (кнопки, скрипты, стили).
