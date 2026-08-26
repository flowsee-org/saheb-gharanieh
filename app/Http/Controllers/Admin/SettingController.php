<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingsRequest;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/** The café's own copy: name, intro, hours, address, phone, Instagram, map links. */
class SettingController extends Controller
{
    /** Group headings, in the order the form shows them. */
    private const GROUPS = [
        'general' => 'معرفی کافه',
        'contact' => 'تماس و ساعات کاری',
        'social' => 'شبکه‌های اجتماعی',
        'navigation' => 'مسیریابی',
    ];

    public function edit(): View
    {
        $settings = Setting::query()
            ->orderBy('id')
            ->get()
            ->groupBy('group');

        return view('admin.settings', [
            // Known groups first, then anything a future seeder adds.
            'groups' => collect(self::GROUPS)
                ->merge($settings->keys()->diff(array_keys(self::GROUPS))->mapWithKeys(fn ($key) => [$key => $key]))
                ->filter(fn ($label, $key) => $settings->has($key))
                ->all(),
            'settings' => $settings,
        ]);
    }

    public function update(SettingsRequest $request): RedirectResponse
    {
        foreach ($request->values() as $key => $value) {
            Setting::put($key, is_bool($value) ? ($value ? '1' : '0') : $value);
        }

        return back()->with('status', 'متن‌های سایت ذخیره شد.');
    }
}
