<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    $this->withoutVite();
});

/*
|--------------------------------------------------------------------------
| Tests
|--------------------------------------------------------------------------
*/

it('has a named home route', function () {
    expect(Route::has('home'))->toBeTrue();
});

it('responds successfully at the root path', function () {
    $this->get('/')
        ->assertOk();
});

it('renders a dedicated homepage', function () {
    $this->get('/')
        ->assertOk()
        ->assertSeeHtml('data-testid="homepage"');
});

it('links to the comparator', function () {
    $this->get('/')
        ->assertOk()
        ->assertSeeHtml('data-testid="open-comparator"')
        ->assertSeeHtml('href="'.route('comparator').'"');
});

it('has a named comparator route', function () {
    expect(Route::has('comparator'))->toBeTrue();
});
