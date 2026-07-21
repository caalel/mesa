<?php

namespace App\Services;

use App\Models\Food;
use Illuminate\Database\Eloquent\Collection;

class FoodSearchService
{
    public function search(string $namePt): Collection
    {
        $namePt = trim($namePt);
        $terms = preg_split('/\s+/', $namePt, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $firstTerm = $terms[0] ?? '';
        $query = Food::query();

        foreach ($terms as $term) {
            $query->where('name_pt', 'like', "%{$term}%");
        }

        return $query
            ->orderByRaw(
                'case when name_pt like ? then 0 when name_pt like ? then 1 else 2 end',
                ["{$namePt}%", "{$firstTerm}%"]
            )
            ->orderBy('name_pt')
            ->limit(8)
            ->get();
    }
}
