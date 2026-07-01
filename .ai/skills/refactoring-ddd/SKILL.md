# Рефакторинг по стандартам DDD, CQRS и DTO

Этот навык предназначен для систематического рефакторинга кода Laravel приложений с целью приведения его к архитектуре DDD (Domain-Driven Design) с использованием паттернов CQRS и типизированных DTO.

## Основные принципы

### 1. Архитектура слоев (DDD)
Разделяйте логику на домены в `app/Domain/{DomainName}`:
- **Http Layer**: Тонкие контроллеры, FormRequests для валидации, Resources для ответов.
- **Domain Layer**: 
    - **Actions**: Атомарные бизнес-задачи.
    - **Commands & Handlers**: Для изменения состояния (Write).
    - **Queries**: Для получения данных (Read).
    - **Models**: Eloquent модели.
- **Infrastructure Layer**: Внешние интеграции и реализации интерфейсов.

### 2. Типизированные DTO
Вместо передачи массивов используйте `readonly` классы.
- Используйте Constructor Property Promotion.
- Создавайте DTO из запросов через статические методы `fromRequest` или `fromArray`.
- *Пример:* `reference/dto-example.php`

### 3. Разделение Read и Write (CQRS)
- **Commands**: Объекты, описывающие намерение изменить данные.
- **Handlers**: Классы, выполняющие логику команды.
- **Queries**: Оптимизированные выборки данных, не имеющие побочных эффектов.
- *Примеры:* `reference/cqrs-command-example.php`, `reference/cqrs-query-example.php`

### 4. Тонкие Контроллеры
Контроллер должен только делегировать работу.
1. Принять запрос.
2. Создать DTO.
3. Вызвать Handler/Action.
4. Вернуть Resource.
- *Пример:* `reference/controller-example.php`

## Процесс рефакторинга

1. **Покрытие тестами**: Перед началом убедитесь, что текущий функционал покрыт тестами (Pest).
2. **Выделение DTO**: Опишите структуру данных.
3. **Перенос логики**: Вынесите логику из контроллера в Action или Handler.
4. **Обновление контроллера**: Упростите контроллер.
5. **Документирование**: Обновите Swagger-аннотации.
6. **Валидация**: Запустите линтеры и статический анализ.

## Инструменты качества

- **Pint**: Исправление стиля кода (`./vendor/bin/sail pint --dirty`).
- **PHPStan**: Проверка типизации (`./vendor/bin/sail phpstan analyse`).
- **Pest**: Запуск тестов (`./vendor/bin/sail test --compact`).
- **Swagger**: Документирование API (`public/swagger.json`).

## Ссылки на примеры
- [DTO Example](reference/dto-example.php)
- [CQRS Command Example](reference/cqrs-command-example.php)
- [CQRS Query Example](reference/cqrs-query-example.php)
- [Controller Example](reference/controller-example.php)
- [Test Example](reference/test-example.php)
- [Swagger Example](reference/swagger-example.php)
