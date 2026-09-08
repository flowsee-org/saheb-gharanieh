{{-- Price slot. Renders the value when set, otherwise a clean dotted placeholder
     so the layout never shifts once real prices are entered in the admin panel. --}}
@props(['price' => null, 'label' => 'قیمت:'])

<div class="price-slot">
    <span class="price-label">{{ $label }}</span>

    @if (! is_null($price))
        <span class="price-value">@price($price)</span>
    @else
        <span class="price-empty" aria-hidden="true"></span>
        <span class="sr-only">قیمت به‌زودی</span>
    @endif
</div>
