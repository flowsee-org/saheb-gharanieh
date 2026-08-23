{{-- 413, which in this panel means one thing only: a photo larger than
     post_max_size. PHP throws the request body away before Laravel sees it — the
     token and every text field with it — so there is no form left to send the
     owner back to with an error on it. Hence a page of its own.

     Reaching this needs a file above post_max_size (12M, see public/.user.ini)
     with the size check in admin.js not running, so it should stay unseen.

     Its own shell rather than the admin layout, like the login page: an error page
     that leans on less is an error page with less to go wrong. --}}
<!DOCTYPE html>
<html lang="fa" dir="rtl" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#050404">
    <meta name="robots" content="noindex, nofollow">

    <title>تصویر بیش از حد بزرگ است | کافه صاحبقرانیه</title>

    <link rel="preload" href="{{ asset('fonts/vazirmatn-arabic-wght-normal.woff2') }}" as="font" type="font/woff2"
          crossorigin>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    @vite(['resources/css/app.css', 'resources/css/admin.css'])
</head>
<body class="admin-body admin-body--login">
    <main class="admin-login">
        <x-frame class="admin-login-card">
            <div class="admin-login-inner">
                <x-emblem class="admin-login-emblem" />

                <h1 class="admin-login-title">تصویر بیش از حد بزرگ است</h1>

                <x-ornament.divider width="w-28" />

                <p class="admin-login-note">
                    حجم فایلی که انتخاب شده از {{ \App\Support\UploadLimit::megabytesLabel() }} مگابایت بیشتر است و
                    سرور آن را نپذیرفت. یک بار به صفحهٔ قبل برگردید و عکس کوچک‌تری انتخاب کنید.
                </p>

                <a href="{{ url()->previous(route('admin.dashboard')) }}" class="admin-btn admin-btn--gold">
                    بازگشت
                </a>
            </div>
        </x-frame>
    </main>
</body>
</html>
