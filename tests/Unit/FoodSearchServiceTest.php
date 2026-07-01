<?php

use App\Models\Food;
use App\Services\FoodSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('returns only foods matching the searched Portuguese name', function () {
    // Arrange
    Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 89,
        'protein_per_100g' => 1.1,
        'carbs_per_100g' => 22.8,
        'fat_per_100g' => 0.3,
    ]);

    Food::factory()->create([
        'name_pt' => 'Maçã',
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
    expect($foods->first()->name_pt)->toBe('Banana');
});

it('returns only foods partially matching the searched Portuguese name', function () {
    // Arrange
    Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 89,
        'protein_per_100g' => 1.1,
        'carbs_per_100g' => 22.8,
        'fat_per_100g' => 0.3,
    ]);

    Food::factory()->create([
        'name_pt' => 'Maçã',
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
    expect($foods->first()->name_pt)->toBe('Banana');
});

it('returns foods matching the searched Portuguese name regardless of case', function () {
    // Arrange
    Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 89,
        'protein_per_100g' => 1.1,
        'carbs_per_100g' => 22.8,
        'fat_per_100g' => 0.3,
    ]);

    $service = new FoodSearchService();

    // Act
    $lowercaseFoods = $service->search('banana');
    $uppercaseFoods = $service->search('BANANA');

    // Assert
    expect($lowercaseFoods)->toHaveCount(1);
    expect($lowercaseFoods->first()->name_pt)->toBe('Banana');
    expect($uppercaseFoods)->toHaveCount(1);
    expect($uppercaseFoods->first()->name_pt)->toBe('Banana');
});

it('returns an empty collection when no food matches the searched Portuguese name', function () {
    // Arrange
    Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 89,
        'protein_per_100g' => 1.1,
        'carbs_per_100g' => 22.8,
        'fat_per_100g' => 0.3,
    ]);

    Food::factory()->create([
        'name_pt' => 'Maçã',
        'calories_per_100g' => 52,
        'protein_per_100g' => 0.3,
        'carbs_per_100g' => 13.8,
        'fat_per_100g' => 0.2,
    ]);

    $service = new FoodSearchService();

    // Act
    $foods = $service->search('Laranja');

    // Assert
    expect($foods)->toBeEmpty();
});

it('returns multiple foods when more than one food matches the searched Portuguese name', function () {
    // Arrange
    Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 89,
        'protein_per_100g' => 1.1,
        'carbs_per_100g' => 22.8,
        'fat_per_100g' => 0.3,
    ]);

    Food::factory()->create([
        'name_pt' => 'Banana Prata',
        'calories_per_100g' => 98,
        'protein_per_100g' => 1.3,
        'carbs_per_100g' => 26,
        'fat_per_100g' => 0.1,
    ]);

    Food::factory()->create([
        'name_pt' => 'Maçã',
        'calories_per_100g' => 52,
        'protein_per_100g' => 0.3,
        'carbs_per_100g' => 13.8,
        'fat_per_100g' => 0.2,
    ]);

    $service = new FoodSearchService();

    // Act
    $foods = $service->search('Ban');

    // Assert
    expect($foods)->toHaveCount(2);
    expect($foods->pluck('name_pt')->all())->toContain('Banana');
    expect($foods->pluck('name_pt')->all())->toContain('Banana Prata');
});
