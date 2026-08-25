{{-- The panel's front door. Deliberately its own page rather than the admin
     layout: there is no nav to show yet, and the emblem centred on black is
     the same welcome the site itself opens with. --}}
<!DOCTYPE html>
<html lang="fa" dir="rtl" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#050404">
    <meta name="robots" content="noindex, nofollow">

    <title>ورود به پنل | کافه صاحبقرانیه</title>

    <link rel="preload" href="{{ asset('fonts/vazirmatn-arabic-wght-normal.woff2') }}" as="font" type="font/woff2"
          crossorigin>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    @vite(['resources/css/app.css', 'resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="admin-body admin-body--login">
    <main class="admin-login">
        <x-frame class="admin-login-card">
            <div class="admin-login-inner">
                <x-logo class="admin-login-emblem" label="" />

                <h1 class="admin-login-title">کافه صاحبقرانیه</h1>
                <p class="admin-login-latin latin">Saheb Gharaniyeh</p>

                <x-ornament.divider width="w-28" />

                <p class="admin-login-note">برای مدیریت منو وارد شوید</p>

                <form method="POST" action="{{ route('admin.login.store') }}" class="admin-login-form">
                    @csrf

                    <x-admin.field label="نام کاربری" name="username" required>
                        <input type="text"
                               class="admin-input"
                               id="username"
                               name="username"
                               value="{{ old('username') }}"
                               autocomplete="username"
                               autocapitalize="none"
                               spellcheck="false"
                               dir="ltr"
                               required
                               autofocus>
                    </x-admin.field>

                    <x-admin.field label="رمز عبور" name="password" required>
                        <input type="password"
                               class="admin-input"
                               id="password"
                               name="password"
                               autocomplete="current-password"
                               dir="ltr"
                               required>
                    </x-admin.field>

                    <label class="admin-check">
                        <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                        <span>مرا به خاطر بسپار</span>
                    </label>

                    <button type="submit" class="admin-btn admin-btn--accent admin-login-submit">
                        <span>ورود</span>
                    </button>
                </form>

                <a href="{{ route('home') }}" class="admin-login-back">بازگشت به سایت</a>
            </div>
        </x-frame>
    </main>

    <x-admin.toasts />
</body>
</html>
