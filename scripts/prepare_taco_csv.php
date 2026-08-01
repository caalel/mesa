<?php

declare(strict_types=1);

/**
 * Derives the MESA CSV from the immutable normalized brolesi source and TACO decisions.
 * Missing values, original precision, and scientific notation such as `1e-05` are preserved;
 * only negative source carbohydrates are normalized to zero.
 *
 * @return array{records: int, overridden: int, removed: int, empty_calories: int}
 */
function prepareTacoCsv(string $inputPath, string $outputPath, string $overridesPath): array
{
    $input = fopen($inputPath, 'r');

    if ($input === false) {
        throw new RuntimeException("Could not open input CSV: {$inputPath}");
    }

    $header = fgetcsv($input, null, ',', '"', '');

    if ($header === false) {
        fclose($input);

        throw new InvalidArgumentException('Input CSV is missing a header row.');
    }

    $requiredColumns = [
        'numero_alimento',
        'descricao',
        'energia_kcal',
        'proteina_g',
        'carboidrato_g',
        'lipideos_g',
    ];

    $columnIndexes = array_flip($header);

    foreach ($requiredColumns as $column) {
        if (! array_key_exists($column, $columnIndexes)) {
            fclose($input);

            throw new InvalidArgumentException("Missing required column: {$column}");
        }
    }

    $overrides = loadTacoOverrides($overridesPath);
    $inputRows = [];
    $sourceCodes = [];

    while (($row = fgetcsv($input, null, ',', '"', '')) !== false) {
        $inputRows[] = $row;
        $sourceCodes[$row[$columnIndexes['numero_alimento']] ?? ''] = true;
    }

    fclose($input);

    foreach (array_keys($overrides) as $sourceCode) {
        if (! array_key_exists($sourceCode, $sourceCodes)) {
            throw new InvalidArgumentException("Override source_code not found in input CSV: {$sourceCode}");
        }
    }

    $output = fopen($outputPath, 'w');

    if ($output === false) {
        throw new RuntimeException("Could not open output CSV: {$outputPath}");
    }

    fputcsv($output, [
        'source_code',
        'name_pt',
        'name_en',
        'calories_per_100g',
        'protein_per_100g',
        'carbs_per_100g',
        'fat_per_100g',
    ], ',', '"', '');

    $records = 0;
    $overridden = 0;
    $removed = 0;
    $emptyCalories = 0;

    foreach ($inputRows as $row) {
        $sourceCode = $row[$columnIndexes['numero_alimento']] ?? '';
        $decision = $overrides[$sourceCode] ?? null;

        if ($decision !== null && $decision['action'] === 'remove') {
            $removed++;

            continue;
        }

        $calories = $row[$columnIndexes['energia_kcal']] ?? '';
        $protein = $row[$columnIndexes['proteina_g']] ?? '';
        $carbohydrates = $row[$columnIndexes['carboidrato_g']] ?? '';
        $fat = $row[$columnIndexes['lipideos_g']] ?? '';

        if ($decision !== null) {
            $calories = $decision['calories_per_100g'];
            $protein = $decision['protein_per_100g'];
            $carbohydrates = $decision['carbs_per_100g'];
            $fat = $decision['fat_per_100g'];
            $overridden++;
        } elseif (is_numeric($carbohydrates) && (float) $carbohydrates < 0) {
            // Small negatives come from the source's calculation by difference and have no practical nutritional meaning, so use zero.
            $carbohydrates = '0';
        }

        // Preserve source precision, empty fields, and scientific notation without rounding.
        fputcsv($output, [
            $sourceCode,
            trim($row[$columnIndexes['descricao']] ?? ''),
            // English names will come from a separate reviewed translation dataset.
            '',
            $calories,
            $protein,
            $carbohydrates,
            $fat,
        ], ',', '"', '');

        $records++;

        if ($calories === '') {
            $emptyCalories++;
        }
    }

    fclose($output);

    return [
        'records' => $records,
        'overridden' => $overridden,
        'removed' => $removed,
        'empty_calories' => $emptyCalories,
    ];
}

/**
 * @return array<string, array{action: string, calories_per_100g: string, protein_per_100g: string, carbs_per_100g: string, fat_per_100g: string}>
 */
function loadTacoOverrides(string $overridesPath): array
{
    $overridesFile = @fopen($overridesPath, 'r');

    if ($overridesFile === false) {
        throw new RuntimeException("Could not open overrides CSV: {$overridesPath}");
    }

    $header = fgetcsv($overridesFile, null, ',', '"', '');

    if ($header === false) {
        fclose($overridesFile);

        throw new InvalidArgumentException('Overrides CSV is missing a header row.');
    }

    $requiredColumns = [
        'source_code',
        'action',
        'calories_per_100g',
        'protein_per_100g',
        'carbs_per_100g',
        'fat_per_100g',
        'nutrient_source',
        'source_reference',
        'notes',
    ];
    $columnIndexes = array_flip($header);

    foreach ($requiredColumns as $column) {
        if (! array_key_exists($column, $columnIndexes)) {
            fclose($overridesFile);

            throw new InvalidArgumentException("Missing required overrides column: {$column}");
        }
    }

    $overrides = [];

    while (($row = fgetcsv($overridesFile, null, ',', '"', '')) !== false) {
        $sourceCode = $row[$columnIndexes['source_code']] ?? '';
        $action = $row[$columnIndexes['action']] ?? '';

        if (array_key_exists($sourceCode, $overrides)) {
            fclose($overridesFile);

            throw new InvalidArgumentException("Duplicate override source_code: {$sourceCode}");
        }

        if (! in_array($action, ['override', 'remove'], true)) {
            fclose($overridesFile);

            throw new InvalidArgumentException("Invalid override action for source_code {$sourceCode}: {$action}");
        }

        $override = [
            'action' => $action,
            'calories_per_100g' => $row[$columnIndexes['calories_per_100g']] ?? '',
            'protein_per_100g' => $row[$columnIndexes['protein_per_100g']] ?? '',
            'carbs_per_100g' => $row[$columnIndexes['carbs_per_100g']] ?? '',
            'fat_per_100g' => $row[$columnIndexes['fat_per_100g']] ?? '',
        ];

        if ($action === 'override') {
            foreach (['calories_per_100g', 'protein_per_100g', 'carbs_per_100g', 'fat_per_100g'] as $column) {
                if ($override[$column] === '') {
                    fclose($overridesFile);

                    throw new InvalidArgumentException("Override source_code {$sourceCode} requires {$column}");
                }

                if (! is_numeric($override[$column])) {
                    fclose($overridesFile);

                    throw new InvalidArgumentException("Override source_code {$sourceCode} has non-numeric {$column}");
                }
            }
        }

        $overrides[$sourceCode] = $override;
    }

    fclose($overridesFile);

    return $overrides;
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $projectPath = dirname(__DIR__);
    $inputPath = $argv[1] ?? $projectPath.'/database/data/foods/taco-composicao-brolesi.csv';
    $outputPath = $argv[2] ?? $projectPath.'/database/data/foods/taco-v4.csv';
    $overridesPath = $argv[3] ?? $projectPath.'/database/data/foods/taco-v4-overrides.csv';

    try {
        $result = prepareTacoCsv($inputPath, $outputPath, $overridesPath);

        fwrite(STDOUT, "Generated {$result['records']} records.\n");
        fwrite(STDOUT, "Overridden records: {$result['overridden']}.\n");
        fwrite(STDOUT, "Removed records: {$result['removed']}.\n");
        fwrite(STDOUT, "Records with empty calories: {$result['empty_calories']}.\n");
    } catch (Throwable $exception) {
        fwrite(STDERR, "Failed to prepare TACO CSV: {$exception->getMessage()}\n");

        exit(1);
    }
}
