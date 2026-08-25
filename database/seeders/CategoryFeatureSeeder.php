<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryFeature;
use Illuminate\Database\Seeder;

/**
 * "همراه با سرویس ویژه شامل" — the extras printed along the bottom of the
 * Super Deluxe hookah panel.
 */
class CategoryFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::where('slug', 'hookah-deluxe')->first();

        if (! $category) {
            return;
        }

        $features = [
            ['چای زغالی', 'teapot'],
            ['میوه فصل', 'fruit-plate'],
            ['باقلوا', 'pastry'],
            ['دستمال مرطوب', 'wipe'],
            ['زغال اضافه', 'flame'],
            ['یخ', 'ice'],
            ['فویل', 'foil'],
            ['انبر', 'tongs'],
        ];

        foreach ($features as $index => [$name, $glyph]) {
            // firstOrCreate: the extras are reorderable and switchable in the
            // panel, so an existing row is the owner's, not ours.
            CategoryFeature::firstOrCreate(
                ['category_id' => $category->id, 'name' => $name],
                ['glyph' => $glyph, 'sort_order' => $index + 1, 'is_active' => true]
            );
        }
    }
}
