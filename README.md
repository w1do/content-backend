## О проекте

**ГазТочка** — автосервис по установке газового оборудования в Тюмени

**Бэкенд построен на PHP 8.5 и Laravel 13 с использованием AI (Vibe Coding), Junie** 

## Технологический стек

- **Core**: PHP 8.5+, Laravel 13
- **Admin Panel**: [Filament PHP v5](https://filamentphp.com/) (включая Media Library и Spatie Tags)
- **Database**: PostgreSQL (поддержка иерархии через `kalnoy/nestedset`)
- **API Documentation**: [Swagger (OpenAPI 3.0)](https://github.com/DarkaOnLine/L5-Swagger)
- **Architecture**: DDD (Domain-Driven Design), CQRS (Command Query Responsibility Segregation)
- **Testing**: [Pest v4](https://pestphp.com/)
- **Code Style**: Laravel Pint

## Архитектура DDD/CQRS

Проект следует принципам **DDD**, разделяя логику на четыре слоя:
1. **Domain**: Сущности, перечисления и интерфейсы репозиториев (`app/Domain`).
2. **Application**: Команды, запросы, хендлеры и DTO (`app/Application`).
3. **Infrastructure**: Реализация репозиториев, работа с БД и внешними сервисами (`app/Infrastructure`).
4. **Presentation**: API Контроллеры, ресурсы и UI компоненты (`app/Presentation`, `app/Http`).

## Основные возможности

- **Иерархический каталог**: Категории с неограниченной вложенностью и автоматическим расчетом хлебных крошек.
- **Управление товарами**: Система товаров с динамическими атрибутами и связью с категориями.
- **CMS**: Управление контентом (блог, статические страницы, системные тексты) через единую админ-панель.
- **Media Library**: Загрузка и оптимизация изображений для товаров и контента.

## Установка

1. Склонируйте репозиторий.
2. Настройте файл `.env` (база данных, ключи).

Или вручную:
```bash
composer install
./vendor/bin/sail php artisan key:generate
./vendor/bin/sail php artisan migrate
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

## Использование

### API
Документация Swagger доступна по адресу:
`GET /api/documentation`

### Админ-панель
Доступ к Filament:
`/admin`

### Тестирование
Запуск всех тестов:
```bash
./vendor/bin/sail php artisan test
```

## Документация
Подробные описания модулей находятся в папке `docs/`:
- [Иерархия категорий](docs/categories.md)
- [Система товаров](docs/products.md)
- [Контент и CMS](docs/contents.md)
- [Сводка изменений](docs/summary.md)

### Контакты

> Разработал Денис Митрофанов

**Сайт: [AI-инженер](https://w1do.ru)**

**TG: [W1DO_DIGITAL](https://t.me/W1DO_DIGITAL)**

**MAX: [Простите за MAX](https://max.ru/u/f9LHodD0cOKlpm9dqNIVXbxyaDeOEKzC4jizdf-1qeqNIOnm7yL9qs68d58)**

**Мой канал: [YouTube](https://www.youtube.com/@w1do_digital)**

## Для работодателей и нанимателей
- Только удаленка
- Внедрение AI / Разработка (Claude, Junie, Codex)

