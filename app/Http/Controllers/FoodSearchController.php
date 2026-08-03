<?php

namespace App\Http\Controllers;

use App\Http\Requests\FoodSearchRequest;
use App\Services\FoodSearchService;
use Illuminate\Http\Response;

class FoodSearchController extends Controller
{
    public function __construct(private FoodSearchService $foodSearchService)
    {
    }

    public function __invoke(FoodSearchRequest $request): Response
    {
        $foods = $this->foodSearchService->search($request->query('query', ''));

        return response(implode(',', $foods->pluck('localized_name')->toArray()));
    }
}
