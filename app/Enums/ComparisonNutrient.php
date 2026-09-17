<?php

namespace App\Enums;

enum ComparisonNutrient: string
{
    case Calories = 'calories';
    case Protein = 'protein';
    case Carbohydrates = 'carbohydrates';
    case Fat = 'fat';

    public function foodAttribute(): string
    {
        return match ($this) {
            self::Calories => 'calories_per_100g',
            self::Protein => 'protein_per_100g',
            self::Carbohydrates => 'carbs_per_100g',
            self::Fat => 'fat_per_100g',
        };
    }
}
