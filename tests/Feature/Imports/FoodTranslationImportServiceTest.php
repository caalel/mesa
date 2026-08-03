<?php

use App\Models\Food;
use App\Services\FoodTranslationImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

afterEach(function () {
    foreach (glob(sys_get_temp_dir().DIRECTORY_SEPARATOR.'food-translation-import-*') ?: [] as $path) {
        @unlink($path);
    }
});

function foodTranslationCsv(string $contents): string
{
    $path = tempnam(sys_get_temp_dir(), 'food-translation-import-');

    if ($path === false) {
        throw new RuntimeException('Could not create CSV fixture.');
    }

    File::put($path, $contents);

    return $path;
}

function foodTranslationSnapshot(Food $food): array
{
    return [
        'data_source' => $food->data_source,
        'source_code' => $food->source_code,
        'source_version' => $food->source_version,
        'name_pt' => $food->name_pt,
        'calories_per_100g' => (float) $food->calories_per_100g,
        'protein_per_100g' => (float) $food->protein_per_100g,
        'carbs_per_100g' => (float) $food->carbs_per_100g,
        'fat_per_100g' => (float) $food->fat_per_100g,
    ];
}

/*
|--------------------------------------------------------------------------
| Tests
|--------------------------------------------------------------------------
*/

it('updates English names for a valid translation CSV', function () {
    $firstFood = Food::factory()->create([
        'data_source' => 'taco',
        'source_code' => '001',
        'source_version' => '4',
        'name_en' => 'Existing first translation',
    ]);
    $secondFood = Food::factory()->create([
        'data_source' => 'taco',
        'source_code' => '002',
        'source_version' => '4',
        'name_en' => 'Existing second translation',
    ]);
    $csvPath = foodTranslationCsv(<<<CSV
        source_code,name_en
        001,Skim milk
        002,Whole milk
        CSV);

    $service = new FoodTranslationImportService();

    $service->import(
        path: $csvPath,
        dataSource: 'taco',
        sourceVersion: '4',
    );

    expect($firstFood->refresh()->name_en)->toBe('Skim milk');
    expect($secondFood->refresh()->name_en)->toBe('Whole milk');
});

it('preserves every non-translation field when importing English names', function () {
    $food = Food::factory()->create([
        'data_source' => 'taco',
        'source_code' => '003',
        'source_version' => '4',
        'name_pt' => 'Leite, de vaca, integral',
        'name_en' => 'Existing whole milk translation',
        'calories_per_100g' => 61,
        'protein_per_100g' => 3.15,
        'carbs_per_100g' => 4.8,
        'fat_per_100g' => 3.25,
    ]);
    $before = foodTranslationSnapshot($food);
    $csvPath = foodTranslationCsv(<<<CSV
        source_code,name_en
        003,Whole cow's milk
        CSV);

    (new FoodTranslationImportService())->import(
        path: $csvPath,
        dataSource: 'taco',
        sourceVersion: '4',
    );

    $food->refresh();

    expect($food->name_en)->toBe("Whole cow's milk");
    expect(foodTranslationSnapshot($food))->toBe($before);
});

it('does not update any food when a translation is empty', function () {
    $firstFood = Food::factory()->create([
        'data_source' => 'taco',
        'source_code' => '004',
        'source_version' => '4',
        'name_en' => 'Existing first translation',
    ]);
    $secondFood = Food::factory()->create([
        'data_source' => 'taco',
        'source_code' => '005',
        'source_version' => '4',
        'name_en' => 'Existing second translation',
    ]);
    $csvPath = foodTranslationCsv(<<<CSV
        source_code,name_en
        004,Updated first translation
        005,   
        CSV);

    expect(fn () => (new FoodTranslationImportService())->import(
        path: $csvPath,
        dataSource: 'taco',
        sourceVersion: '4',
    ))->toThrow(InvalidArgumentException::class);

    expect($firstFood->refresh()->name_en)->toBe('Existing first translation');
    expect($secondFood->refresh()->name_en)->toBe('Existing second translation');
});

it('does not update any food when a source code is unknown for the source version', function () {
    $food = Food::factory()->create([
        'data_source' => 'taco',
        'source_code' => '006',
        'source_version' => '4',
        'name_en' => 'Existing translation',
    ]);
    Food::factory()->create([
        'data_source' => 'usda',
        'source_code' => '007',
        'source_version' => '4',
    ]);
    $csvPath = foodTranslationCsv(<<<CSV
        source_code,name_en
        006,Updated translation
        007,Unknown in TACO
        CSV);

    expect(fn () => (new FoodTranslationImportService())->import(
        path: $csvPath,
        dataSource: 'taco',
        sourceVersion: '4',
    ))->toThrow(InvalidArgumentException::class);

    expect($food->refresh()->name_en)->toBe('Existing translation');
});

it('does not update any food when the translation CSV contains a duplicate source code', function () {
    $firstFood = Food::factory()->create([
        'data_source' => 'taco',
        'source_code' => '008',
        'source_version' => '4',
        'name_en' => 'Existing first translation',
    ]);
    $secondFood = Food::factory()->create([
        'data_source' => 'taco',
        'source_code' => '009',
        'source_version' => '4',
        'name_en' => 'Existing second translation',
    ]);
    $csvPath = foodTranslationCsv(<<<CSV
        source_code,name_en
        008,First translation
        008,Duplicate translation
        009,Second translation
        CSV);

    expect(fn () => (new FoodTranslationImportService())->import(
        path: $csvPath,
        dataSource: 'taco',
        sourceVersion: '4',
    ))->toThrow(InvalidArgumentException::class);

    expect($firstFood->refresh()->name_en)->toBe('Existing first translation');
    expect($secondFood->refresh()->name_en)->toBe('Existing second translation');
});

it('does not update any food when a food has no translation row in the CSV', function () {
    $firstFood = Food::factory()->create([
        'data_source' => 'taco',
        'source_code' => '010',
        'source_version' => '4',
        'name_en' => 'Existing first translation',
    ]);
    $secondFood = Food::factory()->create([
        'data_source' => 'taco',
        'source_code' => '011',
        'source_version' => '4',
        'name_en' => 'Existing second translation',
    ]);
    $csvPath = foodTranslationCsv(<<<CSV
        source_code,name_en
        010,First translation
        CSV);

    expect(fn () => (new FoodTranslationImportService())->import(
        path: $csvPath,
        dataSource: 'taco',
        sourceVersion: '4',
    ))->toThrow(InvalidArgumentException::class);

    expect($firstFood->refresh()->name_en)->toBe('Existing first translation');
    expect($secondFood->refresh()->name_en)->toBe('Existing second translation');
});

it('does not update any food when the translation CSV header is invalid', function () {
    $food = Food::factory()->create([
        'data_source' => 'taco',
        'source_code' => '012',
        'source_version' => '4',
        'name_en' => 'Existing translation',
    ]);
    $csvPath = foodTranslationCsv(<<<CSV
        name_en,source_code
        Updated translation,012
        CSV);

    expect(fn () => (new FoodTranslationImportService())->import(
        path: $csvPath,
        dataSource: 'taco',
        sourceVersion: '4',
    ))->toThrow(InvalidArgumentException::class);

    expect($food->refresh()->name_en)->toBe('Existing translation');
});

it('does not update any food when the translation CSV does not exist', function () {
    $food = Food::factory()->create([
        'data_source' => 'taco',
        'source_code' => '013',
        'source_version' => '4',
        'name_en' => 'Existing translation',
    ]);
    $csvPath = tempnam(sys_get_temp_dir(), 'missing-food-translation-import-');

    if ($csvPath === false) {
        throw new RuntimeException('Could not create missing CSV path.');
    }

    unlink($csvPath);

    expect(fn () => (new FoodTranslationImportService())->import(
        path: $csvPath,
        dataSource: 'taco',
        sourceVersion: '4',
    ))->toThrow(InvalidArgumentException::class);

    expect($food->refresh()->name_en)->toBe('Existing translation');
});
