<?php

namespace App\Services;

class NutritionalValuesCalculatorService
{
    public function calculateValue(int|float $valuePer100g, int|float $weight): float
    {
        return ($valuePer100g * $weight) / 100;
    }
}
