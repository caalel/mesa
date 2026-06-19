<?php

namespace App\Services;

class CompareFoodsService
{
    public function calculateEquivalentWeight(int $foodAValuePer100g, int $foodAWeight, int $foodBValuePer100g): float
    {
        return round(($foodAValuePer100g * $foodAWeight) / $foodBValuePer100g, 2);
    }
}
