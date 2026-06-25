# Документация Контента

## Обзор
Система управления контентом (CMS) позволяет создавать и управлять различными текстовыми материалами на сайте: постами блога, статическими страницами, системными уведомлениями и общими материалами.

## Типы контента (ContentType)
Контент разделен на несколько категорий:
- **Blog** (Блог): Статьи, новости, обзоры.
- **Page** (Страницы): Статические страницы (О нас, Контакты и т.д.).
- **System** (Системный): Служебная информация, тексты для интерфейса.
- **Material** (Материалы): Прочие полезные материалы.

## API Эндпоинты

- `GET /api/v1/blog` - Список постов блога.
- `GET /api/v1/page` - Список статических страниц.
- `GET /api/v1/system` - Список системных страниц.
- `GET /api/v1/content` - Список всех материалов.

*Примечание: Все эндпоинты возвращают коллекцию объектов `ContentResource`.*

## Структура данных (Content)
| Поле | Тип | Описание |
| :--- | :--- | :--- |
| `id` | Integer | Уникальный идентификатор |
| `title` | String | Заголовок |
| `slug` | String | URL-псевдоним |
| `type` | Enum | Тип контента (blog, page, system, material) |
| `content` | Text | Основное тело материала (HTML/Markdown) |
| `status` | String | Статус публикации (active/inactive) |

## Администрирование
Управление контентом осуществляется через Filament ресурс `ContentResource`.
Возможности:
- Полнотекстовый редактор для поля `content`.
- Управление статусом публикации.
- Фильтрация по типам контента в списке.

## Техническая реализация
- **Entity**: `App\Domain\Entities\Content`
- **Enum**: `App\Domain\Enums\ContentType`
- **Handler**: `App\Application\Handlers\Content\GetContentByTypeHandler`
- **Query**: `App\Application\Queries\Content\GetContentByTypeQuery`
- **Filament**: `App\Filament\Resources\Contents\ContentResource`
