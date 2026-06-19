<?php

namespace App\Services;

class CompareFoodsService
{
    public function calculateEquivalentWeight(int $foodAValuePer100g, int $foodAWeight, int $foodBValuePer100g): float
    {
        if ($foodAWeight <= 0) {
            throw new \InvalidArgumentException('The weight must be greater than zero.');
        }

        return round(($foodAValuePer100g * $foodAWeight) / $foodBValuePer100g, 2);
    }
}
