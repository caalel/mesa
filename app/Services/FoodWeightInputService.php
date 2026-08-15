<?php

namespace App\Services;

class FoodWeightInputService
{
    public const MAXIMUM_IN_GRAMS = 10_000;

    public function normalize(string $weight): string
    {
        return str_replace(',', '.', trim($weight));
    }

    public function isNumeric(string $weight): bool
    {
        return is_numeric($this->normalize($weight));
    }

    public function isPositive(string $weight): bool
    {
        return $this->isNumeric($weight) && (float) $this->normalize($weight) > 0;
    }

    public function exceedsMaximum(string $weight): bool
    {
        return $this->isNumeric($weight)
            && (float) $this->normalize($weight) > self::MAXIMUM_IN_GRAMS;
    }

    public function isValid(string $weight): bool
    {
        return $this->isPositive($weight) && ! $this->exceedsMaximum($weight);
    }
}
