<?php

beforeEach(function () {
    $this->withoutVite();
});

it('detects English from the initial Accept-Language header', function () {
    $response = $this->withHeader('Accept-Language', 'en-US,en;q=0.9')->get('/');

    expect(app()->getLocale())->toBe('en');

    $response
        ->assertOk()
        ->assertSeeHtml('<html lang="en">');
});

it('detects Brazilian Portuguese from the initial Accept-Language header', function () {
    $response = $this->withHeader('Accept-Language', 'pt-BR,pt;q=0.9')->get('/');

    expect(app()->getLocale())->toBe('pt_BR');

    $response
        ->assertOk()
        ->assertSeeHtml('<html lang="pt-BR">');
});

it('maps Portuguese from Portugal to Brazilian Portuguese on the initial request', function () {
    $response = $this->withHeader('Accept-Language', 'pt-PT,pt;q=0.9')->get('/');

    expect(app()->getLocale())->toBe('pt_BR');

    $response
        ->assertOk()
        ->assertSeeHtml('<html lang="pt-BR">');
});

it('falls back to Brazilian Portuguese for an unsupported initial Accept-Language header', function () {
    $response = $this->withHeader('Accept-Language', 'fr-FR,fr;q=0.9')->get('/');

    expect(app()->getLocale())->toBe('pt_BR');

    $response
        ->assertOk()
        ->assertSeeHtml('<html lang="pt-BR">');
});

it('uses a valid locale from the session before the browser language', function () {
    $response = $this
        ->withSession(['locale' => 'en'])
        ->withHeader('Accept-Language', 'pt-BR,pt;q=0.9')
        ->get('/');

    expect(app()->getLocale())->toBe('en');

    $response
        ->assertOk()
        ->assertSeeHtml('<html lang="en">');
});

it('ignores an invalid locale from the session', function () {
    $response = $this
        ->withSession(['locale' => 'fr'])
        ->withHeader('Accept-Language', 'en-US,en;q=0.9')
        ->get('/');

    expect(app()->getLocale())->toBe('en');

    $response
        ->assertOk()
        ->assertSeeHtml('<html lang="en">');
});
