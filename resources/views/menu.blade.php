<x-layouts.app title="منوی کافه">
    <div class="menu-page">
        <header class="menu-header menu-shell">
            <div class="menu-brand">
                <a href="{{ route('home') }}" class="menu-brand__identity" aria-label="صفحه اصلی کافه صاحبقرانیه">
                    {{-- The mark carries the café name here; the anchor already
                         names itself, so the logo stays decorative. --}}
                    <x-logo class="menu-brand__mark" label="" />
                    <span class="menu-brand__latin">Saheb Gharaniyeh Cafe</span>
                </a>
                <span class="menu-brand__meta"><span class="menu-brand__status" aria-hidden="true"></span>منوی امروز</span>
            </div>

            <div class="menu-intro">
                <p class="menu-intro__eyebrow">به صاحبقرانیه خوش آمدید</p>
                <h1>منوی کافه</h1>
                <p>نوشیدنی‌ها و انتخاب‌های صاحبقرانیه</p>
                <hr class="menu-rule">
            </div>

            <nav class="menu-category-nav" aria-label="دسته‌بندی‌های منو">
                <div class="menu-category-nav__scroll" id="section-chips">
                    <a href="#menu-all" class="menu-category-link" data-chip="menu-all" aria-current="{{ empty($activeSection) ? 'true' : 'false' }}">همه</a>
                    @foreach ($categories as $category)
                        <a href="#{{ $category->slug }}"
                           class="menu-category-link"
                           data-chip="{{ $category->slug }}"
                           aria-current="{{ $category->slug === $activeSection ? 'true' : 'false' }}">
                            {{ $category->shortName() }}
                        </a>
                    @endforeach
                </div>
            </nav>
        </header>

        <main class="menu-shell" id="menu-root" data-initial-section="{{ $activeSection }}">
            @forelse ($categories as $category)
                <section id="{{ $category->slug }}"
                         class="menu-section"
                         data-section="{{ $category->slug }}"
                         data-section-name="{{ $category->name }}"
                         aria-labelledby="heading-{{ $category->slug }}">
                    <div class="menu-section__heading">
                        <div class="menu-section__title-wrap">
                            @if ($category->subtitle)
                                <p class="menu-section__eyebrow">{{ $category->subtitle }}</p>
                            @endif
                            <h2 class="menu-section__title" id="heading-{{ $category->slug }}">{{ $category->name }}</h2>
                            @if ($category->latin_name)
                                <p class="menu-section__latin">{{ $category->latin_name }}</p>
                            @endif
                        </div>
                        <span class="menu-section__count">{{ $category->activeProducts->count() }} آیتم</span>
                    </div>

                    @if ($category->description)
                        <p class="menu-section__description">{{ $category->description }}</p>
                    @endif

                    @if ($category->activeProducts->isEmpty())
                        <p class="menu-empty">این بخش به‌زودی تکمیل می‌شود.</p>
                    @else
                        <div class="menu-items">
                            @foreach ($category->activeProducts as $product)
                                <x-product-card :product="$product" :category="$category" :index="$loop->iteration" />
                            @endforeach
                        </div>

                        {{-- List sections (the two hookah services) used to carry
                             one price for the whole section here. They price per
                             flavour now, the same as the drinks, so only the
                             what's-included tags are left. --}}
                        @if (!$category->usesGrid() && $category->features->isNotEmpty())
                            <div class="menu-features" aria-label="همراه سرویس">
                                @foreach ($category->features as $feature)
                                    <span class="menu-feature">
                                        <x-icon.glyph :name="$feature->glyph" class="h-3.5 w-3.5" />
                                        {{ $feature->name }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </section>
            @empty
                <p class="menu-empty">منو در حال آماده‌سازی است.</p>
            @endforelse
        </main>
    </div>
</x-layouts.app>
