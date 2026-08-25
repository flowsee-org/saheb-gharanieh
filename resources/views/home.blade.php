<x-layouts.app title="منوی دیجیتال" :meta-description="$settings['intro'] ?? null">
    <main class="menu-home">
        <header class="menu-home__brand">
            <p class="menu-home__eyebrow">منوی دیجیتال</p>
            {{-- Still an h1, still carrying the café name — the name is now in
                 the mark's accessible label rather than set as visible type. --}}
            <h1 class="menu-home__title">
                <x-logo class="menu-home__mark" :label="$settings['cafe_name'] ?? 'کافه صاحبقرانیه'" />
            </h1>
            <p class="menu-brand__latin">{{ $settings['cafe_name_latin'] ?? 'Saheb Gharaniyeh Cafe' }}</p>
            <p class="menu-home__tagline">{{ $settings['tagline'] ?? 'قهوه، قلیان و شب‌های دلنشین' }}</p>
        </header>

        <section class="menu-home__categories" aria-labelledby="menu-home-heading">
            <div class="menu-home__section-head">
                <h2 id="menu-home-heading">دسته‌بندی‌های منو</h2>
                <span>{{ $cards->count() }} بخش</span>
            </div>

            <div class="menu-home__category-grid">
                @foreach ($cards as $card)
                    @php
                        $categoryImage = $card->image_path
                            ? (str_starts_with($card->image_path, 'http') ? $card->image_path : \Illuminate\Support\Facades\Storage::disk('public')->url($card->image_path))
                            : null;
                    @endphp
                    <a href="{{ route('menu.section', $card->slug) }}#{{ $card->slug }}" class="menu-home__category">
                        @if ($categoryImage)
                            <span class="menu-home__category-media">
                                <img src="{{ $categoryImage }}" alt="{{ $card->cardTitle() }}" loading="lazy" decoding="async">
                            </span>
                        @else
                            <span class="menu-home__category-icon-wrap">
                                <x-icon.section :category="$card" class="menu-home__category-icon" />
                            </span>
                        @endif

                        <span class="menu-home__category-body">
                            <span class="menu-home__category-title">{{ $card->cardTitle() }}</span>
                            @if ($card->card_subtitle)
                                <span class="menu-home__category-subtitle">{{ $card->card_subtitle }}</span>
                            @endif
                        </span>

                        <x-icon.chevron dir="start" class="menu-home__arrow h-4 w-4" />
                    </a>
                @endforeach
            </div>
        </section>

        <a href="{{ route('menu') }}" class="menu-home__all-link">
            <span>مشاهده منوی کامل</span>
            <x-icon.chevron dir="start" class="h-4 w-4" />
        </a>
    </main>
</x-layouts.app>
