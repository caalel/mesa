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
    $response = $this->get('/');

    $response
        ->assertOk()
        ->assertSee(__('ui.compare.title'))
        ->assertSee(__('ui.compare.subtitle'));
});

it('renders the nutritional comparator interface in English', function () {
    $response = $this->withHeader('Accept-Language', 'en-US,en;q=0.9')->get('/');

    $response
        ->assertOk()
        ->assertSeeHtml('<html lang="en">')
        ->assertSee('Compare foods')
        ->assertSee('Find out how much of one food is equivalent to another in calories.')
        ->assertSee('Reference food')
        ->assertSee('Food to compare')
        ->assertSee('Compare');
});

it('marks Brazilian Portuguese as the active locale in the header switcher', function () {
    $response = $this->withSession(['locale' => 'pt_BR'])->get('/');

    $response
        ->assertOk()
        ->assertSeeHtml('data-testid="locale-switcher"')
        ->assertSeeHtml('<form method="POST" action="'.route('locale.switch', ['locale' => 'pt_BR']).'">')
        ->assertSeeHtml('<form method="POST" action="'.route('locale.switch', ['locale' => 'en']).'">')
        ->assertSeeHtml('data-testid="locale-option-pt_BR"')
        ->assertSeeHtml('data-testid="locale-option-en"')
        ->assertSeeHtml('data-testid="locale-option-pt_BR" aria-current="true"')
        ->assertDontSeeHtml('data-testid="locale-option-en" aria-current="true"')
        ->assertSee('PT')
        ->assertSee('EN');
});

it('marks English as the active locale in the header switcher', function () {
    $response = $this->withSession(['locale' => 'en'])->get('/');

    $response
        ->assertOk()
        ->assertSeeHtml('data-testid="locale-switcher"')
        ->assertSeeHtml('data-testid="locale-option-pt_BR"')
        ->assertSeeHtml('data-testid="locale-option-en"')
        ->assertSeeHtml('data-testid="locale-option-en" aria-current="true"')
        ->assertDontSeeHtml('data-testid="locale-option-pt_BR" aria-current="true"');
});
