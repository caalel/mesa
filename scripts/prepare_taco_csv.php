<?php

declare(strict_types=1);

/**
 * @return array{records: int, empty_calories: int}
 */
function prepareTacoCsv(string $inputPath, string $outputPath): array
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

    $output = fopen($outputPath, 'w');

    if ($output === false) {
        fclose($input);

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
    $emptyCalories = 0;

    while (($row = fgetcsv($input, null, ',', '"', '')) !== false) {
        $carbohydrates = $row[$columnIndexes['carboidrato_g']] ?? '';

        if (is_numeric($carbohydrates) && (float) $carbohydrates < 0) {
            $carbohydrates = '0';
        }

        $calories = $row[$columnIndexes['energia_kcal']] ?? '';

        fputcsv($output, [
            $row[$columnIndexes['numero_alimento']] ?? '',
            $row[$columnIndexes['descricao']] ?? '',
            '',
            $calories,
            $row[$columnIndexes['proteina_g']] ?? '',
            $carbohydrates,
            $row[$columnIndexes['lipideos_g']] ?? '',
        ], ',', '"', '');

        $records++;

        if ($calories === '') {
            $emptyCalories++;
        }
    }

    fclose($output);
    fclose($input);

    return [
        'records' => $records,
        'empty_calories' => $emptyCalories,
    ];
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $projectPath = dirname(__DIR__);
    $inputPath = $argv[1] ?? $projectPath.'/database/data/foods/taco-composicao-brolesi.csv';
    $outputPath = $argv[2] ?? $projectPath.'/database/data/foods/taco-v4.csv';

    try {
        $result = prepareTacoCsv($inputPath, $outputPath);

        fwrite(STDOUT, "Generated {$result['records']} records.\n");
        fwrite(STDOUT, "Records with empty calories: {$result['empty_calories']}.\n");
    } catch (Throwable $exception) {
        fwrite(STDERR, "Failed to prepare TACO CSV: {$exception->getMessage()}\n");

        exit(1);
    }
}
