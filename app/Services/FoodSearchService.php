<?php

namespace App\Services;

use App\Models\Food;
use Illuminate\Database\Eloquent\Collection;

class FoodSearchService
{
    public function search(string $namePt): Collection
    {
        $terms = preg_split('/\s+/', trim($namePt), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $query = Food::query();

        foreach ($terms as $term) {
            $query->where('name_pt', 'like', "%{$term}%");
        }

        return $query
            ->orderBy('name_pt')
            ->limit(8)
            ->get();
    }
}
