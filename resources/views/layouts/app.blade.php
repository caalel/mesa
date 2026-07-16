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
            <nav class="flex items-center justify-between rounded-3xl px-5 py-4 transition duration-200 hover:bg-[var(--color-surface)] hover:shadow-[0_12px_28px_rgba(29,38,32,0.10)] focus-within:bg-[var(--color-surface)] focus-within:shadow-[0_12px_28px_rgba(29,38,32,0.10)] sm:px-7 sm:py-5">
                <a class="text-xl font-semibold tracking-tight text-[var(--color-text-primary)] sm:text-2xl focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-green)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--color-background)]" href="{{ url('/') }}">
                    {{ config('app.name') }}
                </a>

                <a class="rounded-xl px-4 py-2.5 text-base font-medium text-[var(--color-text-primary)] transition hover:bg-[var(--color-light-green)] focus:bg-[var(--color-light-green)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-green)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--color-surface)]" href="{{ url('/') }}">
                    {{ __('ui.navigation.comparator') }}
                </a>
            </nav>
        </header>

        {{ $slot }}

        @livewireScripts
    </body>
</html>
