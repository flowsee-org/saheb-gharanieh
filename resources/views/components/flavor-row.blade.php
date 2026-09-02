{{-- One hookah flavour row: index badge, hand-drawn glyph, Persian name. --}}
@props(['product', 'index' => 1])

<li class="flavor-row reveal text-right" style="--reveal-delay: {{ min($index * 30, 300) }}ms">
    <span class="num-badge">@fa($index)</span>

    <span class="flavor-name flex-1">{{ $product->name }}</span>

    @if ($product->hasPrice())
        <span class="price-value">@price($product->price)</span>
    @endif

    @if ($product->glyph)
        <x-icon.glyph :name="$product->glyph" class="flavor-glyph" />
    @endif
</li>
<style>
    .flavor-row .price-value { font-size: 1rem !important; }
    .flavor-row .flavor-name { font-size: 0.875rem !important; }
    @media (min-width: 40rem) {
        .flavor-row .flavor-name { font-size: 0.9375rem !important; }
        .flavor-row .price-value { font-size: 1.125rem !important; }
    }
    /* Theme-aware num-badge colors */
    html[data-theme="light"] .flavor-row .num-badge { background: var(--color-ink); color: var(--color-on-accent); }
    html[data-theme="dark"] .flavor-row .num-badge { background: var(--color-accent); color: var(--color-on-accent); }
</style>
