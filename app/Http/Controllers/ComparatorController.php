<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComparatorRequest;
use App\Models\Food;
use App\Services\CompareFoodsService;
use Illuminate\Http\Response;

class ComparatorController extends Controller
{
    public function __construct(private CompareFoodsService $compareFoodsService)
    {
    }

    public function __invoke(ComparatorRequest $request): Response
    {
        $foodA = Food::findOrFail($request->input('food_a_id'));
        $foodB = Food::findOrFail($request->input('food_b_id'));

        $equivalentWeight = $this->compareFoodsService->calculateEquivalentWeight(
            foodAValuePer100g: $foodA->calories_per_100g,
            foodAWeight: (float) $request->input('food_a_weight'),
            foodBValuePer100g: $foodB->calories_per_100g,
        );

        return response($equivalentWeight);
    }
}
