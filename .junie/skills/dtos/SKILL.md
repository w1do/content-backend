# Data Transfer Objects (DTO) with spatie/laravel-data

Данный навык описывает работу с DTO в Laravel с использованием пакета `spatie/laravel-data`. Этот пакет объединяет в себе функционал FormRequests, API Resources и DTO.

## Установка

```bash
composer require spatie/laravel-data
php artisan vendor:publish --provider="Spatie\LaravelData\LaravelDataServiceProvider" --tag="data-config"
```

## Базовое использование

DTO создаются путем расширения класса `Spatie\LaravelData\Data`.

```php
use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phone = null,
    ) {}
}
```

## Использование в контроллерах

### 1. Как Request (валидация)
Пакет автоматически валидирует входящие данные на основе типов PHP и атрибутов.

```php
public function store(UserData $userData)
{
    // $userData уже провалидирован и наполнен данными
    User::create($userData->toArray());
}
```

### 2. Как Response (трансформация)
DTO автоматически преобразуются в JSON, заменяя стандартные API Resources.

```php
public function show(User $user): UserData
{
    return UserData::from($user);
}
```

## Валидация

Вы можете добавлять правила валидации через атрибуты:

```php
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Min;

class UserData extends Data
{
    public function __construct(
        #[Min(3)]
        public string $name,
        
        #[Email]
        public string $email,
    ) {}
}
```

## Создание из различных источников

```php
// Из массива
$data = UserData::from(['name' => 'John', 'email' => 'john@example.com']);

// Из модели
$data = UserData::from(User::first());

// Из Request
$data = UserData::from($request);
```

## Коллекции

Для работы со списками данных используйте `collect()`:

```php
public function index()
{
    return UserData::collect(User::all());
}
```

## Ленивая загрузка (Lazy Properties)

Используйте для оптимизации производительности при загрузке отношений:

```php
public function __construct(
    public string $title,
    public Lazy|AuthorData $author,
) {}

// Загрузится только если запрошено
UserData::from($post)->include('author');
```

## Основные преимущества
- **Type Safety**: Строгая типизация данных во всем приложении.
- **Single Source of Truth**: Описание структуры данных, правил валидации и правил трансформации в одном месте.
- **TypeScript Generation**: Возможность генерации типов для фронтенда (требуется `spatie/typescript-transformer`).
- **IDE Support**: Отличное автодополнение благодаря обычным свойствам PHP.
