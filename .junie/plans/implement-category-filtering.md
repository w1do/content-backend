---
sessionId: session-260729-172153-b2nl
---

# Requirements

### Overview & Goals
The goal is to implement a robust filtering system for the categories API endpoint (`GET /api/v1/categories`). The user wants to search categories by `id`, `name`, `slug`, and `full_path` using the `spatie/laravel-query-builder` package and custom filter classes.

### Scope
- **Infrastructure**: Install `spatie/laravel-query-builder` and implement custom filter classes.
- **Application**: Update Queries and Handlers to support passing filter data via DTO.
- **Presentation**: Update the API controller to accept query parameters and document them in Swagger.
- **Testing**: Ensure all filtering logic is covered by Pest tests.

### Functional Requirements
- Support filtering by `id` (exact, multiple values).
- Support filtering by `name` (partial match).
- Support filtering by `slug` (exact match).
- Support filtering by `full_path` (partial or exact match).
- All filters should use the standard `filter[field]=value` syntax provided by `spatie/laravel-query-builder`.

# Technical Design

### Current Implementation
- `CategoryController::index` returns all categories via `GetCategoriesHandler`.
- `GetCategoriesHandler` calls `CategoryRepositoryInterface::findAll()`.
- `EloquentCategoryRepository::findAll()` returns all categories ordered by the nested set's default order.
- `spatie/laravel-query-builder` is currently not installed in the project.

### Key Decisions
- **Custom Filter Classes**: We will implement individual filter classes for each field to satisfy the requirement of "different classes".
- **DDD Integration**: To maintain clean architecture, we will pass filter data via a `CategoryFilterDTO` instead of passing the `Request` object directly to the repository.
- **Spatie Laravel Data**: We will use `spatie/laravel-data` for the `CategoryFilterDTO` to leverage its validation and mapping capabilities, consistent with the project's tech stack.

### Architecture
1. **Presentation**: `CategoryController` receives `filter[...]` parameters.
2. **Application**: `CategoryFilterDTO` encapsulates filter data; `GetCategoriesQuery` carries it to the `Handler`.
3. **Infrastructure**: `EloquentCategoryRepository` uses `QueryBuilder` to apply filters defined in `app/Infrastructure/Persistence/Eloquent/Filters/Categories/`.

### File Structure
- `app/Infrastructure/Persistence/Eloquent/Filters/Categories/` (New)
    - `CategoryIdFilter.php`
    - `CategoryNameFilter.php`
    - `CategorySlugFilter.php`
    - `CategoryFullPathFilter.php`
- `app/Application/DTO/CategoryFilterDTO.php` (New)
- `app/Presentation/Controllers/Api/V1/CategoryController.php` (Modified)
- `app/Infrastructure/Persistence/Repositories/EloquentCategoryRepository.php` (Modified)

### Risks
- **Package Installation**: Adding a new dependency (`spatie/laravel-query-builder`) requires approval.
- **Nested Set Order**: Ensure that filtering doesn't break the logical ordering if required, though typically filtering returns a flat list where order is less critical than matches.

# Testing

### Validation Approach
- Verify that calling `GET /api/v1/categories?filter[id]=1` returns the category with ID 1.
- Verify that `GET /api/v1/categories?filter[name]=test` returns categories with "test" in their name.
- Verify that `GET /api/v1/categories?filter[slug]=electronics` returns the exact category.
- Verify that `GET /api/v1/categories?filter[full_path]=parent/child` returns the correct path match.
- Check combinations of filters (e.g., `id` and `name`).

### Test Changes
- New test file: `tests/Feature/Api/V1/CategoryFilteringTest.php`.

# Delivery Steps

### ✓ Step 1: Install dependencies and create Filter DTO
- Install `spatie/laravel-query-builder` package using composer.
- Create `app/Application/DTO/CategoryFilterDTO.php` using `spatie/laravel-data` to represent filtering criteria.
- The DTO will include fields for `id`, `name`, `slug`, and `full_path`.

### ✓ Step 2: Implement custom filter classes
- Create `app/Infrastructure/Persistence/Eloquent/Filters/Categories/CategoryIdFilter.php`.
- Create `app/Infrastructure/Persistence/Eloquent/Filters/Categories/CategoryNameFilter.php`.
- Create `app/Infrastructure/Persistence/Eloquent/Filters/Categories/CategorySlugFilter.php`.
- Create `app/Infrastructure/Persistence/Eloquent/Filters/Categories/CategoryFullPathFilter.php`.
- Each class will implement `Spatie\QueryBuilder\Filters\Filter` and handle specific filtering logic.

### ✓ Step 3: Update Repository and Application layers
- Update `CategoryRepositoryInterface.php` to accept `CategoryFilterDTO` in `findAll()`.
- Update `EloquentCategoryRepository.php` to use `QueryBuilder` with the provided filters.
- Update `GetCategoriesQuery.php` and `GetCategoriesHandler.php` to pass the filtering data from the presentation layer to the repository.

### ✓ Step 4: Update Controller and Documentation
- Update `CategoryController::index` to instantiate `CategoryFilterDTO` from the request and pass it to the handler.
- Add Swagger documentation (`#[OA\Parameter]`) for the `filter` parameters in `CategoryController::index`.
- Run `vendor/bin/pint` to ensure code style compliance.

### ✓ Step 5: Add tests for category filtering
- Create `tests/Feature/Api/V1/CategoryFilteringTest.php` to verify all filters.
- Test cases should cover individual filters (`id`, `name`, `slug`, `full_path`) and their combinations.
- Verify that filtered results match expected categories.