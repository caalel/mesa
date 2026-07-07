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

it('shows Food A results when the search has leading and trailing spaces', function () {
    Food::factory()->create([
        'name_pt' => 'Banana',
    ]);

    Livewire::test(NutritionalComparator::class)
        ->set('foodASearch', '  Banana  ')
        ->assertSee('Banana');
});

it('does not show Food A results when the search contains only spaces', function () {
    Food::factory()->create([
        'name_pt' => 'Banana',
    ]);

    Livewire::test(NutritionalComparator::class)
        ->set('foodASearch', '   ')
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

it('formats decimal Food A weight and calories summary using pt-BR numbers', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 128,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $banana->id)
        ->set('foodAWeight', '50.5')
        ->assertSeeHtml('data-testid="food-a-summary"')
        ->assertSee('50,5 g')
        ->assertSee('64,64 kcal');
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

it('shows foods matching the Food B portuguese name search when the user types at least two characters', function () {
    Food::factory()->create([
        'name_pt' => 'Maçã',
    ]);

    Food::factory()->create([
        'name_pt' => 'Mamão',
    ]);

    Food::factory()->create([
        'name_pt' => 'Banana',
    ]);

    Livewire::test(NutritionalComparator::class)
        ->set('foodBSearch', 'Ma')
        ->assertSee('Maçã')
        ->assertSee('Mamão')
        ->assertDontSee('Banana');
});

it('does not show Food B results when the user types less than two characters', function () {
    Food::factory()->create([
        'name_pt' => 'Maçã',
    ]);

    Livewire::test(NutritionalComparator::class)
        ->set('foodBSearch', 'M')
        ->assertDontSee('Maçã');
});

it('shows Food B results when the search has leading and trailing spaces', function () {
    Food::factory()->create([
        'name_pt' => 'Maçã',
    ]);

    Livewire::test(NutritionalComparator::class)
        ->set('foodBSearch', '  Maçã  ')
        ->assertSee('Maçã');
});

it('does not show Food B results when the search contains only spaces', function () {
    Food::factory()->create([
        'name_pt' => 'Maçã',
    ]);

    Livewire::test(NutritionalComparator::class)
        ->set('foodBSearch', '   ')
        ->assertDontSee('Maçã');
});

it('shows the selected Food B and hides the search state when the user selects a result', function () {
    $maca = Food::factory()->create([
        'name_pt' => 'Maçã',
    ]);

    Food::factory()->create([
        'name_pt' => 'Mamão',
    ]);

    Livewire::test(NutritionalComparator::class)
        ->set('foodBSearch', 'Ma')
        ->call('selectFoodB', $maca->id)
        ->assertSee('Maçã')
        ->assertDontSee('Mamão')
        ->assertDontSeeHtml('id="food-b-search"')
        ->assertSee(__('ui.compare.change_food'));
});

it('returns Food B to the search state when the user changes the selected food', function () {
    $maca = Food::factory()->create([
        'name_pt' => 'Maçã',
    ]);

    Food::factory()->create([
        'name_pt' => 'Laranja',
    ]);

    Livewire::test(NutritionalComparator::class)
        ->set('foodBSearch', 'Ma')
        ->call('selectFoodB', $maca->id)
        ->call('changeFoodB')
        ->assertDontSee('Maçã')
        ->assertSeeHtml('id="food-b-search"')
        ->set('foodBSearch', 'La')
        ->assertSee('Laranja');
});

it('keeps the compare button disabled when Food A is not selected', function () {
    $maca = Food::factory()->create([
        'name_pt' => 'Maçã',
        'calories_per_100g' => 52,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->set('foodAWeight', 100)
        ->call('selectFoodB', $maca->id)
        ->assertSeeHtml('data-testid="compare-button-disabled"');
});

it('keeps the compare button disabled when Food A weight is missing', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 89,
    ]);
    $maca = Food::factory()->create([
        'name_pt' => 'Maçã',
        'calories_per_100g' => 52,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $banana->id)
        ->call('selectFoodB', $maca->id)
        ->assertSeeHtml('data-testid="compare-button-disabled"');
});

it('keeps the compare button disabled when Food A weight is zero', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 89,
    ]);
    $maca = Food::factory()->create([
        'name_pt' => 'Maçã',
        'calories_per_100g' => 52,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $banana->id)
        ->set('foodAWeight', 0)
        ->call('selectFoodB', $maca->id)
        ->assertSeeHtml('data-testid="compare-button-disabled"');
});

it('keeps the compare button disabled when Food A weight is negative', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 89,
    ]);
    $maca = Food::factory()->create([
        'name_pt' => 'Maçã',
        'calories_per_100g' => 52,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $banana->id)
        ->set('foodAWeight', -100)
        ->call('selectFoodB', $maca->id)
        ->assertSeeHtml('data-testid="compare-button-disabled"');
});

it('keeps the compare button disabled when Food A weight is not numeric', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 89,
    ]);
    $maca = Food::factory()->create([
        'name_pt' => 'Maçã',
        'calories_per_100g' => 52,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $banana->id)
        ->set('foodAWeight', 'invalid')
        ->call('selectFoodB', $maca->id)
        ->assertSeeHtml('data-testid="compare-button-disabled"');
});

it('keeps the compare button disabled when Food B is not selected', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 89,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $banana->id)
        ->set('foodAWeight', 100)
        ->assertSeeHtml('data-testid="compare-button-disabled"');
});

it('enables the compare button when Food A, valid weight, and Food B are selected', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 89,
    ]);
    $maca = Food::factory()->create([
        'name_pt' => 'Maçã',
        'calories_per_100g' => 52,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $banana->id)
        ->set('foodAWeight', 100)
        ->call('selectFoodB', $maca->id)
        ->assertSeeHtml('data-testid="compare-button-enabled"');
});

it('does not show a comparison result when compare is called with incomplete state', function () {
    Livewire::test(NutritionalComparator::class)
        ->call('compare')
        ->assertDontSeeHtml('data-testid="comparison-result"');
});

it('shows the comparison result when compare is called with valid state', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 89,
    ]);
    $maca = Food::factory()->create([
        'name_pt' => 'Maçã',
        'calories_per_100g' => 52,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $banana->id)
        ->set('foodAWeight', 100)
        ->call('selectFoodB', $maca->id)
        ->call('compare')
        ->assertSeeHtml('data-testid="comparison-result"')
        ->assertSee('100 g')
        ->assertSee('Banana')
        ->assertSee('171,15 g')
        ->assertSee('Maçã')
        ->assertSee(__('ui.compare.calorie_equivalence', [
            'foodAWeight' => '100',
            'foodAName' => 'Banana',
            'foodBWeight' => '171,15',
            'foodBName' => 'Maçã',
        ]))
        ->assertSee(__('ui.compare.calorie_equivalence_description', [
            'foodAWeight' => '100',
            'foodAName' => 'Banana',
            'foodBWeight' => '171,15',
            'foodBName' => 'Maçã',
        ]));
});

it('shows the comparison result preserving decimal Food A weight', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 89,
    ]);
    $maca = Food::factory()->create([
        'name_pt' => 'Maçã',
        'calories_per_100g' => 52,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $banana->id)
        ->set('foodAWeight', '50.5')
        ->call('selectFoodB', $maca->id)
        ->call('compare')
        ->assertSeeHtml('data-testid="comparison-result"')
        ->assertSee('50,5 g')
        ->assertSee('86,43 g');
});

it('clears the comparison result immediately when Food A weight changes', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 89,
    ]);
    $maca = Food::factory()->create([
        'name_pt' => 'Maçã',
        'calories_per_100g' => 52,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $banana->id)
        ->set('foodAWeight', 100)
        ->call('selectFoodB', $maca->id)
        ->call('compare')
        ->assertSeeHtml('data-testid="comparison-result"')
        ->set('foodAWeight', 120)
        ->assertDontSeeHtml('data-testid="comparison-result"');
});
