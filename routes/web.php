<?php

use App\Livewire\Meals;
use App\Livewire\NutritionalComparator;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.homepage')->name('home');
Route::get('/comparator', NutritionalComparator::class)->name('comparator');
Route::get('/meals', Meals::class)->name('meals');
Route::view('/register', 'pages.register')->middleware('guest')->name('register');
Route::view('/login', 'pages.login')->middleware('guest')->name('login');

Route::post('/locale/{locale}', function (string $locale) {
    session(['locale' => $locale]);

    return back();
})->whereIn('locale', ['en', 'pt_BR'])->name('locale.switch');
