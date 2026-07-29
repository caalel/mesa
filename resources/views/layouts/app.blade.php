<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body>
        <header class="mx-auto w-full max-w-6xl px-4 pt-4 sm:px-6 lg:pt-6">
            <nav class="flex flex-wrap items-center justify-between gap-3 rounded-3xl px-5 py-4 transition duration-200 hover:bg-[var(--color-surface)] hover:shadow-[0_12px_28px_rgba(29,38,32,0.10)] focus-within:bg-[var(--color-surface)] focus-within:shadow-[0_12px_28px_rgba(29,38,32,0.10)] sm:px-7 sm:py-5">
                <a class="text-xl font-semibold tracking-tight text-[var(--color-text-primary)] sm:text-2xl focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-green)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--color-background)]" href="{{ url('/') }}">
                    {{ config('app.name') }}
                </a>

                <div class="flex shrink-0 items-center gap-2 sm:gap-3">
                    <a class="rounded-xl px-4 py-2.5 text-base font-medium text-[var(--color-text-primary)] transition hover:bg-[var(--color-light-green)] focus:bg-[var(--color-light-green)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-green)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--color-surface)]" href="{{ url('/') }}">
                        {{ __('ui.navigation.comparator') }}
                    </a>

                    <div class="flex overflow-hidden rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)]" data-testid="locale-switcher">
                        <form method="POST" action="{{ route('locale.switch', ['locale' => 'pt_BR']) }}">
                            @csrf
                            <button
                                type="submit"
                                data-testid="locale-option-pt_BR"@if (app()->getLocale() === 'pt_BR') aria-current="true" @endif
                                class="px-3 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[var(--color-primary-green)] @if (app()->getLocale() === 'pt_BR') bg-[var(--color-light-green)] text-[var(--color-primary-green)] @else text-[var(--color-text-secondary)] hover:bg-[var(--color-background)] @endif"
                            >PT</button>
                        </form>

                        <form method="POST" action="{{ route('locale.switch', ['locale' => 'en']) }}">
                            @csrf
                            <button
                                type="submit"
                                data-testid="locale-option-en"@if (app()->getLocale() === 'en') aria-current="true" @endif
                                class="px-3 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[var(--color-primary-green)] @if (app()->getLocale() === 'en') bg-[var(--color-light-green)] text-[var(--color-primary-green)] @else text-[var(--color-text-secondary)] hover:bg-[var(--color-background)] @endif"
                            >EN</button>
                        </form>
                    </div>
                </div>
            </nav>
        </header>

        {{ $slot }}

        @livewireScripts
    </body>
</html>
