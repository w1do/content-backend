---
sessionId: session-260701-144331-2bat
---

# Requirements

### Overview & Goals
The objective of this task is to ensure the testing stage successfully passes during the Docker build sequence, specifically addressing problems connected to missing app keys and missing PHP extensions required by the tests.

### Scope
- **In Scope:** Modifying `phpunit.xml` and `Dockerfile` to adjust the testing environment, enabling in-memory SQLite and compiling required extensions (`intl`).
- **Out of Scope:** Refactoring or skipping failing application tests; code logic itself is not altered.

### Functional Requirements
1. Tests should complete successfully without causing the CI/CD or docker build phases to abort with error status.
2. The `APP_KEY` must be automatically provided when `php artisan test` runs.
3. Tests should connect to `sqlite :memory:` and avoid using any local pgsql databases to reduce testing instability and decouple from environmental databases.
4. Extentions necessary (e.g. `intl`) for frontend frameworks and components (e.g., Filament) should be present in the testing Docker build stage.

# Technical Design

### Current Implementation
In `Dockerfile`, the test stage runs `php artisan test` but tests fail due to:
1. `MissingAppKeyException` due to the lack of an application encryption key setting in the environment variables.
2. `ViewException` in a test related to Filament rendering where the `intl` PHP extension is missing in the docker layer.
Furthermore, testing initially was failing due to PostgreSQL dependency errors because it defaulted to the application default (`testing` over a pgsql driver) rather than an isolated SQLite memory database.

### Proposed Changes
- **Update phpunit.xml**: Configure an `APP_KEY` node and force `DB_CONNECTION` to `sqlite` and `DB_DATABASE` to `:memory:` for all tests.
- **Update Dockerfile**: Add `intl` in the list of extensions under `docker-php-ext-install` inside the `test_stage`.
- Also in `Dockerfile`, ensure `.env.example` is copied and `key:generate` is executed before `php artisan test --no-ansi --exclude-group slow`.

# Delivery Steps

### ✓ Step 1: Fix unit and feature tests configurations
- Modify `phpunit.xml` to include an `APP_KEY` which is necessary for tests checking application endpoints (resolving `MissingAppKeyException`).
- Update the default database connection configuration in `phpunit.xml` to use `sqlite` instead of `pgsql` and point `DB_DATABASE` to `:memory:` (resolving test environment dependency limits).

### ✓ Step 2: Update Dockerfile tests stage dependencies
- Update the `test_stage` in `Dockerfile` to install the `intl` extension which is necessary for Filament tests (resolving `ViewException` linked to the missing `intl` module).
- Execute copying `.env.example` to `.env` and `php artisan key:generate` prior to executing the tests to create the missing application key correctly during the build stage.