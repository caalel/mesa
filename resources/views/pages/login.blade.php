@component('layouts.app', ['title' => __('ui.metadata.login.title')])
    <main class="mx-auto flex min-h-[calc(100vh-11rem)] w-full max-w-[460px] items-center px-4 py-12 sm:px-6 sm:py-16" data-testid="login-placeholder">
        <h1 class="text-4xl font-semibold tracking-[-0.05em] text-[var(--color-text-primary)] sm:text-[2.4rem]">
            {{ __('ui.auth.login.title') }}
        </h1>
    </main>
@endcomponent
