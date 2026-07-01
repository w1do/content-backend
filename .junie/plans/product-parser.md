---
sessionId: session-260701-213550-msas
---

# Requirements

### Overview & Goals
The goal is to implement an automated product parsing tool that fetches data from an external link (specifically optimized for `mirgaza.ru`) and auto-fills the product creation form in the admin interface.

### Scope
- **In Scope:** 
  - Add an input field at the top of the Product creation form in the Filament admin panel.
  - Fetch product data (Name, Description, Characteristics) via HTTP GET.
  - Parse the raw HTML using standard PHP `DOMDocument`/`DOMXPath`.
  - Dynamically populate form fields (Name, Slug, Description, and Key-Value attributes) without reloading the page.
- **Out of Scope:** 
  - Parsing product images (unless requested in the future).
  - Handling parsing strategies for an unlimited array of sites (focus is strictly on the provided DOM structure for `mirgaza.ru`).

### Functional Requirements
- Users will paste a URL into a new dedicated text input inside the `ProductForm`.
- Clicking "Загрузить" (Load) triggers a background action that calls a parsing service.
- The parser retrieves the name, extracts the textual description by stripping the characteristics section, and transforms the HTML table of characteristics into a structured array.
- The Filament form fields `name`, `slug` (generated via Str::slug), `description` (RichEditor), and `attributes` (KeyValue) are updated instantly.

# Technical Design

### Current Implementation
The application currently leverages Laravel 13, Filament 5, and an established DDD/CQRS architecture pattern. Products are created via `App\Filament\Resources\Products\Schemas\ProductForm`, mapping back to the `App\Domain\Entities\Product` entity through Command Handlers.

### Key Decisions
1. **Parser Execution Strategy:** Execution will happen server-side during a Livewire request triggered by a Filament `Action`. This keeps credentials and HTTP activity safely in the backend.
2. **Parser Location:** Following DDD principles, a parser Interface will reside in the `Domain` layer, its specific HTML parsing implementation (e.g. `MirGazaProductParser`) will sit in the `Infrastructure` layer, and execution will occur through a Query and Handler within the `Application` layer.
3. **HTML Parsing Tool:** `DOMDocument` paired with `DOMXPath` will be used as the parser since it natively supports finding the exact tags outlined (`div.content[itemprop=description]`, `table.props_list`, etc.) without bringing in heavy external libraries initially.

### Proposed Changes

#### Architecture Components
- **`App\Application\DTO\ParsedProductDTO`**: Standard structure capturing `name` (string), `description` (string/HTML), and `attributes` (array).
- **`App\Domain\Services\ProductParserInterface`**: Interface defining `parse(string $url): ParsedProductDTO`.
- **`App\Infrastructure\Services\MirGazaProductParser`**: Actual DOM extraction logic.

#### File Structure
- Add `app/Application/DTO/ParsedProductDTO.php`
- Add `app/Domain/Services/ProductParserInterface.php`
- Add `app/Infrastructure/Services/MirGazaProductParser.php`
- Add `app/Application/Queries/Products/ParseProductFromUrlQuery.php`
- Add `app/Application/Handlers/Products/ParseProductFromUrlHandler.php`
- Edit `app/Filament/Resources/Products/Schemas/ProductForm.php`

#### Data Flow
1. User interacts with UI → Input fires Filament `Action`.
2. Action builds `ParseProductFromUrlQuery` passing URL to `ParseProductFromUrlHandler`.
3. `Handler` invokes `ProductParserInterface->parse($url)`.
4. Extracted `ParsedProductDTO` resolves back up to UI action closure.
5. `$set()` maps the returned DTO values directly onto the Filament live form state.

# Delivery Steps

### ✓ Step 1: define-interfaces-and-dto
- Create `App\Application\DTO\ParsedProductDTO` with fields `name`, `description`, and `attributes`.
- Create `App\Domain\Services\ProductParserInterface` with `parse(string $url): ParsedProductDTO`.

### ✓ Step 2: implement-parser-service
- Create `App\Infrastructure\Services\MirGazaProductParser` implementing `ProductParserInterface`.
- Use Laravel's `Http` facade to fetch the HTML.
- Use `DOMDocument` and `DOMXPath` to extract:
  - Name (H1 or meta).
  - Description (text inside `<div class="content">` excluding `<div class="char-wrp">`).
  - Characteristics (key-value pairs from `<table class="props_list">`).
- Bind `ProductParserInterface` to `MirGazaProductParser` in `App\Providers\AppServiceProvider`.

### ✓ Step 3: implement-application-handler
- Create `App\Application\Queries\Products\ParseProductFromUrlQuery`.
- Create `App\Application\Handlers\Products\ParseProductFromUrlHandler`.
- Inject `ProductParserInterface` into the handler and call its `parse` method.

### ✓ Step 4: update-product-form-ui
- Open `App\Filament\Resources\Products\Schemas\ProductForm.php`.
- Add a new `Section` at the top of the schema named 'Загрузить по ссылке'.
- Include a `TextInput` for `external_url` with a suffix or embedded action `Загрузить`.
- In the action callback, instantiate `ParseProductFromUrlHandler`, execute the query, and use the `$set` closure to fill `name`, `slug`, `description`, and `attributes` fields dynamically.
- Add success/error notifications upon execution.