# Architecture

## Architectural Style

The application follows a Laravel Full Stack approach with Blade views and MVC as the foundation.

To keep business logic isolated from controllers, a Service Layer is adopted.

Goal:

* Keep controllers thin.
* Centralize business logic.
* Improve maintainability and testability.
* Avoid unnecessary complexity.

---

## Project Structure

```text
app/
├── Http/
│   ├── Controllers/
│   └── Requests/
│
├── Models/
│   ├── Food.php
│   ├── Meal.php
│   └── MealItem.php
│
├── Services/
│   ├── FoodSearchService.php
│   ├── CompareFoodsService.php
│   └── MealCalculatorService.php
│
└── Providers/
```

---

## Layer Responsibilities

### Controllers

Controllers are responsible for:

* Receiving HTTP requests.
* Calling Request validation classes.
* Delegating business rules to Services.
* Returning views or responses.

Controllers must remain thin.

---

### Requests

Requests are responsible for:

* Input validation.
* Data sanitization when necessary.

Business rules must not be implemented inside Request classes.

---

### Services

Services contain the application's business logic.

Responsibilities include:

* Food search.
* Food comparison.
* Meal calculations.

Services should remain focused on a single responsibility.

---

### Models

Models represent domain entities.

Current models:

* Food
* Meal
* MealItem

---

## Persistence Strategy

Only the Food model is persisted during V1.

Meal and MealItem are part of the domain model but are not persisted.

Reasons:

* No authentication system exists yet.
* Meals do not need to be recovered.
* Reduces implementation complexity.
* Keeps the focus on validating the core features.

Future versions may introduce:

* User authentication.
* Saved meals.
* Meal history.

---

## Database

Current database tables:

```text
foods
```

Future tables:

```text
meals
meal_items
users
```

---

## Services

### FoodSearchService

Responsible for searching foods.

---

### CompareFoodsService

Responsible for calculating caloric equivalence between foods.

---

### MealCalculatorService

Responsible for calculating meal nutritional totals.

---

## Routes

Home page:

```text
GET /
```

Comparator:

```text
GET /compare
POST /compare
```

Meal Calculator:

```text
GET /meal
POST /meal
```

---

## Database Modeling

### Foods Table

Attributes:

* id
* name_pt
* name_en (nullable)
* calories_per_100g
* protein_per_100g
* carbs_per_100g
* fat_per_100g
* data_source
* source_code
* source_version
* created_at
* updated_at

Nutritional values are stored per 100g.

Calculated values are never persisted.

`data_source`, `source_code`, and `source_version` are required strings that form the
external identity of a food. Their combination is unique. `data_source` uses technical
identifiers such as `taco`; this identity will support future idempotent upserts when an
importer is implemented.

---

## Data Source Strategy

Primary source:

* TACO database stored locally.

Fallback source:

* USDA API.

Future improvement:

* Local cache for external data.

Goal:

Maintain fast searches while avoiding full dependency on external APIs.

---

## Internationalization

Internal language:

* Code in English.
* Database in English.
* Documentation in English.
* Commits in English.

User interface:

* Prepared for future multi-language support.
* Portuguese and English planned.
* Initial implementation kept simple.

---

## Testing Strategy

The project follows Test-Driven Development (TDD).

Rule:

Never write production code without a failing test that justifies it.

Cycle:

```text
Red
↓
Green
↓
Refactor
```

---

## Testing Tools

Framework:

* Pest

Test types:

* Unit tests
* Feature tests

Goal:

Maintain a high ratio between tests and production code.

---

## Design Principles

* Keep things simple.
* Avoid premature optimization.
* Avoid unnecessary abstractions.
* Prefer convention over configuration.
* Follow Laravel standards whenever possible.
* Add complexity only when justified by real requirements.

---

## Test Architecture

Folder structure:

```text
tests/
├── Feature/
│   CompareControllerTest.php
│   MealControllerTest.php
│
├── Unit/
│   CompareFoodsServiceTest.php
│   MealCalculatorServiceTest.php
│
├── Datasets/
│
└── Pest.php
```

Principles:

* One test file per class.
* Prefer simple and descriptive test names.
* Use Pest's `it()` syntax.
* Separate Unit and Feature tests.
* Prioritize business rules and edge cases.

---

## Database Testing

A dedicated database will be used for automated tests.

Example:

```text
mesa
mesa_testing
```

Production environment:

```env
DB_DATABASE=mesa
```

Testing environment:

```env
DB_DATABASE=mesa_testing
```

Tests use Laravel's `RefreshDatabase` trait to guarantee isolation.

Goal:

Ensure reproducible tests and avoid affecting production data.

---

## Implementation Plan

Order of implementation:

1. Project setup.
2. Foods migration.
3. Food model.
4. Food factory.
5. CompareFoodsService.
6. Comparator feature.
7. MealCalculatorService.
8. Meal feature.
9. TACO import.
10. UI improvements.

The project will be developed incrementally through small TDD cycles.

---

## Commit Philosophy

Commits should remain small and focused.

Examples:

```text
test: add equivalent food weight calculation

feat: implement equivalent food weight calculation

test: add zero grams validation

feat: prevent zero grams
```

Large commits containing multiple responsibilities should be avoided.

---

## AI Workflow

The project adopts an AI-assisted pair programming approach inspired by XP practices.

Responsibilities:

Developer:

* Architecture.
* Design decisions.
* Domain modeling.
* Code review.
* Accept or reject changes.

AI Agent:

* Code generation.
* Boilerplate.
* Refactoring.
* Test implementation.

Rule:

The AI assists the development process but never replaces human decision-making.

---

## TDD Workflow

Development cycle:

```text
Write test
↓
RED
↓
Minimal implementation
↓
GREEN
↓
Refactor
↓
Commit
↓
Repeat
```

Fundamental rule:

Never write production code without a failing test that justifies that code.
