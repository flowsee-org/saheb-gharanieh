{{-- One hookah flavour row: index badge, hand-drawn glyph, Persian name. --}}
@props(['product', 'index' => 1])

<li class="flavor-row reveal" style="--reveal-delay: {{ min($index * 30, 300) }}ms">
    @if ($product->glyph)
        <x-icon.glyph :name="$product->glyph" class="flavor-glyph" />
    @endif

    <span class="num-badge">@fa($index)</span>

    <span class="flavor-name flex-1">{{ $product->name }}</span>

    @if ($product->hasPrice())
        <span class="price-value">@price($product->price)</span>
    @endif
</li>
