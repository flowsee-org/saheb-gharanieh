<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * The Balad and Neshan map links shown in the footer.
     *
     * A migration rather than a line in SettingSeeder, because deploy never
     * seeds — .github/workflows/ci-cd.yml runs migrate --force and nothing
     * else. A setting that only exists in the seeder would be absent in
     * production, the admin form would have no box to type the address into,
     * and the footer would silently show no links. (They are in the seeder too,
     * for a fresh local install.)
     *
     * Empty on purpose: the owner pastes the two share links in the panel, and
     * the footer hides a logo whose link is not set yet.
     */
    private const SETTINGS = [
        ['key' => 'balad_url', 'group' => 'navigation', 'label' => 'ادرس بلد'],
        ['key' => 'neshan_url', 'group' => 'navigation', 'label' => 'ادرس نشان'],
    ];

    public function up(): void
    {
        foreach (self::SETTINGS as $setting) {
            // firstOrCreate, not updateOrCreate: if this ever runs twice it must
            // not wipe an address the owner has already saved.
            Setting::query()->firstOrCreate(
                ['key' => $setting['key']],
                $setting + ['type' => 'url', 'value' => null]
            );
        }
    }

    public function down(): void
    {
        Setting::query()->whereIn('key', array_column(self::SETTINGS, 'key'))->delete();
    }
};
