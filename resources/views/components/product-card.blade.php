{{-- One menu item: empty (or uploaded) photo, Persian name, price slot. --}}
@props(['product', 'category', 'index' => 1])

<article
    class="product-card reveal"
    style="--reveal-delay: {{ min($index * 45, 360) }}ms; --sheen-delay: {{ ($index % 6) * 0.55 }}s"
    @if (! $product->is_available) data-unavailable="true" @endif
>
    <div class="media">
        @if ($url = $product->imageUrl())
            <img src="{{ $url }}" alt="{{ $product->name }}" loading="lazy" decoding="async" data-fade-in>
        @else
            {{-- The item's own glyph when it has one, otherwise the section's. --}}
            <x-icon.glyph :name="$product->glyph ?: \App\Support\Glyph::forCategory($category)" class="media-glyph" />
            <span class="media-note">تصویر به‌زودی</span>
        @endif

        @if (! $product->is_available)
            <span class="absolute inset-0 z-3 grid place-items-center bg-page/70 text-[0.6875rem] font-bold text-ink">
                موقتاً تمام شد
            </span>
        @endif
    </div>

    <div class="flex items-start gap-1.5">
        <span class="num-badge mt-0.5">@fa($index)</span>

        <div class="min-w-0 flex-1">
            <h3 class="product-name">{{ $product->name }}</h3>
            @if ($product->latin_name)
                <p class="product-latin mt-0.5">{{ $product->latin_name }}</p>
            @endif
        </div>
    </div>

    @if ($product->description)
        <p class="text-[0.6875rem] leading-relaxed text-ink-dim">{{ $product->description }}</p>
    @endif

    <x-price-tag :price="$product->price" />
</article>
