<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CategoryFeature;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_active_sections_and_their_products(): void
    {
        $category = Category::factory()->create([
            'slug' => 'hot-drinks',
            'name' => 'نوشیدنی‌های گرم',
            'sort_order' => 1,
        ]);

        Product::factory()->for($category)->create(['name' => 'اسپرسو']);

        $this->get('/menu')
            ->assertOk()
            ->assertSee('نوشیدنی‌های گرم', false)
            ->assertSee('اسپرسو', false)
            ->assertSee('id="hot-drinks"', false);
    }

    public function test_it_hides_inactive_categories_and_products(): void
    {
        $hidden = Category::factory()->create(['name' => 'بخش غیرفعال', 'is_active' => false]);
        Product::factory()->for($hidden)->create(['name' => 'آیتم پنهان']);

        $visible = Category::factory()->create(['name' => 'بخش فعال']);
        Product::factory()->for($visible)->hidden()->create(['name' => 'نوشیدنی حذف‌شده']);

        $this->get('/menu')
            ->assertOk()
            ->assertSee('بخش فعال', false)
            ->assertDontSee('بخش غیرفعال', false)
            ->assertDontSee('آیتم پنهان', false)
            ->assertDontSee('نوشیدنی حذف‌شده', false);
    }

    public function test_sections_follow_sort_order(): void
    {
        Category::factory()->create(['name' => 'بخش دوم', 'sort_order' => 2]);
        Category::factory()->create(['name' => 'بخش اول', 'sort_order' => 1]);

        $this->get('/menu')->assertOk()->assertSeeInOrder(['بخش اول', 'بخش دوم'], false);
    }

    public function test_a_deep_link_marks_the_requested_section_as_active(): void
    {
        Category::factory()->create(['slug' => 'hookah-deluxe', 'name' => 'قلیان']);

        $this->get('/menu/hookah-deluxe')
            ->assertOk()
            ->assertViewHas('activeSection', 'hookah-deluxe')
            ->assertSee('data-initial-section="hookah-deluxe"', false);
    }

    public function test_an_unknown_section_falls_back_to_the_full_menu(): void
    {
        Category::factory()->create(['slug' => 'hot-drinks']);

        $this->get('/menu/does-not-exist')
            ->assertOk()
            ->assertViewHas('activeSection', null);
    }

    public function test_hookah_sections_show_their_service_price_and_extras(): void
    {
        $category = Category::factory()->hookah()->create([
            'slug' => 'hookah-deluxe',
            'name' => 'قلیان — سرویس سوپر ویژه',
            'price' => 185_000,
            'price_note' => 'قیمت هر سرویس',
        ]);

        Product::factory()->for($category)->create(['name' => 'دوسیب']);
        CategoryFeature::factory()->for($category)->create(['name' => 'چای زغالی']);

        $this->get('/menu')
            ->assertOk()
            ->assertSee('دوسیب', false)
            ->assertSee('قیمت هر سرویس', false)
            ->assertSee('۱۸۵٬۰۰۰ تومان', false)
            ->assertSee('چای زغالی', false);
    }

    public function test_products_without_a_price_render_the_empty_price_slot(): void
    {
        $category = Category::factory()->create();
        Product::factory()->for($category)->create(['name' => 'لاته', 'price' => null]);

        $this->get('/menu')
            ->assertOk()
            ->assertSee('price-empty', false)
            ->assertSee('تصویر به‌زودی', false);
    }
}
