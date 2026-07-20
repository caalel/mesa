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
