<?php

namespace Database\Seeders;

use App\Enums\CategoryKind;
use App\Enums\CategoryLayout;
use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * The four sections of the printed menu, in the order they appear on the site.
 */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'slug' => 'hot-drinks',
                'name' => 'نوشیدنی‌های گرم',
                'short_name' => 'گرم',
                'latin_name' => 'HOT DRINKS',
                'subtitle' => 'منوی نوشیدنی‌های گرم',
                'description' => 'اسپرسوی تازه دم، لاته‌های امضای کافه و دمنوش‌های سنتی ایرانی.',
                'kind' => CategoryKind::Drink,
                'layout' => CategoryLayout::Grid,
                'glyph' => 'cup',
                'sort_order' => 1,
                'card_order' => 1,
                'card_title' => 'نوشیدنی‌های گرم',
                'card_subtitle' => 'قهوه، لاته، چای و دمنوش',
                'card_latin' => 'HOT DRINKS',
            ],
            [
                'slug' => 'cold-drinks',
                'name' => 'نوشیدنی‌های سرد',
                'short_name' => 'سرد',
                'latin_name' => 'COLD DRINKS',
                'subtitle' => 'منوی نوشیدنی‌های سرد',
                'description' => 'موهیتوها، شربت‌های سنتی، آب‌میوه‌های طبیعی و فراپه‌های خنک.',
                'kind' => CategoryKind::Drink,
                'layout' => CategoryLayout::Grid,
                'glyph' => 'glass',
                'sort_order' => 2,
                'card_order' => 2,
                'card_title' => 'نوشیدنی‌های سرد',
                'card_subtitle' => 'موهیتو، شربت، آب‌میوه و فراپه',
                'card_latin' => 'COLD DRINKS',
            ],
            [
                'slug' => 'hookah-normal',
                'name' => 'قلیان — سرویس معمولی',
                'short_name' => 'قلیان معمولی',
                'latin_name' => 'NORMAL SERVICE',
                'subtitle' => 'منوی قلیان',
                'description' => 'شانزده طعم اصیل قلیان با ذغال آماده و سرویس‌دهی در طول شب.',
                'kind' => CategoryKind::Hookah,
                'layout' => CategoryLayout::List,
                'glyph' => 'hookah',
                'price_note' => 'قیمت هر سرویس',
                'sort_order' => 3,
                'card_order' => 3,
                'card_title' => 'قلیان',
                'card_subtitle' => 'سرویس معمولی و سوپر ویژه',
                'card_latin' => 'HOOKAH',
            ],
            [
                'slug' => 'hookah-deluxe',
                'name' => 'قلیان — سرویس سوپر ویژه',
                'short_name' => 'قلیان سوپر ویژه',
                'latin_name' => 'SUPER DELUXE SERVICE',
                'subtitle' => 'منوی قلیان',
                'description' => 'همان شانزده طعم، همراه با پذیرایی کامل صاحبقرانیه.',
                'kind' => CategoryKind::Hookah,
                'layout' => CategoryLayout::List,
                'glyph' => 'crown',
                'price_note' => 'قیمت هر سرویس',
                'sort_order' => 4,
                'card_order' => null, // reachable through the قلیان card
            ],
        ];

        foreach ($categories as $attributes) {
            // firstOrCreate, not updateOrCreate: every field above is editable in
            // the panel — name, description, glyph, layout, the landing-card copy
            // and its order — so updating would hand the owner's wording back to
            // the printed-menu default on any re-seed. Same reasoning as
            // AdminUserSeeder and ProductSeeder.
            Category::firstOrCreate(['slug' => $attributes['slug']], $attributes);
        }
    }
}
