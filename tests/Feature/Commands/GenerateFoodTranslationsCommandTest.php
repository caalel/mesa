<?php

use App\Services\FoodTranslationFileGeneratorService;
use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

afterEach(function () {
    foreach (glob(sys_get_temp_dir().DIRECTORY_SEPARATOR.'gf*.tmp') ?: [] as $path) {
        File::deleteDirectory($path);
    }

    Mockery::close();
});

function foodTranslationGenerationCommandDirectory(): string
{
    $path = tempnam(sys_get_temp_dir(), 'gfd');

    if ($path === false || ! unlink($path) || ! mkdir($path)) {
        throw new RuntimeException('Could not create CSV fixture directory.');
    }

    return $path;
}

function foodTranslationGenerationCommandFixture(string $directory, string $filename, string $contents): string
{
    $path = $directory.DIRECTORY_SEPARATOR.$filename;

    if (file_put_contents($path, $contents) === false) {
        throw new RuntimeException('Could not create CSV fixture.');
    }

    return $path;
}

function foodTranslationGenerationCanonicalCsv(array $rows): string
{
    return "source_code,name_pt,name_en,calories_per_100g,protein_per_100g,carbs_per_100g,fat_per_100g\n".implode("\n", $rows)."\n";
}

function foodTranslationGenerationCatalogCsv(array $rows): string
{
    return "source_code,name_pt,name_en,review_status,review_notes\n".implode("\n", $rows)."\n";
}

function validFoodTranslationGenerationCanonicalRows(): array
{
    return [
        '001,Leite,,34,3.37,4.96,0.08',
        '002,Pão,,300,8,58,3',
        '003,Água,,0,0,0,0',
    ];
}

function validFoodTranslationGenerationCatalogRows(): array
{
    return [
        '001,Leite,Skim milk,approved,',
        '002,Pão,French bread,approved,',
        '003,Água,Water,approved,',
    ];
}

/*
|--------------------------------------------------------------------------
| Tests
|--------------------------------------------------------------------------
*/

it('generates translations using the paths provided by options', function () {
    $directory = foodTranslationGenerationCommandDirectory();
    $catalogPath = foodTranslationGenerationCommandFixture($directory, 'catalog.csv', foodTranslationGenerationCatalogCsv(validFoodTranslationGenerationCatalogRows()));
    $sourcePath = foodTranslationGenerationCommandFixture($directory, 'source.csv', foodTranslationGenerationCanonicalCsv(validFoodTranslationGenerationCanonicalRows()));
    $outputPath = $directory.DIRECTORY_SEPARATOR.'translations.csv';

    $this->artisan('foods:generate-translations', [
        '--catalog' => $catalogPath,
        '--source' => $sourcePath,
        '--output' => $outputPath,
    ])
        ->expectsOutput('Generated 3 food translations.')
        ->assertSuccessful();

    expect(file_get_contents($outputPath))->toBe("source_code,name_en\n001,Skim milk\n002,French bread\n003,Water\n");
});

it('replaces an existing output using the paths provided by options', function () {
    $directory = foodTranslationGenerationCommandDirectory();
    $catalogPath = foodTranslationGenerationCommandFixture($directory, 'catalog.csv', foodTranslationGenerationCatalogCsv(validFoodTranslationGenerationCatalogRows()));
    $sourcePath = foodTranslationGenerationCommandFixture($directory, 'source.csv', foodTranslationGenerationCanonicalCsv(validFoodTranslationGenerationCanonicalRows()));
    $outputPath = foodTranslationGenerationCommandFixture($directory, 'translations.csv', "old,content\n");

    $this->artisan('foods:generate-translations', [
        '--catalog' => $catalogPath,
        '--source' => $sourcePath,
        '--output' => $outputPath,
    ])->assertSuccessful();

    expect(file_get_contents($outputPath))->not->toContain('old,content');
});

it('reports service failures without replacing an existing output', function () {
    $directory = foodTranslationGenerationCommandDirectory();
    $catalogPath = foodTranslationGenerationCommandFixture($directory, 'catalog.csv', foodTranslationGenerationCatalogCsv([
        '001,Leite,Skim milk,pending,',
        '002,Pão,French bread,approved,',
        '003,Água,Water,approved,',
    ]));
    $sourcePath = foodTranslationGenerationCommandFixture($directory, 'source.csv', foodTranslationGenerationCanonicalCsv(validFoodTranslationGenerationCanonicalRows()));
    $outputPath = foodTranslationGenerationCommandFixture($directory, 'translations.csv', "preserve,this\n");

    $this->artisan('foods:generate-translations', [
        '--catalog' => $catalogPath,
        '--source' => $sourcePath,
        '--output' => $outputPath,
    ])
        ->expectsOutputToContain('Catalog review_status must be approved')
        ->assertFailed();

    expect(file_get_contents($outputPath))->toBe("preserve,this\n");
});

it('uses the official paths when no options are provided', function () {
    $catalogPath = base_path('database/data/foods/taco-v4-en-translation-catalog.csv');
    $sourcePath = base_path('database/data/foods/taco-v4.csv');
    $outputPath = base_path('database/data/foods/taco-v4-en-translations.csv');
    $generator = Mockery::mock(FoodTranslationFileGeneratorService::class);

    $generator
        ->shouldReceive('generate')
        ->once()
        ->with($catalogPath, $sourcePath, $outputPath)
        ->andReturn(3);

    app()->instance(FoodTranslationFileGeneratorService::class, $generator);

    $this->artisan('foods:generate-translations')
        ->expectsOutput('Generated 3 food translations.')
        ->assertSuccessful();
});
