<?php

namespace App\Services;

use App\Models\Food;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FoodTranslationImportService
{
    public function validate(string $path, string $dataSource, string $sourceVersion): int
    {
        return count($this->prepareTranslations($path, $dataSource, $sourceVersion));
    }

    public function import(string $path, string $dataSource, string $sourceVersion): int
    {
        $translations = $this->prepareTranslations($path, $dataSource, $sourceVersion);

        DB::transaction(function () use ($translations, $dataSource, $sourceVersion): void {
            foreach ($translations as $translation) {
                Food::query()
                    ->where('data_source', $dataSource)
                    ->where('source_code', $translation['source_code'])
                    ->where('source_version', $sourceVersion)
                    ->update(['name_en' => $translation['name_en']]);
            }
        });

        return count($translations);
    }

    /**
     * @return array<int, array{source_code: string, name_en: string}>
     */
    private function prepareTranslations(string $path, string $dataSource, string $sourceVersion): array
    {
        $translations = $this->readTranslations($path);
        $csvSourceCodes = array_column($translations, 'source_code');
        $existingSourceCodes = Food::query()
            ->where('data_source', $dataSource)
            ->where('source_version', $sourceVersion)
            ->pluck('source_code')
            ->all();

        sort($csvSourceCodes);
        sort($existingSourceCodes);

        if ($csvSourceCodes !== $existingSourceCodes) {
            throw new InvalidArgumentException('CSV must contain each food source code for this source version exactly once.');
        }

        return $translations;
    }

    /**
     * @return array<int, array{source_code: string, name_en: string}>
     */
    private function readTranslations(string $path): array
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new InvalidArgumentException("Could not open CSV: {$path}");
        }

        $csv = fopen($path, 'r');

        if ($csv === false) {
            throw new InvalidArgumentException("Could not open CSV: {$path}");
        }

        try {
            $header = fgetcsv($csv, null, ',', '"', '');

            if ($header !== ['source_code', 'name_en']) {
                throw new InvalidArgumentException('CSV must have the source_code,name_en header.');
            }

            $translations = [];
            $sourceCodes = [];
            $line = 1;

            while (($row = fgetcsv($csv, null, ',', '"', '')) !== false) {
                $line++;

                if (count($row) !== 2) {
                    throw new InvalidArgumentException("Invalid CSV row: {$line}");
                }

                $sourceCode = trim($row[0]);
                $nameEn = trim($row[1]);

                if ($sourceCode === '' || $nameEn === '') {
                    throw new InvalidArgumentException("Invalid CSV row: {$line}");
                }

                if (array_key_exists($sourceCode, $sourceCodes)) {
                    throw new InvalidArgumentException("Duplicate source_code: {$sourceCode}");
                }

                $translations[] = [
                    'source_code' => $sourceCode,
                    'name_en' => $nameEn,
                ];
                $sourceCodes[$sourceCode] = true;
            }

            return $translations;
        } finally {
            fclose($csv);
        }
    }
}
