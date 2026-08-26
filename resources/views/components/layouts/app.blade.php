@props(['title' => null, 'metaDescription' => null])

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0D1126">
    <meta name="description" content="{{ $metaDescription ?? 'منوی دیجیتال کافه صاحبقرانیه — نوشیدنی‌های گرم، نوشیدنی‌های سرد و قلیان' }}">

    <title>{{ $title ? $title.' | کافه صاحبقرانیه' : 'کافه صاحبقرانیه' }}</title>

    <script>
        (function () {
            var theme = 'dark';
            try {
                if (localStorage.getItem('sg-theme') === 'light') theme = 'light';
            } catch (e) {}
            document.documentElement.dataset.theme = theme;
            document.querySelector('meta[name="theme-color"]')?.setAttribute('content', theme === 'light' ? '#FFFFFF' : '#0D1126');
        })();
    </script>

    <link rel="preload" href="{{ asset('fonts/vazirmatn-arabic-wght-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    @vite([
         'resources/css/app.css',
        'resources/css/brand.css',
        'resources/css/theme-overrides.css',
        'resources/css/menu-redesign.css',
        'resources/js/app.js',
        'resources/js/menu-redesign.js',
    ])
</head>
<body class="min-h-dvh antialiased">
    {{ $slot }}

    <x-site-footer />
    <x-theme-toggle />
</body>
</html>
