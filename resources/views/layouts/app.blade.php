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
            <nav class="relative grid grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] items-center gap-x-3 gap-y-4 rounded-3xl px-5 py-4 transition duration-200 hover:bg-[var(--color-surface)] hover:shadow-[0_12px_28px_rgba(29,38,32,0.10)] focus-within:bg-[var(--color-surface)] focus-within:shadow-[0_12px_28px_rgba(29,38,32,0.10)] sm:flex sm:justify-between sm:gap-3 sm:px-7 sm:py-5">
                <a class="justify-self-start text-xl font-semibold tracking-tight text-[var(--color-text-primary)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-green)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--color-background)] sm:shrink-0 sm:text-2xl" href="{{ route('home') }}">
                    {{ config('app.name') }}
                </a>

                <div class="contents sm:flex sm:shrink-0 sm:items-center sm:gap-3">
                    <div class="col-start-2 row-start-1 justify-self-center" data-testid="header-locale-region">
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

                    <div class="col-start-3 row-start-1 min-w-0 justify-self-end" data-testid="header-auth-region">
                        @auth
                            <details class="group relative w-full min-w-0 sm:w-auto" data-testid="account-menu-container">
                                <summary class="flex w-full min-w-0 max-w-[8.5rem] cursor-pointer list-none items-center gap-2 rounded-lg px-2 py-2 text-sm font-semibold text-[var(--color-primary-green)] transition hover:bg-[var(--color-light-green)] hover:opacity-90 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-green)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--color-surface)] [&::-webkit-details-marker]:hidden sm:max-w-40" aria-label="{{ auth()->user()->name }}" data-testid="account-menu-trigger">
                                    <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle cx="12" cy="8" r="3.25" stroke="currentColor" stroke-width="1.8" />
                                        <path d="M5.5 20c.75-3.15 3.02-5 6.5-5s5.75 1.85 6.5 5" stroke="currentColor" stroke-linecap="round" stroke-width="1.8" />
                                    </svg>
                                    <span class="min-w-0 flex-1 truncate max-[470px]:sr-only">{{ auth()->user()->name }}</span>
                                    <svg class="size-3 shrink-0 transition group-open:rotate-180" fill="none" viewBox="0 0 16 16" aria-hidden="true">
                                        <path d="m4 6 4 4 4-4" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" />
                                    </svg>
                                </summary>

                                <div class="absolute right-0 z-10 mt-2 w-64 rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-4 shadow-[0_16px_35px_rgba(29,38,32,0.12)]" data-testid="account-menu">
                                    <p class="truncate text-sm font-semibold text-[var(--color-text-primary)]">{{ auth()->user()->name }}</p>
                                    <p class="mt-1 truncate text-xs text-[var(--color-text-secondary)]">{{ auth()->user()->email }}</p>

                                    <div class="my-4 border-t border-[var(--color-border)]"></div>

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button class="cursor-pointer text-sm font-semibold text-[var(--color-error)] transition hover:opacity-75 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-green)] focus-visible:ring-offset-2" type="submit" data-testid="account-logout">
                                            {{ __('ui.auth.header.logout') }}
                                        </button>
                                    </form>
                                </div>
                            </details>
                        @else
                            <a class="inline-flex items-center gap-2 rounded-lg px-2 py-2 text-sm font-semibold text-[var(--color-primary-green)] transition hover:bg-[var(--color-light-green)] hover:opacity-90 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-green)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--color-surface)]" href="{{ route('login') }}" aria-label="{{ __('ui.auth.login.title') }}" data-testid="header-login">
                                <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="12" cy="8" r="3.25" stroke="currentColor" stroke-width="1.8" />
                                    <path d="M5.5 20c.75-3.15 3.02-5 6.5-5s5.75 1.85 6.5 5" stroke="currentColor" stroke-linecap="round" stroke-width="1.8" />
                                </svg>
                                <span class="max-[350px]:sr-only">{{ __('ui.auth.login.title') }}</span>
                            </a>
                        @endauth
                    </div>
                </div>

                <div class="col-span-3 row-start-2 flex items-center justify-center gap-1 sm:absolute sm:left-1/2 sm:top-1/2 sm:w-auto sm:-translate-x-1/2 sm:-translate-y-1/2 sm:gap-2" data-testid="header-navigation">
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

            </nav>
        </header>

        {{ $slot }}

        @livewireScripts
    </body>
</html>
