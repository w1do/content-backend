---
sessionId: session-260729-224700-sjrn
---

# Requirements

### Overview & Goals
The goal is to automate the generation of SEO meta tags (title and description) for Categories and Products using Polza AI. This ensures that every new item in the catalog is optimized for search engines even if the content manager forgets to fill the SEO fields.

### Scope
- **In Scope**:
    - Automatic SEO generation for `Category` and `Product` models upon creation.
    - Asynchronous processing using Laravel Jobs to handle external API calls.
    - Intelligent text extraction from model fields (`description` with fallback to `name`).
    - Safety check to avoid overwriting manually entered SEO data.
- **Out of Scope**:
    - Automatic SEO generation for existing records (can be done via a separate command if needed).
    - SEO generation for other models like `Content` (unless requested later).

# Technical Design

### Current Implementation
- `SeoGenerator`: A service in `App\Application\Services` that interacts with `PolzaAiService`.
- `HasSeo`: A trait in `App\Infrastructure\Persistence\Eloquent\Concerns` used by `Category` and `Product` models.
- `EloquentSeoRepository`: A repository in `App\Infrastructure\Persistence\Repositories` that handles `Seo` domain entities.

### Proposed Changes
#### New Job: `App\Application\Jobs\GenerateSeoJob`
A queueable job that performs the following steps:
1. Receives the Eloquent model.
2. Checks if the `seo` relationship already has a `title` or `description`. If yes, it aborts to avoid overwriting.
3. Extracts text for generation: `strip_tags($model->description)` or `$model->name` if description is empty.
4. Calls `SeoGenerator->generateFromText($text)`.
5. Maps the result to a `Seo` domain entity.
6. Saves the entity via `SeoRepositoryInterface`.

#### Model Hooks
In `Category` and `Product` Eloquent models, the `booted()` method will be updated to include a `created` event listener:
```php
static::created(function (self $model): void {
    dispatch(new \App\Application\Jobs\GenerateSeoJob($model));
});
```

### Key Decisions
- **Asynchronous Execution**: SEO generation involves an external AI API call, which can be slow or fail. Moving this to a background job ensures the user doesn't wait and provides automatic retries.
- **Preserve Manual Input**: If a user fills the SEO section in the Filament form, those values are saved in the same transaction or immediately after. The job will check for their presence before generating new ones.
- **DDD Compliance**: The job will use the `SeoRepositoryInterface` to save data, maintaining the architectural separation between the Application layer and Infrastructure persistence.

# Testing

### Validation Approach
- **Manual Test**: Create a new Category in the admin panel with only a name and description. Wait a few seconds and refresh the page to see the generated SEO title and description.
- **Overwrite Check**: Create a new Category and manually fill the SEO title. Verify that the generated SEO description appears, but the manual title remains unchanged.
- **Log Verification**: Check `storage/logs/laravel.log` for any "SEO Generator Error" messages if the AI service fails.

### Edge Cases
- **Missing API Key**: The job should handle `PolzaAiException` gracefully.
- **Empty Description**: The system will fallback to the `name` field for generation.
- **HTML Content**: The system will clean HTML tags from the description to provide clean text to the AI.

# Delivery Steps

### ✓ Step 1: Implement GenerateSeoJob
Create the `GenerateSeoJob` to handle SEO generation asynchronously.

- Create `app/Application/Jobs/GenerateSeoJob.php`.
- Implement logic to extract text from the model (`description` with `strip_tags` or `name` as fallback).
- Use `SeoGenerator` service to call Polza AI.
- Use `SeoRepositoryInterface` to save the generated SEO data.
- Ensure the job skips if SEO is already present.

### ✓ Step 2: Integrate SEO Job with Models
Add hooks to the Category and Product models to trigger the SEO job.

- Update `App\Infrastructure\Persistence\Eloquent\Category` to dispatch the job on the `created` event.
- Update `App\Infrastructure\Persistence\Eloquent\Product` to dispatch the job on the `created` event.
- Ensure the job is dispatched after the database transaction is committed to avoid race conditions.

### ✓ Step 3: Verify and Test
Ensure the implementation works as expected through automated tests and manual verification checks.

- Create Pest tests to verify job dispatching and execution.
- Fix any discovered bugs in the SEO repository or model.
- Run Pint for code formatting.