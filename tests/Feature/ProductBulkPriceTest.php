<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductBulkPriceTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_percentage_raise_scales_every_priced_item_in_a_category(): void
    {
        $category = Category::factory()->create(['name' => 'نوشیدنی‌های گرم']);

        Product::factory()->for($category)->create(['name' => 'اسپرسو', 'price' => 50_000]);
        Product::factory()->for($category)->create(['name' => 'لاته', 'price' => 40_000]);
        Product::factory()->for($category)->create(['name' => 'بدون قیمت', 'price' => null]);

        // The scope excludes everything outside the chosen category.
        $other = Category::factory()->create(['name' => 'قلیان']);
        $outside = Product::factory()->for($other)->create(['name' => 'دوسیب', 'price' => 100_000]);

        $this->actingAs(AdminUser::factory()->create(), 'admin')
            ->post(route('admin.products.bulk-price'), [
                'category_id' => $category->id,
                'mode' => 'percentage',
                'amount' => '۱۰',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame(55_000, $category->products()->where('name', 'اسپرسو')->value('price'));
        $this->assertSame(44_000, $category->products()->where('name', 'لاته')->value('price'));

        // An unpriced item has nothing to scale, and stays unpriced; the other
        // category is untouched.
        $this->assertNull($category->products()->where('name', 'بدون قیمت')->value('price'));
        $this->assertSame(100_000, $outside->refresh()->price);
    }

    public function test_a_fixed_raise_adds_the_amount_to_every_item_in_the_whole_menu(): void
    {
        $hot = Category::factory()->create(['name' => 'نوشیدنی‌های گرم']);
        $cold = Category::factory()->create(['name' => 'نوشیدنی‌های سرد']);

        Product::factory()->for($hot)->create(['name' => 'اسپرسو', 'price' => 50_000]);
        Product::factory()->for($cold)->create(['name' => 'دمنوش', 'price' => 30_000]);

        // No category_id means "every item in the menu".
        $this->actingAs(AdminUser::factory()->create(), 'admin')
            ->post(route('admin.products.bulk-price'), [
                'category_id' => '',
                'mode' => 'fixed',
                'amount' => '۵٬۰۰۰',
            ])
            ->assertRedirect();

        $this->assertSame(55_000, $hot->products()->value('price'));
        $this->assertSame(35_000, $cold->products()->value('price'));
    }

    public function test_the_success_message_counts_only_items_whose_price_changed(): void
    {
        $category = Category::factory()->create(['name' => 'قلیان']);

        Product::factory()->for($category)->create(['price' => 100_000]);
        Product::factory()->for($category)->create(['price' => 100_000]);
        Product::factory()->for($category)->create(['price' => null]); // skipped

        $this->actingAs(AdminUser::factory()->create(), 'admin')
            ->post(route('admin.products.bulk-price'), [
                'category_id' => $category->id,
                'mode' => 'percentage',
                'amount' => '۵',
            ])
            ->assertSessionHas('status', fn (string $status) => str_contains($status, '۲ مورد'));
    }

    public function test_the_amount_field_rejects_junk(): void
    {
        $this->actingAs(AdminUser::factory()->create(), 'admin')
            ->post(route('admin.products.bulk-price'), [
                'category_id' => '',
                'mode' => 'percentage',
                'amount' => 'abc',
            ])
            ->assertSessionHasErrors('amount');
    }
}