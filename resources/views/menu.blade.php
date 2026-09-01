<x-layouts.app title="منو">
    {{-- ─── Sticky bar: café mark, live section flag, chip nav, progress ──── --}}
    <div class="topbar" id="topbar" data-scrolled="false">
        <div class="mx-auto w-full max-w-3xl px-3 sm:px-6 relative">
            {{-- Logo at true viewport top-right (visual right in RTL), outside the centered container --}}
            <a href="{{ route('home') }}" class="topbar-logo flex shrink-0 items-center" aria-label="صفحه اصلی">
                <x-logo class="w-12 sm:w-14" label="" />
            </a>

            {{-- Centered content: section flag + chips --}}
            <div class="flex flex-col items-center gap-2 pt-2.5 pb-2">
                {{-- Always reflects the section currently in view. --}}
                <p class="section-flag" id="section-flag" aria-live="polite">
                    <span class="flag-dot"></span>
                    <span class="section-flag-text" id="section-flag-text">
                        {{ $categories->firstWhere('slug', $activeSection)?->name ?? $categories->first()?->name }}
                    </span>
                </p>

                <nav class="chips pb-2" id="section-chips" aria-label="بخش‌های منو">
                    @foreach ($categories as $category)
                        <a href="#{{ $category->slug }}"
                           class="chip"
                           data-chip="{{ $category->slug }}"
                           aria-current="{{ $loop->first ? 'true' : 'false' }}">
                            @if ($category->glyph)
                                <x-icon.glyph :name="$category->glyph" class="chip-glyph" />
                            @endif
                            {{ $category->shortName() }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>

        <span class="topbar-progress" id="topbar-progress" aria-hidden="true"></span>
    </div>

    <main class="mx-auto w-full max-w-3xl px-2.5 pb-6 pt-[104px] sm:px-6 sm:pt-[116px]"
          id="menu-root"
          data-initial-section="{{ $activeSection }}">

        @forelse ($categories as $category)
            {{-- Each section is a printed-menu panel: ornate frame, title inside. --}}
            <section id="{{ $category->slug }}"
                     class="section-anchor scroll-section mt-4 first:mt-0"
                     data-section="{{ $category->slug }}"
                     data-section-name="{{ $category->name }}"
                     aria-labelledby="heading-{{ $category->slug }}">

                <x-frame class="px-3 py-6 sm:px-6 sm:py-8">
                    {{-- ─── Panel heading ────────────────────────────────── --}}
                    <header class="reveal relative mb-5 text-center">

                        <h2 id="heading-{{ $category->slug }}"
                            class="inline-flex items-center gap-2 text-xl font-black text-ink sm:text-3xl">
                            <x-icon.section :category="$category" class="heading-glyph" />
                            {{ $category->name }}
                        </h2>

                        <x-ornament.divider class="mx-auto mt-3 max-w-[14rem] sm:max-w-sm" />
                    </header>

                    {{-- ─── Items ────────────────────────────────────────── --}}
                    @if ($category->activeProducts->isEmpty())
                        <p class="py-6 text-center text-xs text-ink-dim">این بخش به‌زودی تکمیل می‌شود.</p>

                    @elseif ($category->usesGrid())
                        <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 sm:gap-3.5 lg:grid-cols-4 mb-8">
                            @foreach ($category->activeProducts as $product)
                                <x-product-card :product="$product" :category="$category" :index="$loop->iteration" />
                            @endforeach
                        </div>

                    @else
                        <p class="mb-3.5 text-center text-xs font-bold text-accent-ink">طعم‌های قلیان</p>

                        <ul class="grid grid-cols-2 gap-1.5 sm:grid-cols-3 sm:gap-2.5 mb-8">
                            @foreach ($category->activeProducts as $product)
                                <x-flavor-row :product="$product" :index="$loop->iteration" />
                            @endforeach
                        </ul>

                        {{-- Service price for the whole section --}}
                        <div class="service-price reveal mt-4">
                            <span class="text-xs text-ink-dim">{{ $category->price_note ?? 'قیمت' }}</span>
                            @if ($category->price)
                                <span class="price-value text-sm">@price($category->price)</span>
                            @else
                                <span class="text-[0.6875rem] text-ink-dim">در محل از پرسنل بپرسید</span>
                            @endif
                        </div>

                        {{-- Extras bundled with the service (Super Deluxe) --}}
                        @if ($category->features->isNotEmpty())
                            <div class="mt-5">
                                <x-ornament.divider class="mb-3" />
                                <p class="mb-3 text-center text-[0.6875rem] font-semibold text-ink-dim">
                                    همراه با این سرویس شامل
                                </p>

                                <div class="grid grid-cols-4 gap-2 sm:grid-cols-8">
                                    @foreach ($category->features as $feature)
                                        <div class="feature-pill reveal"
                                             style="--reveal-delay: {{ $loop->iteration * 40 }}ms">
                                            <span class="feature-name">{{ $feature->name }}</span>
                                            <x-icon.glyph :name="$feature->glyph" class="feature-glyph" />
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif
                </x-frame>
            </section>
        @empty
            <x-frame class="px-6 py-12 text-center">
                <p class="text-sm text-ink-dim">منو در حال آماده‌سازی است.</p>
            </x-frame>
        @endforelse
    </main>
</x-layouts.app>
