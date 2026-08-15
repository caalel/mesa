<?php

use App\Services\FoodTranslationFileGenerator;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

uses(TestCase::class);

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

afterEach(function () {
    foreach (glob(sys_get_temp_dir().DIRECTORY_SEPARATOR.'ft*.tmp') ?: [] as $path) {
        File::deleteDirectory($path);
    }
});

function foodTranslationGeneratorDirectory(): string
{
    $path = tempnam(sys_get_temp_dir(), 'ftd');

    if ($path === false || ! unlink($path) || ! mkdir($path)) {
        throw new RuntimeException('Could not create CSV fixture directory.');
    }

    return $path;
}

function foodTranslationGeneratorFixture(string $directory, string $filename, string $contents): string
{
    $path = $directory.DIRECTORY_SEPARATOR.$filename;

    if (file_put_contents($path, $contents) === false) {
        throw new RuntimeException('Could not create CSV fixture.');
    }

    return $path;
}

function canonicalFoodCsv(array $rows): string
{
    return "source_code,name_pt,name_en,calories_per_100g,protein_per_100g,carbs_per_100g,fat_per_100g\n".implode("\n", $rows)."\n";
}

function translationCatalogCsv(array $rows): string
{
    return "source_code,name_pt,name_en,review_status,review_notes\n".implode("\n", $rows)."\n";
}

function validCanonicalFoodRows(): array
{
    return [
        '001,"Leite, desnatado",,34,3.37,4.96,0.08',
        '002,Pão francês,,300,8,58,3',
        '003,Água,,0,0,0,0',
    ];
}

function validTranslationCatalogRows(): array
{
    return [
        '001,"Leite, desnatado",Skim milk,approved,',
        '002,Pão francês,French bread,approved,Reviewed',
        '003,Água,Water,approved,',
    ];
}

function expectGenerationToLeaveDestinationUnchanged(string $catalogPath, string $canonicalSourcePath, string $outputPath, string $originalContents): void
{
    expect(fn () => (new FoodTranslationFileGenerator())->generate(
        catalogPath: $catalogPath,
        canonicalSourcePath: $canonicalSourcePath,
        outputPath: $outputPath,
    ))->toThrow(InvalidArgumentException::class);

    expect(file_get_contents($outputPath))->toBe($originalContents);
}

/*
|--------------------------------------------------------------------------
| Tests
|--------------------------------------------------------------------------
*/

it('generates the operational translation CSV from a compatible approved catalog', function () {
    $directory = foodTranslationGeneratorDirectory();
    $canonicalSourcePath = foodTranslationGeneratorFixture($directory, 'canonical.csv', canonicalFoodCsv(validCanonicalFoodRows()));
    $catalogPath = foodTranslationGeneratorFixture($directory, 'catalog.csv', translationCatalogCsv(validTranslationCatalogRows()));
    $outputPath = $directory.DIRECTORY_SEPARATOR.'translations.csv';

    $count = (new FoodTranslationFileGenerator())->generate(
        catalogPath: $catalogPath,
        canonicalSourcePath: $canonicalSourcePath,
        outputPath: $outputPath,
    );

    $contents = file_get_contents($outputPath);

    expect($count)->toBe(3);
    expect($contents)->toBe("source_code,name_en\n001,Skim milk\n002,French bread\n003,Water\n");
    expect($contents)->not->toStartWith("\xEF\xBB\xBF");
    expect($contents)->not->toContain("\r");
    expect($contents)->not->toContain('name_pt');
    expect($contents)->not->toContain('review_status');
    expect($contents)->not->toContain('review_notes');
});

it('deterministically replaces an existing operational translation CSV', function () {
    $directory = foodTranslationGeneratorDirectory();
    $canonicalSourcePath = foodTranslationGeneratorFixture($directory, 'canonical.csv', canonicalFoodCsv(validCanonicalFoodRows()));
    $catalogPath = foodTranslationGeneratorFixture($directory, 'catalog.csv', translationCatalogCsv(validTranslationCatalogRows()));
    $outputPath = foodTranslationGeneratorFixture($directory, 'translations.csv', "old,content\n");
    $service = new FoodTranslationFileGenerator();

    $service->generate($catalogPath, $canonicalSourcePath, $outputPath);
    $firstGeneration = file_get_contents($outputPath);
    $service->generate($catalogPath, $canonicalSourcePath, $outputPath);

    expect(file_get_contents($outputPath))->toBe($firstGeneration);
    expect(file_get_contents($outputPath))->not->toContain('old,content');
});

it('rejects an unexpected catalog header without replacing the destination', function () {
    $directory = foodTranslationGeneratorDirectory();
    $canonicalSourcePath = foodTranslationGeneratorFixture($directory, 'canonical.csv', canonicalFoodCsv(validCanonicalFoodRows()));
    $catalogPath = foodTranslationGeneratorFixture($directory, 'catalog.csv', "name_en,source_code,name_pt,review_status,review_notes\nSkim milk,001,Leite,approved,\n");
    $outputPath = foodTranslationGeneratorFixture($directory, 'translations.csv', "preserve,this\n");

    expectGenerationToLeaveDestinationUnchanged($catalogPath, $canonicalSourcePath, $outputPath, "preserve,this\n");
});

it('rejects catalog records that are not exactly approved without replacing the destination', function () {
    $directory = foodTranslationGeneratorDirectory();
    $canonicalSourcePath = foodTranslationGeneratorFixture($directory, 'canonical.csv', canonicalFoodCsv(validCanonicalFoodRows()));
    $catalogPath = foodTranslationGeneratorFixture($directory, 'catalog.csv', translationCatalogCsv([
        '001,"Leite, desnatado",Skim milk,approved,',
        '002,Pão francês,French bread,pending,',
        '003,Água,Water,approved,',
    ]));
    $outputPath = foodTranslationGeneratorFixture($directory, 'translations.csv', "preserve,this\n");

    expectGenerationToLeaveDestinationUnchanged($catalogPath, $canonicalSourcePath, $outputPath, "preserve,this\n");
});

it('rejects catalog records with empty required translation fields without replacing the destination', function (string $row) {
    $directory = foodTranslationGeneratorDirectory();
    $canonicalSourcePath = foodTranslationGeneratorFixture($directory, 'canonical.csv', canonicalFoodCsv(validCanonicalFoodRows()));
    $catalogPath = foodTranslationGeneratorFixture($directory, 'catalog.csv', translationCatalogCsv([$row]));
    $outputPath = foodTranslationGeneratorFixture($directory, 'translations.csv', "preserve,this\n");

    expectGenerationToLeaveDestinationUnchanged($catalogPath, $canonicalSourcePath, $outputPath, "preserve,this\n");
})->with([
    'empty source code' => [',"Leite, desnatado",Skim milk,approved,'],
    'empty English name' => ['001,"Leite, desnatado",,approved,'],
]);

it('rejects duplicate catalog source codes without replacing the destination', function () {
    $directory = foodTranslationGeneratorDirectory();
    $canonicalSourcePath = foodTranslationGeneratorFixture($directory, 'canonical.csv', canonicalFoodCsv(validCanonicalFoodRows()));
    $catalogPath = foodTranslationGeneratorFixture($directory, 'catalog.csv', translationCatalogCsv([
        '001,"Leite, desnatado",Skim milk,approved,',
        '001,Pão francês,French bread,approved,',
        '003,Água,Water,approved,',
    ]));
    $outputPath = foodTranslationGeneratorFixture($directory, 'translations.csv', "preserve,this\n");

    expectGenerationToLeaveDestinationUnchanged($catalogPath, $canonicalSourcePath, $outputPath, "preserve,this\n");
});

it('rejects catalog divergences from the canonical source without replacing the destination', function (array $canonicalRows, array $catalogRows) {
    $directory = foodTranslationGeneratorDirectory();
    $canonicalSourcePath = foodTranslationGeneratorFixture($directory, 'canonical.csv', canonicalFoodCsv($canonicalRows));
    $catalogPath = foodTranslationGeneratorFixture($directory, 'catalog.csv', translationCatalogCsv($catalogRows));
    $outputPath = foodTranslationGeneratorFixture($directory, 'translations.csv', "preserve,this\n");

    expectGenerationToLeaveDestinationUnchanged($catalogPath, $canonicalSourcePath, $outputPath, "preserve,this\n");
})->with([
    'different record count' => [
        array_slice(validCanonicalFoodRows(), 0, 2),
        validTranslationCatalogRows(),
    ],
    'different source code' => [
        validCanonicalFoodRows(),
        [
            '001,"Leite, desnatado",Skim milk,approved,',
            '099,Pão francês,French bread,approved,',
            '003,Água,Water,approved,',
        ],
    ],
    'different Portuguese name' => [
        validCanonicalFoodRows(),
        [
            '001,"Leite, desnatado",Skim milk,approved,',
            '002,Pão integral,French bread,approved,',
            '003,Água,Water,approved,',
        ],
    ],
    'different record order' => [
        validCanonicalFoodRows(),
        [
            '002,Pão francês,French bread,approved,Reviewed',
            '001,"Leite, desnatado",Skim milk,approved,',
            '003,Água,Water,approved,',
        ],
    ],
]);

it('rejects structurally invalid catalog CSV rows without replacing the destination', function () {
    $directory = foodTranslationGeneratorDirectory();
    $canonicalSourcePath = foodTranslationGeneratorFixture($directory, 'canonical.csv', canonicalFoodCsv(validCanonicalFoodRows()));
    $catalogPath = foodTranslationGeneratorFixture($directory, 'catalog.csv', "source_code,name_pt,name_en,review_status,review_notes\n001,Leite,Skim milk,approved\n");
    $outputPath = foodTranslationGeneratorFixture($directory, 'translations.csv', "preserve,this\n");

    expectGenerationToLeaveDestinationUnchanged($catalogPath, $canonicalSourcePath, $outputPath, "preserve,this\n");
});

it('does not create an output or leave temporary files when validation fails', function () {
    $directory = foodTranslationGeneratorDirectory();
    $canonicalSourcePath = foodTranslationGeneratorFixture($directory, 'canonical.csv', canonicalFoodCsv(validCanonicalFoodRows()));
    $catalogPath = foodTranslationGeneratorFixture($directory, 'catalog.csv', translationCatalogCsv([
        '001,"Leite, desnatado",Skim milk,draft,',
        '002,Pão francês,French bread,approved,',
        '003,Água,Water,approved,',
    ]));
    $outputPath = $directory.DIRECTORY_SEPARATOR.'translations.csv';

    expect(fn () => (new FoodTranslationFileGenerator())->generate(
        catalogPath: $catalogPath,
        canonicalSourcePath: $canonicalSourcePath,
        outputPath: $outputPath,
    ))->toThrow(InvalidArgumentException::class);

    expect(file_exists($outputPath))->toBeFalse();
    expect(glob($directory.DIRECTORY_SEPARATOR.'*'))->toHaveCount(2);
});

it('rejects a missing output directory without creating files or changing inputs', function () {
    $directory = foodTranslationGeneratorDirectory();
    $canonicalSourcePath = foodTranslationGeneratorFixture($directory, 'canonical.csv', canonicalFoodCsv(validCanonicalFoodRows()));
    $catalogPath = foodTranslationGeneratorFixture($directory, 'catalog.csv', translationCatalogCsv(validTranslationCatalogRows()));
    $catalogContents = file_get_contents($catalogPath);
    $canonicalSourceContents = file_get_contents($canonicalSourcePath);
    $outputDirectory = $directory.DIRECTORY_SEPARATOR.'missing-output-directory';
    $outputPath = $outputDirectory.DIRECTORY_SEPARATOR.'translations.csv';

    expect(fn () => (new FoodTranslationFileGenerator())->generate(
        catalogPath: $catalogPath,
        canonicalSourcePath: $canonicalSourcePath,
        outputPath: $outputPath,
    ))->toThrow(RuntimeException::class, "Output directory is not writable: {$outputDirectory}");

    expect(file_exists($outputPath))->toBeFalse();
    expect(is_dir($outputDirectory))->toBeFalse();
    expect(glob($directory.DIRECTORY_SEPARATOR.'*'))->toHaveCount(2);
    expect(file_get_contents($catalogPath))->toBe($catalogContents);
    expect(file_get_contents($canonicalSourcePath))->toBe($canonicalSourceContents);
});
