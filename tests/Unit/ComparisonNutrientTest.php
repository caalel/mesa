<?php

use App\Enums\ComparisonNutrient;

it('maps each comparison nutrient to its Food attribute', function () {
    expect(array_column(ComparisonNutrient::cases(), 'value', 'name'))->toBe([
        'Calories' => 'calories',
        'Protein' => 'protein',
        'Carbohydrates' => 'carbohydrates',
        'Fat' => 'fat',
    ]);

    expect(array_map(
        fn (ComparisonNutrient $nutrient) => $nutrient->foodAttribute(),
        ComparisonNutrient::cases(),
    ))->toBe([
        'calories_per_100g',
        'protein_per_100g',
        'carbs_per_100g',
        'fat_per_100g',
    ]);
});
