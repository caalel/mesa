<?php

namespace App\Services;

use App\Models\Food;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FoodTranslationImportService
{
    public function import(string $path, string $dataSource, string $sourceVersion): void
    {
        $translations = $this->readTranslations($path);

        foreach ($translations as $translation) {
            $exists = Food::query()
                ->where('data_source', $dataSource)
                ->where('source_code', $translation['source_code'])
                ->where('source_version', $sourceVersion)
                ->exists();

            if (! $exists) {
                throw new InvalidArgumentException("Unknown source_code: {$translation['source_code']}");
            }
        }

        DB::transaction(function () use ($translations, $dataSource, $sourceVersion): void {
            foreach ($translations as $translation) {
                Food::query()
                    ->where('data_source', $dataSource)
                    ->where('source_code', $translation['source_code'])
                    ->where('source_version', $sourceVersion)
                    ->update(['name_en' => $translation['name_en']]);
            }
        });
    }

    /**
     * @return array<int, array{source_code: string, name_en: string}>
     */
    private function readTranslations(string $path): array
    {
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

                $translations[] = [
                    'source_code' => $sourceCode,
                    'name_en' => $nameEn,
                ];
            }

            return $translations;
        } finally {
            fclose($csv);
        }
    }
}
