<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Every item is transcribed from the printed menu (photo_5767233449319141139_y.jpg).
 * Prices are intentionally left NULL — the printed menu leaves "قیمت :" blank,
 * so the site shows a styled placeholder until prices are entered in the admin panel.
 *
 * Rows are created, never updated: see the note in seed().
 */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        /** Panel 2 — منوی نوشیدنی‌های گرم (15 items) */
        $hotDrinks = [
            ['اسپرسو', 'Espresso'],
            ['دبل اسپرسو', 'Double Espresso'],
            ['آمریکانو', 'Americano'],
            ['کاپوچینو', 'Cappuccino'],
            ['لاته', 'Latte'],
            ['کارامل لاته', 'Caramel Latte'],
            ['موکا', 'Mocha'],
            ['هات چاکلت بلژیکی', 'Belgian Hot Chocolate'],
            ['وایت چاکلت', 'White Chocolate'],
            ['ماسالا', 'Masala'],
            ['چای زعفران', 'Saffron Tea'],
            ['چای مراکشی نعناع', 'Moroccan Mint Tea'],
            ['دمنوش گل گاوزبان و لیمو عمانی', 'Borage & Omani Lemon Infusion'],
            ['دمنوش به لیمو و بهارنارنج', 'Lemon Verbena & Orange Blossom Infusion'],
            ['شیر زعفران و دارچین', 'Saffron & Cinnamon Milk'],
        ];

        /** Panel 1 — منوی نوشیدنی‌های سرد (18 items) */
        $coldDrinks = [
            ['موهیتو کلاسیک', 'Classic Mojito'],
            ['موهیتو توت فرنگی', 'Strawberry Mojito'],
            ['لیموناد نعناع', 'Mint Lemonade'],
            ['بلو لاگون', 'Blue Lagoon'],
            ['شربت بهار نارنج', 'Orange Blossom Sherbet'],
            ['شربت زعفران', 'Saffron Sherbet'],
            ['شربت آلبالو طبیعی', 'Natural Sour Cherry Sherbet'],
            ['شربت خاکشیر', 'Khakshir Sherbet'],
            ['شربت تخم شربتی', 'Basil Seed Sherbet'],
            ['میوه‌ی بلوبینو', 'Blue Bino'],
            ['آب پرتقال طبیعی', 'Fresh Orange Juice'],
            ['آب هندوانه', 'Watermelon Juice'],
            ['آب طالبی', 'Cantaloupe Juice'],
            ['آیس آمریکانو', 'Iced Americano'],
            ['آیس لاته', 'Iced Latte'],
            ['فراپه کارامل', 'Caramel Frappe'],
            ['فراپه موکا', 'Mocha Frappe'],
            ['میلک شیک شکلات', 'Chocolate Milkshake'],
        ];

        /** Panels 3 & 4 — طعم‌های قلیان (16 flavours, shared by both services) */
        $hookahFlavours = [
            ['دوسیب', 'Double Apple', 'apple'],
            ['دوسیب آلبالو', 'Double Apple & Sour Cherry', 'cherry'],
            ['سیب یخ', 'Ice Apple', 'apple-ice'],
            ['بلوبری', 'Blueberry', 'berry'],
            ['آدامس دارچین', 'Cinnamon Gum', 'cinnamon'],
            ['لیمو نعنا', 'Lemon Mint', 'lemon'],
            ['بستنی سنتی', 'Traditional Ice Cream', 'ice-cream'],
            ['تی تاپ', 'Tea Top', 'lollipop'],
            ['شب‌های مسکو', 'Moscow Nights', 'moon'],
            ['لاو', 'Love', 'heart'],
            ['پرتقال نعناع', 'Orange Mint', 'orange'],
            ['پرتقال خامه', 'Orange Cream', 'cream'],
            ['عصرهای صاحبقرانیه', 'Saheb Gharaniyeh Evenings', 'palace'],
            ['طالبی', 'Cantaloupe', 'melon'],
            ['هندونه یخ', 'Ice Watermelon', 'watermelon'],
            ['انگور یخ', 'Ice Grape', 'grape'],
        ];

        $this->seed('hot-drinks', $hotDrinks);
        $this->seed('cold-drinks', $coldDrinks);
        $this->seed('hookah-normal', $hookahFlavours);
        $this->seed('hookah-deluxe', $hookahFlavours);
    }

    /**
     * @param  array<int, array{0: string, 1: ?string, 2?: string}>  $items
     */
    private function seed(string $categorySlug, array $items): void
    {
        $category = Category::where('slug', $categorySlug)->first();

        if (! $category) {
            return;
        }

        foreach ($items as $index => $item) {
            // firstOrCreate, not updateOrCreate — same reasoning as
            // AdminUserSeeder. Every field below is one the owner edits in the
            // panel: 'price' => null was resetting every price that had been
            // entered, and sort_order was undoing the hand-sorted order. Once a
            // row exists the database is the source of truth for it; this
            // seeder only puts the printed menu in place the first time.
            //
            // The trade: fixing a transcription typo here no longer reaches an
            // installation that already has the row. Correct it in the panel.
            Product::firstOrCreate(
                ['category_id' => $category->id, 'name' => $item[0]],
                [
                    'latin_name' => $item[1] ?? null,
                    'glyph' => $item[2] ?? null,
                    'sort_order' => $index + 1,
                    'price' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
