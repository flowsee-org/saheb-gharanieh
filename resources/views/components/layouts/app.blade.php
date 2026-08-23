@props(['title' => null, 'metaDescription' => null])

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#1A1A1A">
    <meta name="description"
          content="{{ $metaDescription ?? 'منوی دیجیتال کافه صاحبقرانیه — نوشیدنی‌های گرم، نوشیدنی‌های سرد و قلیان' }}">

    <title>{{ $title ? $title.' | کافه صاحبقرانیه' : 'کافه صاحبقرانیه' }}</title>

    {{-- Theme before first paint: dark unless the visitor chose light. Inline on
         purpose — the bundle is deferred, which would flash the wrong palette. --}}
    <script>
        (function () {
            var theme = 'dark';
            try {
                if (localStorage.getItem('sg-theme') === 'light') theme = 'light';
            } catch (e) { /* private mode: stay on the house theme */ }

            document.documentElement.dataset.theme = theme;
            document.querySelector('meta[name="theme-color"]')
                ?.setAttribute('content', theme === 'light' ? '#F2F2F2' : '#1A1A1A');
        })();
    </script>

    <link rel="preload" href="{{ asset('fonts/vazirmatn-arabic-wght-normal.woff2') }}" as="font" type="font/woff2"
          crossorigin>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh antialiased">
    {{-- Loading veil: emblem inside spinning accent rings, lifted on window load. --}}
    <div class="preloader" id="preloader" role="status" aria-live="polite">
        <div class="flex flex-col items-center gap-3">
            <div class="preloader-ring">
                <x-emblem class="w-12 text-ink-dim" />
            </div>
            <p class="text-sm font-bold text-ink">کافه صاحبقرانیه</p>
            <p class="latin text-[0.5625rem] tracking-[0.3em] text-ink-dim">Saheb Gharaniyeh</p>
            <p class="preloader-dots mt-1" aria-label="در حال بارگذاری">
                <span></span><span></span><span></span>
            </p>
        </div>
    </div>

    {{ $slot }}

    <x-site-footer />

    <x-theme-toggle />

    <button type="button" class="to-top" id="to-top" aria-label="بازگشت به بالا">
        <x-icon.chevron dir="up" class="h-5 w-5" />
    </button>
</body>
</html>
