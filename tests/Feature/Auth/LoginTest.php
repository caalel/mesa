<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

it('allows guests to access the login page with its required form controls', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSeeHtml('data-testid="login-page"')
        ->assertSeeHtml('data-testid="login-form"')
        ->assertSeeHtml('method="POST" action="'.route('login.store').'"')
        ->assertSeeHtml('data-testid="login-email"')
        ->assertSeeHtml('data-testid="login-password"')
        ->assertSeeHtml('data-testid="login-submit"')
        ->assertSeeHtml('data-testid="login-register-link"')
        ->assertSeeHtml('href="'.route('register').'"');
});

it('redirects authenticated users away from the login page', function () {
    $user = User::query()->create([
        'name' => 'Ana Silva',
        'email' => 'ana@example.com',
        'password' => Hash::make('correct-horse-battery-staple'),
    ]);

    $this->actingAs($user)
        ->get('/login')
        ->assertRedirect('/');
});

it('authenticates a user with valid credentials through Fortify', function () {
    $user = User::query()->create([
        'name' => 'Ana Silva',
        'email' => 'ana@example.com',
        'password' => Hash::make('correct-horse-battery-staple'),
    ]);

    $this->from('/login')
        ->post('/login', [
            'email' => $user->email,
            'password' => 'correct-horse-battery-staple',
        ])
        ->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials and renders the generic error state', function () {
    User::query()->create([
        'name' => 'Ana Silva',
        'email' => 'ana@example.com',
        'password' => Hash::make('correct-horse-battery-staple'),
    ]);

    $this->followingRedirects()
        ->from('/login')
        ->post('/login', [
            'email' => 'ana@example.com',
            'password' => 'incorrect-password',
        ])
        ->assertOk()
        ->assertSeeHtml('data-testid="login-credentials-error"')
        ->assertDontSeeHtml('aria-invalid="true"')
        ->assertDontSeeHtml('aria-describedby="login-credentials-error"');

    $this->assertGuest();
});

it('redirects an authenticated user to the intended destination', function () {
    $user = User::query()->create([
        'name' => 'Ana Silva',
        'email' => 'ana@example.com',
        'password' => Hash::make('correct-horse-battery-staple'),
    ]);

    $this->withSession(['url.intended' => route('meals')])
        ->from('/login')
        ->post('/login', [
            'email' => $user->email,
            'password' => 'correct-horse-battery-staple',
        ])
        ->assertRedirect(route('meals'));

    $this->assertAuthenticatedAs($user);
});

it('localizes the login page', function (
    string $locale,
    string $title,
    string $intro,
    string $description,
    string $email,
    string $password,
    string $newAccount,
    string $register,
) {
    App::setLocale($locale);

    $this->withSession(['locale' => $locale])->get('/login')
        ->assertSee($title)
        ->assertSee($intro)
        ->assertSee($description)
        ->assertSee($email)
        ->assertSee($password)
        ->assertSee($newAccount)
        ->assertSee($register);
})->with([
    'Brazilian Portuguese' => [
        'pt_BR',
        'Entrar',
        'Entre para continuar no MESA.',
        'Acesse suas refeições salvas e os recursos associados à sua conta.',
        'E-mail',
        'Senha',
        'Ainda não tem uma conta?',
        'Criar conta',
    ],
    'English' => [
        'en',
        'Sign in',
        'Sign in to continue with MESA.',
        'Access your saved meals and the features associated with your account.',
        'Email',
        'Password',
        "Don't have an account yet?",
        'Create account',
    ],
]);
