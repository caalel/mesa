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

it('allows guests to access the registration page with its required form controls', function () {
    $this->get('/register')
        ->assertOk()
        ->assertSeeHtml('data-testid="registration-page"')
        ->assertSeeHtml('data-testid="registration-form"')
        ->assertSeeHtml('method="POST" action="'.route('register.store').'"')
        ->assertSeeHtml('data-testid="registration-name"')
        ->assertSeeHtml('data-testid="registration-email"')
        ->assertSeeHtml('data-testid="registration-password"')
        ->assertSeeHtml('data-testid="registration-password-confirmation"')
        ->assertSeeHtml('data-testid="registration-submit"')
        ->assertSeeHtml('data-testid="registration-login-link"')
        ->assertSeeHtml('href="'.route('login').'"');
});

it('redirects authenticated users away from the registration page', function () {
    $user = User::query()->create([
        'name' => 'Ana Silva',
        'email' => 'ana@example.com',
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user)
        ->get('/register')
        ->assertRedirect('/');
});

it('registers and authenticates a valid user through Fortify', function () {
    $this->from('/register')
        ->post('/register', [
            'name' => 'Ana Silva',
            'email' => 'ana@example.com',
            'password' => 'correct-horse-battery-staple',
            'password_confirmation' => 'correct-horse-battery-staple',
        ])
        ->assertRedirect('/');

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'name' => 'Ana Silva',
        'email' => 'ana@example.com',
    ]);
});

it('migrates guest meals after a successful Fortify registration', function () {
    $food = Food::factory()->create();

    $this->withSession([
        'meals' => [
            [
                'id' => 27,
                'name' => 'Guest dinner',
                'items' => [
                    ['food_id' => $food->id, 'weight' => 250.0],
                ],
            ],
        ],
        'url.intended' => route('meals'),
    ])
        ->from('/register')
        ->post('/register', [
            'name' => 'Ana Silva',
            'email' => 'ana@example.com',
            'password' => 'correct-horse-battery-staple',
            'password_confirmation' => 'correct-horse-battery-staple',
        ])
        ->assertRedirect(route('meals'))
        ->assertSessionMissing('meals');

    $user = User::query()->where('email', 'ana@example.com')->sole();

    $this->assertAuthenticatedAs($user);
    $this->assertDatabaseHas('meals', [
        'user_id' => $user->id,
        'name' => 'Guest dinner',
    ]);

    $meal = Meal::query()->with('items')->sole();

    expect($meal->items->pluck('food_id')->all())->toBe([$food->id]);
    expect($meal->items->pluck('weight')->all())->toBe([250.0]);
});

it('leaves guest meals available without authenticating when migration fails during registration', function () {
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
        ->post('/register', [
            'name' => 'Ana Silva',
            'email' => 'ana@example.com',
            'password' => 'correct-horse-battery-staple',
            'password_confirmation' => 'correct-horse-battery-staple',
        ])
        ->assertStatus(500)
        ->assertSessionHas('meals', $guestMeals)
        ->assertSessionHas('meal_auth_callout_handled', true)
        ->assertSessionHas('url.intended', route('meals'))
        ->assertSessionMissing($guardSessionKey);

    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'ana@example.com']);
    $this->assertDatabaseCount('meals', 0);
});

it('rejects invalid registration data and displays field errors', function () {
    $response = $this->followingRedirects()
        ->from('/register')
        ->post('/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'password',
            'password_confirmation' => 'different-password',
        ]);

    $response
        ->assertOk()
        ->assertSeeHtml('data-testid="registration-name-error"')
        ->assertSeeHtml('data-testid="registration-email-error"')
        ->assertSeeHtml('data-testid="registration-password-error"');

    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'invalid-email']);
});

it('localizes the registration page', function (
    string $locale,
    string $title,
    string $intro,
    string $description,
    string $name,
    string $email,
    string $password,
    string $passwordConfirmation,
    string $existingAccount,
    string $login,
) {
    App::setLocale($locale);

    $this->withSession(['locale' => $locale])->get('/register')
        ->assertSee($title)
        ->assertSee($intro)
        ->assertSee($description)
        ->assertSee($name)
        ->assertSee($email)
        ->assertSee($password)
        ->assertSee($passwordConfirmation)
        ->assertSee($existingAccount)
        ->assertSee($login);
})->with([
    'Brazilian Portuguese' => [
        'pt_BR',
        'Criar conta',
        'Crie sua conta no MESA.',
        'Mantenha suas refeições salvas e acesse os recursos associados à sua conta.',
        'Nome',
        'E-mail',
        'Senha',
        'Confirmar senha',
        'Já tem uma conta?',
        'Entrar',
    ],
    'English' => [
        'en',
        'Create account',
        'Create your MESA account.',
        'Keep your meals saved and access the features associated with your account.',
        'Name',
        'Email',
        'Password',
        'Confirm password',
        'Already have an account?',
        'Sign in',
    ],
]);
