<?php

namespace App\Http\Controllers;

use App\Services\FoodSearchService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FoodSearchController extends Controller
{
    public function __construct(private FoodSearchService $foodSearchService)
    {
    }

    public function __invoke(Request $request): Response
    {
        $foods = $this->foodSearchService->search($request->query('query', ''));

        return response(implode(',', $foods->pluck('name')->toArray()));
    }
}
