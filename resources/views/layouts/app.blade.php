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
            {{-- 353px is the content breakpoint identified in manual validation where the header controls stop fitting on one line; keep it content-driven instead of replacing it with sm. --}}
            <nav class="relative flex flex-wrap items-center justify-between gap-3 rounded-3xl px-5 py-4 transition duration-200 hover:bg-[var(--color-surface)] hover:shadow-[0_12px_28px_rgba(29,38,32,0.10)] focus-within:bg-[var(--color-surface)] focus-within:shadow-[0_12px_28px_rgba(29,38,32,0.10)] max-[353px]:flex-col max-[353px]:flex-nowrap max-[353px]:gap-4 sm:px-7 sm:py-5">
                <a class="text-xl font-semibold tracking-tight text-[var(--color-text-primary)] sm:text-2xl focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-green)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--color-background)]" href="{{ route('home') }}">
                    {{ config('app.name') }}
                </a>

                <div class="order-3 flex w-full items-center justify-center gap-1 max-[353px]:order-none sm:absolute sm:left-1/2 sm:order-none sm:w-auto sm:-translate-x-1/2 sm:gap-2">
                    <a class="cursor-pointer rounded-xl px-3 py-2 text-sm font-medium text-[var(--color-text-primary)] transition hover:bg-[var(--color-light-green)] focus:bg-[var(--color-light-green)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-green)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--color-surface)] sm:px-4 sm:py-2.5 sm:text-base" href="{{ route('meals') }}">
                        {{ __('ui.navigation.meals') }}
                    </a>

                    <a class="cursor-pointer rounded-xl px-3 py-2 text-sm font-medium text-[var(--color-text-primary)] transition hover:bg-[var(--color-light-green)] focus:bg-[var(--color-light-green)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-green)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--color-surface)] sm:px-4 sm:py-2.5 sm:text-base" href="{{ route('comparator') }}">
                        {{ __('ui.navigation.comparator') }}
                    </a>
                </div>

                <div class="flex shrink-0 items-center max-[353px]:w-full max-[353px]:justify-center">
                    <div class="flex overflow-hidden rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)]" data-testid="locale-switcher">
                        <form method="POST" action="{{ route('locale.switch', ['locale' => 'pt_BR']) }}">
                            @csrf
                            <button
                                type="submit"
                                data-testid="locale-option-pt_BR"@if (app()->getLocale() === 'pt_BR') aria-current="true" @endif
                                class="cursor-pointer px-3 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[var(--color-primary-green)] @if (app()->getLocale() === 'pt_BR') bg-[var(--color-light-green)] text-[var(--color-primary-green)] @else text-[var(--color-text-secondary)] hover:bg-[var(--color-background)] @endif"
                            >PT</button>
                        </form>

                        <form method="POST" action="{{ route('locale.switch', ['locale' => 'en']) }}">
                            @csrf
                            <button
                                type="submit"
                                data-testid="locale-option-en"@if (app()->getLocale() === 'en') aria-current="true" @endif
                                class="cursor-pointer px-3 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[var(--color-primary-green)] @if (app()->getLocale() === 'en') bg-[var(--color-light-green)] text-[var(--color-primary-green)] @else text-[var(--color-text-secondary)] hover:bg-[var(--color-background)] @endif"
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
