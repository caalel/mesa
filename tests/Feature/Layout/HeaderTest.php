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
