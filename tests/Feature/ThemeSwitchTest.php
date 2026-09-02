<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Dark is the house theme; light is an opt-in remembered in localStorage. The
 * switch has to be on every page and the palette has to be decided in <head>,
 * before the first paint, or the visitor sees the wrong theme flash.
 */
class ThemeSwitchTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<int, string> */
    public static function pages(): array
    {
        return ['/', '/menu'];
    }

    public function test_every_page_carries_the_theme_switch(): void
    {
        foreach (self::pages() as $page) {
            $this->get($page)
                ->assertOk()
                ->assertSee('id="theme-toggle"', false)
                ->assertSee('تغییر پوسته روشن و تاریک', false);
        }
    }

    public function test_the_dark_theme_is_the_default(): void
    {
        foreach (self::pages() as $page) {
            $response = $this->get($page)->assertOk();

            $response->assertSee("var theme = 'light';", false);
            $response->assertSee('aria-pressed="false"', false);
        }
    }

    public function test_the_theme_is_resolved_before_the_deferred_bundle_runs(): void
    {
        $content = $this->get('/')->assertOk()->getContent();

        $inlineScript = strpos($content, "localStorage.getItem('sg-theme')");
        $bundle = strpos($content, 'resources/js/app.js') ?: strpos($content, '/build/assets/app-');

        $this->assertNotFalse($inlineScript);
        $this->assertNotFalse($bundle);
        $this->assertLessThan($bundle, $inlineScript, 'The no-flash script must come before the bundle.');
    }
}
