@component('layouts.app', [
    'title' => __('ui.metadata.register.title'),
    'description' => __('ui.metadata.register.description'),
])
    <main class="mx-auto flex min-h-[calc(100vh-11rem)] w-full max-w-[460px] items-center px-4 py-12 sm:px-6 sm:py-16" data-testid="registration-page">
        <form class="w-full rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-[0_8px_18px_rgba(29,38,32,0.025)] sm:p-8" method="POST" action="{{ route('register.store') }}" novalidate data-testid="registration-form">
            @csrf

            <h1 class="text-4xl font-semibold tracking-[-0.05em] text-[var(--color-text-primary)] sm:text-[2.4rem]">
                {{ __('ui.auth.register.title') }}
            </h1>

            <p class="mt-3 text-base text-[var(--color-text-primary)] sm:text-lg">
                {{ __('ui.auth.register.introduction') }}
            </p>

            <p class="mt-2 text-sm leading-6 text-[var(--color-text-secondary)]">
                {{ __('ui.auth.register.description') }}
            </p>

            <div class="mt-7">
                <label class="block text-sm font-semibold text-[var(--color-text-primary)]" for="name">
                    {{ __('ui.auth.register.name') }}
                </label>
                <input
                    class="mt-2 h-12 w-full rounded-lg border bg-transparent px-4 outline-none transition placeholder:text-[var(--color-text-secondary)] focus:ring-2 @error('name') border-[var(--color-error)] focus:border-[var(--color-error)] focus:ring-[var(--color-error)]/20 @else border-[var(--color-border)] focus:border-[var(--color-primary-green)] focus:ring-[var(--color-primary-green)]/20 @enderror"
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name') }}"
                    autocomplete="name"
                    autofocus
                    aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                    @error('name') aria-describedby="name-error" @enderror
                    data-testid="registration-name"
                >
                @error('name')
                    <p class="mt-2 text-sm font-medium text-[var(--color-error)]" id="name-error" role="alert" data-testid="registration-name-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6">
                <label class="block text-sm font-semibold text-[var(--color-text-primary)]" for="email">
                    {{ __('ui.auth.register.email') }}
                </label>
                <input
                    class="mt-2 h-12 w-full rounded-lg border bg-transparent px-4 outline-none transition placeholder:text-[var(--color-text-secondary)] focus:ring-2 @error('email') border-[var(--color-error)] focus:border-[var(--color-error)] focus:ring-[var(--color-error)]/20 @else border-[var(--color-border)] focus:border-[var(--color-primary-green)] focus:ring-[var(--color-primary-green)]/20 @enderror"
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                    @error('email') aria-describedby="email-error" @enderror
                    data-testid="registration-email"
                >
                @error('email')
                    <p class="mt-2 text-sm font-medium text-[var(--color-error)]" id="email-error" role="alert" data-testid="registration-email-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6">
                <label class="block text-sm font-semibold text-[var(--color-text-primary)]" for="password">
                    {{ __('ui.auth.register.password') }}
                </label>
                <input
                    class="mt-2 h-12 w-full rounded-lg border bg-transparent px-4 outline-none transition placeholder:text-[var(--color-text-secondary)] focus:ring-2 @error('password') border-[var(--color-error)] focus:border-[var(--color-error)] focus:ring-[var(--color-error)]/20 @else border-[var(--color-border)] focus:border-[var(--color-primary-green)] focus:ring-[var(--color-primary-green)]/20 @enderror"
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="new-password"
                    aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                    @error('password') aria-describedby="password-error" @enderror
                    data-testid="registration-password"
                >
                @error('password')
                    <p class="mt-2 text-sm font-medium text-[var(--color-error)]" id="password-error" role="alert" data-testid="registration-password-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6">
                <label class="block text-sm font-semibold text-[var(--color-text-primary)]" for="password_confirmation">
                    {{ __('ui.auth.register.password_confirmation') }}
                </label>
                <input
                    class="mt-2 h-12 w-full rounded-lg border border-[var(--color-border)] bg-transparent px-4 outline-none transition placeholder:text-[var(--color-text-secondary)] focus:border-[var(--color-primary-green)] focus:ring-2 focus:ring-[var(--color-primary-green)]/20"
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    data-testid="registration-password-confirmation"
                >
            </div>

            <button class="cursor-pointer mt-8  w-full rounded-lg bg-[var(--color-primary-green)] px-5 py-3 text-sm font-semibold text-white transition hover:opacity-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-green)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--color-surface)]" type="submit" data-testid="registration-submit">
                {{ __('ui.auth.register.submit') }}
            </button>

            <p class="mt-6 text-center text-sm text-[var(--color-text-secondary)]">
                {{ __('ui.auth.register.already_registered') }}
                <a class="font-semibold text-[var(--color-primary-green)] transition hover:opacity-80 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-green)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--color-surface)]" href="{{ route('login') }}" data-testid="registration-login-link">
                    {{ __('ui.auth.register.login') }}
                </a>
            </p>
        </form>
    </main>
@endcomponent
