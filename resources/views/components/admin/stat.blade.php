{{-- A number and what it counts. `tone` tints the figure when it is a to-do
     rather than a fact (items without a price, hidden sections …). --}}
@props(['label', 'value', 'icon' => null, 'tone' => 'accent', 'href' => null, 'note' => null])

@php $tag = $href ? 'a' : 'div'; @endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif
    {{ $attributes->class(['admin-stat', 'admin-stat--'.$tone, 'admin-stat--link' => (bool) $href]) }}>

    @if ($icon)
        <span class="admin-stat-icon" aria-hidden="true">
            <x-icon.admin :name="$icon" class="h-4 w-4" />
        </span>
    @endif

    <span class="admin-stat-value">{{ \App\Support\Persian::digits($value) }}</span>
    <span class="admin-stat-label">{{ $label }}</span>

    @if ($note)
        <span class="admin-stat-note">{{ $note }}</span>
    @endif
</{{ $tag }}>
