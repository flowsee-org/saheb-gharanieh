<x-layouts.app title="منوی دیجیتال" :meta-description="$settings['intro'] ?? null">
    <main class="mx-auto w-full max-w-2xl px-4 pb-4 pt-10 sm:px-6 sm:pt-14">

        {{-- ─── Hero ─────────────────────────────────────────────────────── --}}
        <header class="relative text-center">
            <div class="pointer-events-none absolute inset-x-0 -top-10 flex justify-center" aria-hidden="true">
                <x-ornament.arabesque class="w-64 text-ink-dim opacity-[0.06] sm:w-80" />
            </div>

            <div class="reveal relative flex flex-col items-center gap-3">
                {{-- The mark *is* the name — it carries the calligraphy — so the
                     heading holds it instead of repeating the words underneath. --}}
                <h1 class="m-0">
                    <x-logo class="w-44 sm:w-52" :label="$settings['cafe_name'] ?? 'کافه صاحبقرانیه'" />
                </h1>

                <p class="latin text-[0.625rem] text-latin sm:text-xs">
                    {{ $settings['cafe_name_latin'] ?? 'Saheb Gharaniyeh Cafe' }}
                </p>

                <x-ornament.divider class="mt-1 max-w-[16rem] sm:max-w-xs" />

                <p class="text-sm text-ink sm:text-base">
                    {{ $settings['tagline'] ?? 'قهوه، قلیان و شب‌های دلنشین' }}
                </p>
            </div>
        </header>

        {{-- ─── Section cards ────────────────────────────────────────────── --}}
        {{-- Kept directly under the hero: the cards are why people open the page. --}}
        <section class="mt-7 sm:mt-9" aria-labelledby="menu-heading">
            <div class="reveal flex flex-col items-center gap-2">
                <h2 id="menu-heading" class="text-lg font-bold text-ink sm:text-2xl">منوی کافه</h2>
                <p class="text-[0.6875rem] text-ink-dim">یک دسته را انتخاب کنید</p>
                <x-ornament.divider class="mt-1 max-w-[12rem]" />
            </div>

            <div class="mt-6 grid gap-3.5 sm:gap-4">
                @foreach ($cards as $index => $card)
                    <a href="{{ route('menu.section', $card->slug) }}#{{ $card->slug }}"
                       class="hero-card reveal group"
                       style="--reveal-delay: {{ 100 + $index * 110 }}ms"
                       aria-label="مشاهده {{ $card->cardTitle() }}">
                        <span class="relative z-2 flex items-center gap-4 px-4 py-5 sm:px-6 sm:py-6">
                            <x-icon.section :category="$card" class="hero-card-glyph" />

                            <span class="min-w-0 flex-1">
                                <span class="block text-base font-bold text-ink sm:text-xl">
                                    {{ $card->cardTitle() }}
                                </span>

                                @if ($card->card_subtitle)
                                    <span class="mt-1 block text-[0.6875rem] leading-relaxed text-ink-dim sm:text-xs">
                                        {{ $card->card_subtitle }}
                                    </span>
                                @endif

                                @if ($latin = $card->card_latin ?? $card->latin_name)
                                    {{-- Pure latin run: keeps word order without dir="ltr", so it stays right-aligned. --}}
                                    <span class="latin mt-1.5 block text-[0.5625rem] text-latin sm:text-[0.625rem]">
                                        {{ $latin }}
                                    </span>
                                @endif
                            </span>

                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-hair bg-sunk text-accent-ink transition-transform duration-500 group-hover:-translate-x-1">
                                <x-icon.chevron dir="start" class="h-4 w-4" />
                            </span>
                        </span>

                        {{-- hairline top glow --}}
                        <span class="absolute inset-x-0 top-0 h-px bg-gradient-to-l from-transparent via-rule to-transparent"></span>
                    </a>
                @endforeach
            </div>

            <div class="reveal mt-6 text-center" style="--reveal-delay: 420ms">
                <a href="{{ route('menu') }}"
                   class="inline-flex items-center gap-2 rounded-full border border-hair px-5 py-2.5 text-xs font-semibold text-ink transition hover:border-rule hover:bg-card">
                    مشاهده کل منو
                    <x-icon.chevron dir="start" class="h-3.5 w-3.5" />
                </a>
            </div>
        </section>

        {{-- ─── About the café ───────────────────────────────────────────── --}}
        {{-- Sits below the cards on purpose: read it after you have picked a section. --}}
        @if ($intro = $settings['intro'] ?? null)
            <x-frame class="reveal mt-9 px-5 py-6 sm:mt-11 sm:px-8 sm:py-7">
                <p class="text-center text-[0.8125rem] leading-[2] text-ink sm:text-sm sm:leading-[2.1]">
                    {{ $intro }}
                </p>

                <div class="mt-5 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-[0.6875rem] text-ink-dim">
                    @if ($hours = $settings['working_hours'] ?? null)
                        <span class="inline-flex items-center gap-1.5">
                            <span class="h-1 w-1 rounded-full bg-accent"></span>{{ $hours }}
                        </span>
                    @endif
                    @if ($address = $settings['address'] ?? null)
                        <span class="inline-flex items-center gap-1.5">
                            <span class="h-1 w-1 rounded-full bg-accent"></span>{{ $address }}
                        </span>
                    @endif
                </div>
            </x-frame>
        @endif
    </main>
</x-layouts.app>
