{{-- Ornate panel: double brand rule + four arabesque corners. Carries no fill. --}}
@props(['corners' => true])

<div {{ $attributes->class('frame') }}>
    @if ($corners)
        <x-ornament.corner class="frame-corner frame-corner--tr" />
        <x-ornament.corner class="frame-corner frame-corner--tl" />
        <x-ornament.corner class="frame-corner frame-corner--br" />
        <x-ornament.corner class="frame-corner frame-corner--bl" />
    @endif

    {{ $slot }}
</div>
