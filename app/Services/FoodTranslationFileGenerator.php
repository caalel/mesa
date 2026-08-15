<?php

namespace App\Services;

use InvalidArgumentException;
use RuntimeException;

class FoodTranslationFileGenerator
{
    private const CATALOG_HEADER = [
        'source_code',
        'name_pt',
        'name_en',
        'review_status',
        'review_notes',
    ];

    private const CANONICAL_SOURCE_HEADER = [
        'source_code',
        'name_pt',
        'name_en',
        'calories_per_100g',
        'protein_per_100g',
        'carbs_per_100g',
        'fat_per_100g',
    ];

    public function generate(string $catalogPath, string $canonicalSourcePath, string $outputPath): int
    {
        $catalogRows = $this->readCatalog($catalogPath);
        $canonicalRows = $this->readCanonicalSource($canonicalSourcePath);

        $this->ensureCatalogMatchesCanonicalSource($catalogRows, $canonicalRows);

        $this->writeOperationalFile($catalogRows, $outputPath);

        return count($catalogRows);
    }

    /**
     * @return array<int, array{source_code: string, name_pt: string, name_en: string}>
     */
    private function readCatalog(string $path): array
    {
        $rows = $this->readCsv($path, self::CATALOG_HEADER, 'catalog');
        $catalogRows = [];
        $sourceCodes = [];

        foreach ($rows as $line => $row) {
            [$sourceCode, $namePt, $nameEn, $reviewStatus] = $row;

            if ($sourceCode === '') {
                throw new InvalidArgumentException("Catalog source_code is empty on line {$line}.");
            }

            if ($nameEn === '') {
                throw new InvalidArgumentException("Catalog name_en is empty on line {$line}.");
            }

            if ($reviewStatus !== 'approved') {
                throw new InvalidArgumentException("Catalog review_status must be approved on line {$line}.");
            }

            if (array_key_exists($sourceCode, $sourceCodes)) {
                throw new InvalidArgumentException("Duplicate catalog source_code: {$sourceCode}.");
            }

            $catalogRows[] = [
                'source_code' => $sourceCode,
                'name_pt' => $namePt,
                'name_en' => $nameEn,
            ];
            $sourceCodes[$sourceCode] = true;
        }

        return $catalogRows;
    }

    /**
     * @return array<int, array{source_code: string, name_pt: string}>
     */
    private function readCanonicalSource(string $path): array
    {
        $rows = $this->readCsv($path, self::CANONICAL_SOURCE_HEADER, 'canonical source');
        $canonicalRows = [];

        foreach ($rows as $line => $row) {
            [$sourceCode, $namePt] = $row;

            if ($sourceCode === '' || $namePt === '') {
                throw new InvalidArgumentException("Canonical source has an empty required field on line {$line}.");
            }

            $canonicalRows[] = [
                'source_code' => $sourceCode,
                'name_pt' => $namePt,
            ];
        }

        return $canonicalRows;
    }

    /**
     * @param array<int, string> $expectedHeader
     * @return array<int, array<int, string>>
     */
    private function readCsv(string $path, array $expectedHeader, string $label): array
    {
        $csv = fopen($path, 'rb');

        if ($csv === false) {
            throw new InvalidArgumentException("Could not open {$label} CSV: {$path}");
        }

        try {
            $header = fgetcsv($csv, null, ',', '"', '');

            if ($header !== $expectedHeader) {
                throw new InvalidArgumentException("Unexpected {$label} CSV header.");
            }

            $rows = [];
            $line = 1;

            while (($row = fgetcsv($csv, null, ',', '"', '')) !== false) {
                $line++;

                if (count($row) !== count($expectedHeader)) {
                    throw new InvalidArgumentException("Invalid {$label} CSV row: {$line}.");
                }

                $rows[$line] = $row;
            }

            return $rows;
        } finally {
            fclose($csv);
        }
    }

    /**
     * @param array<int, array{source_code: string, name_pt: string, name_en: string}> $catalogRows
     * @param array<int, array{source_code: string, name_pt: string}> $canonicalRows
     */
    private function ensureCatalogMatchesCanonicalSource(array $catalogRows, array $canonicalRows): void
    {
        if (count($catalogRows) !== count($canonicalRows)) {
            throw new InvalidArgumentException('Catalog record count does not match the canonical source.');
        }

        foreach ($catalogRows as $index => $catalogRow) {
            $canonicalRow = $canonicalRows[$index];

            if ($catalogRow['source_code'] !== $canonicalRow['source_code']) {
                throw new InvalidArgumentException('Catalog source_code does not match the canonical source order.');
            }

            if ($catalogRow['name_pt'] !== $canonicalRow['name_pt']) {
                throw new InvalidArgumentException('Catalog name_pt does not match the canonical source.');
            }
        }
    }

    /**
     * @param array<int, array{source_code: string, name_pt: string, name_en: string}> $catalogRows
     */
    private function writeOperationalFile(array $catalogRows, string $outputPath): void
    {
        $directory = dirname($outputPath);

        if (! is_dir($directory) || ! is_writable($directory)) {
            throw new RuntimeException("Output directory is not writable: {$directory}");
        }

        $temporaryPath = tempnam($directory, '.food-translation-');

        if ($temporaryPath === false) {
            throw new RuntimeException("Could not create temporary CSV: {$outputPath}");
        }

        try {
            $csv = fopen($temporaryPath, 'wb');

            if ($csv === false) {
                throw new RuntimeException("Could not write temporary CSV: {$temporaryPath}");
            }

            try {
                $this->writeCsvRow($csv, ['source_code', 'name_en']);

                foreach ($catalogRows as $row) {
                    $this->writeCsvRow($csv, [$row['source_code'], $row['name_en']]);
                }
            } finally {
                fclose($csv);
            }

            if (! rename($temporaryPath, $outputPath)) {
                throw new RuntimeException("Could not replace operational CSV: {$outputPath}");
            }

            $temporaryPath = null;
        } finally {
            if ($temporaryPath !== null && is_file($temporaryPath)) {
                unlink($temporaryPath);
            }
        }
    }

    /**
     * @param resource $csv
     * @param array<int, string> $row
     */
    private function writeCsvRow($csv, array $row): void
    {
        $serializedRow = implode(',', array_map(
            fn (string $value): string => strpbrk($value, ",\"\r\n") === false
                ? $value
                : '"'.str_replace('"', '""', $value).'"',
            $row,
        ))."\n";

        if (fwrite($csv, $serializedRow) !== strlen($serializedRow)) {
            throw new RuntimeException('Could not write operational CSV row.');
        }
    }
}
