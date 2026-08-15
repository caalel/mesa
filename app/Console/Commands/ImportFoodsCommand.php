<?php

namespace App\Console\Commands;

use App\Services\FoodImporter;
use Illuminate\Console\Command;
use InvalidArgumentException;
use RuntimeException;

class ImportFoodsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'foods:import {--dry-run} {--path=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import foods from a CSV file';

    public function handle(FoodImporter $foodImportService): int
    {
        $path = $this->option('path') ?: database_path('data/foods/taco-v4.csv');

        try {
            if ($this->option('dry-run')) {
                $prepared = $foodImportService->prepare($path, 'taco', '4');
                $validCount = count($prepared['valid_rows']);
                $invalidRows = $prepared['invalid_rows'];
            } else {
                $result = $foodImportService->import($path, 'taco', '4');
                $validCount = $result['valid_count'];
                $invalidRows = $result['invalid_rows'];
            }
        } catch (InvalidArgumentException|RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->line("Valid rows: {$validCount}");
        $this->line('Invalid rows: '.count($invalidRows));

        foreach ($invalidRows as $invalidRow) {
            $this->line("Line {$invalidRow['line']}: ".implode(', ', $invalidRow['errors']));
        }

        return self::SUCCESS;
    }
}
