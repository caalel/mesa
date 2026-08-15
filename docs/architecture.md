# MESA Architecture

## Purpose

MESA is an MVP that compares foods through caloric equivalence. The user selects a
reference food, enters its weight, and chooses a second food. The application then
calculates and displays the amount of the second food that provides approximately
the same number of calories.

## Current Stack

* PHP 8.3.
* Laravel 13.
* Livewire 4.
* Blade.
* Tailwind CSS 4.
* Vite.
* MySQL.
* Pest/PHPUnit.

## Application Architecture

The application uses Laravel's MVC foundation with Blade and Livewire. Controllers
and Livewire components coordinate HTTP and interface state; services contain the
focused business and data-import operations.

### Components

* `Food` represents a persisted food and its nutritional data per 100 g. Its
  `localized_name` accessor returns the name for the active locale.
* `NutritionalComparator` is the full-page Livewire component for food selection,
  weight validation, summaries, and comparison results.
* `CompareFoodsService` calculates the equivalent weight from caloric values.
* `NutritionalValuesCalculator` calculates a nutritional value for a given
  weight.
* `FoodSearchService` queries and ranks food-name search results in the active
  locale.
* `FoodImporter` validates compatible CSV rows and imports them with upserts.
* `FoodTranslationFileGenerator` validates the reviewed editorial catalog
  against the canonical source and writes the operational translation CSV.
* `GenerateFoodTranslationsCommand` exposes translation-file generation through
  Artisan.
* `ImportFoodsCommand` exposes CSV imports through Artisan.
* `FoodSeeder` imports the official CSV through `FoodImporter`.
* `DatabaseSeeder` calls `FoodSeeder`.
* `SetLocale` resolves the active locale for every web request.

## HTTP Routes

### Interface

```text
GET /
```

Renders `NutritionalComparator`.

### HTTP endpoint

```text
POST /locale/{locale}
```

`POST /locale/{locale}` accepts `pt_BR` and `en`, stores the selection in the
session, and redirects back. The Livewire interface uses services directly.

### Artisan Commands

`foods:import` and `foods:generate-translations` are Artisan commands, not HTTP
routes.

## Localization and Food Search

`SetLocale` uses a valid session locale first. Without one, it evaluates the
`Accept-Language` header: English maps to `en`, any Portuguese variant such as
`pt-BR` or `pt-PT` maps to `pt_BR`, and unsupported languages default to `pt_BR`.
The header selector submits to `POST /locale/{locale}` for manual switching.

`Food::localized_name` returns `name_pt` for `pt_BR` and `name_en` for `en`.
Food searches use only that same locale-specific column; there is no fallback
between food-name languages. The Livewire component and its Blade components
present `localized_name`.

Every searched term is required, but terms do not need to be contiguous in the
food name. The query returns at most eight results.

Results are ranked in the database as follows:

1. names that start with the full search string;
2. names that start with the first searched term;
3. other names that contain every searched term;
4. alphabetical order by the active locale's name column within the same level.

## Caloric Comparison

Nutritional values are stored per 100 g. Equivalence is calculated from calories,
not from complete nutritional equivalence.

Food A weight accepts a point or comma as its decimal separator. The component
normalizes the value only for validation and calculations, preserving the public
input state. The weight must be greater than zero and no more than 10,000 g.

The same food may be selected on both sides. Foods with zero or negative calories
cannot produce a caloric equivalence. Food summaries and comparison results use
number formatting for the active locale.

## Persistence and Source Identity

The `foods` table stores:

```text
id
name_pt
name_en
calories_per_100g
protein_per_100g
carbs_per_100g
fat_per_100g
data_source
source_code
source_version
created_at
updated_at
```

`data_source` + `source_version` + `source_code` form a unique external source
identity. This constraint lets imports be idempotent for a source and version.

## Data Pipeline

```text
taco-v4-en-translation-catalog.csv
→ foods:generate-translations
→ taco-v4-en-translations.csv
→ scripts/prepare_taco_csv.php
→ taco-v4.csv
→ FoodSeeder or foods:import
→ foods table
```

TACO 4, normalized through `brolesi/taco`, is the primary dataset. The preparation
script combines it with explicit overrides and the operational translation CSV to
generate `database/data/foods/taco-v4.csv`. The translation generator validates
the editorial catalog against its configured canonical source before writing its
output. USDA FoodData Central values are used only in documented overrides of the
prepared dataset, never as a runtime API or fallback.

The canonical CSV and its required generated inputs are versioned. A normal clean
clone can run migrations and seed directly; generation and preparation are dataset
maintenance operations, not installation prerequisites.

Scientific provenance, transformations, overrides, and attribution belong in
[`docs/data-sources.md`](data-sources.md).

## Import Operations

```bash
php artisan foods:import
php artisan foods:import --dry-run
php artisan foods:import --path=/path/to/custom-foods.csv
php artisan db:seed
php artisan migrate:fresh --seed
php artisan foods:generate-translations
php artisan foods:generate-translations --catalog=/path/to/catalog.csv --source=/path/to/canonical-foods.csv --output=/path/to/translations.csv
```

`--dry-run` validates a CSV without persisting rows. The `--path` option allows a
custom path to be provided for another compatible CSV file. The command and seeder
reuse `FoodImporter`, whose `upsert()` operation keeps imports idempotent.
`foods:generate-translations` accepts optional `--catalog`, `--source`, and
`--output` paths. Details of source provenance and the regeneration workflow belong
in [`docs/data-sources.md`](data-sources.md).

## Databases

Development uses the MySQL `mesa` database. Tests use the dedicated MySQL
`mesa_testing` database.

`phpunit.xml` imposes `APP_ENV=testing`, `DB_CONNECTION=mysql`, and
`DB_DATABASE=mesa_testing`. `.env.testing` supplies only the local connection
details: host, port, username, and password. `.env.testing.example` is the
public template and contains no real credentials.

If the test database does not exist or the connection fails, the suite fails
instead of using the development database. Tests use `RefreshDatabase` where
database isolation is required.

## Testing and Build

The suite includes unit coverage for services and the data pipeline, plus feature
coverage for imports, the Artisan command, seeders, HTTP endpoints, Livewire, and
search and comparison rules.

```bash
php artisan test
npm.cmd run build
```

Development conventions, including TDD, are defined in
[`AGENTS.md`](../AGENTS.md). Interface behavior and visual direction belong in
[`docs/design.md`](design.md).
