<?php

namespace App\Services;

use App\Models\Food;
use Illuminate\Database\Eloquent\Collection;

class FoodSearchService
{
    public function search(string $name): Collection
    {
        $name = trim($name);
        $terms = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $firstTerm = $terms[0] ?? '';
        $nameColumn = match (app()->getLocale()) {
            'pt_BR' => 'name_pt',
            'en' => 'name_en',
        };
        $query = Food::query();

        foreach ($terms as $term) {
            $query->where($nameColumn, 'like', "%{$term}%");
        }

        return $query
            ->orderByRaw(
                "case when {$nameColumn} like ? then 0 when {$nameColumn} like ? then 1 else 2 end",
                ["{$name}%", "{$firstTerm}%"]
            )
            ->orderBy($nameColumn)
            ->limit(8)
            ->get();
    }
}
