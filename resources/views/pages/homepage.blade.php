@component('layouts.app', [
    'title' => __('ui.metadata.home.title'),
    'description' => __('ui.metadata.home.description'),
])
    <main class="mx-auto w-full max-w-6xl px-4 pb-14 pt-16 sm:px-6 sm:pb-20 sm:pt-20 lg:px-8 lg:pb-24 lg:pt-24" data-testid="homepage">
        <section class="mx-auto max-w-3xl text-center">
            <h1 class="text-5xl font-semibold tracking-[-0.075em] text-[var(--color-text-primary)] sm:text-6xl lg:text-7xl">
                {{ __('ui.homepage.name') }}
            </h1>

            <p class="mx-auto mt-7 max-w-2xl text-xl font-semibold leading-8 tracking-tight text-[var(--color-text-primary)] sm:text-2xl sm:leading-9">
                {{ __('ui.homepage.full_name') }}
            </p>

            <p class="mx-auto mt-7 max-w-2xl text-base leading-7 text-[var(--color-text-secondary)] sm:text-lg sm:leading-8">
                {{ __('ui.homepage.introduction') }}
            </p>
        </section>

        <section class="mt-16 sm:mt-20" aria-labelledby="homepage-tools-title">
            <h2 class="text-2xl font-semibold leading-8 text-[var(--color-text-primary)] sm:text-3xl" id="homepage-tools-title">
                {{ __('ui.homepage.tools_title') }}
            </h2>

            <div class="mt-7 grid gap-4 sm:grid-cols-2">
                <article class="flex min-h-56 flex-col rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 transition duration-200 hover:-translate-y-0.5 hover:border-[var(--color-primary-green)] hover:shadow-[0_14px_30px_rgba(29,38,32,0.08)]" data-testid="comparator-tool-card">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-[var(--color-light-green)] text-lg font-semibold text-[var(--color-primary-green)]" aria-hidden="true">≈</span>

                    <h3 class="mt-6 text-xl font-semibold leading-7 text-[var(--color-text-primary)]">
                        {{ __('ui.homepage.comparator_title') }}
                    </h3>

                    <p class="mt-3 text-base leading-7 text-[var(--color-text-secondary)]">
                        {{ __('ui.homepage.comparator_description') }}
                    </p>

                    <a class="mt-auto inline-flex self-start rounded-lg bg-[var(--color-primary-green)] px-5 py-3 text-sm font-semibold text-white transition hover:opacity-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-green)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--color-surface)]" href="{{ route('comparator') }}" data-testid="open-comparator">
                        {{ __('ui.homepage.open_comparator') }}
                    </a>
                </article>

                <article class="flex min-h-56 flex-col rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 transition duration-200 hover:-translate-y-0.5 hover:border-[var(--color-primary-green)] hover:shadow-[0_14px_30px_rgba(29,38,32,0.08)]" data-testid="meals-tool-card">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-[var(--color-light-green)] text-lg font-semibold text-[var(--color-primary-green)]" aria-hidden="true">+</span>

                    <h3 class="mt-6 text-xl font-semibold leading-7 text-[var(--color-text-primary)]">
                        {{ __('ui.meals.title') }}
                    </h3>

                    <p class="mt-3 text-base leading-7 text-[var(--color-text-secondary)]">
                        {{ __('ui.homepage.meals_description') }}
                    </p>

                    <a class="mt-auto inline-flex self-start rounded-lg border border-[var(--color-primary-green)] px-5 py-3 text-sm font-semibold text-[var(--color-primary-green)] transition hover:bg-[var(--color-light-green)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-green)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--color-surface)]" href="{{ route('meals') }}" data-testid="open-meals">
                        {{ __('ui.homepage.open_meals') }}
                    </a>
                </article>
            </div>
        </section>
    </main>
@endcomponent
