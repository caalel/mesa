<?php

use App\Services\NutritionalValuesCalculatorService;

it('calculates a nutritional value for a given weight', function () {
    // Arrange
    $valuePer100g = 128;
    $weight = 50;
    $calculator = new NutritionalValuesCalculatorService();

    // Act
    $value = $calculator->calculateValue(
        valuePer100g: $valuePer100g,
        weight: $weight,
    );

    // Assert
    expect($value)->toBe(64.0);
});

it('calculates a decimal nutritional value rounded to two decimal places', function () {
    // Arrange
    $valuePer100g = 128;
    $weight = 50.5;
    $calculator = new NutritionalValuesCalculatorService();

    // Act
    $value = $calculator->calculateValue(
        valuePer100g: $valuePer100g,
        weight: $weight,
    );

    // Assert
    expect($value)->toBe(64.64);
});
