<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'cafe_name', 'value' => 'کافه صاحبقرانیه', 'group' => 'general', 'label' => 'نام کافه'],
            ['key' => 'cafe_name_latin', 'value' => 'SAHEB GHARANIYEH CAFE', 'group' => 'general', 'label' => 'نام لاتین'],
            ['key' => 'tagline', 'value' => 'قهوه، قلیان و شب‌های دلنشین تهران', 'group' => 'general', 'label' => 'شعار کافه'],
            [
                'key' => 'intro',
                'type' => 'text',
                'group' => 'general',
                'label' => 'معرفی کافه',
                'value' => 'در دل خیابانی آرام، کافه صاحبقرانیه میعادگاه دوستی‌های قدیمی است؛ '
                    .'جایی که عطر قهوه‌ی تازه دم با نقش‌های کاشی و نور فانوس‌ها در هم می‌آمیزد. '
                    .'از دمنوش‌های سنتی و شربت‌های خانگی تا قلیان‌های خوش‌طعم، هر سفارش با وسواس و '
                    .'مهمان‌نوازی ایرانی آماده می‌شود.',
            ],
            ['key' => 'working_hours', 'value' => 'هر روز از ۱۰ صبح تا ۱ بامداد', 'group' => 'contact', 'label' => 'ساعات کاری'],
            ['key' => 'address', 'value' => 'تهران، نیاوران، خیابان صاحبقرانیه', 'group' => 'contact', 'label' => 'نشانی'],
            ['key' => 'phone', 'value' => '۰۲۱-۱۲۳۴۵۶۷۸', 'group' => 'contact', 'label' => 'شماره تماس'],
            ['key' => 'instagram', 'value' => 'sahebgharaniyeh.cafe', 'group' => 'social', 'label' => 'اینستاگرام'],

            // No value: the owner pastes the two map links in the admin panel.
            // Also added by 2026_08_25_000001_add_navigation_link_settings, which
            // is what puts them on the server — deploy migrates, it never seeds.
            ['key' => 'balad_url', 'value' => 'https://balad.ir/p/PA8U4WiqGnyitG', 'type' => 'url', 'group' => 'navigation', 'label' => 'آدرس بلد'],
            ['key' => 'neshan_url', 'value' => 'https://nshn.ir/4e_b1xGB2B0Jq9', 'type' => 'url', 'group' => 'navigation', 'label' => 'آدرس نشان'],
        ];

        foreach ($settings as $setting) {
            // The value is seeded on create only. Re-running the seeder used to
            // overwrite every box the owner had edited in the panel with these
            // defaults; the shape of the row (label, group, type) is still kept
            // current, since that is ours to own and not editable in the panel.
            $row = Setting::firstOrNew(['key' => $setting['key']]);

            $existing = $row->exists ? $row->value : null;

            $row->fill($setting + ['type' => 'string']);

            if ($row->exists) {
                $row->value = $existing;
            }

            $row->save();
        }
    }
}
