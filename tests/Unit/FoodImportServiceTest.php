<?php

use App\Services\FoodImportService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

uses(TestCase::class);

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

afterEach(function () {
    foreach (glob(sys_get_temp_dir().DIRECTORY_SEPARATOR.'food-import-*') ?: [] as $path) {
        @unlink($path);
    }
});

/*
|--------------------------------------------------------------------------
| Tests
|--------------------------------------------------------------------------
*/

it('prepares valid food rows for import', function () {
    $csvPath = tempnam(sys_get_temp_dir(), 'food-import-');

    if ($csvPath === false) {
        throw new RuntimeException('Could not create CSV fixture.');
    }

    File::put($csvPath, <<<CSV
        source_code,name_pt,name_en,calories_per_100g,protein_per_100g,carbs_per_100g,fat_per_100g
        001,Leite desnatado,Skim milk,34,3.37,4.96,0.08
        002,Alimento trace,Trace food,10,1,1e-05,0.5
        CSV);

    $service = new FoodImportService();

    $result = $service->prepare(
        path: $csvPath,
        dataSource: 'taco',
        sourceVersion: '4',
    );

    expect($result['valid_rows'])->toBe([
        [
            'name_pt' => 'Leite desnatado',
            'name_en' => 'Skim milk',
            'calories_per_100g' => 34.0,
            'protein_per_100g' => 3.37,
            'carbs_per_100g' => 4.96,
            'fat_per_100g' => 0.08,
            'data_source' => 'taco',
            'source_code' => '001',
            'source_version' => '4',
        ],
        [
            'name_pt' => 'Alimento trace',
            'name_en' => 'Trace food',
            'calories_per_100g' => 10.0,
            'protein_per_100g' => 1.0,
            'carbs_per_100g' => 0.00001,
            'fat_per_100g' => 0.5,
            'data_source' => 'taco',
            'source_code' => '002',
            'source_version' => '4',
        ],
    ])->and($result['invalid_rows'])->toBeEmpty();
});

it('separates invalid food rows from valid food rows', function () {
    $csvPath = tempnam(sys_get_temp_dir(), 'food-import-');

    if ($csvPath === false) {
        throw new RuntimeException('Could not create CSV fixture.');
    }

    File::put($csvPath, <<<CSV
        source_code,name_pt,name_en,calories_per_100g,protein_per_100g,carbs_per_100g,fat_per_100g
        100,Alimento valido,Valid food,10,1,2,3
        ,Sem codigo,Missing code,10,1,2,3
        102,Nutriente invalido,Invalid nutrient,invalid,1,2,3
        103,Nutriente negativo,Negative nutrient,10,1,-2,3
        CSV);

    $service = new FoodImportService();

    $result = $service->prepare(
        path: $csvPath,
        dataSource: 'taco',
        sourceVersion: '4',
    );

    expect($result['valid_rows'])->toBe([
        [
            'name_pt' => 'Alimento valido',
            'name_en' => 'Valid food',
            'calories_per_100g' => 10.0,
            'protein_per_100g' => 1.0,
            'carbs_per_100g' => 2.0,
            'fat_per_100g' => 3.0,
            'data_source' => 'taco',
            'source_code' => '100',
            'source_version' => '4',
        ],
    ]);

    $invalidRows = $result['invalid_rows'];

    expect($invalidRows)->toBe([
        [
            'line' => 3,
            'errors' => ['source_code'],
        ],
        [
            'line' => 4,
            'errors' => ['calories_per_100g'],
        ],
        [
            'line' => 5,
            'errors' => ['carbs_per_100g'],
        ],
    ]);
});

it('rejects a CSV without every required header', function () {
    $csvPath = tempnam(sys_get_temp_dir(), 'food-import-');

    if ($csvPath === false) {
        throw new RuntimeException('Could not create CSV fixture.');
    }

    File::put($csvPath, <<<CSV
        source_code,name_pt,name_en,calories_per_100g,protein_per_100g,carbs_per_100g
        100,Alimento valido,,10,1,2
        CSV);

    $service = new FoodImportService();

    expect(fn () => $service->prepare(
        path: $csvPath,
        dataSource: 'taco',
        sourceVersion: '4',
    ))->toThrow(InvalidArgumentException::class);
});

it('rejects a food row with an empty English name', function () {
    $csvPath = tempnam(sys_get_temp_dir(), 'food-import-');

    if ($csvPath === false) {
        throw new RuntimeException('Could not create CSV fixture.');
    }

    File::put($csvPath, <<<CSV
        source_code,name_pt,name_en,calories_per_100g,protein_per_100g,carbs_per_100g,fat_per_100g
        104,Alimento sem ingles,,10,1,2,3
        CSV);

    $result = (new FoodImportService())->prepare($csvPath, 'taco', '4');

    expect($result['valid_rows'])->toBeEmpty()
        ->and($result['invalid_rows'])->toBe([
            ['line' => 2, 'errors' => ['name_en']],
        ]);
});

it('rejects a food row with a whitespace-only English name', function () {
    $csvPath = tempnam(sys_get_temp_dir(), 'food-import-');

    if ($csvPath === false) {
        throw new RuntimeException('Could not create CSV fixture.');
    }

    File::put($csvPath, <<<CSV
        source_code,name_pt,name_en,calories_per_100g,protein_per_100g,carbs_per_100g,fat_per_100g
        105,Alimento sem ingles,   ,10,1,2,3
        CSV);

    $result = (new FoodImportService())->prepare($csvPath, 'taco', '4');

    expect($result['valid_rows'])->toBeEmpty()
        ->and($result['invalid_rows'])->toBe([
            ['line' => 2, 'errors' => ['name_en']],
        ]);
});
