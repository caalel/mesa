<?php

use App\Models\Food;
use App\Services\FoodSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('searches Portuguese names when the locale is pt_BR', function () {
    Food::factory()->create([
        'name_pt' => 'Arroz integral',
        'name_en' => 'Brown rice',
    ]);
    Food::factory()->create([
        'name_pt' => 'Feijao preto',
        'name_en' => 'Black beans',
    ]);

    App::setLocale('pt_BR');
    $service = new FoodSearchService();

    expect($service->search('Arroz')->pluck('name_pt')->all())->toBe(['Arroz integral'])
        ->and($service->search('Brown'))->toBeEmpty();
});

it('searches English names when the locale is en', function () {
    Food::factory()->create([
        'name_pt' => 'Arroz integral',
        'name_en' => 'Brown rice',
    ]);
    Food::factory()->create([
        'name_pt' => 'Feijao preto',
        'name_en' => 'Black beans',
    ]);

    App::setLocale('en');
    $service = new FoodSearchService();

    expect($service->search('Brown')->pluck('name_en')->all())->toBe(['Brown rice'])
        ->and($service->search('Feijao'))->toBeEmpty();
});

it('orders English search results by relevance and English name when the locale is en', function () {
    Food::factory()->create([
        'name_pt' => 'Zeta arroz',
        'name_en' => 'Rice alpha',
    ]);
    Food::factory()->create([
        'name_pt' => 'Alpha arroz',
        'name_en' => 'Brown rice',
    ]);
    Food::factory()->create([
        'name_pt' => 'Beta arroz',
        'name_en' => 'Rice beta',
    ]);

    App::setLocale('en');

    expect((new FoodSearchService())->search('rice')->pluck('name_en')->all())->toBe([
        'Rice alpha',
        'Rice beta',
        'Brown rice',
    ]);
});

it('returns only foods matching the searched Portuguese name', function () {
    // Arrange
    Food::factory()->create([
        'name_pt' => 'Banana',
    ]);

    Food::factory()->create([
        'name_pt' => 'Maçã',
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
    ]);

    Food::factory()->create([
        'name_pt' => 'Maçã',
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
    ]);

    Food::factory()->create([
        'name_pt' => 'Maçã',
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
    ]);

    Food::factory()->create([
        'name_pt' => 'Banana Prata',
    ]);

    Food::factory()->create([
        'name_pt' => 'Maçã',
    ]);

    $service = new FoodSearchService();

    // Act
    $foods = $service->search('Ban');

    // Assert
    expect($foods)->toHaveCount(2);
    expect($foods->pluck('name_pt')->all())->toContain('Banana');
    expect($foods->pluck('name_pt')->all())->toContain('Banana Prata');
});

it('returns matching foods ordered by Portuguese name', function () {
    // Arrange
    Food::factory()->create([
        'name_pt' => 'Banana Prata',
    ]);

    Food::factory()->create([
        'name_pt' => 'Banana',
    ]);

    Food::factory()->create([
        'name_pt' => 'Banana Nanica',
    ]);

    // Act
    $foods = app(FoodSearchService::class)->search('Banana');

    // Assert
    expect($foods->pluck('name_pt')->all())->toBe([
        'Banana',
        'Banana Nanica',
        'Banana Prata',
    ]);
});

it('returns at most eight matching foods', function () {
    // Arrange
    foreach (range(1, 10) as $index) {
        Food::factory()->create([
            'name_pt' => 'Banana '.$index,
        ]);
    }

    // Act
    $foods = app(FoodSearchService::class)->search('Banana');

    // Assert
    expect($foods)->toHaveCount(8);
});

it('returns foods containing every search term even when they are separated', function () {
    Food::factory()->create([
        'name_pt' => 'Leite, de vaca, integral',
    ]);

    Food::factory()->create([
        'name_pt' => 'Leite, de vaca, desnatado',
    ]);

    $foods = app(FoodSearchService::class)->search('leite integral');
    $names = $foods->pluck('name_pt')->all();

    expect($names)->toContain('Leite, de vaca, integral');
    expect($names)->not->toContain('Leite, de vaca, desnatado');
});

it('orders matching foods by relevance before alphabetical order', function () {
    Food::factory()->create([
        'name_pt' => 'Canjica, com leite integral',
    ]);

    Food::factory()->create([
        'name_pt' => 'Leite integral',
    ]);

    Food::factory()->create([
        'name_pt' => 'Leite, de vaca, integral',
    ]);

    Food::factory()->create([
        'name_pt' => 'Leite, de vaca, integral, pó',
    ]);

    $foods = app(FoodSearchService::class)->search('leite integral');

    expect($foods->pluck('name_pt')->all())->toBe([
        'Leite integral',
        'Leite, de vaca, integral',
        'Leite, de vaca, integral, pó',
        'Canjica, com leite integral',
    ]);
});
