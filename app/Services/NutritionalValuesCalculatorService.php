<?php

namespace App\Services;

class NutritionalValuesCalculatorService
{
    public function calculateValue(int $valuePer100g, int $weight): int
    {
        return (int) round(($valuePer100g * $weight) / 100);
    }
}
