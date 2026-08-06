<?php

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

it('renders the nutritional comparator initial screen', function () {
    $response = $this->get('/comparator');

    $response
        ->assertOk()
        ->assertSee(__('ui.compare.title'))
        ->assertSee(__('ui.compare.subtitle'));
});

it('renders the nutritional comparator interface in English', function () {
    $response = $this->withHeader('Accept-Language', 'en-US,en;q=0.9')->get('/comparator');

    $response
        ->assertOk()
        ->assertSeeHtml('<html lang="en">')
        ->assertSee('Compare foods')
        ->assertSee('Find out how much of one food is equivalent to another in calories.')
        ->assertSee('Reference food')
        ->assertSee('Food to compare')
        ->assertSee('Compare');
});
