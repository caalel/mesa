<?php

it('switches the locale to English and returns to the previous URL', function () {
    $this->from('/')->post('/locale/en')
        ->assertRedirect('/')
        ->assertSessionHas('locale', 'en');
});

it('switches the locale to Brazilian Portuguese and returns to the previous URL', function () {
    $this->from('/')->post('/locale/pt_BR')
        ->assertRedirect('/')
        ->assertSessionHas('locale', 'pt_BR');
});

it('rejects an unsupported locale', function () {
    $this->post('/locale/fr')
        ->assertNotFound()
        ->assertSessionMissing('locale');
});
