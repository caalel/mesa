@component('layouts.app', ['title' => __('ui.metadata.login.title')])
    <main class="mx-auto flex min-h-[calc(100vh-11rem)] w-full max-w-[460px] items-center px-4 py-12 sm:px-6 sm:py-16" data-testid="login-page">
        <form class="w-full rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-[0_8px_18px_rgba(29,38,32,0.025)] sm:p-8" method="POST" action="{{ route('login.store') }}" novalidate data-testid="login-form">
            @csrf

            <h1 class="text-4xl font-semibold tracking-[-0.05em] text-[var(--color-text-primary)] sm:text-[2.4rem]">
                {{ __('ui.auth.login.title') }}
            </h1>

            <p class="mt-3 text-base text-[var(--color-text-primary)] sm:text-lg">
                {{ __('ui.auth.login.introduction') }}
            </p>

            <p class="mt-2 text-sm leading-6 text-[var(--color-text-secondary)]">
                {{ __('ui.auth.login.description') }}
            </p>

            @error('email')
                <p class="mt-6 rounded-lg border border-[var(--color-error)]/25 bg-[var(--color-error)]/5 px-4 py-3 text-sm font-medium text-[var(--color-error)]" id="login-credentials-error" role="alert" data-testid="login-credentials-error">{{ $message }}</p>
            @enderror

            <div class="mt-7">
                <label class="block text-sm font-semibold text-[var(--color-text-primary)]" for="email">
                    {{ __('ui.auth.login.email') }}
                </label>
                <input
                    class="mt-2 h-12 w-full rounded-lg border border-[var(--color-border)] bg-transparent px-4 outline-none transition placeholder:text-[var(--color-text-secondary)] focus:border-[var(--color-primary-green)] focus:ring-2 focus:ring-[var(--color-primary-green)]/20"
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    autofocus
                    data-testid="login-email"
                >
            </div>

            <div class="mt-6">
                <label class="block text-sm font-semibold text-[var(--color-text-primary)]" for="password">
                    {{ __('ui.auth.login.password') }}
                </label>
                <input
                    class="mt-2 h-12 w-full rounded-lg border border-[var(--color-border)] bg-transparent px-4 outline-none transition placeholder:text-[var(--color-text-secondary)] focus:border-[var(--color-primary-green)] focus:ring-2 focus:ring-[var(--color-primary-green)]/20"
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    data-testid="login-password"
                >
            </div>

            <button class="mt-8 w-full cursor-pointer rounded-lg bg-[var(--color-primary-green)] px-5 py-3 text-sm font-semibold text-white transition hover:opacity-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-green)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--color-surface)]" type="submit" data-testid="login-submit">
                {{ __('ui.auth.login.submit') }}
            </button>

            <p class="mt-6 text-center text-sm text-[var(--color-text-secondary)]">
                {{ __('ui.auth.login.not_registered') }}
                <a class="font-semibold text-[var(--color-primary-green)] transition hover:opacity-80 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-green)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--color-surface)]" href="{{ route('register') }}" data-testid="login-register-link">
                    {{ __('ui.auth.login.register') }}
                </a>
            </p>
        </form>
    </main>
@endcomponent
