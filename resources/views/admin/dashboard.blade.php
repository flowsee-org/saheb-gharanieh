<x-layouts.admin title="داشبورد" heading="داشبورد" subheading="یک نگاه کلی به منو">
    <x-slot:actions>
        <a href="{{ route('admin.products.create') }}" class="admin-btn admin-btn--accent">
            <x-icon.admin name="plus" class="h-4 w-4" />
            <span>مورد جدید</span>
        </a>
    </x-slot:actions>

    {{-- Headline figures. Each one links to the list already filtered, so a number
         that looks wrong is one tap from the items that make it up. --}}
    <div class="admin-stats">
        <x-admin.stat label="کل موارد منو" :value="$stats['products']" icon="items"
                      :href="route('admin.products.index')" />

        <x-admin.stat label="در حال نمایش" :value="$stats['products_active']" icon="eye" tone="ok"
                      :href="route('admin.products.index', ['status' => 'active'])" />

        <x-admin.stat label="پنهان" :value="$stats['products_hidden']" icon="hidden" tone="mute"
                      :href="route('admin.products.index', ['status' => 'hidden'])" />

        <x-admin.stat label="دسته‌ها" :value="$stats['categories']" icon="categories"
                      :href="route('admin.categories.index')"
                      :note="$stats['categories_hidden'] ? \App\Support\Persian::digits($stats['categories_hidden']).' پنهان' : null" />
    </div>

    {{-- The to-do row: only worth showing when there is something to do. --}}
    @if ($stats['products_no_price'] || $stats['products_no_image'] || $stats['products_sold_out'])
        <div class="admin-stats admin-stats--todo">
            @if ($stats['products_no_price'])
                <x-admin.stat label="بدون قیمت" :value="$stats['products_no_price']" icon="price" tone="warn"
                              :href="route('admin.products.index', ['status' => 'no-price'])" />
            @endif

            @if ($stats['products_no_image'])
                <x-admin.stat label="بدون تصویر" :value="$stats['products_no_image']" icon="image" tone="warn"
                              :href="route('admin.products.index', ['status' => 'no-image'])" />
            @endif

            @if ($stats['products_sold_out'])
                <x-admin.stat label="تمام شده" :value="$stats['products_sold_out']" icon="warning" tone="warn"
                              :href="route('admin.products.index', ['status' => 'sold-out'])" />
            @endif
        </div>
    @endif

    <div class="admin-grid-2">
        <x-admin.card title="موارد هر دسته" icon="categories">
            <x-slot:action>
                <a href="{{ route('admin.categories.index') }}" class="admin-link">مدیریت دسته‌ها</a>
            </x-slot:action>

            @forelse ($categories as $category)
                <a href="{{ route('admin.products.index', ['category' => $category->id]) }}"
                   class="admin-row admin-row--link">
                    <span class="admin-row-glyph">
                        <x-icon.glyph :name="\App\Support\Glyph::forCategory($category)" class="h-5 w-5" />
                    </span>

                    <span class="admin-row-main">
                        <span class="admin-row-title">{{ $category->name }}</span>
                        <span class="admin-row-meta">
                            @if (! $category->is_active)
                                <span class="admin-tag admin-tag--mute">پنهان</span>
                            @endif
                            @if ($category->products_count !== $category->active_products_count)
                                @fa($category->active_products_count)
                                از @fa($category->products_count) در نمایش
                            @endif
                        </span>
                    </span>

                    <span class="admin-row-count">@fa($category->products_count)</span>
                </a>
            @empty
                <x-admin.empty title="هنوز دسته‌ای ساخته نشده" icon="categories" />
            @endforelse
        </x-admin.card>

        <x-admin.card title="آخرین موارد اضافه‌شده" icon="sparkle">
            <x-slot:action>
                <a href="{{ route('admin.products.index', ['sort' => 'newest']) }}" class="admin-link">همه</a>
            </x-slot:action>

            @forelse ($recent as $product)
                <a href="{{ route('admin.products.edit', $product) }}" class="admin-row admin-row--link">
                    <span class="admin-row-thumb">
                        @if ($product->imageUrl())
                            <img src="{{ $product->imageUrl() }}" alt="" loading="lazy">
                        @else
                            <x-icon.glyph :name="$product->glyph ?: 'cup'" class="h-4 w-4" />
                        @endif
                    </span>

                    <span class="admin-row-main">
                        <span class="admin-row-title">{{ $product->name }}</span>
                        <span class="admin-row-meta">
                            {{ $product->category?->name }}
                            <span class="admin-dot" aria-hidden="true"></span>
                            {{ \App\Support\Persian::date($product->created_at) }}
                        </span>
                    </span>

                    <span class="admin-row-price">@price($product->price)</span>
                </a>
            @empty
                <x-admin.empty title="هنوز موردی اضافه نشده" text="اولین نوشیدنی یا طعم قلیان را اضافه کنید.">
                    <x-slot:action>
                        <a href="{{ route('admin.products.create') }}" class="admin-btn admin-btn--accent">
                            <x-icon.admin name="plus" class="h-4 w-4" />
                            <span>مورد جدید</span>
                        </a>
                    </x-slot:action>
                </x-admin.empty>
            @endforelse
        </x-admin.card>
    </div>

    @if ($needsAttention->isNotEmpty())
        <x-admin.card title="نیاز به تکمیل" subtitle="مواردی که قیمت یا تصویر ندارند" icon="warning">
            <div class="admin-attention">
                @foreach ($needsAttention as $product)
                    <a href="{{ route('admin.products.edit', $product) }}" class="admin-row admin-row--link">
                        <span class="admin-row-main">
                            <span class="admin-row-title">{{ $product->name }}</span>
                            <span class="admin-row-meta">{{ $product->category?->name }}</span>
                        </span>

                        <span class="admin-row-tags">
                            @unless ($product->hasPrice())
                                <span class="admin-tag admin-tag--warn">بدون قیمت</span>
                            @endunless
                            @unless ($product->hasImage())
                                <span class="admin-tag admin-tag--warn">بدون تصویر</span>
                            @endunless
                        </span>
                    </a>
                @endforeach
            </div>
        </x-admin.card>
    @endif
</x-layouts.admin>
