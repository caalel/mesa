<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

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

it('marks only Meals as the current navigation link on the meals route', function () {
    $response = $this->get(route('meals'));

    $response
        ->assertOk()
        ->assertSeeHtml('href="'.route('meals').'" aria-current="page"')
        ->assertDontSeeHtml('href="'.route('comparator').'" aria-current="page"');
});

it('marks only Comparator as the current navigation link on the comparator route', function () {
    $response = $this->get(route('comparator'));

    $response
        ->assertOk()
        ->assertSeeHtml('href="'.route('comparator').'" aria-current="page"')
        ->assertDontSeeHtml('href="'.route('meals').'" aria-current="page"');
});

it('shows the guest login action without authenticated account controls', function () {
    $this->get('/')
        ->assertOk()
        ->assertSeeHtml('data-testid="header-locale-region"')
        ->assertSeeHtml('data-testid="header-auth-region"')
        ->assertSeeHtml('data-testid="header-login"')
        ->assertSeeHtml('href="'.route('login').'"')
        ->assertDontSeeHtml('data-testid="account-menu-trigger"')
        ->assertDontSeeHtml('data-testid="account-menu"');
});

it('shows the authenticated account menu without the guest login action', function () {
    $user = User::query()->create([
        'name' => 'Ana Silva',
        'email' => 'ana@example.com',
        'password' => Hash::make('correct-horse-battery-staple'),
    ]);

    $this->actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertSeeHtml('data-testid="account-menu-trigger"')
        ->assertSeeHtml('data-testid="account-menu"')
        ->assertSeeHtml('data-testid="account-logout"')
        ->assertSee('Ana Silva')
        ->assertSee('ana@example.com')
        ->assertDontSeeHtml('data-testid="header-login"');
});

it('localizes header authentication actions', function (string $locale, string $login, string $logout) {
    App::setLocale($locale);

    $this->withSession(['locale' => $locale])
        ->get('/')
        ->assertSee($login);

    $user = User::query()->create([
        'name' => 'Ana Silva',
        'email' => 'ana@example.com',
        'password' => Hash::make('correct-horse-battery-staple'),
    ]);

    $this->actingAs($user)
        ->withSession(['locale' => $locale])
        ->get('/')
        ->assertSee($logout);
})->with([
    'Brazilian Portuguese' => ['pt_BR', 'Entrar', 'Sair'],
    'English' => ['en', 'Sign in', 'Sign out'],
]);
