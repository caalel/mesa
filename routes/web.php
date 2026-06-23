<?php

use App\Http\Controllers\FoodSearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    echo "Hello, World!";
});

Route::get('/foods/search', FoodSearchController::class);
