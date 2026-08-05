@component('layouts.app')
    <main class="mx-auto w-full max-w-6xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8 lg:py-20" data-testid="homepage">
        <section class="mx-auto max-w-3xl space-y-5 text-center sm:space-y-6">
            <h1 class="text-4xl font-semibold tracking-tight text-[var(--color-text-primary)] sm:text-5xl lg:text-6xl">
                {{ __('ui.homepage.name') }}
            </h1>

            <p class="mx-auto max-w-2xl text-xl font-medium leading-8 text-[var(--color-text-primary)] sm:text-2xl sm:leading-9">
                {{ __('ui.homepage.full_name') }}
            </p>

            <p class="mx-auto max-w-2xl text-base leading-7 text-[var(--color-text-secondary)] sm:text-lg sm:leading-8">
                {{ __('ui.homepage.introduction') }}
            </p>
        </section>

        <section class="mt-14 space-y-6 sm:mt-20 sm:space-y-8" aria-labelledby="homepage-tools-title">
            <h2 class="text-2xl font-semibold leading-8 text-[var(--color-text-primary)] sm:text-3xl" id="homepage-tools-title">
                {{ __('ui.homepage.tools_title') }}
            </h2>

            <article class="max-w-2xl rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5 sm:p-6" data-testid="comparator-tool-card">
                <div class="space-y-3">
                    <h3 class="text-xl font-semibold leading-7 text-[var(--color-text-primary)]">
                        {{ __('ui.homepage.comparator_title') }}
                    </h3>

                    <p class="text-base leading-7 text-[var(--color-text-secondary)]">
                        {{ __('ui.homepage.comparator_description') }}
                    </p>
                </div>

                <a class="mt-6 inline-flex rounded-lg bg-[var(--color-primary-green)] px-5 py-3 text-sm font-semibold text-white transition hover:opacity-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-green)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--color-surface)]" href="{{ route('comparator') }}" data-testid="open-comparator">
                    {{ __('ui.homepage.open_comparator') }}
                </a>
            </article>
        </section>
    </main>
@endcomponent
