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
    expect($equivalentWeight)->toEqual(50);
});

it('keeps the equivalent weight equal to the original weight when both foods have the same value per 100g', function () {
    // Arrange
    $foodAValuePer100g = 100;
    $foodAWeight = 80;
    $foodBValuePer100g = 100;
    $service = new CompareFoodsService();

    // Act
    $equivalentWeight = $service->calculateEquivalentWeight(
        foodAValuePer100g: $foodAValuePer100g,
        foodAWeight: $foodAWeight,
        foodBValuePer100g: $foodBValuePer100g,
    );

    // Assert
    expect($equivalentWeight)->toEqual(80);
});

it('calculates the equivalent weight with two decimal places when the result is decimal', function () {
    // Arrange
    $foodAValuePer100g = 100;
    $foodAWeight = 50;
    $foodBValuePer100g = 300;
    $service = new CompareFoodsService();

    // Act
    $equivalentWeight = $service->calculateEquivalentWeight(
        foodAValuePer100g: $foodAValuePer100g,
        foodAWeight: $foodAWeight,
        foodBValuePer100g: $foodBValuePer100g,
    );

    // Assert
    expect($equivalentWeight)->toEqual(16.67);
});
