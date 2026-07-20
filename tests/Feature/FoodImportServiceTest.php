<?php

use App\Models\Food;
use App\Services\FoodImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

afterEach(function () {
    foreach (glob(sys_get_temp_dir().DIRECTORY_SEPARATOR.'food-import-feature-*') ?: [] as $path) {
        @unlink($path);
    }
});

it('inserts valid foods from a CSV import', function () {
    $csvPath = tempnam(sys_get_temp_dir(), 'food-import-feature-');

    if ($csvPath === false) {
        throw new RuntimeException('Could not create CSV fixture.');
    }

    File::put($csvPath, <<<CSV
        source_code,name_pt,name_en,calories_per_100g,protein_per_100g,carbs_per_100g,fat_per_100g
        001,Leite desnatado,,34,3.37,4.96,0.08
        002,Leite integral,Whole milk,61,3.15,4.80,3.25
        CSV);

    $service = new FoodImportService();

    $result = $service->import(
        path: $csvPath,
        dataSource: 'taco',
        sourceVersion: '4',
    );

    $food = Food::query()
        ->where('data_source', 'taco')
        ->where('source_code', '001')
        ->where('source_version', '4')
        ->firstOrFail();

    $persistedFood = [
        'name_pt' => $food->name_pt,
        'name_en' => $food->name_en,
        'calories_per_100g' => (float) $food->calories_per_100g,
        'data_source' => $food->data_source,
        'source_code' => $food->source_code,
        'source_version' => $food->source_version,
    ];

    expect(Food::query()->count())->toBe(2);

    expect($persistedFood)->toBe([
        'name_pt' => 'Leite desnatado',
        'name_en' => null,
        'calories_per_100g' => 34.0,
        'data_source' => 'taco',
        'source_code' => '001',
        'source_version' => '4',
    ]);

    expect($result)->toBe([
        'valid_count' => 2,
        'invalid_rows' => [],
    ]);
});

it('does not duplicate foods when importing the same CSV again', function () {
    $csvPath = tempnam(sys_get_temp_dir(), 'food-import-feature-');

    if ($csvPath === false) {
        throw new RuntimeException('Could not create CSV fixture.');
    }

    File::put($csvPath, <<<CSV
        source_code,name_pt,name_en,calories_per_100g,protein_per_100g,carbs_per_100g,fat_per_100g
        001,Leite desnatado,,34,3.37,4.96,0.08
        002,Leite integral,,61,3.15,4.80,3.25
        CSV);

    $service = new FoodImportService();

    $service->import(
        path: $csvPath,
        dataSource: 'taco',
        sourceVersion: '4',
    );

    $service->import(
        path: $csvPath,
        dataSource: 'taco',
        sourceVersion: '4',
    );

    expect(Food::query()->count())->toBe(2);
});

it('updates an existing food when its source identity is imported again', function () {
    $initialCsvPath = tempnam(sys_get_temp_dir(), 'food-import-feature-');
    $updatedCsvPath = tempnam(sys_get_temp_dir(), 'food-import-feature-');

    if ($initialCsvPath === false || $updatedCsvPath === false) {
        throw new RuntimeException('Could not create CSV fixture.');
    }

    File::put($initialCsvPath, <<<CSV
        source_code,name_pt,name_en,calories_per_100g,protein_per_100g,carbs_per_100g,fat_per_100g
        001,Leite desnatado,,34,3.37,4.96,0.08
        CSV);
    File::put($updatedCsvPath, <<<CSV
        source_code,name_pt,name_en,calories_per_100g,protein_per_100g,carbs_per_100g,fat_per_100g
        001,Leite desnatado atualizado,,40,3.50,5,0.10
        CSV);

    $service = new FoodImportService();

    $service->import(
        path: $initialCsvPath,
        dataSource: 'taco',
        sourceVersion: '4',
    );

    $service->import(
        path: $updatedCsvPath,
        dataSource: 'taco',
        sourceVersion: '4',
    );

    $food = Food::query()
        ->where('data_source', 'taco')
        ->where('source_code', '001')
        ->where('source_version', '4')
        ->firstOrFail();

    $updatedFood = [
        'name_pt' => $food->name_pt,
        'calories_per_100g' => (float) $food->calories_per_100g,
        'protein_per_100g' => (float) $food->protein_per_100g,
    ];

    expect(Food::query()->count())->toBe(1);

    expect($updatedFood)->toBe([
        'name_pt' => 'Leite desnatado atualizado',
        'calories_per_100g' => 40.0,
        'protein_per_100g' => 3.5,
    ]);
});

it('does not persist invalid food rows', function () {
    $csvPath = tempnam(sys_get_temp_dir(), 'food-import-feature-');

    if ($csvPath === false) {
        throw new RuntimeException('Could not create CSV fixture.');
    }

    File::put($csvPath, <<<CSV
        source_code,name_pt,name_en,calories_per_100g,protein_per_100g,carbs_per_100g,fat_per_100g
        001,Leite desnatado,,34,3.37,4.96,0.08
        002,Nutriente invalido,,invalid,1,2,3
        CSV);

    $service = new FoodImportService();

    $result = $service->import(
        path: $csvPath,
        dataSource: 'taco',
        sourceVersion: '4',
    );

    expect(Food::query()->count())->toBe(1);

    expect($result)->toBe([
        'valid_count' => 1,
        'invalid_rows' => [
            [
                'line' => 3,
                'errors' => ['calories_per_100g'],
            ],
        ],
    ]);
});
