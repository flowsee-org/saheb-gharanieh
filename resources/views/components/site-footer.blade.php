@php
    $settings = \App\Models\Setting::map();

    // Balad and Neshan, the two iranian map apps. A logo is only shown once its
    // link is saved in the panel — an unset link would be a dead tile.
    $maps = array_values(array_filter([
        ['key' => 'balad', 'label' => 'آدرس بلد', 'url' => $settings['balad_url'] ?? null],
        ['key' => 'neshan', 'label' => 'آدرس نشان', 'url' => $settings['neshan_url'] ?? null],
    ], fn ($map) => filled($map['url'])));
@endphp

<footer class="relative mt-14 pb-10">
    <x-ornament.divider class="mb-6 px-6" />

    <div class="mx-auto flex max-w-md flex-col items-center gap-3 px-6 text-center">
        <x-logo class="w-32 sm:w-36 mt-4" label="" />

        <p class="latin text-[0.6875rem] text-latin">
            {{ $settings['cafe_name_latin'] ?? 'Saheb Gharaniyeh Cafe' }}
        </p>

        @if ($hours = $settings['working_hours'] ?? null)
            <p class="text-xs text-ink-dim">{{ $hours }}</p>
        @endif

        
        @if ($maps)
            <nav class="mt-2 flex flex-wrap items-center justify-center gap-x-4 gap-y-2" aria-label="مسیریابی به کافه">
                @foreach ($maps as $map)
                    <a href="{{ $map['url'] }}" target="_blank" rel="noopener" class="map-link flex flex-col items-center gap-1 px-3 py-2">
                        <img src="{{ asset('images/'.$map['key'].'.svg') }}"
                             alt=""
                             class="map-logo"
                             width="40" height="40"
                             loading="lazy" decoding="async">
                        <span class="text-[0.6875rem] text-ink-dim">{{ $map['key'] === 'balad' ? 'بلد' : 'نشان' }}</span>
                    </a>
                @endforeach
            </nav>
        @endif
        <style>
            .map-link { padding-inline: 1rem !important; }
        </style>

        <div class="mt-1 flex flex-wrap items-center justify-center gap-x-4 gap-y-2 text-xs">
            @if ($phone = $settings['phone'] ?? null)
                <a href="tel:{{ \App\Support\Persian::western($phone) }}"
                   class="text-accent-ink transition hover:text-ink">{{ $phone }}</a>
            @endif

            @if ($instagram = $settings['instagram'] ?? null)
                <a href="https://instagram.com/{{ $instagram }}" target="_blank" rel="noopener" dir="ltr"
                   class="latin text-[0.6875rem] text-latin transition hover:text-ink">
                    {{ '@'.$instagram }}
                </a>
            @endif
        </div>

        <p class="mt-3 text-[0.625rem] text-ink-faint">
            تمام حقوق برای کافه صاحبقرانیه محفوظ است.
        </p>
    </div>
</footer>
