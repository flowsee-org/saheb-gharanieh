{{-- Hairline rule with a central lozenge — the separator used throughout the printed menu. --}}
@props(['width' => 'w-full'])

<div class="flex items-center justify-center gap-2 {{ $width }}" aria-hidden="true">
    <span class="hairline h-px flex-1"></span>

    {{-- Same tint as the rules it sits between, so the three read as one line. --}}
    <svg viewBox="0 0 34 14" class="h-3 w-[34px] shrink-0 text-rule" fill="none">
        <path d="M17 1.6 22.4 7 17 12.4 11.6 7 17 1.6Z" stroke="currentColor" stroke-width="1" />
        <circle cx="17" cy="7" r="1.6" fill="currentColor" />
        <path d="M8.5 7H1M33 7h-7.5" stroke="currentColor" stroke-width="1" stroke-linecap="round" opacity=".7" />
    </svg>

    <span class="hairline h-px flex-1"></span>
</div>
