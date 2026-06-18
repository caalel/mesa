<?php

namespace App\Services;

class CompareFoodsService
{
    public function calculateEquivalentWeight(int $foodAValuePer100g, int $foodAWeight, int $foodBValuePer100g): int 
    {
        return (int) round(($foodAValuePer100g * $foodAWeight) / $foodBValuePer100g);
    }
}
