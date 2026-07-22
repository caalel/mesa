# MESA Data Sources

This document records the origin, preparation, explicit decisions, traceability,
and attribution of the nutritional dataset used by MESA. The application uses a
reviewed local CSV at runtime; it does not query third-party composition tables
during food search or caloric comparison.

## Source Chain

```text
TACO 4
→ brolesi/taco
→ explicit overrides
→ scripts/prepare_taco_csv.php
→ database/data/foods/taco-v4.csv
→ FoodImportService
→ database
```

TACO 4 is the primary scientific source. `brolesi/taco` is the technical source
used to extract and normalize the official spreadsheet; it is not the scientific
authority for the food values.

### Primary Scientific Source

> Tabela Brasileira de Composição de Alimentos - TACO
> 4th revised and expanded edition
> Núcleo de Estudos e Pesquisas em Alimentação - NEPA
> Universidade Estadual de Campinas - UNICAMP
> Campinas, 2011

Official publication:

* https://nepa.unicamp.br/publicacoes/tabela-taco-pdf/
* https://nepa.unicamp.br/publicacoes/tabela-taco-excel/

The original fourth-edition dataset contains 597 food records. Original TACO codes
are preserved as strings in `source_code` to keep each prepared record traceable.

### Technical Processing Source

The normalized input was derived from:

> brolesi/taco
> Fabio Fogliarini Brolesi

Repository: https://github.com/brolesi/taco

## Pipeline Files

| File | Role |
| --- | --- |
| `database/data/foods/taco-composicao-brolesi.csv` | Immutable preserved technical input derived from `brolesi/taco`. |
| `database/data/foods/taco-v4-overrides.csv` | Auditable declarative decisions for overrides, removals, sources, references, and notes. |
| `scripts/prepare_taco_csv.php` | Reproducibly prepares the official MESA CSV from the input and decisions. |
| `database/data/foods/taco-v4.csv` | Generated official dataset and the CSV imported by the application. |

The immutable input is not edited for project-specific corrections. The generated
CSV must not be manually edited.

## Current Prepared Dataset

`database/data/foods/taco-v4.csv` contains 592 foods. The final count results from
the source processing and documented decisions: 10 overrides and 5 removed records.

Its columns are:

```text
source_code
name_pt
name_en
calories_per_100g
protein_per_100g
carbs_per_100g
fat_per_100g
```

The preparation preserves Portuguese descriptions, source codes, source precision,
optional empty fields unless explicitly resolved, and positive scientific notation such as
`1e-05`. A positive trace value must not be converted to zero. English names are
empty in the current prepared dataset. Missing nutritional values are not converted to zero
without an explicit reviewed decision.

## Reproducible Transformations

The preparation script validates the expected source and overrides headers, required
decision fields, supported actions, duplicate override codes, numeric override
values, and that every override references an existing TACO code. It then applies
decisions, excludes removed records, normalizes negative source carbohydrates when
there is no explicit override, writes the simplified CSV, and reports totals.

Negative carbohydrates originate from calculation by difference and are normalized
to `0` only when numeric, negative, and not explicitly overridden. Empty values are
not converted to zero.

## Explicit Override Decisions

`taco-v4-overrides.csv` supports `override` and `remove` actions. An override keeps
the original `source_code`, `name_pt`, and `name_en`, while replacing the four stored
nutritional values.

The 10 current overrides are:

* eight explicit oil normalizations (TACO 259, 260, 267–272), where TACO reports
  protein and carbohydrates as `NA`; these scientifically non-applicable fields are
  represented as zero, not through a global `NA → 0` rule;
* two complementary nutrient decisions for TACO 457 and 458 using USDA FoodData
  Central.

No other override categories are present in the current decisions file.

### Oil Normalizations

For codes 259, 260, and 267–272, the prepared values are:

```text
884 kcal
0 g protein
0 g carbohydrates
100 g fat
```

These are explicit, reviewed overrides. They do not authorize a generic conversion
of missing nutritional values to zero.

### Complementary USDA FoodData Central Values

USDA FoodData Central provides complementary nutrients only. It is not the primary
source identity for either food:

```text
data_source = taco
source_code = 457 or 458
source_version = 4
```

The `nutrient_source` field in the overrides identifies USDA for these decisions.
The selected records are approximate nutritional matches, not exact matches for the
UHT processing described by TACO. There is no USDA API query or external fallback at
runtime.

#### TACO 457

```text
TACO description: Leite, de vaca, desnatado, UHT
USDA FoodData Central FDC ID: 171269
USDA food: Milk, nonfat, fluid, with added vitamin A and vitamin D (fat free or skim)

Calories: 34 kcal
Protein: 3.37 g
Carbohydrates: 4.96 g
Fat: 0.08 g
```

#### TACO 458

```text
TACO description: Leite, de vaca, integral
USDA FoodData Central FDC ID: 171265
USDA food: Milk, whole, 3.25% milkfat, with added vitamin D

Calories: 61 kcal
Protein: 3.15 g
Carbohydrates: 4.80 g
Fat: 3.25 g
```

The reviewed USDA values are stored in `taco-v4-overrides.csv` and must not be
presented as original TACO measurements.

## Removed Records

Five TACO records are excluded from the final dataset:

| TACO code | Food | Documented reason |
| --- | --- | --- |
| 450 | Iogurte, sabor abacaxi | Incomplete composition and no reliable complementary source for the exact food. |
| 472 | Cana, aguardente 1 | Calories exist, but protein, carbohydrates, and fat required by MESA are absent. |
| 516 | Sal, dietético | Zero calories and no practical use for caloric equivalence. |
| 517 | Sal, grosso | Zero calories and no practical use for caloric equivalence. |
| 591 | Coco, verde, cru | Incomplete composition and no reliable specific source for raw green coconut. |

Generic coconut or coconut-water sources were not treated as equivalent to TACO 591.

## Validation and Audit

The preparation and import pipeline is covered by automated tests. The preparation
script audits its source and override inputs as described above. `FoodImportService`
validates compatible final CSVs for their expected header, required `source_code`
and `name_pt`, numeric nutritional values, and non-negative nutritional values.

The final dataset audit confirms:

```text
592 records
7 columns per record
0 empty source codes
0 duplicate source codes
0 empty Portuguese names
0 empty calories
0 empty proteins
0 empty carbohydrates
0 empty fats
0 negative nutritional values
```

The audit also confirms the five expected removals, the eight oil overrides, the two
USDA overrides, and preservation of positive `1e-05` traces.

## Identity and Idempotent Import

Persisted foods have a unique composite source identity:

```text
data_source
source_code
source_version
```

`FoodImportService` reads and validates compatible CSVs. Valid rows are persisted
with `upsert()` using that identity, so repeated imports do not duplicate foods and
new imports update records from the same source, code, and version. Technical
details of the import architecture are in [`docs/architecture.md`](architecture.md).

## Dataset Operations

### Regenerate the Official CSV

```bash
php scripts/prepare_taco_csv.php
```

### Audit Without Persisting

```bash
php artisan foods:import --dry-run
```

### Import the Official Dataset

```bash
php artisan foods:import
```

Without a path option, the command uses `database/data/foods/taco-v4.csv`. See
[`docs/architecture.md`](architecture.md) for the complete import operation.

## Licensing and Attribution

### TACO

TACO data originates from NEPA / UNICAMP. The project cites the official publication
when the dataset or derived information is distributed or presented.

Recommended attribution:

> UNIVERSIDADE ESTADUAL DE CAMPINAS - UNICAMP. Núcleo de Estudos e Pesquisas em Alimentação - NEPA. Tabela Brasileira de Composição de Alimentos - TACO. 4. ed. rev. e ampl. Campinas: UNICAMP/NEPA, 2011.

Consult the official publication for complete reproduction and attribution terms.

### brolesi/taco

The technical processing repository is distributed under the MIT License:

```text
MIT License

Copyright (c) 2026 Fabio Fogliarini Brolesi
```

When code or substantial portions of that repository are included, its copyright and
permission notice must be preserved. This code license does not determine the
attribution or licensing terms of the TACO-derived data.

### USDA FoodData Central

The complementary USDA records 171269 and 171265 are public-domain / CC0 data.
They are documented approximations and are not original TACO measurements.

## Scientific Limitations

Values are reference composition values per 100 g. MESA compares foods by calories;
it does not establish complete nutritional equivalence. Food composition can vary by
origin, brand, processing, preparation, sampling, laboratory method, and regional
or publication differences.

The USDA decisions for TACO 457 and 458 are documented approximations. This dataset
does not replace professional nutritional guidance or act as a clinical database.
