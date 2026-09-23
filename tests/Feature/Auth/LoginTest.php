<?php

use App\Models\User;
use App\Models\Food;
use App\Models\Meal;
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

it('migrates guest meals after a successful Fortify login', function () {
    $user = User::query()->create([
        'name' => 'Ana Silva',
        'email' => 'ana@example.com',
        'password' => Hash::make('correct-horse-battery-staple'),
    ]);
    $food = Food::factory()->create();

    $this->withSession([
        'meals' => [
            [
                'id' => 42,
                'name' => 'Guest lunch',
                'items' => [
                    ['food_id' => $food->id, 'weight' => 125.5],
                ],
            ],
        ],
        'meal_auth_callout_handled' => true,
        'url.intended' => route('meals'),
    ])
        ->from('/login')
        ->post('/login', [
            'email' => $user->email,
            'password' => 'correct-horse-battery-staple',
        ])
        ->assertRedirect(route('meals'))
        ->assertSessionMissing('meals')
        ->assertSessionHas('meal_auth_callout_handled', true);

    $this->assertAuthenticatedAs($user);
    $this->assertDatabaseHas('meals', [
        'user_id' => $user->id,
        'name' => 'Guest lunch',
    ]);

    $meal = Meal::query()->with('items')->sole();

    expect($meal->items->pluck('food_id')->all())->toBe([$food->id]);
    expect($meal->items->pluck('weight')->all())->toBe([125.5]);
});

it('leaves guest meals available without authenticating when migration fails during login', function () {
    $user = User::query()->create([
        'name' => 'Ana Silva',
        'email' => 'ana@example.com',
        'password' => Hash::make('correct-horse-battery-staple'),
    ]);
    $guestMeals = [
        [
            'id' => 1,
            'name' => 'Invalid guest meal',
            'items' => [
                ['food_id' => 999999, 'weight' => 100.0],
            ],
        ],
    ];
    $guardSessionKey = auth()->guard()->getName();

    $this->withSession([
        'meals' => $guestMeals,
        'meal_auth_callout_handled' => true,
        'url.intended' => route('meals'),
    ])
        ->post('/login', [
            'email' => $user->email,
            'password' => 'correct-horse-battery-staple',
        ])
        ->assertStatus(500)
        ->assertSessionHas('meals', $guestMeals)
        ->assertSessionHas('meal_auth_callout_handled', true)
        ->assertSessionHas('url.intended', route('meals'))
        ->assertSessionMissing($guardSessionKey);

    $this->assertGuest();
    $this->assertDatabaseCount('meals', 0);
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
