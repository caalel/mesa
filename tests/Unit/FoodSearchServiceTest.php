<?php

use App\Models\Food;
use App\Services\FoodSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('returns only foods matching the searched name', function () {
    // Arrange
    Food::factory()->create([
        'name' => 'Banana',
        'calories_per_100g' => 89,
        'protein_per_100g' => 1.1,
        'carbs_per_100g' => 22.8,
        'fat_per_100g' => 0.3,
    ]);

    Food::factory()->create([
        'name' => 'Maçã',
        'calories_per_100g' => 52,
        'protein_per_100g' => 0.3,
        'carbs_per_100g' => 13.8,
        'fat_per_100g' => 0.2,
    ]);

    $service = new FoodSearchService();

    // Act
    $foods = $service->search('Banana');

    // Assert
    expect($foods)->toHaveCount(1);
    expect($foods->first()->name)->toBe('Banana');
});

it('returns only foods partially matching the searched name', function () {
    // Arrange
    Food::factory()->create([
        'name' => 'Banana',
        'calories_per_100g' => 89,
        'protein_per_100g' => 1.1,
        'carbs_per_100g' => 22.8,
        'fat_per_100g' => 0.3,
    ]);

    Food::factory()->create([
        'name' => 'Maçã',
        'calories_per_100g' => 52,
        'protein_per_100g' => 0.3,
        'carbs_per_100g' => 13.8,
        'fat_per_100g' => 0.2,
    ]);

    $service = new FoodSearchService();

    // Act
    $foods = $service->search('Ban');

    // Assert
    expect($foods)->toHaveCount(1);
    expect($foods->first()->name)->toBe('Banana');
});
