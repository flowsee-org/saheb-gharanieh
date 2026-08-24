@props(['product', 'category', 'index' => 1])

@php($image = $product->imageUrl())

<article
    class="menu-product{{ $image ? ' menu-product--image' : '' }}"
    @if (! $product->is_available) data-unavailable="true" @endif
>
    @if ($image)
        <div class="menu-product__media">
            <img src="{{ $image }}" alt="{{ $product->name }}" loading="lazy" decoding="async" onerror="this.closest('.menu-product__media')?.remove(); this.closest('.menu-product')?.classList.remove('menu-product--image')">
        </div>
    @else
        <div class="menu-product__media menu-product__media--empty" aria-hidden="true">
            <x-icon.glyph :name="$product->glyphKey()" class="h-7 w-7" />
        </div>
    @endif

    <div class="menu-product__main">
        <div class="menu-product__topline">
            @if ($product->is_featured)
                <span class="menu-product__badge">پیشنهاد</span>
            @endif
            <h3 class="menu-product__name">{{ $product->name }}</h3>
        </div>

        @if ($product->latin_name)
            <p class="menu-product__latin">{{ $product->latin_name }}</p>
        @endif

        @if ($product->description)
            <p class="menu-product__description">{{ $product->description }}</p>
        @endif

        @if (! $product->is_available)
            <p class="menu-product__availability">موقتاً ناموجود</p>
        @endif
    </div>

    <div class="menu-product__price">
        @if ($product->price)
            <strong>@price($product->price)</strong>
            <span class="menu-product__unit">تومان</span>
        @else
            <span class="menu-product__unit">قیمت در محل</span>
        @endif
    </div>
</article>
