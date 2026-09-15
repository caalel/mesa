<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? config('app.name') }}</title>
        @isset($description)
            <meta name="description" content="{{ $description }}">
        @endisset
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body>
        <header class="mx-auto w-full max-w-6xl px-4 pt-4 sm:px-6 lg:pt-6">
            <nav class="relative flex flex-col items-center gap-4 rounded-3xl px-5 py-4 transition duration-200 hover:bg-[var(--color-surface)] hover:shadow-[0_12px_28px_rgba(29,38,32,0.10)] focus-within:bg-[var(--color-surface)] focus-within:shadow-[0_12px_28px_rgba(29,38,32,0.10)] sm:flex-row sm:justify-between sm:gap-3 sm:px-7 sm:py-5">
                <a class="text-xl font-semibold tracking-tight text-[var(--color-text-primary)] sm:text-2xl focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-green)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--color-background)]" href="{{ route('home') }}">
                    {{ config('app.name') }}
                </a>

                <div class="flex w-full items-center justify-center gap-1 sm:absolute sm:left-1/2 sm:w-auto sm:-translate-x-1/2 sm:gap-2">
                    <a
                        @class([
                            'cursor-pointer rounded-xl px-3 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-green)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--color-surface)] sm:px-4 sm:py-2.5 sm:text-base',
                            'bg-[var(--color-light-green)] text-[var(--color-primary-green)]' => request()->routeIs('comparator'),
                            'text-[var(--color-text-primary)] hover:bg-[var(--color-light-green)] focus:bg-[var(--color-light-green)]' => ! request()->routeIs('comparator'),
                        ])
                        href="{{ route('comparator') }}"@if (request()->routeIs('comparator')) aria-current="page"@endif
                    >
                        {{ __('ui.navigation.comparator') }}
                    </a>

                    <a
                        @class([
                            'cursor-pointer rounded-xl px-3 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-green)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--color-surface)] sm:px-4 sm:py-2.5 sm:text-base',
                            'bg-[var(--color-light-green)] text-[var(--color-primary-green)]' => request()->routeIs('meals'),
                            'text-[var(--color-text-primary)] hover:bg-[var(--color-light-green)] focus:bg-[var(--color-light-green)]' => ! request()->routeIs('meals'),
                        ])
                        href="{{ route('meals') }}"@if (request()->routeIs('meals')) aria-current="page"@endif
                    >
                        {{ __('ui.navigation.meals') }}
                    </a>
                </div>

                <div class="flex shrink-0 items-center">
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
