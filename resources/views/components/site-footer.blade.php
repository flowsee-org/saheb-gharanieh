@php
    $settings = \App\Models\Setting::map();
@endphp

<footer class="relative mt-14 pb-10">
    <x-ornament.divider class="mb-6 px-6" />

    <div class="mx-auto flex max-w-md flex-col items-center gap-3 px-6 text-center">
        <x-emblem class="w-24 text-ink-faint" />

        <p class="latin text-[0.6875rem] text-ink-dim">
            {{ $settings['cafe_name_latin'] ?? 'Saheb Gharaniyeh Cafe' }}
        </p>

        @if ($hours = $settings['working_hours'] ?? null)
            <p class="text-xs text-ink-dim">{{ $hours }}</p>
        @endif

        @if ($address = $settings['address'] ?? null)
            <p class="text-xs text-ink-dim">{{ $address }}</p>
        @endif

        <div class="mt-1 flex flex-wrap items-center justify-center gap-x-4 gap-y-2 text-xs">
            @if ($phone = $settings['phone'] ?? null)
                <a href="tel:{{ \App\Support\Persian::western($phone) }}"
                   class="text-accent-ink transition hover:text-ink">{{ $phone }}</a>
            @endif

            @if ($instagram = $settings['instagram'] ?? null)
                <a href="https://instagram.com/{{ $instagram }}" target="_blank" rel="noopener" dir="ltr"
                   class="latin text-[0.625rem] text-ink-dim transition hover:text-ink">
                    {{ '@'.$instagram }}
                </a>
            @endif
        </div>

        <p class="mt-3 text-[0.625rem] text-ink-faint">
            تمام حقوق برای کافه صاحبقرانیه محفوظ است.
        </p>
    </div>
</footer>
