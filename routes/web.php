<?php

use App\Http\Controllers\ComparatorController;
use App\Http\Controllers\FoodSearchController;
use App\Livewire\NutritionalComparator;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.homepage')->name('home');
Route::get('/comparator', NutritionalComparator::class)->name('comparator');

Route::get('/foods/search', FoodSearchController::class);
Route::post('/compare', ComparatorController::class);

Route::post('/locale/{locale}', function (string $locale) {
    session(['locale' => $locale]);

    return back();
})->whereIn('locale', ['en', 'pt_BR'])->name('locale.switch');
