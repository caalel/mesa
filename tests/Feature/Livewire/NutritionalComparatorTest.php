<?php

use App\Livewire\NutritionalComparator;
use App\Enums\ComparisonNutrient;
use App\Models\Food;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('starts with calories as the selected comparison nutrient', function () {
    Livewire::test(NutritionalComparator::class)
        ->assertSet('selectedNutrient', ComparisonNutrient::Calories);
});

it('shows foods matching the Food A portuguese name search when the user types at least one character', function () {
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
        ->set('foodASearch', 'B')
        ->assertSee('Banana')
        ->assertSee('Banana Prata')
        ->assertDontSee('Maçã');
});

it('shows localized English names in Food A search results', function () {
    App::setLocale('en');

    Food::factory()->create([
        'name_pt' => 'Arroz integral',
        'name_en' => 'Brown rice',
    ]);

    Livewire::test(NutritionalComparator::class)
        ->set('foodASearch', 'Brown')
        ->assertSee('Brown rice')
        ->assertDontSee('Arroz integral');
});

it('shows Food A results when the user types one character', function () {
    Food::factory()->create([
        'name_pt' => 'Zucchini',
        'calories_per_100g' => 89,
        'protein_per_100g' => 1.1,
        'carbs_per_100g' => 22.8,
        'fat_per_100g' => 0.3,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->set('foodASearch', 'Z')
        ->assertSee('Zucchini');
});

it('shows localized Portuguese placeholders for the food search fields', function () {
    App::setLocale('pt_BR');

    $component = Livewire::test(NutritionalComparator::class)
        ->assertSeeHtml('id="food-a-search"')
        ->assertSeeHtml('id="food-b-search"')
        ->assertDontSee('Digite pelo menos 2 caracteres para buscar.');

    expect(substr_count($component->html(), 'placeholder="Digite o nome do alimento"'))->toBe(2);
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

it('shows an empty state when a one-character Food A search has no results', function () {
    Food::factory()->create([
        'name_pt' => 'Banana',
    ]);

    Livewire::test(NutritionalComparator::class)
        ->assertDontSee(__('ui.compare.no_foods_found'))
        ->set('foodASearch', 'X')
        ->assertSee(__('ui.compare.no_foods_found'));
});

it('shows the selected Food A and hides the search state when the user selects a result', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
    ]);

    Food::factory()->create([
        'name_pt' => 'Banana Prata',
    ]);

    Livewire::test(NutritionalComparator::class)
        ->set('foodASearch', 'B')
        ->call('selectFoodA', $banana->id)
        ->assertSee('Banana')
        ->assertDontSee('Banana Prata')
        ->assertDontSeeHtml('id="food-a-search"')
        ->assertSee(__('ui.compare.change_food'));
});

it('progressively reveals the debounced Food A weight input and summary after selecting a food', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 128,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->assertDontSeeHtml('id="food-a-quantity"')
        ->assertDontSeeHtml('data-testid="food-a-summary"')
        ->call('selectFoodA', $banana->id)
        ->set('foodAWeight', 50)
        ->assertSeeHtml('id="food-a-quantity"')
        ->assertSeeHtml('wire:model.live.debounce.300ms="foodAWeight"')
        ->assertSeeHtml('data-testid="food-a-summary"');
});

it('shows the localized English name for selected Food A', function () {
    App::setLocale('en');

    $brownRice = Food::factory()->create([
        'name_pt' => 'Arroz integral',
        'name_en' => 'Brown rice',
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $brownRice->id)
        ->assertSee('Brown rice');
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

it('shows the localized English name in the Food A summary', function () {
    App::setLocale('en');

    $brownRice = Food::factory()->create([
        'name_pt' => 'Arroz integral',
        'name_en' => 'Brown rice',
        'calories_per_100g' => 128,
    ]);

    $component = Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $brownRice->id)
        ->set('foodAWeight', 50);

    expect(substr_count($component->html(), 'Brown rice'))->toBe(2);
});

it('shows a less than value for positive Food A summary calories lower than one hundredth', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana Prata',
        'calories_per_100g' => 0.01,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $banana->id)
        ->set('foodAWeight', 1)
        ->assertSeeHtml('data-testid="food-a-summary"')
        ->assertSee('1 g')
        ->assertSee('< 0,01 kcal')
        ->assertDontSee('0 kcal');
});

it('does not show the Food A calories summary when Food A has unavailable calorie data', function () {
    $agua = Food::factory()->create([
        'name_pt' => 'Água',
        'calories_per_100g' => 0,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $agua->id)
        ->set('foodAWeight', 100)
        ->assertDontSeeHtml('data-testid="food-a-summary"');
});

it('disables the Food A quantity input when Food A has unavailable calorie data', function () {
    $agua = Food::factory()->create([
        'name_pt' => 'Água',
        'calories_per_100g' => 0,
    ]);

    $html = Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $agua->id)
        ->html();

    preg_match('/<input\b[^>]*id="food-a-quantity"[^>]*>/s', $html, $matches);

    expect($matches)->not->toBeEmpty();
    expect($matches[0])->toContain('disabled');
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

it('shows a friendly message when Food A weight is zero', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 89,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $banana->id)
        ->set('foodAWeight', 0)
        ->assertSee(__('ui.compare.quantity_must_be_positive'));
});

it('shows a friendly message when Food A weight is negative', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 89,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $banana->id)
        ->set('foodAWeight', -1)
        ->assertSee(__('ui.compare.quantity_must_be_positive'));
});

it('shows a friendly message when Food A weight is not numeric', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 89,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $banana->id)
        ->set('foodAWeight', 'invalid')
        ->assertSee(__('ui.compare.quantity_must_be_numeric'));
});

it('does not show a friendly quantity message when Food A weight is empty', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 89,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $banana->id)
        ->set('foodAWeight', '')
        ->assertDontSee(__('ui.compare.quantity_must_be_positive'))
        ->assertDontSee(__('ui.compare.quantity_must_be_numeric'));
});

it('shows a friendly message when Food A weight is greater than 10000 grams', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 128,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $banana->id)
        ->set('foodAWeight', 10001)
        ->assertSee(__('ui.compare.quantity_too_high', ['max' => '10.000']));
});

it('shows the translated quantity placeholder', function () {
    App::setLocale('pt_BR');

    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 89,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $banana->id)
        ->assertSeeHtml('placeholder="Informe a quantidade em gramas."');
});

it('shows foods matching the Food B portuguese name search when the user types at least one character', function () {
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
        ->set('foodBSearch', 'M')
        ->assertSee('Maçã')
        ->assertSee('Mamão')
        ->assertDontSee('Banana');
});

it('shows localized English names in Food B search results', function () {
    App::setLocale('en');

    Food::factory()->create([
        'name_pt' => 'Feijão preto',
        'name_en' => 'Black beans',
    ]);

    Livewire::test(NutritionalComparator::class)
        ->set('foodBSearch', 'Black')
        ->assertSee('Black beans')
        ->assertDontSee('Feijão preto');
});

it('shows localized English placeholders for the food search fields', function () {
    App::setLocale('en');

    $component = Livewire::test(NutritionalComparator::class)
        ->assertSeeHtml('id="food-a-search"')
        ->assertSeeHtml('id="food-b-search"')
        ->assertDontSee('Enter at least 2 characters to search.');

    expect(substr_count($component->html(), 'placeholder="Type the food name"'))->toBe(2);
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

it('shows an empty state when Food B search has no results', function () {
    Food::factory()->create([
        'name_pt' => 'Banana',
    ]);

    Livewire::test(NutritionalComparator::class)
        ->set('foodBSearch', 'Abacaxi')
        ->assertSee(__('ui.compare.no_foods_found'));
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

it('shows the localized English name for selected Food B', function () {
    App::setLocale('en');

    $blackBeans = Food::factory()->create([
        'name_pt' => 'Feijão preto',
        'name_en' => 'Black beans',
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodB', $blackBeans->id)
        ->assertSee('Black beans');
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


it('does not show a comparison result when Food A has unavailable calorie data', function () {
    $agua = Food::factory()->create([
        'name_pt' => 'Água',
        'calories_per_100g' => 0,
    ]);
    $maca = Food::factory()->create([
        'name_pt' => 'Maçã',
        'calories_per_100g' => 52,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $agua->id)
        ->set('foodAWeight', 100)
        ->call('selectFoodB', $maca->id)
        ->assertDontSeeHtml('data-testid="comparison-result"');
});

it('shows the comparison result automatically when the state is valid', function () {
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

it('calculates the comparison result with each selected nutrient', function (ComparisonNutrient $nutrient, string $expectedFoodBWeight) {
    $foodA = Food::factory()->create([
        'name_pt' => 'Alimento A',
        'calories_per_100g' => 100,
        'protein_per_100g' => 20,
        'carbs_per_100g' => 30,
        'fat_per_100g' => 40,
    ]);
    $foodB = Food::factory()->create([
        'name_pt' => 'Alimento B',
        'calories_per_100g' => 25,
        'protein_per_100g' => 8,
        'carbs_per_100g' => 60,
        'fat_per_100g' => 100,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $foodA->id)
        ->set('foodAWeight', 100)
        ->call('selectFoodB', $foodB->id)
        ->set('selectedNutrient', $nutrient->value)
        ->assertSeeHtml('data-testid="comparison-result"')
        ->assertSee($expectedFoodBWeight.' g de Alimento B');
})->with([
    [ComparisonNutrient::Calories, '400'],
    [ComparisonNutrient::Protein, '250'],
    [ComparisonNutrient::Carbohydrates, '50'],
    [ComparisonNutrient::Fat, '40'],
]);

it('recalculates with the selected nutrient without changing the selected foods or weight', function () {
    $foodA = Food::factory()->create([
        'name_pt' => 'Alimento A',
        'calories_per_100g' => 100,
        'protein_per_100g' => 20,
    ]);
    $foodB = Food::factory()->create([
        'name_pt' => 'Alimento B',
        'calories_per_100g' => 25,
        'protein_per_100g' => 80,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $foodA->id)
        ->set('foodAWeight', 100)
        ->call('selectFoodB', $foodB->id)
        ->assertSee('400 g de Alimento B')
        ->set('selectedNutrient', ComparisonNutrient::Protein->value)
        ->assertSet('foodAId', $foodA->id)
        ->assertSet('foodAWeight', '100')
        ->assertSet('foodBId', $foodB->id)
        ->assertSee('25 g de Alimento B');
});

it('does not calculate when the selected nutrient is not positive in either food', function (float $foodAProtein, float $foodBProtein) {
    $foodA = Food::factory()->create([
        'name_pt' => 'Alimento A',
        'protein_per_100g' => $foodAProtein,
    ]);
    $foodB = Food::factory()->create([
        'name_pt' => 'Alimento B',
        'protein_per_100g' => $foodBProtein,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $foodA->id)
        ->set('foodAWeight', 100)
        ->call('selectFoodB', $foodB->id)
        ->set('selectedNutrient', ComparisonNutrient::Protein->value)
        ->assertDontSeeHtml('data-testid="comparison-result"');
})->with([
    [0, 10],
    [-1, 10],
    [10, 0],
    [10, -1],
]);

it('shows localized English food names in the comparison result', function () {
    App::setLocale('en');

    $brownRice = Food::factory()->create([
        'name_pt' => 'Arroz integral',
        'name_en' => 'Brown rice',
        'calories_per_100g' => 111,
    ]);
    $blackBeans = Food::factory()->create([
        'name_pt' => 'Feijão preto',
        'name_en' => 'Black beans',
        'calories_per_100g' => 222,
    ]);

    $component = Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $brownRice->id)
        ->set('foodAWeight', 100)
        ->call('selectFoodB', $blackBeans->id);

    $component
        ->assertSee('Brown rice')
        ->assertSee('Black beans')
        ->assertDontSee('Arroz integral')
        ->assertDontSee('Feijão preto');
});

it('shows the comparison result using a less than phrase for positive equivalent weight lower than one hundredth', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana Prata',
        'calories_per_100g' => 0.01,
    ]);
    $maca = Food::factory()->create([
        'name_pt' => 'Maçã',
        'calories_per_100g' => 52,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $banana->id)
        ->set('foodAWeight', 1)
        ->call('selectFoodB', $maca->id)
        ->assertSeeHtml('data-testid="comparison-result"')
        ->assertSee(__('ui.compare.calorie_equivalence_less_than', [
            'foodAWeight' => '1',
            'foodAName' => 'Banana Prata',
            'foodBWeight' => '0,01',
            'foodBName' => 'Maçã',
        ]))
        ->assertDontSee(__('ui.compare.calorie_equivalence', [
            'foodAWeight' => '1',
            'foodAName' => 'Banana Prata',
            'foodBWeight' => '0',
            'foodBName' => 'Maçã',
        ]));
});

it('shows the comparison result description using a less than phrase for positive equivalent weight lower than one hundredth', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana Prata',
        'calories_per_100g' => 0.01,
    ]);
    $maca = Food::factory()->create([
        'name_pt' => 'Maçã',
        'calories_per_100g' => 52,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $banana->id)
        ->set('foodAWeight', 1)
        ->call('selectFoodB', $maca->id)
        ->assertSeeHtml('data-testid="comparison-result"')
        ->assertSee(__('ui.compare.calorie_equivalence_less_than_description', [
            'foodAWeight' => '1',
            'foodAName' => 'Banana Prata',
            'foodBWeight' => '0,01',
            'foodBName' => 'Maçã',
        ]))
        ->assertDontSee(__('ui.compare.calorie_equivalence_description', [
            'foodAWeight' => '1',
            'foodAName' => 'Banana Prata',
            'foodBWeight' => '0',
            'foodBName' => 'Maçã',
        ]));
});

it('shows a friendly message when Food A has unavailable calorie data', function () {
    $agua = Food::factory()->create([
        'name_pt' => 'Água',
        'calories_per_100g' => 0,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $agua->id)
        ->assertSee(__('ui.compare.calorie_data_unavailable'));
});

it('shows a friendly message when Food B has unavailable calorie data', function () {
    $agua = Food::factory()->create([
        'name_pt' => 'Água',
        'calories_per_100g' => 0,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodB', $agua->id)
        ->assertSee(__('ui.compare.calorie_data_unavailable'));
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
        ->assertSeeHtml('data-testid="comparison-result"')
        ->assertSee('50,5 g')
        ->assertSee('86,43 g');
});

it('recalculates the comparison result when Food A weight changes', function () {
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
        ->assertSeeHtml('data-testid="comparison-result"')
        ->set('foodAWeight', 120)
        ->assertSeeHtml('data-testid="comparison-result"')
        ->assertSee('205,38 g');
});

it('removes the derived comparison result when Food A weight becomes invalid', function () {
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
        ->assertSeeHtml('data-testid="comparison-result"')
        ->set('foodAWeight', 0)
        ->assertDontSeeHtml('data-testid="comparison-result"');
});

it('compares foods when Food A weight uses a comma decimal separator', function () {
    $foodA = Food::factory()->create([
        'name_pt' => 'Alimento A',
        'calories_per_100g' => 100,
    ]);
    $foodB = Food::factory()->create([
        'name_pt' => 'Alimento B',
        'calories_per_100g' => 50,
    ]);

    Livewire::test(NutritionalComparator::class)
        ->call('selectFoodA', $foodA->id)
        ->set('foodAWeight', '50,5')
        ->call('selectFoodB', $foodB->id)
        ->assertSeeHtml('data-testid="comparison-result"')
        ->assertSee('50,5 g de Alimento A ≈ 101 g de Alimento B.')
        ->assertDontSee(__('ui.compare.quantity_must_be_numeric'));
});
