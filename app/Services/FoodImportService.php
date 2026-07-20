<?php

namespace App\Services;

use InvalidArgumentException;
use RuntimeException;

class FoodImportService
{
    /**
     * @return array{valid_rows: array<int, array<string, float|string|null>>, invalid_rows: array<int, array{line: int, errors: array<int, string>}>}
     */
    public function prepare(string $path, string $dataSource, string $sourceVersion): array
    {
        $csv = fopen($path, 'r');

        if ($csv === false) {
            throw new RuntimeException("Could not open CSV: {$path}");
        }

        try {
            $header = fgetcsv($csv, null, ',', '"', '');

            if ($header === false) {
                throw new InvalidArgumentException('CSV is missing a header row.');
            }

            $requiredColumns = [
                'source_code',
                'name_pt',
                'name_en',
                'calories_per_100g',
                'protein_per_100g',
                'carbs_per_100g',
                'fat_per_100g',
            ];
            $columnIndexes = array_flip($header);

            foreach ($requiredColumns as $column) {
                if (! array_key_exists($column, $columnIndexes)) {
                    throw new InvalidArgumentException("Missing required CSV column: {$column}");
                }
            }

            $validRows = [];
            $invalidRows = [];
            $line = 1;

            while (($row = fgetcsv($csv, null, ',', '"', '')) !== false) {
                $line++;
                $values = [];

                foreach ($requiredColumns as $column) {
                    $values[$column] = $row[$columnIndexes[$column]] ?? '';
                }

                $errors = [];

                foreach (['source_code', 'name_pt'] as $column) {
                    if (trim($values[$column]) === '') {
                        $errors[] = $column;
                    }
                }

                foreach ([
                    'calories_per_100g',
                    'protein_per_100g',
                    'carbs_per_100g',
                    'fat_per_100g',
                ] as $column) {
                    if (! is_numeric($values[$column]) || (float) $values[$column] < 0) {
                        $errors[] = $column;
                    }
                }

                if ($errors !== []) {
                    $invalidRows[] = [
                        'line' => $line,
                        'errors' => $errors,
                    ];

                    continue;
                }

                $validRows[] = [
                    'name_pt' => $values['name_pt'],
                    'name_en' => $values['name_en'] === '' ? null : $values['name_en'],
                    'calories_per_100g' => (float) $values['calories_per_100g'],
                    'protein_per_100g' => (float) $values['protein_per_100g'],
                    'carbs_per_100g' => (float) $values['carbs_per_100g'],
                    'fat_per_100g' => (float) $values['fat_per_100g'],
                    'data_source' => $dataSource,
                    'source_code' => $values['source_code'],
                    'source_version' => $sourceVersion,
                ];
            }

            return [
                'valid_rows' => $validRows,
                'invalid_rows' => $invalidRows,
            ];
        } finally {
            fclose($csv);
        }
    }
}
