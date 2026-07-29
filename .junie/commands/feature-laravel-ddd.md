---
name: feature-laravel-ddd
description: "Реализация функционала в стиле DDD + CQRS + DTO с декомпозированным UI, FSD и обязательной документацией"
---

При реализации любой фичи, правки или задачи следуй этой архитектуре и правилам. Сначала проверь существующую реализацию и переиспользуемые компоненты, затем изучи актуальные инструкции в `.ai/skills`.

### 📚 Инструкции и навыки (Skills)
Перед началом работы обязательно изучи соответствующие навыки в директории `.ai/skills/`:
- **DDD и CQRS**: `.ai/skills/architecture-ddd` и `.ai/skills/refactoring-ddd`
- **DTO**: `.ai/skills/dtos` — используй `spatie/laravel-data`, не передавай сырые массивы между слоями
- **Laravel Best Practices**: `.ai/skills/spatie-laravel-php` и доступный навык `laravel-best-practices`
- **UI из source**: `.ai/skills/source-copy` — обязательно при переносе страницы, блока или вёрстки из `source/`
- **Документация**: `.ai/skills/documentation`
- **Тестирование**: доступный навык `pest-testing`; проектные соглашения — Pest 4

### 🚀 Работа через Laravel Sail (Обязательно)
Все команды должны выполняться строго внутри Docker-контейнеров через Laravel Sail. Использование локальных PHP, Composer или Artisan напрямую в хост-системе запрещено.

**Примеры использования:**
- **Artisan**: `./vendor/bin/sail artisan make:controller ...`
- **Composer**: `./vendor/bin/sail composer require ...`
- **Тесты**: `./vendor/bin/sail test --compact`
- **Статический анализ**: `./vendor/bin/sail phpstan analyse`
- **Форматирование**: `./vendor/bin/sail pint --dirty`
- **Shell**: `./vendor/bin/sail shell` (для входа внутрь контейнера)

### 🏗 Архитектурные слои (DDD + CQRS + DTO)

1. **Http Layer (`app/Http`)**:
   - **Controllers**: Тонкие. Только вызывают Actions или Handlers.
   - **Requests**: Вся валидация данных (`./vendor/bin/sail artisan make:request`). Используй DTO для передачи данных дальше.
   - **Resources**: Форматирование API ответов; для DTO-ответов предпочитай `spatie/laravel-data`.

2. **Domain Layer (`app/Domain/{DomainName}`)**:
   - **Actions**: Простые классы для изменения состояния или выполнения одной бизнес-задачи.
   - **Commands**: типизированные DTO, описывающие намерение изменить состояние (write).
   - **Handlers**: Сложная бизнес-логика, обрабатывающая Commands.
   - **Models**: Eloquent модели.
   - **Queries**: Специализированные классы для получения данных (Read models).

3. **Infrastructure Layer (`app/Infrastructure`)**:
   - Реализации интерфейсов, внешние API, системные сервисы.

Правила CQRS обязательны: write-операции проходят через `Command` → `Handler`, read-операции — через отдельный `Query`; не смешивай чтение и изменение состояния в одном классе.

### 🧩 Декомпозиция UI и FSD

- Разбивай каждую страницу на самостоятельные UI-блоки; не создавай монолитные `.tsx`-файлы и компоненты «портянки».
- Перед созданием компонента найди существующий аналог и переиспользуй его вместо дублирования.
- Общие компоненты храни в `resources/js/Components/**`, компоненты конкретного домена — в `resources/js/Pages/{Domain}/Components/**`.
- Для фронтенд-части соблюдай FSD: `app` — инициализация, `pages` — страницы, `widgets` — крупные композиции, `features` — пользовательские действия, `entities` — доменные сущности, `shared` — переиспользуемый UI/утилиты. Не допускай импортов из нижнего слоя в верхний и циклических зависимостей.
- Новую страницу собирай из слоёв FSD и отдельных компонентов блоков; каждый компонент должен иметь одну ответственность и типизированные props.
- При переносе из `source/` применяй строгий `source-copy`: сначала определи формат и состав блоков, затем переноси разметку блок-в-блок без добавления/улучшения классов, обёрток, отступов или поведения. Разрешены только адаптация TSX, динамические данные, русские тексты и абсолютные пути к статике.
- После фронтенд-изменений запускай `./vendor/bin/sail npm run build`.

---

### ✅ Обязательный Pipeline (перед сабмитом)

Каждое изменение должно пройти следующие проверки:

1. **Статический анализ (Larastan)**:
   - Выполни: `./vendor/bin/sail phpstan analyse` (или соответствующую команду проекта).
   - Исправь все ошибки типизации.

2. **Форматирование (Pint)**:
   - Выполни: `./vendor/bin/sail pint --dirty` для исправления стиля кода.

3. **Тестирование (Pest)**:
   - Напиши тесты для нового функционала.
   - Запусти: `./vendor/bin/sail test --compact`. Все тесты должны быть зелеными.

4. **Документация (обязательно)**:
   - Обнови или создай файл в `/docs` с описанием изменений, структуры и решений.
   - Обнови `SUMMARY.md` и раздел «Функционал проекта» в `README.md`, если изменение заметно для проекта.
   - Для API добавь/обнови Swagger-аннотации `OpenApi\Attributes` и проверь генерацию документации.
   - Запиши новые пути бизнес-логики в `removed.txt` по правилам `clean-project`; ядро и авторизацию не записывай.

---

### 📝 Примеры кода

Используй современные возможности PHP 8.5 (Constructor Property Promotion, Readonly, Enums) и паттерны из `.ai/skills`.

#### Command & Handler
```php
// app/Domain/Assistant/Commands/IndexChunksCommand.php
readonly class IndexChunksCommand {
    public function __construct(public int $id, public array $chunks) {}
}

// app/Domain/Assistant/Handlers/IndexAssistantChunksHandler.php
class IndexAssistantChunksHandler {
    public function handle(IndexChunksCommand $command): void {
        // Бизнес-логика...
    }
}
```
