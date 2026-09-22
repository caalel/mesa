<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Food extends Model
{
    /** @use HasFactory<\Database\Factories\FoodFactory> */
    use HasFactory;

    protected $table = 'foods';

    public function getLocalizedNameAttribute(): string
    {
        return match (app()->getLocale()) {
            'pt_BR' => $this->name_pt,
            'en' => $this->name_en,
        };
    }

    public function mealItems(): HasMany
    {
        return $this->hasMany(MealItem::class);
    }
}
