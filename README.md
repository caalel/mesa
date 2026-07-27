# MESA

**Medidor de Equivalência e Síntese Alimentar**

MESA calculates caloric equivalence between foods. The user selects a reference food, enters its weight, and chooses a second food. The application then displays the amount of the second food that provides approximately the same number of calories.

## About the project

MESA is a Laravel and Livewire MVP focused on clear, practical food comparison. The current interface is localized in pt-BR, and all nutritional data is prepared and stored locally, so food search and comparison do not depend on external APIs at runtime.

## Features

- Multi-term food search with relevance-based result ranking.
- A maximum of eight search results.
- Selection and replacement of Food A and Food B.
- Weight input that accepts a point or comma as the decimal separator.
- Friendly validation feedback and a maximum weight of 10,000 g.
- Selection of the same food on both sides.
- Calorie summary for the reference food.
- Approximate caloric-equivalence calculation.
- pt-BR number formatting, with positive values below `0.01` displayed as `< 0,01` instead of zero.
- Automatic smooth scrolling to the result.
- Responsive interface.
- Local prepared nutritional dataset.
- Idempotent food imports.
- Artisan import command with dry-run support.
- Database seeder integrated with Laravel's standard seeding flow.
- Automated tests.

## Technologies

- PHP 8.3
- Laravel 13
- Livewire 4
- Blade
- Tailwind CSS 4
- Vite
- MySQL
- Pest/PHPUnit

## Nutritional data

TACO 4 is the primary scientific source, while `brolesi/taco` is the normalized technical processing source. Preparation decisions and overrides are explicit and reproducible. The prepared CSV currently contains 592 foods.

USDA FoodData Central complements only the nutritional values of TACO codes 457 and 458; TACO identity remains preserved for both records. The application does not query USDA or any other nutritional API at runtime.

[Data sources and preparation](docs/data-sources.md)

## Requirements

- PHP 8.3 or later
- Composer
- Node.js 20.19+ or 22.12+
- npm
- MySQL

## Installation

Clone the repository and install the PHP and frontend dependencies:

```bash
git clone <REPOSITORY_URL>
cd mesa
composer install
npm install
```

Create the local environment file:

```bash
cp .env.example .env
```

On Windows:

```powershell
copy .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Create a MySQL database and configure its connection in `.env`. Never commit database credentials.

Create the schema, import the official food dataset, and compile frontend assets:

```bash
php artisan migrate --seed
npm run build
```

`migrate --seed` creates the schema and imports the official food dataset.

After creating the MySQL database and configuring its connection in `.env`, you
may run the optional setup shortcut:

```bash
composer setup
```

It generates the application key, runs the migrations, imports the official
dataset through the seeder, installs the frontend dependencies, and builds the
frontend assets.

The script can create `.env` from `.env.example` when the file is missing, but the
database connection must be configured before the migration step.

The manual steps above remain available when you need to run each stage separately.

## Running the application

Use Laravel's standard development flow:

```bash
php artisan serve
npm run dev
```

`php artisan serve` starts Laravel's local server, and `npm run dev` starts Vite for frontend development. These commands are optional when the project is served through Laragon, Docker, Valet, Herd, or another local web server.

## Food import commands

```bash
php artisan foods:import --dry-run
php artisan foods:import
```

The dry run validates the CSV without persisting data. The normal command inserts or updates valid foods, and imports are idempotent. By default, the command uses `database/data/foods/taco-v4.csv`.

`php artisan migrate --seed` already imports the official dataset through the seeder. See [Architecture](docs/architecture.md) for advanced import details.

## Test environment

Tests use a dedicated MySQL database named `mesa_testing`, never the development database. Create it locally, then create the local test environment file:

```bash
cp .env.testing.example .env.testing
php artisan key:generate --env=testing
```

On Windows:

```powershell
copy .env.testing.example .env.testing
php artisan key:generate --env=testing
```

The key-generation command writes the application key to `.env.testing`. Configure in that file only the local connection details (host, port, user, and password). Never commit real credentials. The project has technical protection that keeps the suite on the dedicated test database; if it is unavailable, tests fail instead of using the development database. See [Architecture](docs/architecture.md) for technical details.

## Tests and build

```bash
php artisan test
npm run build
```

The test command validates domain, Livewire, HTTP, import, command, seeder, and integration behavior. The build command validates production frontend asset compilation.

## Technical decisions

- Domain and import logic are isolated in services.
- TDD is used for behavior changes.
- Multi-term matching and relevance ranking run in the database.
- Imports are idempotent through composite source identity.
- A local, reproducible dataset replaces runtime nutritional APIs.
- Livewire provides interactive, server-driven UI behavior.

[Architecture](docs/architecture.md)<br>
[Interface design](docs/design.md)

## MVP limitations

- Equivalence is based only on calories.
- Nutritional values are references per 100 g.
- Real composition may vary by brand, origin, preparation, and processing.
- The project does not replace professional nutritional guidance.
- The MVP has no authentication.
- The MVP has no user-created custom foods.
- Search does not include typo-tolerant fuzzy matching.

## Documentation

- [Architecture](docs/architecture.md)
- [Interface design](docs/design.md)
- [Data sources and preparation](docs/data-sources.md)
- [Agent instructions](AGENTS.md) — instructions for coding agents working on this repository.

## License

The MESA source code is available under the [MIT License](LICENSE).

Nutritional datasets and third-party source materials retain their own attribution
and licensing terms, documented in
[Data sources and preparation](docs/data-sources.md).
