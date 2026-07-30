<?php

namespace App\Console\Commands;

use App\Services\FoodTranslationImportService;
use Illuminate\Console\Command;
use InvalidArgumentException;

class ImportFoodTranslationsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'foods:import-translations
        {path : Path to the translation CSV}
        {--source=taco : Food data source}
        {--source-version=4 : Food source version}
        {--dry-run : Validate without updating the database}';

    /**
     * @var string
     */
    protected $description = 'Import English food translations from a CSV file';

    public function handle(FoodTranslationImportService $foodTranslationImportService): int
    {
        $path = $this->argument('path');
        $dataSource = $this->option('source');
        $sourceVersion = $this->option('source-version');

        try {
            if ($this->option('dry-run')) {
                $count = $foodTranslationImportService->validate($path, $dataSource, $sourceVersion);
                $this->line("Dry run: {$count} food translations would be imported.");
            } else {
                $count = $foodTranslationImportService->import($path, $dataSource, $sourceVersion);
                $this->line("Imported {$count} food translations.");
            }
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
