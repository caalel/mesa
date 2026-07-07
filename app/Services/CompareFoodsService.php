<?php

namespace App\Services;

class CompareFoodsService
{
    public function calculateEquivalentWeight(
        int|float $foodAValuePer100g,
        int|float $foodAWeight,
        int|float $foodBValuePer100g,
    ): float
    {
        if ($foodAWeight <= 0) {
            throw new \InvalidArgumentException('The weight must be greater than zero.');
        }

        if ($foodAValuePer100g <= 0) {
            throw new \InvalidArgumentException('The nutritional value must be greater than zero.');
        }

        if ($foodBValuePer100g <= 0) {
            throw new \InvalidArgumentException('The nutritional value must be greater than zero.');
        }

        return round(($foodAValuePer100g * $foodAWeight) / $foodBValuePer100g, 2);
    }
}
