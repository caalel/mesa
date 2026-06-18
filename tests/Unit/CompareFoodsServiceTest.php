<?php

use App\Services\CompareFoodsService;

it('calculates the equivalent weight for food b based on values per 100g of food A', function () {
    // Arrange
    $foodAValuePer100g = 128;
    $foodAWeight = 100;
    $foodBValuePer100g = 256;
    $service = new CompareFoodsService();

    // Act
    $equivalentWeight = $service->calculateEquivalentWeight(
        foodAValuePer100g: $foodAValuePer100g,
        foodAWeight: $foodAWeight,
        foodBValuePer100g: $foodBValuePer100g,
    );

    // Assert
    expect($equivalentWeight)->toBe(50);
});
