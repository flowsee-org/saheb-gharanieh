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
     * Seeded with the café's own two share links, so the footer works the moment
     * this runs; the owner can replace either one in the panel, and the footer
     * hides a logo whose link has been cleared.
     */
    private const SETTINGS = [
        ['key' => 'balad_url', 'group' => 'navigation', 'label' => 'آدرس بلد', 'value' => 'https://balad.ir/p/PA8U4WiqGnyitG'],
        ['key' => 'neshan_url', 'group' => 'navigation', 'label' => 'آدرس نشان', 'value' => 'https://nshn.ir/4e_b1xGB2B0Jq9'],
    ];

    public function up(): void
    {
        foreach (self::SETTINGS as $setting) {
            // firstOrCreate, not updateOrCreate: if this ever runs twice it must
            // not wipe an address the owner has already saved.
            Setting::query()->firstOrCreate(
                ['key' => $setting['key']],
                $setting + ['type' => 'url']
            );
        }
    }

    public function down(): void
    {
        Setting::query()->whereIn('key', array_column(self::SETTINGS, 'key'))->delete();
    }
};
