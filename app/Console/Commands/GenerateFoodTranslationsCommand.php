<?php

namespace App\Console\Commands;

use App\Services\FoodTranslationFileGenerator;
use Illuminate\Console\Command;
use InvalidArgumentException;
use RuntimeException;

class GenerateFoodTranslationsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'foods:generate-translations {--catalog=} {--source=} {--output=}';

    /**
     * @var string
     */
    protected $description = 'Generate the operational English food translations CSV';

    public function handle(FoodTranslationFileGenerator $generator): int
    {
        $catalogPath = $this->option('catalog')
            ?: base_path('database/data/foods/taco-v4-en-translation-catalog.csv');
        $sourcePath = $this->option('source')
            ?: base_path('database/data/foods/taco-v4.csv');
        $outputPath = $this->option('output')
            ?: base_path('database/data/foods/taco-v4-en-translations.csv');

        try {
            $count = $generator->generate($catalogPath, $sourcePath, $outputPath);
        } catch (InvalidArgumentException|RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Generated {$count} food translations.");

        return self::SUCCESS;
    }
}
