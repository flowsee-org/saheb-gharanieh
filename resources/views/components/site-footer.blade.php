@php
    $settings = \App\Models\Setting::map();
@endphp

<footer class="menu-footer menu-shell">
    <p class="font-bold" style="color:var(--sg-text)">{{ $settings['cafe_name'] ?? 'کافه صاحبقرانیه' }}</p>

    @if ($hours = $settings['working_hours'] ?? null)
        <p>{{ $hours }}</p>
    @endif

    @if ($address = $settings['address'] ?? null)
        <p>{{ $address }}</p>
    @endif

    <div class="mt-3 flex flex-wrap items-center justify-center gap-x-4 gap-y-2">
        @if ($phone = $settings['phone'] ?? null)
            <a href="tel:{{ \App\Support\Persian::western($phone) }}" style="color:var(--sg-brand-soft)">{{ $phone }}</a>
        @endif

        @if ($instagram = $settings['instagram'] ?? null)
            <a href="https://instagram.com/{{ $instagram }}" target="_blank" rel="noopener" dir="ltr" style="color:var(--sg-brand-soft)">
                {{ '@'.$instagram }}
            </a>
        @endif
    </div>

    <p class="mt-4 opacity-60">تمام حقوق برای کافه صاحبقرانیه محفوظ است.</p>
</footer>
