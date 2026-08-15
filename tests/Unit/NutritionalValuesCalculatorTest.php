<?php

use App\Services\NutritionalValuesCalculator;

it('calculates a nutritional value for a given weight', function () {
    // Arrange
    $valuePer100g = 128;
    $weight = 50;
    $calculator = new NutritionalValuesCalculator();

    // Act
    $value = $calculator->calculateValue(
        valuePer100g: $valuePer100g,
        weight: $weight,
    );

    // Assert
    expect($value)->toBe(64.0);
});

it('calculates a decimal nutritional value without rounding', function () {
    // Arrange
    $valuePer100g = 0.01;
    $weight = 1;
    $calculator = new NutritionalValuesCalculator();

    // Act
    $value = $calculator->calculateValue(
        valuePer100g: $valuePer100g,
        weight: $weight,
    );

    // Assert
    expect($value)->toBeFloat();
    expect(abs($value - 0.0001))->toBeLessThan(0.000000000001);
});
