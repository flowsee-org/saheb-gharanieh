<?php

namespace App\Http\Requests\Admin;

use App\Models\Setting;

/**
 * Site copy. The keys are whatever the settings table holds, so the form and
 * the rules are both built from the rows rather than hard-coded here.
 */
class SettingsRequest extends AdminRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $rules = [];

        foreach (Setting::query()->get(['key', 'type']) as $setting) {
            $rules['values.'.$setting->key] = match ($setting->type) {
                'text' => ['nullable', 'string', 'max:2000'],
                'number' => ['nullable', 'string', 'max:40'],
                'boolean' => ['nullable', 'boolean'],
                // Map share links. Validated, because these end up as the href
                // of a link in the footer and a typo there is a dead footer.
                'url' => ['nullable', 'url:http,https', 'max:400'],
                default => ['nullable', 'string', 'max:400'],
            };
        }

        return [...$rules, 'values' => ['required', 'array']];
    }

    /**
     * Only the keys that already exist as rows: `values` is validated as a whole
     * array, so without this filter a hand-crafted post could invent settings.
     *
     * @return array<string, string|null>
     */
    public function values(): array
    {
        /** @var array<string, mixed> $posted */
        $posted = $this->safe()->array('values');

        $known = Setting::query()->pluck('key')->all();

        $values = [];

        foreach (array_intersect(array_keys($posted), $known) as $key) {
            $value = $posted[$key];
            $values[$key] = is_string($value)
                ? (trim($value) === '' ? null : trim($value))
                : $value;
        }

        return $values;
    }

    /**
     * Field labels come from the settings rows, so an error names the box the
     * owner is looking at ("نشانی") instead of `values.address`.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $labels = [];

        foreach (Setting::query()->get(['key', 'label']) as $setting) {
            $labels['values.'.$setting->key] = $setting->label ?: $setting->key;
        }

        return $labels;
    }
}
