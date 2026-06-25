# DDD Architecture in Laravel

Domain-Driven Design (DDD) focus on the core domain and domain logic. This skill provides guidelines for a simplified, non-bloated DDD structure in Laravel projects.

## Core Layers

### 1. Presentation Layer (`app/Presentation`)
Responsibility: Handling HTTP requests, CLI commands, and returning responses.
- **Controllers**: Thin, only handle request validation (via FormRequests) and call Application Services/Actions.
- **Resources**: Eloquent API Resources for data transformation.
- **Requests**: FormRequest classes for validation.

### 2. Application Layer (`app/Application`)
Responsibility: Orchestrating domain objects to perform specific tasks (use cases).
- **Services/Actions**: Classes like `CreateOrderAction` or `SubscribeUserTask`.
- **DTOs**: Data Transfer Objects to pass data between layers.
- **Commands/Queries**: If using CQRS.

### 3. Domain Layer (`app/Domain`)
Responsibility: Core business logic, rules, and entities. This layer must be independent of external frameworks.
- **Entities/Models**: Eloquent models (or POPOs if strict) containing business logic.
- **Value Objects**: Immutable objects (e.g., `Price`, `Address`).
- **Domain Services**: Logic that doesn't belong to a single entity.
- **Repositories (Interfaces)**: Abstractions for data access.
- **Events**: Domain events (e.g., `OrderPlaced`).

### 4. Infrastructure Layer (`app/Infrastructure`)
Responsibility: Technical implementation of domain abstractions.
- **Persistence**: Concrete Repository implementations (Eloquent), Migrations.
- **External Services**: API clients, Mailers, File storage.
- **Providers**: Laravel Service Providers for binding interfaces to implementations.

## Directory Structure Example

```text
app/
├── Application/
│   └── Actions/
│       └── Orders/
│           └── CreateOrderAction.php
├── Domain/
│   ├── Orders/
│   │   ├── Models/
│   │   │   └── Order.php
│   │   ├── ValueObjects/
│   │   │   └── OrderStatus.php
│   │   └── Repositories/
│   │       └── OrderRepositoryInterface.php
├── Infrastructure/
│   ├── Persistence/
│   │   └── Eloquent/
│   │       └── EloquentOrderRepository.php
│   └── ExternalServices/
│       └── StripePaymentGateway.php
└── Presentation/
    ├── Controllers/
    │   └── Api/
    │       └── V1/
    │           └── OrderController.php
    └── Resources/
        └── OrderResource.php
```

## Key Principles

1. **Dependency Inversion**: High-level modules (Domain/Application) should not depend on low-level modules (Infrastructure). Use interfaces.
2. **Thin Controllers**: No business logic in controllers.
3. **Rich Models**: Keep business logic inside Models or Domain Services, not in Application Services.
4. **Single Responsibility**: Each Action/Service should do one thing.

## When to Use

- Use for complex projects with evolving business rules.
- Avoid for simple CRUD applications where standard Laravel structure is faster.
