<x-layouts.admin title="حساب مدیر" heading="حساب مدیر"
                 subheading="نام کاربری و رمز عبور ورود به پنل">
    <div class="admin-form-grid admin-form-grid--narrow">
        <div class="admin-form-col">
            <form method="POST" action="{{ route('admin.account.update') }}" class="admin-form">
                @csrf
                @method('PUT')

                <x-admin.card title="مشخصات ورود" icon="account">
                    <x-admin.field label="نام نمایشی" name="name" required>
                        <input type="text" class="admin-input" id="name" name="name"
                               value="{{ old('name', $admin->name) }}" required maxlength="60">
                    </x-admin.field>

                    <x-admin.field label="نام کاربری" name="username" required
                                   hint="حرف لاتین، عدد، خط تیره و زیرخط.">
                        <input type="text" class="admin-input latin-input" id="username" name="username"
                               value="{{ old('username', $admin->username) }}"
                               dir="ltr" autocapitalize="none" spellcheck="false" required maxlength="60">
                    </x-admin.field>

                    <span class="admin-card-rule hairline" aria-hidden="true"></span>

                    <p class="admin-note admin-note--tight">
                        <x-icon.admin name="warning" class="h-4 w-4 shrink-0" />
                        <span>
                            برای تغییر رمز، هر سه کادر زیر را پر کنید. اگر فقط نام را عوض می‌کنید،
                            این سه کادر را خالی بگذارید.
                        </span>
                    </p>

                    <x-admin.field label="رمز عبور فعلی" name="current_password">
                        <input type="password" class="admin-input" id="current_password" name="current_password"
                               dir="ltr" autocomplete="current-password">
                    </x-admin.field>

                    <x-admin.field label="رمز عبور جدید" name="password" hint="حداقل ۶ نویسه.">
                        <input type="password" class="admin-input" id="password" name="password"
                               dir="ltr" autocomplete="new-password">
                    </x-admin.field>

                    <x-admin.field label="تکرار رمز عبور جدید" name="password_confirmation">
                        <input type="password" class="admin-input" id="password_confirmation"
                               name="password_confirmation" dir="ltr" autocomplete="new-password">
                    </x-admin.field>
                </x-admin.card>

                <div class="admin-form-bar">
                    <button type="submit" class="admin-btn admin-btn--accent">
                        <x-icon.admin name="check" class="h-4 w-4" />
                        <span>ذخیرهٔ حساب</span>
                    </button>
                </div>
            </form>
        </div>

        <div class="admin-form-col">
            <x-admin.card title="این حساب" icon="settings">
                <dl class="admin-facts">
                    <div>
                        <dt>نام کاربری</dt>
                        <dd class="latin-input" dir="ltr">{{ $admin->username }}</dd>
                    </div>
                    <div>
                        <dt>آخرین ورود</dt>
                        <dd>{{ \App\Support\Persian::dateTime($admin->last_login_at) }}</dd>
                    </div>
                    <div>
                        <dt>ساخته شده در</dt>
                        <dd>{{ \App\Support\Persian::date($admin->created_at) }}</dd>
                    </div>
                </dl>

                <span class="admin-card-rule hairline" aria-hidden="true"></span>

                <p class="admin-hint">
                    اگر رمز را فراموش کردید، از روی سرور این دستور رمز تازه می‌سازد:
                </p>
                <code class="admin-code" dir="ltr">php artisan admin:password {{ $admin->username }}</code>
            </x-admin.card>

            <x-admin.card title="خروج" icon="logout">
                <p class="admin-hint">با خروج، دفعهٔ بعد باید نام کاربری و رمز را دوباره وارد کنید.</p>

                <form method="POST" action="{{ route('admin.logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="admin-btn admin-btn--ghost">
                        <x-icon.admin name="logout" class="h-4 w-4" />
                        <span>خروج از پنل</span>
                    </button>
                </form>
            </x-admin.card>
        </div>
    </div>
</x-layouts.admin>
