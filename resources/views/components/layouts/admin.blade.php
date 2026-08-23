{{-- The café panel shell. Dark only: the owner works behind the bar at night,
     and one palette keeps the panel visually part of the printed-menu site. --}}
@props(['title' => null, 'heading' => null, 'subheading' => null])

<!DOCTYPE html>
<html lang="fa" dir="rtl" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#050404">
    <meta name="robots" content="noindex, nofollow">

    <title>{{ $title ? $title.' | پنل کافه' : 'پنل کافه صاحبقرانیه' }}</title>

    <link rel="preload" href="{{ asset('fonts/vazirmatn-arabic-wght-normal.woff2') }}" as="font" type="font/woff2"
          crossorigin>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    @vite(['resources/css/app.css', 'resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="admin-body">
    <a href="#admin-main" class="admin-skip">رفتن به محتوا</a>

    <header class="admin-bar">
        <div class="admin-bar-inner">
            <a href="{{ route('admin.dashboard') }}" class="admin-brand">
                <x-emblem class="admin-brand-emblem" />
                <span class="admin-brand-text">
                    <span class="admin-brand-name">پنل کافه</span>
                    <span class="admin-brand-latin latin">Saheb Gharaniyeh</span>
                </span>
            </a>

            <x-admin.nav class="admin-nav--bar" />

            <div class="admin-bar-actions">
                <a href="{{ route('home') }}" target="_blank" rel="noopener" class="admin-icon-btn"
                   title="دیدن سایت" aria-label="دیدن سایت">
                    <x-icon.admin name="eye" class="h-4 w-4" />
                </a>

                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="admin-icon-btn" title="خروج" aria-label="خروج از پنل">
                        <x-icon.admin name="logout" class="h-4 w-4" />
                    </button>
                </form>
            </div>
        </div>

        <span class="admin-bar-rule" aria-hidden="true"></span>
    </header>

    <main id="admin-main" class="admin-main">
        @if ($heading || $subheading)
            <div class="admin-head">
                <div class="admin-head-titles">
                    @if ($heading)
                        <h1 class="admin-head-title">{{ $heading }}</h1>
                    @endif
                    @if ($subheading)
                        <p class="admin-head-sub">{{ $subheading }}</p>
                    @endif
                </div>

                @isset($actions)
                    <div class="admin-head-actions">{{ $actions }}</div>
                @endisset
            </div>
        @endif

        {{ $slot }}
    </main>

    <x-admin.nav class="admin-nav--tabs" />

    <x-admin.toasts />
    <x-admin.confirm />
</body>
</html>
