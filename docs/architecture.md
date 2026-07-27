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

* `Food` represents a persisted food and its nutritional data per 100 g.
* `NutritionalComparator` is the full-page Livewire component for food selection,
  weight validation, summaries, and comparison results.
* `CompareFoodsService` calculates the equivalent weight from caloric values.
* `NutritionalValuesCalculatorService` calculates a nutritional value for a given
  weight.
* `FoodSearchService` queries and ranks Portuguese food-name search results.
* `FoodImportService` validates compatible CSV rows and imports them with upserts.
* `ImportFoodsCommand` exposes CSV imports through Artisan.
* `FoodSeeder` imports the official CSV through `FoodImportService`.
* `DatabaseSeeder` calls `FoodSeeder`.

## HTTP Routes

### Interface

```text
GET /
```

Renders `NutritionalComparator`.

### Existing HTTP endpoints

```text
GET  /foods/search
POST /compare
```

The Livewire interface uses services directly and does not call these endpoints
internally.

### Artisan command

`foods:import` is an Artisan command, not an HTTP route.

## Food Search

Food searches use `name_pt`. Every searched term is required, but terms do not
need to be contiguous in the food name. The query returns at most eight results.

Results are ranked in the database as follows:

1. names that start with the full search string;
2. names that start with the first searched term;
3. other names that contain every searched term;
4. alphabetical order by `name_pt` within the same level.

## Caloric Comparison

Nutritional values are stored per 100 g. Equivalence is calculated from calories,
not from complete nutritional equivalence.

Food A weight accepts a point or comma as its decimal separator. The component
normalizes the value only for validation and calculations, preserving the public
input state. The weight must be greater than zero and no more than 10,000 g.

The same food may be selected on both sides. Foods with zero or negative calories
cannot produce a caloric equivalence. Food summaries and comparison results use
pt-BR number formatting.

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

`data_source`, `source_code`, and `source_version` form a unique external source
identity. This constraint lets imports be idempotent for a source and version.

## Data Pipeline

```text
TACO 4
→ brolesi/taco
→ explicit overrides
→ preparation script
→ taco-v4.csv
→ FoodImportService
→ database
```

TACO 4 is the primary dataset. The preparation script generates the official
`database/data/foods/taco-v4.csv` file. USDA FoodData Central values are used only
in documented overrides of that prepared dataset, never as a runtime API or
fallback.

Scientific provenance, transformations, overrides, and attribution belong in
[`docs/data-sources.md`](data-sources.md).

## Import Operations

```bash
php artisan foods:import
php artisan foods:import --dry-run
php artisan foods:import --path=/path/to/custom-foods.csv
php artisan db:seed
php artisan migrate:fresh --seed
```

`--dry-run` validates a CSV without persisting rows. The `--path` option allows a
custom path to be provided for another compatible CSV file. The command and seeder
reuse `FoodImportService`, whose `upsert()` operation keeps imports idempotent.

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
