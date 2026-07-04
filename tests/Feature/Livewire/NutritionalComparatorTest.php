<?php

use App\Livewire\NutritionalComparator;
use App\Models\Food;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows foods matching the Food A portuguese name search when the user types at least two characters', function () {
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

    Livewire::test(NutritionalComparator::class)
        ->set('foodASearch', 'Ba')
        ->assertSee('Banana')
        ->assertSee('Banana Prata')
        ->assertDontSee('Maçã');
});

it('does not show Food A results when the user types less than two characters', function () {
    Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 89,
        'protein_per_100g' => 1.1,
        'carbs_per_100g' => 22.8,
        'fat_per_100g' => 0.3,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->set('foodASearch', 'B')
        ->assertDontSee('Banana');
});

it('shows the selected Food A and hides the search state when the user selects a result', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
    ]);

    Food::factory()->create([
        'name_pt' => 'Banana Prata',
    ]);

    Livewire::test(NutritionalComparator::class)
        ->set('foodASearch', 'Ba')
        ->call('selectFoodA', $banana->id)
        ->assertSee('Banana')
        ->assertDontSee('Banana Prata')
        ->assertDontSeeHtml('id="food-a-search"')
        ->assertSee(__('ui.compare.change_food'));
});

it('returns Food A to the search state when the user changes the selected food', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
    ]);

    Food::factory()->create([
        'name_pt' => 'Melancia',
    ]);

    Livewire::test(NutritionalComparator::class)
        ->set('foodASearch', 'Ba')
        ->call('selectFoodA', $banana->id)
        ->call('changeFoodA')
        ->assertDontSee('Banana')
        ->assertSeeHtml('id="food-a-search"')
        ->set('foodASearch', 'Me')
        ->assertSee('Melancia');
});

it('shows the Food A weight and calories summary when the selected food has a valid weight', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 128,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $banana->id)
        ->set('foodAWeight', 50)
        ->assertSeeHtml('data-testid="food-a-summary"')
        ->assertSee('50')
        ->assertSee('64');
});

it('preserves decimal Food A weight when calculating the calories summary', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 128,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $banana->id)
        ->set('foodAWeight', '50.5')
        ->assertSeeHtml('data-testid="food-a-summary"')
        ->assertSee('50.5')
        ->assertSee('64.64');
});

it('does not show the Food A calories summary when no Food A is selected', function () {
    Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 128,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->set('foodAWeight', 50)
        ->assertDontSeeHtml('data-testid="food-a-summary"');
});

it('does not show the Food A calories summary when the weight is empty', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 128,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $banana->id)
        ->set('foodAWeight', '')
        ->assertDontSeeHtml('data-testid="food-a-summary"');
});

it('does not show the Food A calories summary when the weight is zero', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 128,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $banana->id)
        ->set('foodAWeight', 0)
        ->assertDontSeeHtml('data-testid="food-a-summary"');
});

it('does not show the Food A calories summary when the weight is negative', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 128,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $banana->id)
        ->set('foodAWeight', -50)
        ->assertDontSeeHtml('data-testid="food-a-summary"');
});

it('does not show the Food A calories summary when the weight is not numeric', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 128,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $banana->id)
        ->set('foodAWeight', 'invalid')
        ->assertDontSeeHtml('data-testid="food-a-summary"');
});
