@php
    $settings = \App\Models\Setting::map();

    // Balad and Neshan, the two iranian map apps. A logo is only shown once its
    // link is saved in the panel — an unset link would be a dead tile.
    $maps = array_values(array_filter([
        ['key' => 'balad', 'label' => 'ادرس بلد', 'url' => $settings['balad_url'] ?? null],
        ['key' => 'neshan', 'label' => 'ادرس نشان', 'url' => $settings['neshan_url'] ?? null],
    ], fn ($map) => filled($map['url'])));
@endphp

<footer class="menu-footer menu-shell">
    <x-logo class="menu-footer__mark" label="" />

    <p class="menu-footer__name">{{ $settings['cafe_name'] ?? 'کافه صاحبقرانیه' }}</p>

    @if ($hours = $settings['working_hours'] ?? null)
        <p>{{ $hours }}</p>
    @endif

    @if ($address = $settings['address'] ?? null)
        <p>{{ $address }}</p>
    @endif

    @if ($maps)
        <nav class="menu-footer__maps" aria-label="مسیریابی به کافه">
            @foreach ($maps as $map)
                <a href="{{ $map['url'] }}" target="_blank" rel="noopener" class="menu-footer__map">
                    <img src="{{ asset('images/'.$map['key'].'.svg') }}"
                         alt=""
                         class="menu-footer__map-logo"
                         width="40" height="40"
                         loading="lazy" decoding="async">
                    <span>{{ $map['label'] }}</span>
                </a>
            @endforeach
        </nav>
    @endif

    <div class="menu-footer__contact">
        @if ($phone = $settings['phone'] ?? null)
            <a href="tel:{{ \App\Support\Persian::western($phone) }}">{{ $phone }}</a>
        @endif

        @if ($instagram = $settings['instagram'] ?? null)
            <a href="https://instagram.com/{{ $instagram }}" target="_blank" rel="noopener" dir="ltr">
                {{ '@'.$instagram }}
            </a>
        @endif
    </div>

    <p class="menu-footer__legal">تمام حقوق برای کافه صاحبقرانیه محفوظ است.</p>
</footer>
