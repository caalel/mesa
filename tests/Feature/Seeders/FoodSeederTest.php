<?php

use App\Models\Food;
use Database\Seeders\FoodSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('imports every food from the official CSV', function () {
    $csvPath = database_path('data/foods/taco-v4.csv');
    $expectedCount = count(file($csvPath, FILE_IGNORE_NEW_LINES)) - 1;

    $this->seed(FoodSeeder::class);

    expect(Food::query()->count())->toBe($expectedCount);
    expect(Food::query()->where('data_source', 'taco')->count())->toBe($expectedCount);
    expect(Food::query()->where('source_version', '4')->count())->toBe($expectedCount);
});

it('is idempotent when importing the official CSV again', function () {
    $csvPath = database_path('data/foods/taco-v4.csv');
    $expectedCount = count(file($csvPath, FILE_IGNORE_NEW_LINES)) - 1;

    $this->seed(FoodSeeder::class);
    $this->seed(FoodSeeder::class);

    expect(Food::query()->count())->toBe($expectedCount);
});

it('imports foods through the default database seeder', function () {
    $csvPath = database_path('data/foods/taco-v4.csv');
    $expectedCount = count(file($csvPath, FILE_IGNORE_NEW_LINES)) - 1;

    $this->seed();

    expect(Food::query()->count())->toBe($expectedCount);
});

it('imports all 592 foods from the canonical CSV', function () {
    $this->seed(FoodSeeder::class);

    expect(Food::query()->count())->toBe(592);
});

it('imports English names for every food', function () {
    $this->seed(FoodSeeder::class);

    expect(Food::query()->whereNull('name_en')->count())->toBe(0)
        ->and(Food::query()->where('name_en', '')->count())->toBe(0);
});

it('imports canonical Portuguese and English names for selected foods', function () {
    $this->seed(FoodSeeder::class);

    expect(Food::query()->where([
        'data_source' => 'taco',
        'source_version' => '4',
        'source_code' => '443',
    ])->sole()->only([
        'data_source',
        'source_version',
        'source_code',
        'name_pt',
        'name_en',
    ]))->toBe([
        'data_source' => 'taco',
        'source_version' => '4',
        'source_code' => '443',
        'name_pt' => 'Salame',
        'name_en' => 'Salami',
    ]);

    expect(Food::query()->where([
        'data_source' => 'taco',
        'source_version' => '4',
        'source_code' => '1',
    ])->sole()->only([
        'data_source',
        'source_version',
        'source_code',
        'name_pt',
        'name_en',
    ]))->toBe([
        'data_source' => 'taco',
        'source_version' => '4',
        'source_code' => '1',
        'name_pt' => 'Arroz, integral, cozido',
        'name_en' => 'Cooked brown rice',
    ]);
});
