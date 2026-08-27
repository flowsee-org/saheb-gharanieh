<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_the_cafe_intro(): void
    {
        Setting::put('cafe_name', 'کافه صاحبقرانیه');
        Setting::put('intro', 'میعادگاه دوستی‌های قدیمی.');

        $this->get('/')
            ->assertOk()
            ->assertSee('کافه صاحبقرانیه', false)
            ->assertSee('میعادگاه دوستی‌های قدیمی.', false);
    }

    public function test_it_shows_only_categories_promoted_to_a_landing_card(): void
    {
        $promoted = Category::factory()->onLanding(1)->create([
            'name' => 'نوشیدنی‌های گرم',
            'card_title' => 'نوشیدنی‌های گرم',
        ]);

        $notPromoted = Category::factory()->create(['name' => 'سرویس پنهان']);

        $this->get('/')
            ->assertOk()
            ->assertSee($promoted->card_title, false)
            ->assertDontSee($notPromoted->name, false);
    }

    public function test_it_hides_inactive_categories(): void
    {
        Category::factory()->onLanding(1)->create([
            'name' => 'دسته غیرفعال',
            'is_active' => false,
        ]);

        $this->get('/')->assertOk()->assertDontSee('دسته غیرفعال', false);
    }

    public function test_each_card_deep_links_into_its_menu_section(): void
    {
        $category = Category::factory()->onLanding(1)->create(['slug' => 'hot-drinks']);

        $this->get('/')
            ->assertOk()
            ->assertSee(route('menu.section', $category->slug).'#hot-drinks', false);
    }

    public function test_cards_are_ordered_by_card_order(): void
    {
        Category::factory()->onLanding(2)->create(['name' => 'دومی', 'card_title' => 'دومی']);
        Category::factory()->onLanding(1)->create(['name' => 'اولی', 'card_title' => 'اولی']);

        $this->get('/')->assertOk()->assertSeeInOrder(['اولی', 'دومی'], false);
    }

    /** The cards are the point of the page, so the about block sits under them. */
    public function test_the_about_block_comes_after_the_cards(): void
    {
        Setting::put('intro', 'میعادگاه دوستی‌های قدیمی.');
        Category::factory()->onLanding(1)->create(['card_title' => 'نوشیدنی‌های گرم']);

        $content = $this->get('/')->assertOk()->getContent();
        // The intro also fills <meta name="description">, so compare inside <main> only.
        $main = substr($content, (int) strpos($content, '<main'));

        $this->assertLessThan(
            strpos($main, 'میعادگاه دوستی‌های قدیمی.'),
            strpos($main, 'یک دسته را انتخاب کنید')
        );
    }
}
