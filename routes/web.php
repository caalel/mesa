<?php

use App\Http\Controllers\ComparatorController;
use App\Http\Controllers\FoodSearchController;
use App\Livewire\NutritionalComparator;
use Illuminate\Support\Facades\Route;

Route::get('/', NutritionalComparator::class);

Route::get('/foods/search', FoodSearchController::class);
Route::post('/compare', ComparatorController::class);
